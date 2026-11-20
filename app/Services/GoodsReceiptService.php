<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\ProductPackaging;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Réception d'un bon de commande (BL) — seul chemin normal d'entrée en stock.
 *
 * Les quantités arrivent par produit, en unité de base (g, mL, pcs), qu'elles viennent d'un scan
 * (caisse, bouteille…) ou d'une saisie (pesée de vrac). Elles sont réparties sur les lignes du bon
 * de commande ; un surplus est accepté et affecté à la dernière ligne du produit (comme Odoo).
 *
 * Chaque réception crée : le BL (goods_receipts), ses lignes, la hausse du stock, un mouvement
 * de stock par produit (traçabilité) et l'écriture comptable d'achat.
 */
class GoodsReceiptService
{
    public function __construct(private AccountingEntryService $accounting)
    {
    }

    /**
     * @param  array<int, array{base: float, detail?: string}>  $perProduct  quantités reçues par produit, en unité de base
     * @param  array{supplier_delivery_note?: ?string, notes?: ?string}  $meta
     * @param  float|null  $amount  montant comptable imposé (réception directe) ; sinon calculé depuis les prix du BC
     */
    public function receive(PurchaseOrder $order, int $stockId, array $perProduct, array $meta = [], ?float $amount = null): GoodsReceipt
    {
        return DB::transaction(function () use ($order, $stockId, $perProduct, $meta, $amount) {
            $order->load('items.product');

            $receipt = GoodsReceipt::create([
                'purchase_order_id'      => $order->id,
                'stock_id'               => $stockId,
                'supplier_delivery_note' => $meta['supplier_delivery_note'] ?? null,
                'notes'                  => $meta['notes'] ?? null,
                'received_by'            => auth()->id(),
                'received_at'            => now(),
            ]);

            $computedAmount = 0.0;

            foreach ($perProduct as $productId => $info) {
                $base = round((float) $info['base'], 2);
                if ($base <= 0) {
                    continue;
                }

                $lines = $order->items->where('product_id', $productId)->values();
                if ($lines->isEmpty()) {
                    $name = \App\Models\Product::find($productId)?->name ?? "#{$productId}";
                    throw new \InvalidArgumentException("« {$name} » ne fait pas partie de cette commande : faites une réception directe pour ce produit.");
                }

                // Répartition sur les lignes du BC : chaque ligne jusqu'à son restant, le surplus sur la dernière.
                $left = $base;
                foreach ($lines as $i => $line) {
                    $factor = $this->lineFactor($line);
                    $isLast = $i === $lines->count() - 1;
                    $alloc  = $isLast ? $left : min($left, $line->remaining_quantity * $factor);
                    if ($alloc <= 0) {
                        continue;
                    }

                    $receivedInLineUnit = round($alloc / $factor, 4);
                    GoodsReceiptItem::create([
                        'goods_receipt_id'  => $receipt->id,
                        'order_item_id'     => $line->id,
                        'product_id'        => $productId,
                        'quantity'          => round($alloc, 2),
                        'received_quantity' => round($receivedInLineUnit, 2),
                    ]);
                    $computedAmount += $receivedInLineUnit * (float) ($line->price ?? 0);
                    $left -= $alloc;
                    if ($left <= 0.0001) {
                        break;
                    }
                }

                $item = StockItem::where('stock_id', $stockId)->where('product_id', $productId)->lockForUpdate()->first()
                    ?? StockItem::create(['stock_id' => $stockId, 'product_id' => $productId, 'quantity' => 0]);
                $item->increment('quantity', $base);

                StockMovement::create([
                    'stock_id'          => $stockId,
                    'product_id'        => $productId,
                    'type'              => 'in',
                    'quantity'          => $base,
                    'origin_module'     => 'purchase',
                    'origin_type'       => 'goods_receipt',
                    'origin_id'         => $receipt->id,
                    'purchase_order_id' => $order->id,
                    'user_id'           => auth()->id(),
                    'notes'             => mb_substr(trim(implode(' — ', array_filter([
                        $receipt->reference . ' / ' . $order->reference,
                        !empty($meta['supplier_delivery_note']) ? 'BL fournisseur ' . $meta['supplier_delivery_note'] : null,
                        $info['detail'] ?? null,
                    ]))), 0, 1000),
                ]);
            }

            if (!$receipt->items()->exists()) {
                throw new \InvalidArgumentException('Aucune quantité reçue.');
            }

            // Reçue seulement si toutes les lignes sont livrées, sinon partielle (le reste = reliquat).
            $order->load('items');
            $allReceived = $order->items->every(fn ($i) => $i->remaining_quantity <= 0.001);
            $order->update(['status' => $allReceived ? 'received' : 'partial']);

            $module = Stock::find($stockId)?->module ?? 'default';
            $this->accounting->postPurchaseReceipt($receipt, round($amount ?? $computedAmount, 2), $module);

            return $receipt;
        });
    }

    /** Contenance d'une unité de ligne de BC en unité de base (1 si la ligne est déjà en unité de base). */
    public function lineFactor($line): float
    {
        if (!$line->packaging_id) {
            return 1.0;
        }

        $qty = ProductPackaging::where('product_id', $line->product_id)
            ->where('packaging_id', $line->packaging_id)
            ->value('quantity');

        return $qty ? (float) $qty : 1.0;
    }
}
