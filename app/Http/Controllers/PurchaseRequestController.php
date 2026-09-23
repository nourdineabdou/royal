<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('purchases.requests.view');
        $query = PurchaseRequest::with('items', 'orders.supplier')->withCount('orders');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(20);

        return view('purchases.requests.index', compact('requests'));
    }

    public function create()
    {
        $this->perm('purchases.requests.create');
        $products = Product::with('unit')->orderBy('name')->get();

        return view('purchases.requests.create', compact('products'));
    }

    public function store(Request $request)
    {
        $this->perm('purchases.requests.create');
        $validated = $request->validate([
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.notes'        => 'nullable|string',
        ]);

        $purchaseRequest = PurchaseRequest::create([
            'reference'    => 'DA-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'requested_by' => auth()->id(),
            'status'       => 'pending',
            'notes'        => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            PurchaseRequestItem::create([
                'purchase_request_id' => $purchaseRequest->id,
                'product_id'          => $item['product_id'],
                'quantity'            => $item['quantity'],
                'notes'               => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('purchases.requests.show', $purchaseRequest)->with('success', 'Demande d\'achat créée avec succès.');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $this->perm('purchases.requests.view');
        $purchaseRequest->load(
            'items.product.unit', 'requestedBy', 'orders.supplier', 'orders.items',
            'quotes.supplier', 'quotes.items'
        );
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.requests.show', compact('purchaseRequest', 'suppliers'));
    }

    /**
     * Enregistre un devis reçu d'un fournisseur consulté (prix par ligne de la demande) —
     * pour comparer plusieurs fournisseurs côte à côte avant de créer le(s) BC.
     */
    public function storeQuote(Request $request, PurchaseRequest $purchaseRequest)
    {
        $this->perm('purchases.requests.create');
        $validated = $request->validate([
            'supplier_id'                    => 'required|exists:suppliers,id',
            'reference'                      => 'nullable|string|max:255',
            'notes'                          => 'nullable|string',
            'items'                          => 'required|array|min:1',
            'items.*.purchase_request_item_id' => 'required|exists:purchase_request_items,id',
            'items.*.unit_price'             => 'nullable|numeric|min:0',
        ]);

        $priced = array_filter($validated['items'], fn ($i) => isset($i['unit_price']) && $i['unit_price'] !== '' && $i['unit_price'] !== null);
        if (empty($priced)) {
            return back()->withErrors(['items' => 'Renseignez au moins un prix pour ce devis.'])->withInput();
        }

        $quote = \App\Models\SupplierQuote::create([
            'purchase_request_id' => $purchaseRequest->id,
            'supplier_id'         => $validated['supplier_id'],
            'reference'           => $validated['reference'] ?? null,
            'notes'               => $validated['notes'] ?? null,
            'created_by'          => auth()->id(),
        ]);

        foreach ($priced as $item) {
            \App\Models\SupplierQuoteItem::create([
                'supplier_quote_id'         => $quote->id,
                'purchase_request_item_id'  => $item['purchase_request_item_id'],
                'unit_price'                => $item['unit_price'],
            ]);
        }

        return back()->with('success', 'Devis enregistré pour comparaison.');
    }

    public function destroyQuote(\App\Models\SupplierQuote $quote)
    {
        $this->perm('purchases.requests.create');
        $purchaseRequest = $quote->purchaseRequest;
        $quote->delete();

        return redirect()->route('purchases.requests.show', $purchaseRequest)->with('success', 'Devis supprimé.');
    }

    /**
     * Version imprimable de la demande — l'acheteur l'emporte pour la remettre à un fournisseur
     * lors de la consultation (pas de prix ni de fournisseur dessus, c'est juste le besoin exprimé).
     */
    public function print(PurchaseRequest $purchaseRequest)
    {
        $this->perm('purchases.requests.view');
        $purchaseRequest->load('items.product.unit', 'requestedBy');
        $company = config('app.company');

        return view('purchases.requests.print', compact('purchaseRequest', 'company'));
    }

    /**
     * Crée un Bon de Commande (pour UN fournisseur choisi) à partir d'une partie (ou de la totalité)
     * des lignes restantes de cette demande d'achat. Peut être appelé plusieurs fois pour scinder
     * la demande entre plusieurs fournisseurs (BC1→F1, BC2→F2, BC3→F3...) — chaque BC garde
     * purchase_request_id pointant vers cette même demande, pour tracer que tous répondent au même besoin.
     */
    public function storeOrder(Request $request, PurchaseRequest $purchaseRequest)
    {
        $this->perm('purchases.requests.create');
        $validated = $request->validate([
            'supplier_id'                => 'nullable|exists:suppliers,id',
            'items'                      => 'required|array|min:1',
            'items.*.request_item_id'    => 'required|exists:purchase_request_items,id',
            'items.*.packaging_id'       => 'nullable|exists:packagings,id',
            'items.*.quantity'           => 'required|numeric|min:0.01',
            'items.*.unit_price'         => 'nullable|numeric|min:0',
        ]);

        $requestItems = $purchaseRequest->items()->get()->keyBy('id');

        // Valider que chaque ligne appartient bien à cette demande et ne dépasse pas le restant
        $baseQtyByLine = [];
        foreach ($validated['items'] as $item) {
            $reqItem = $requestItems[$item['request_item_id']] ?? null;
            if (!$reqItem) {
                return back()->withErrors(['items' => 'Ligne invalide pour cette demande.'])->withInput();
            }

            $baseQty = (float) $item['quantity'];
            if (!empty($item['packaging_id'])) {
                $pkgQty = ProductPackaging::where('product_id', $reqItem->product_id)
                    ->where('packaging_id', $item['packaging_id'])
                    ->value('quantity');
                if ($pkgQty) {
                    $baseQty = $baseQty * (float) $pkgQty;
                }
            }

            if ($baseQty > $reqItem->remaining_quantity + 0.001) {
                return back()->withErrors([
                    'items' => "La quantité pour {$reqItem->product->name} dépasse le restant à commander ({$reqItem->remaining_quantity})."
                ])->withInput();
            }

            $baseQtyByLine[$item['request_item_id']] = $baseQty;
        }

        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $price = isset($item['unit_price']) && $item['unit_price'] !== '' ? (float) $item['unit_price'] : null;
            $totalAmount += ($price ?? 0) * (float) $item['quantity'];
        }

        DB::transaction(function () use ($validated, $purchaseRequest, $requestItems, $baseQtyByLine, $totalAmount) {
            $order = PurchaseOrder::create([
                'supplier_id'         => $validated['supplier_id'] ?? null,
                'purchase_request_id' => $purchaseRequest->id,
                'reference'           => 'CMD-' . strtoupper(uniqid()),
                'total_amount'        => $totalAmount,
                'paid_amount'         => 0,
                'remaining_amount'    => $totalAmount,
                'payment_status'      => 'unpaid',
                'status'              => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $reqItem = $requestItems[$item['request_item_id']];
                $price = isset($item['unit_price']) && $item['unit_price'] !== '' ? (float) $item['unit_price'] : null;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id'        => $reqItem->product_id,
                    'packaging_id'      => $item['packaging_id'] ?? null,
                    'quantity'          => $item['quantity'],
                    'price'             => $price,
                    'total'             => $price !== null ? $price * (float) $item['quantity'] : null,
                ]);

                $reqItem->increment('ordered_quantity', $baseQtyByLine[$item['request_item_id']]);
            }
        });

        $purchaseRequest->refreshStatus();

        return redirect()->route('purchases.requests.show', $purchaseRequest)->with('success', 'Bon de commande créé pour cette demande.');
    }
}
