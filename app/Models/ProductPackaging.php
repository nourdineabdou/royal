<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Emballage d'un produit (bouteille, pack, caisse…).
 *
 * Les emballages sont « à plat » : chacun indique sa contenance en unité de base du produit
 * (quantity). La hiérarchie (Bouteille → Caisse) est déduite des contenances : un emballage
 * en contient un autre quand sa contenance en est un multiple exact.
 */
class ProductPackaging extends Model
{
    protected $fillable = ['product_id', 'packaging_id', 'quantity', 'barcode'];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class);
    }

    // ─── Hiérarchie des emballages ───────────────────────────────────────────

    /** L'emballage qui contient directement celui-ci (ex : Bouteille → Caisse), ou null. */
    public function parentLevel(): ?self
    {
        return static::directParent($this->siblings(), $this);
    }

    /** L'emballage contenu directement dans celui-ci (ex : Caisse → Bouteille), ou null. */
    public function childLevel(): ?self
    {
        return static::directChild($this->siblings(), $this);
    }

    /** Combien de $child tiennent dans cet emballage (ex : Caisse contient 12 Bouteilles). */
    public function contains(self $child): float
    {
        return (float) $this->quantity / (float) $child->quantity;
    }

    /** Texte « 12 × Bouteille 1 L », ou null si l'emballage ne contient aucun autre emballage. */
    public function breakdown(?Collection $levels = null): ?string
    {
        $child = static::directChild($levels ?? $this->siblings(), $this);
        if (!$child) {
            return null;
        }

        $n = rtrim(rtrim(number_format($this->contains($child), 2, ',', ' '), '0'), ',');

        return $n . ' × ' . ($child->packaging->name ?? 'emballage');
    }

    /**
     * Version « collection » : utile quand les emballages du produit sont déjà chargés,
     * pour éviter une requête par emballage.
     */
    public static function directParent(Collection $levels, self $pp): ?self
    {
        return $levels
            ->filter(fn (self $o) => $o->id !== $pp->id && static::isMultiple($o, $pp))
            ->sortBy(fn (self $o) => (float) $o->quantity)
            ->first();
    }

    public static function directChild(Collection $levels, self $pp): ?self
    {
        return $levels
            ->filter(fn (self $o) => $o->id !== $pp->id && static::isMultiple($pp, $o))
            ->sortByDesc(fn (self $o) => (float) $o->quantity)
            ->first();
    }

    /** Vrai si $big contient un nombre entier (> 1) de $small. */
    private static function isMultiple(self $big, self $small): bool
    {
        $b = (float) $big->quantity;
        $s = (float) $small->quantity;
        if ($s <= 0 || $b <= $s) {
            return false;
        }
        $ratio = $b / $s;

        return abs($ratio - round($ratio)) < 1e-6;
    }

    private function siblings(): Collection
    {
        return $this->product->productPackagings()->with('packaging')->get();
    }
}
