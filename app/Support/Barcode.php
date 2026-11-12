<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductPackaging;

/**
 * Résolution des codes-barres produits.
 *
 * Un code peut désigner :
 *  - l'unité de base d'un produit (products.barcode)          → facteur 1
 *  - un conditionnement du produit (product_packagings.barcode) → facteur = quantity
 *
 * Les douchettes et caméras ne renvoient pas toujours le même format pour un même article
 * (UPC-A 12 chiffres vs EAN-13 avec un 0 devant, GTIN-14…). On compare donc aussi
 * les variantes numériques avec / sans zéros de tête, comme le font les ERP.
 */
class Barcode
{
    public static function normalize(?string $code): string
    {
        return preg_replace('/\s+/', '', trim((string) $code));
    }

    /** Variantes équivalentes d'un code (GTIN-8/12/13/14 complétés de zéros). */
    public static function variants(string $code): array
    {
        $code = self::normalize($code);
        if ($code === '') {
            return [];
        }

        $variants = [$code];
        if (ctype_digit($code)) {
            $core = ltrim($code, '0');
            foreach ([8, 12, 13, 14] as $len) {
                if (strlen($core) <= $len) {
                    $variants[] = str_pad($core, $len, '0', STR_PAD_LEFT);
                }
            }
            $variants[] = $core;
        }

        return array_values(array_unique(array_filter($variants, fn ($v) => $v !== '')));
    }

    /**
     * @return array{product: Product, packaging: ?ProductPackaging, factor: float}|null
     */
    public static function resolve(?string $code): ?array
    {
        $variants = self::variants((string) $code);
        if (!$variants) {
            return null;
        }

        $pp = ProductPackaging::with(['product.unit', 'packaging'])->whereIn('barcode', $variants)->first();
        if ($pp && $pp->product) {
            return ['product' => $pp->product, 'packaging' => $pp, 'factor' => (float) $pp->quantity];
        }

        $product = Product::with('unit')->whereIn('barcode', $variants)->first();
        if ($product) {
            return ['product' => $product, 'packaging' => null, 'factor' => 1.0];
        }

        return null;
    }

    /**
     * Description lisible de l'article qui utilise déjà ce code, ou null s'il est libre.
     * $ignore permet d'exclure l'enregistrement en cours de modification.
     */
    public static function owner(?string $code, ?Product $ignoreProduct = null, ?ProductPackaging $ignorePackaging = null): ?string
    {
        $variants = self::variants((string) $code);
        if (!$variants) {
            return null;
        }

        $pp = ProductPackaging::with(['product', 'packaging'])->whereIn('barcode', $variants)
            ->when($ignorePackaging?->exists, fn ($q) => $q->where('id', '!=', $ignorePackaging->id))
            ->first();
        if ($pp) {
            return ($pp->product->name ?? 'Produit') . ' — ' . ($pp->packaging->name ?? 'conditionnement');
        }

        $product = Product::whereIn('barcode', $variants)
            ->when($ignoreProduct?->exists, fn ($q) => $q->where('id', '!=', $ignoreProduct->id))
            ->first();

        return $product ? $product->name . ' (unité de base)' : null;
    }
}
