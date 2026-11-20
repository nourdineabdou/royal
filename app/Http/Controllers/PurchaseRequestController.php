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
        $filters = $request->validate([
            'status' => 'nullable|in:' . implode(',', array_keys(PurchaseRequest::STATUSES)),
            'from'   => 'nullable|date',
            'to'     => 'nullable|date|after_or_equal:from',
            'q'      => 'nullable|string|max:50',
        ], ['to.after_or_equal' => 'La date de fin doit être après la date de début.']);

        // Période et n° de DA ; le statut s'applique ensuite (les pastilles comptent chaque statut sur la période)
        $base = PurchaseRequest::query()
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($filters['q'] ?? null, fn ($q, $s) => $q->where('reference', 'like', '%' . trim($s) . '%'));

        $counts = (clone $base)->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        $requests = (clone $base)
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->with('items', 'orders.supplier', 'requestedBy')->withCount('orders')
            ->latest()->paginate(20)->withQueryString();

        return view('purchases.requests.index', compact('requests', 'filters', 'counts'));
    }

    public function create()
    {
        $this->perm('purchases.requests.create');
        $products = Product::with(['unit', 'productPackagings.packaging'])->orderBy('name')->get();
        [$catalog, $refName] = $this->requestCatalog($products);

        // Pré-remplissage depuis la page Ruptures : prefill[product_id] = quantité en unité de base
        $prefill = collect((array) request('prefill', []))
            ->filter(fn ($q, $id) => is_numeric($id) && is_numeric($q) && $q > 0)
            ->map(fn ($q) => round((float) $q, 2))->all();

        return view('purchases.requests.create', compact('products', 'prefill', 'catalog', 'refName'));
    }

    /** Pour chaque produit : emballages (principal en premier), stock Catering et seuil — aide à la saisie. */
    private function requestCatalog($products): array
    {
        $ref = app(\App\Services\StockAlertService::class)->referenceStock();
        $stockQty = \App\Models\StockItem::when($ref, fn ($q) => $q->where('stock_id', $ref->id))
            ->selectRaw('product_id, SUM(quantity) as qty')->groupBy('product_id')->pluck('qty', 'product_id');
        $catalog = $products->map(fn ($p) => [
            'id'    => $p->id,
            'name'  => $p->name,
            'unit'  => $p->unit->symbol ?? '',
            'stock' => (float) ($stockQty[$p->id] ?? 0),
            'min'   => $p->effectiveMinStock(),
            'packs' => $p->productPackagings->filter(fn ($pp) => (float) $pp->quantity > 0)
                ->sortByDesc(fn ($pp) => (float) $pp->quantity)
                ->map(fn ($pp) => ['name' => $pp->packaging->name ?? 'Emballage', 'qty' => (float) $pp->quantity])->values(),
        ])->values();

        return [$catalog, $ref->name ?? 'stock'];
    }

    /** Brouillon de la DA automatique, remis à jour à l'ouverture. */
    public function auto(\App\Services\AutoPurchaseRequestService $auto, \App\Services\StockAlertService $alerts)
    {
        $this->perm('purchases.requests.create');

        $draft = $auto->sync();
        $draft?->load('items.product.unit', 'items.product.productPackagings.packaging');
        $info = $alerts->alerts()->keyBy(fn ($a) => $a['product']->id);
        $ref = $alerts->referenceStock();
        $covered = $alerts->alerts()->filter(fn ($a) => !$a['needs_order'] && $a['incoming'] > 0)->values();
        [$catalog] = $this->requestCatalog(Product::with(['unit', 'productPackagings.packaging'])->orderBy('name')->get());

        // Pourquoi un produit sous le seuil n'est pas reproposé : la DA ou le BC qui le couvre déjà
        $sources = $alerts->incomingSources();
        $coveringDocs = $covered->flatMap(fn ($a) => array_values($sources[$a['product']->id] ?? []))
            ->groupBy(fn ($s) => $s['kind'] . $s['id'])
            ->map(fn ($g) => $g->first() + ['count' => $g->count()])->values();

        return view('purchases.requests.auto', compact('draft', 'info', 'ref', 'covered', 'catalog', 'sources', 'coveringDocs'));
    }

    public function saveAuto(Request $request, \App\Services\AutoPurchaseRequestService $auto)
    {
        $this->perm('purchases.requests.create');

        $data = $request->validate([
            'action'         => 'required|in:save,send,add',
            'notes'          => 'nullable|string|max:1000',
            'quantities'     => 'array',
            'quantities.*'   => 'nullable|numeric|min:0|max:99999999', // unité de base
            'remove'         => 'array',
            'add_product_id' => 'required_if:action,add|nullable|exists:products,id',
            'add_quantity'   => 'required_if:action,add|nullable|numeric|gt:0|max:99999999', // unité de base
        ], [
            'add_product_id.required_if' => 'Choisissez le produit à ajouter.',
            'add_quantity.required_if'   => 'Tapez la quantité du produit à ajouter.',
            'add_quantity.gt'            => 'La quantité à ajouter doit être supérieure à 0.',
        ]);

        // Ajouter un produit : le brouillon est créé s'il n'existe pas encore
        $draft = $data['action'] === 'add' ? $auto->openDraft() : $auto->draft();
        abort_unless($draft, 404, 'Aucune demande automatique en brouillon.');

        DB::transaction(function () use ($draft, $data) {
            foreach ($draft->items as $item) {
                $qty = (float) ($data['quantities'][$item->id] ?? $item->quantity);
                if (!empty($data['remove'][$item->id]) || $qty <= 0) {
                    $item->delete();
                } else {
                    $item->update(['quantity' => round($qty, 2)]);
                }
            }
            $draft->update(['notes' => $data['notes'] ?? $draft->notes]);

            if ($data['action'] === 'add') {
                $qty  = round((float) $data['add_quantity'], 2);
                $line = $draft->items()->where('product_id', $data['add_product_id'])->first();
                if ($line) {
                    $line->update(['quantity' => round((float) $line->quantity + $qty, 2)]);
                } else {
                    $draft->items()->create([
                        'product_id'     => $data['add_product_id'],
                        'quantity'       => $qty,
                        'notes'          => 'Ajouté par ' . auth()->user()->name,
                        'added_manually' => true,
                    ]);
                }
            }

            if ($data['action'] === 'send') {
                if (!$draft->items()->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['quantities' => 'La demande ne contient plus aucun produit.']);
                }
                $draft->update(['requested_by' => auth()->id()] + PurchaseRequest::submittedStatusFor(auth()->user()));
            }
        });

        if ($data['action'] === 'send') {
            $draft->refresh();
            return redirect()->route('purchases.requests.show', $draft)
                ->with('success', "Demande d'achat {$draft->reference} envoyée : " . PurchaseRequest::nextStepText($draft->status));
        }

        return redirect()->route('purchases.requests.auto')->with('success', $data['action'] === 'add'
            ? 'Produit ajouté à la demande (brouillon ' . $draft->reference . ').'
            : 'Brouillon enregistré.');
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

        // Statut de départ selon qui la crée (magasinier / acheteur / responsable des achats)
        $purchaseRequest = PurchaseRequest::create([
            'reference'    => 'DA-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'requested_by' => auth()->id(),
            'notes'        => $validated['notes'] ?? null,
        ] + PurchaseRequest::submittedStatusFor(auth()->user()));

        foreach ($validated['items'] as $item) {
            PurchaseRequestItem::create([
                'purchase_request_id' => $purchaseRequest->id,
                'product_id'          => $item['product_id'],
                'quantity'            => $item['quantity'],
                'notes'               => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('purchases.requests.show', $purchaseRequest)
            ->with('success', "Demande d'achat {$purchaseRequest->reference} créée : " . PurchaseRequest::nextStepText($purchaseRequest->status));
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $this->perm('purchases.requests.view');
        $purchaseRequest->load(
            'items.product.unit', 'items.product.productPackagings.packaging', 'requestedBy', 'orders.supplier', 'orders.items',
            'quotes.supplier', 'quotes.items', 'achatApprover', 'rhApprover', 'rejecter'
        );
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.requests.show', compact('purchaseRequest', 'suppliers'));
    }

    /** ① Le responsable achat approuve une DA venue du magasinier. */
    public function approve(PurchaseRequest $purchaseRequest)
    {
        $this->perm('purchases.requests.approve');
        abort_unless($purchaseRequest->status === 'pending', 422, 'Cette demande n\'est pas en attente d\'approbation.');

        $purchaseRequest->update(['status' => 'to_validate', 'achat_approved_by' => auth()->id(), 'achat_approved_at' => now()]);

        return back()->with('success', "Demande {$purchaseRequest->reference} approuvée : elle part à la validation du responsable des achats.");
    }

    /** ② Le responsable des achats valide : il peut créer les bons de commande. */
    public function validateRequest(PurchaseRequest $purchaseRequest)
    {
        $this->perm('purchases.requests.validate');
        abort_unless($purchaseRequest->status === 'to_validate', 422, 'Cette demande n\'est pas en attente de validation.');

        $purchaseRequest->update(['status' => 'approved', 'rh_approved_by' => auth()->id(), 'rh_approved_at' => now()]);

        return back()->with('success', "Demande {$purchaseRequest->reference} validée : le bon de commande peut être fait.");
    }

    /** Refus par le responsable achat ou la RH, avec un motif. */
    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();
        $allowed = ($purchaseRequest->status === 'pending' && $user?->can('purchases.requests.approve'))
                || ($purchaseRequest->status === 'to_validate' && $user?->can('purchases.requests.validate'));
        abort_unless($allowed, 403, 'Vous ne pouvez pas refuser cette demande.');

        $data = $request->validate(['rejection_reason' => 'required|string|max:1000'], ['rejection_reason.required' => 'Indiquez le motif du refus.']);
        $purchaseRequest->update([
            'status' => 'rejected', 'rejected_by' => $user->id, 'rejected_at' => now(), 'rejection_reason' => $data['rejection_reason'],
        ]);

        return back()->with('success', "Demande {$purchaseRequest->reference} refusée.");
    }

    /**
     * Enregistre un devis reçu d'un fournisseur consulté (prix par ligne de la demande) —
     * pour comparer plusieurs fournisseurs côte à côte avant de créer le(s) BC.
     */
    public function storeQuote(Request $request, PurchaseRequest $purchaseRequest)
    {
        $this->quotePerm();
        $validated = $request->validate([
            'supplier_id'                    => 'required|exists:suppliers,id',
            'reference'                      => 'nullable|string|max:255',
            'notes'                          => 'nullable|string',
            'items'                          => 'required|array|min:1',
            'items.*.purchase_request_item_id' => 'required|exists:purchase_request_items,id',
            'items.*.packaging_id'           => 'nullable|exists:packagings,id', // le prix est donné pour cet emballage
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
                'packaging_id'              => $item['packaging_id'] ?? null,
                'unit_price'                => $item['unit_price'],
            ]);
        }

        return back()->with('success', 'Devis enregistré pour comparaison.');
    }

    /** Devis : l'acheteur les ramène, le responsable des achats peut aussi les saisir. */
    private function quotePerm(): void
    {
        abort_unless(auth()->user()?->canAny(['purchases.quotes.manage', 'purchases.orders.create']), 403, 'Action non autorisée.');
    }

    public function destroyQuote(\App\Models\SupplierQuote $quote)
    {
        $this->quotePerm();
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
        $purchaseRequest->load('items.product.unit', 'items.product.productPackagings.packaging', 'requestedBy');
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
        // Seul le responsable des achats commande, et seulement après sa validation de la DA
        $this->perm('purchases.orders.create');
        if (!$purchaseRequest->canBeOrdered()) {
            return back()->withErrors(['items' => 'Cette demande doit d\'abord être validée par le responsable des achats avant de commander.']);
        }
        $validated = $request->validate([
            'supplier_id'                => 'nullable|exists:suppliers,id',
            'items'                      => 'required|array|min:1',
            'items.*.request_item_id'    => 'required|exists:purchase_request_items,id',
            'items.*.packaging_id'       => 'nullable|exists:packagings,id',
            'items.*.quantity'           => 'required|numeric|min:0.01',
            'items.*.unit_price'         => 'nullable|numeric|min:0',
            // Devis du fournisseur en photo / PDF, joint directement au BC (facultatif)
            'devis_files'                => 'nullable|array|max:10',
            'devis_files.*'              => \App\Http\Controllers\PurchaseOrderDocumentController::FILE_RULES,
        ], [
            'devis_files.*.mimes' => 'Devis : formats acceptés photo (JPG, PNG, HEIC) ou PDF.',
            'devis_files.*.max'   => 'Devis : un fichier dépasse 15 Mo.',
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

        $order = DB::transaction(function () use ($validated, $purchaseRequest, $requestItems, $baseQtyByLine, $totalAmount) {
            $order = PurchaseOrder::create([
                'supplier_id'         => $validated['supplier_id'] ?? null,
                'purchase_request_id' => $purchaseRequest->id,
                'reference'           => 'CMD-' . strtoupper(uniqid()),
                'total_amount'        => $totalAmount,
                'paid_amount'         => 0,
                'remaining_amount'    => $totalAmount,
                'payment_status'      => 'unpaid',
                // Créé par le responsable des achats : déjà confirmé. Créé par l'acheteur : à confirmer.
                'status'              => auth()->user()->can('purchases.orders.confirm') ? 'ordered' : 'pending',
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

            return $order;
        });

        $purchaseRequest->refreshStatus();
        $files = \App\Http\Controllers\PurchaseOrderDocumentController::storeFiles($order, $request->file('devis_files', []), 'devis');

        return redirect()->route('purchases.requests.show', $purchaseRequest)
            ->with('success', "Bon de commande {$order->reference} créé" . ($files ? " avec {$files} devis joint(s)" : '')
                . ($order->status === 'ordered'
                    ? ' et confirmé : il part au magasinier pour la réception.'
                    : ' : en attente de la confirmation du responsable des achats.'));
    }
}
