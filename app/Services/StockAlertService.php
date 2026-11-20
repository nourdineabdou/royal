<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequestItem;
use App\Models\Stock;
use App\Models\StockItem;
use App\Support\Qty;
use Illuminate\Support\Collection;

/**
 * Produits en rupture ou sous leur seuil minimum dans le stock de référence (Stock Catering).
 *
 * Tient compte de ce qui est déjà en commande (DA envoyées non encore commandées + BC non reçus)
 * pour ne pas redemander un produit qui arrive déjà. Propose une quantité à commander,
 * arrondie au plus grand emballage d'achat (ex : sacs de 25 kg).
 */
class StockAlertService
{
    /** Magasin de référence pour les seuils : celui lié au module Catering. */
    public function referenceStock(): ?Stock
    {
        return Stock::forModule('catering');
    }

    /**
     * Tous les produits du catalogue sont surveillés : seuil de leur fiche, sinon seuil par défaut
     * (3 emballages d'achat).
     *
     * @return Collection<int, array{product: Product, total: float, min: float, min_default: bool, min_label: ?string, incoming: float, level: string, needs_order: bool, suggested: float, suggested_label: string}>
     */
    public function alerts(): Collection
    {
        $ref = $this->referenceStock();
        $totals = StockItem::query()
            ->when($ref, fn ($q) => $q->where('stock_id', $ref->id))
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id')
            ->map(fn ($q) => (float) $q);
        $incoming = $this->incoming();

        return Product::with(['unit', 'productPackagings.packaging'])
            ->orderBy('name')->get()
            ->map(function (Product $p) use ($totals, $incoming) {
                $total = $totals[$p->id] ?? 0.0;
                // Seuil de la fiche produit, sinon seuil par défaut : 3 emballages d'achat
                $min = $p->effectiveMinStock();

                if ($total <= 0) {
                    $level = 'rupture';
                } elseif ($total <= $min) {
                    $level = 'below';
                } else {
                    return null;
                }

                $in = $incoming[$p->id] ?? 0.0;
                // À commander si, même avec ce qui arrive, on reste au niveau du seuil
                $needs = ($total + $in) <= $min;
                [$qty, $label] = $needs ? $this->suggest($p, $total + $in, $min) : [0.0, 'Déjà en commande'];

                return [
                    'product'         => $p,
                    'total'           => $total,
                    'min'             => $min,
                    'min_default'     => $p->usesDefaultMinStock(),
                    'min_label'       => $p->usesDefaultMinStock() ? $p->defaultMinLabel() . ' (par défaut)' : null,
                    'incoming'        => $in,
                    'level'           => $level,
                    'needs_order'     => $needs && $qty > 0,
                    'suggested'       => $qty,
                    'suggested_label' => $label,
                ];
            })
            ->filter()
            ->sortBy(fn ($a) => $a['level'] === 'rupture' ? 0 : 1)
            ->values();
    }

    public function count(): int
    {
        return $this->alerts()->count();
    }

    /** Le produit est-il au niveau de son seuil dans le stock de référence ? */
    public function isBelow(Product $p): bool
    {
        $ref = $this->referenceStock();
        $rows = StockItem::where('product_id', $p->id)->when($ref, fn ($x) => $x->where('stock_id', $ref->id));
        $p->loadMissing(['unit', 'productPackagings.packaging']);

        return (float) $rows->sum('quantity') <= $p->effectiveMinStock();
    }

    /**
     * Quantités déjà en route, par produit (unité de base) :
     * DA envoyées (pas en brouillon) pas encore passées en BC + BC non entièrement reçus.
     */
    public function incoming(): array
    {
        $in = [];

        PurchaseRequestItem::query()
            ->whereHas('purchaseRequest', fn ($q) => $q->whereIn('status', \App\Models\PurchaseRequest::OPEN))
            ->get(['product_id', 'quantity', 'ordered_quantity'])
            ->each(function ($i) use (&$in) {
                $in[$i->product_id] = ($in[$i->product_id] ?? 0) + max(0, (float) $i->quantity - (float) $i->ordered_quantity);
            });

        $receipts = app(GoodsReceiptService::class);
        PurchaseOrder::with('items')->whereIn('status', ['pending', 'ordered', 'partial'])->get()
            ->each(function ($order) use (&$in, $receipts) {
                foreach ($order->items as $line) {
                    $rest = $line->remaining_quantity * $receipts->lineFactor($line);
                    if ($rest > 0) {
                        $in[$line->product_id] = ($in[$line->product_id] ?? 0) + $rest;
                    }
                }
            });

        return $in;
    }

    /**
     * D'où vient le « en commande » de chaque produit : DA ouvertes (pas encore entièrement en BC)
     * et BC pas encore entièrement reçus. Sert à expliquer pourquoi un produit n'est pas reproposé.
     *
     * @return array<int, array<string, array{kind: string, id: int, reference: string, state: string}>>
     */
    public function incomingSources(): array
    {
        $src = [];

        \App\Models\PurchaseRequest::with('items')->whereIn('status', \App\Models\PurchaseRequest::OPEN)->get()
            ->each(function ($da) use (&$src) {
                foreach ($da->items as $i) {
                    if ((float) $i->quantity - (float) $i->ordered_quantity > 0) {
                        $src[$i->product_id]['da' . $da->id] = ['kind' => 'da', 'id' => $da->id, 'reference' => $da->reference, 'state' => $da->status_label];
                    }
                }
            });

        $states = ['pending' => 'BC à confirmer', 'ordered' => 'BC envoyé, livraison attendue', 'partial' => 'BC reçu en partie'];
        PurchaseOrder::with('items')->whereIn('status', ['pending', 'ordered', 'partial'])->get()
            ->each(function ($o) use (&$src, $states) {
                foreach ($o->items as $line) {
                    if ($line->remaining_quantity > 0) {
                        $src[$line->product_id]['bc' . $o->id] = ['kind' => 'bc', 'id' => $o->id, 'reference' => $o->reference, 'state' => $states[$o->status]];
                    }
                }
            });

        return $src;
    }

    /** Remonter à 2 × le seuil, arrondi au plus grand emballage. */
    private function suggest(Product $p, float $available, float $min): array
    {
        $unit = $p->unit->symbol ?? '';
        $need = max($min * 2 - $available, $min);
        $pack = $p->productPackagings->sortByDesc(fn ($pp) => (float) $pp->quantity)->first();

        if ($pack && (float) $pack->quantity > 0) {
            $n = (int) ceil($need / (float) $pack->quantity);
            $qty = $n * (float) $pack->quantity;

            return [$qty, $n . ' × ' . ($pack->packaging->name ?? 'emballage') . ' (' . Qty::format($qty, $unit) . ')'];
        }

        return [round($need, 2), Qty::format($need, $unit)];
    }
}
