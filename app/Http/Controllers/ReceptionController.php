<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Services\GoodsReceiptService;
use App\Support\ScanCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Réceptions fournisseurs (BL) — l'entrée normale en stock, comme l'opération « Réceptions » d'Odoo.
 *  - depuis un bon de commande confirmé (quantités attendues, reliquats, surplus signalé) ;
 *  - ou en réception directe (achat au marché, livraison sans BC) : le BC est créé automatiquement.
 * Les articles sont scannés (caisse, bouteille…) ou saisis (pesée du vrac : viande, poisson, légumes).
 */
class ReceptionController extends Controller
{
    public function __construct(private GoodsReceiptService $receipts)
    {
    }

    public function index(Request $request)
    {
        $this->perm('purchases.orders.receipt');

        $filters = $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
            'type' => 'nullable|in:' . implode(',', array_keys(PurchaseOrder::ORIGINS)),
        ], ['to.after_or_equal' => 'La date de fin doit être après la date de début.']);

        $toReceive = PurchaseOrder::with(['supplier', 'items.product'])
            ->whereIn('status', ['ordered', 'partial'])
            ->latest()->get();

        // BC créés mais pas encore confirmés : affichés pour expliquer pourquoi on ne peut pas encore les réceptionner
        $toConfirm = PurchaseOrder::with('supplier')->where('status', 'pending')->latest()->get();

        // Historique filtré : période (date de réception) et origine (DA, directe…)
        $filtered = GoodsReceipt::query()
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->whereDate('received_at', '>=', $d))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->whereDate('received_at', '<=', $d));

        // Nombre de réceptions par origine sur la période (pour les pastilles du filtre)
        $counts = collect(PurchaseOrder::ORIGINS)->map(fn ($label, $key) =>
            (clone $filtered)->whereHas('purchaseOrder', fn ($q) => $q->ofOrigin($key))->count());

        $receipts = (clone $filtered)
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->whereHas('purchaseOrder', fn ($o) => $o->ofOrigin($t)))
            ->with(['purchaseOrder.supplier', 'purchaseOrder.purchaseRequest', 'stock', 'receivedBy.roles'])
            ->withCount('items')
            ->latest('received_at')->latest('id')
            ->paginate(20)->withQueryString();

        // Pour les réceptions issues d'une DA : demandé / commandé / reçu, pour voir les écarts
        $comparisons = $receipts->getCollection()
            ->map(fn ($r) => $r->purchaseOrder?->purchaseRequest)->filter()->unique('id')
            ->mapWithKeys(fn ($da) => [$da->id => $da->receptionComparison()]);

        return view('purchases.receptions.index', compact('toReceive', 'toConfirm', 'receipts', 'filters', 'counts', 'comparisons'));
    }

    public function createFromOrder(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.receipt');

        if (!in_array($order->status, ['ordered', 'partial'])) {
            return redirect()->route('purchases.orders.show', $order)
                ->with('error', $order->status === 'pending'
                    ? 'Confirmez d\'abord la commande avant de la réceptionner.'
                    : 'Cette commande ne peut plus être réceptionnée.');
        }

        $order->load(['supplier', 'items.product.unit', 'items.packaging']);

        // Attendu par produit, en unité de base (une commande peut avoir 2 lignes du même produit).
        $expected = $order->items->groupBy('product_id')->map(function ($lines, $productId) {
            $ordered = $received = 0.0;
            $labels = [];
            foreach ($lines as $line) {
                $f = $this->receipts->lineFactor($line);
                $ordered  += (float) $line->quantity * $f;
                $received += $line->received_quantity * $f;
                $labels[]  = $this->fmt($line->quantity) . ' ' . ($line->packaging->name ?? ($line->product->unit->symbol ?? ''));
            }

            // Unité dans laquelle la ligne a été commandée (ex : « Carton de 12 ») : c'est la case
            // « Reçu » principale que le magasinier remplit, comme sur le BL papier.
            $first = $lines->first();
            $primaryPp = $first->packaging_id
                ? ProductPackaging::where('product_id', $productId)->where('packaging_id', $first->packaging_id)->value('id')
                : null;

            return [
                'product_id' => (int) $productId,
                'ordered'    => round($ordered, 2),
                'received'   => round($received, 2),
                'label'      => implode(' + ', $labels),
                'primary_pp' => $primaryPp,
            ];
        })->values();

        return view('purchases.receptions.form', $this->formData() + [
            'order'    => $order,
            'expected' => $expected,
        ]);
    }

    public function createDirect()
    {
        $this->perm('purchases.orders.receipt');

        return view('purchases.receptions.form', $this->formData() + [
            'order'    => null,
            'expected' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $this->perm('purchases.orders.receipt');

        $data = $request->validate([
            'purchase_order_id'                        => 'nullable|exists:purchase_orders,id',
            'supplier_id'                              => 'required|exists:suppliers,id',
            'stock_id'                                 => 'required|exists:stocks,id',
            'supplier_delivery_note'                   => 'nullable|string|max:100',
            'notes'                                    => 'nullable|string|max:500',
            'products'                                 => 'required|array|min:1',
            'products.*.product_id'                    => 'required|integer|exists:products,id',
            'products.*.levels'                        => 'nullable|array',
            'products.*.levels.*.product_packaging_id' => 'required|integer',
            'products.*.levels.*.count'                => 'required|numeric|min:0|max:1000000',
            'products.*.loose'                         => 'nullable|numeric|min:0|max:99999999',
            'products.*.amount'                        => 'nullable|numeric|min:0|max:99999999',
        ], [
            'supplier_id.required' => 'Choisissez le fournisseur qui a livré.',
            'stock_id.required'    => 'Choisissez le stock de destination.',
            'products.required'    => 'Aucun article reçu.',
        ]);

        // Quantités par produit en unité de base — coefficients relus en base, jamais ceux du navigateur.
        $perProduct = [];
        $amounts = [];
        foreach ($data['products'] as $row) {
            $pid = (int) $row['product_id'];
            $base = 0.0;
            $parts = [];
            foreach ($row['levels'] ?? [] as $lv) {
                $count = (float) $lv['count'];
                if ($count <= 0) {
                    continue;
                }
                $pp = ProductPackaging::with('packaging')->where('product_id', $pid)->find($lv['product_packaging_id']);
                if (!$pp) {
                    throw ValidationException::withMessages(['products' => 'Un emballage reçu n\'existe plus. Rechargez la page.']);
                }
                $base += $count * (float) $pp->quantity;
                $parts[] = $this->fmt($count) . ' × ' . ($pp->packaging->name ?? 'emballage');
            }
            $loose = (float) ($row['loose'] ?? 0);
            if ($loose > 0) {
                $base += $loose;
                $parts[] = $this->fmt($loose) . ' ' . (Product::with('unit')->find($pid)->unit->symbol ?? '') . ' en vrac';
            }
            if ($base <= 0) {
                continue;
            }
            $perProduct[$pid] = [
                'base'   => ($perProduct[$pid]['base'] ?? 0) + $base,
                'detail' => trim(($perProduct[$pid]['detail'] ?? '') . ' ' . implode(', ', $parts)),
            ];
            $amounts[$pid] = ($amounts[$pid] ?? 0) + (float) ($row['amount'] ?? 0);
        }

        if (!$perProduct) {
            throw ValidationException::withMessages(['products' => 'Toutes les quantités sont à zéro.']);
        }

        $meta = [
            'supplier_delivery_note' => trim($data['supplier_delivery_note'] ?? '') ?: null,
            'notes'                  => trim($data['notes'] ?? '') ?: null,
        ];

        try {
            $receipt = DB::transaction(function () use ($data, $perProduct, $amounts, $meta) {
                if (!empty($data['purchase_order_id'])) {
                    $order = PurchaseOrder::lockForUpdate()->findOrFail($data['purchase_order_id']);
                    if (!in_array($order->status, ['ordered', 'partial'])) {
                        throw new \InvalidArgumentException('Cette commande n\'est plus à réceptionner.');
                    }
                    // Le fournisseur n'est connu qu'au moment de la livraison — on le fixe (ou corrige) ici.
                    $order->update(['supplier_id' => $data['supplier_id']]);

                    return $this->receipts->receive($order, (int) $data['stock_id'], $perProduct, $meta);
                }

                // Réception directe : on crée le BC correspondant (prix = montants payés s'ils sont saisis).
                $total = array_sum($amounts);
                $order = PurchaseOrder::create([
                    'supplier_id'      => $data['supplier_id'],
                    'is_direct'        => true,
                    'reference'        => 'CMD-' . strtoupper(uniqid()),
                    'total_amount'     => $total,
                    'paid_amount'      => 0,
                    'remaining_amount' => $total,
                    'payment_status'   => 'unpaid',
                    'status'           => 'ordered',
                ]);
                foreach ($perProduct as $pid => $info) {
                    $amount = $amounts[$pid] ?? 0;
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $order->id,
                        'product_id'        => $pid,
                        'packaging_id'      => null,
                        'quantity'          => round($info['base'], 2),
                        'price'             => $amount > 0 ? round($amount / $info['base'], 2) : null,
                        'total'             => $amount > 0 ? $amount : null,
                    ]);
                }

                return $this->receipts->receive($order, (int) $data['stock_id'], $perProduct, $meta, $total);
            });
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['products' => $e->getMessage()]);
        }

        return response()->json([
            'message'  => "Réception {$receipt->reference} enregistrée : " . count($perProduct) . ' produit(s) entré(s) en stock.',
            'redirect' => route('purchases.orders.show', $receipt->purchase_order_id),
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function formData(): array
    {
        return [
            'catalog'   => ScanCatalog::all(),
            'stocks'    => Stock::orderBy('name')->get(['id', 'name', 'module']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'canLink'   => (bool) auth()->user()?->can('products.edit'),
            'packagingNames' => \App\Models\Packaging::orderBy('name')->pluck('name'),
        ];
    }

    private function fmt($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 2, ',', ' '), '0'), ',');
    }
}
