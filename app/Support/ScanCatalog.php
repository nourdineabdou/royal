<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductPackaging;
use Illuminate\Support\Collection;

/**
 * Catalogue envoyé aux écrans de scan (sorties, réceptions) : produits, unités,
 * emballages avec leur contenance et leur code-barres.
 */
class ScanCatalog
{
    public static function all(): Collection
    {
        return Product::with(['unit', 'productPackagings.packaging'])->orderBy('name')->get()
            ->map(fn (Product $p) => self::entry($p))->values();
    }

    public static function entry(Product $p): array
    {
        $p->loadMissing(['unit', 'productPackagings.packaging']);

        return [
            'id'         => $p->id,
            'name'       => $p->name,
            'ref'        => $p->reference,
            'unit'       => $p->unit->symbol ?? '',
            'bulk'       => (bool) $p->is_bulk,
            'barcode'    => $p->barcode,
            'packagings' => $p->productPackagings
                ->sortByDesc(fn ($pp) => (float) $pp->quantity)
                ->map(fn (ProductPackaging $pp) => [
                    'id'           => $pp->id,
                    'packaging_id' => $pp->packaging_id,
                    'name'         => $pp->packaging->name ?? 'Emballage',
                    'qty'          => (float) $pp->quantity,
                    'barcode'      => $pp->barcode,
                    'breakdown'    => $pp->breakdown($p->productPackagings), // ex : « 12 × Bouteille 1 L »
                ])->values(),
        ];
    }
}
