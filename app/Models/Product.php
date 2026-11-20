<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'reference',
        'barcode',
        'min_stock',
        'is_bulk',
        'is_consumable',
        'sale_price',
        'unit_id',
        'packaging_id',
        'product_category_id',
    ];

    protected $casts = [
        'is_bulk' => 'boolean',
        'is_consumable' => 'boolean',
        'sale_price' => 'decimal:2',
        'min_stock' => 'decimal:2',
    ];

    /** Seuil par défaut quand le produit n'a pas le sien : 3 emballages d'achat (3 sacs, 3 cartons…). */
    public const DEFAULT_MIN_PACKS = 3;

    /** Seuil appliqué, en unité de base : celui de la fiche produit, sinon 3 × le plus grand emballage. */
    public function effectiveMinStock(): float
    {
        if ($this->min_stock !== null) {
            return (float) $this->min_stock;
        }

        return self::DEFAULT_MIN_PACKS * $this->defaultMinUnit();
    }

    public function usesDefaultMinStock(): bool
    {
        return $this->min_stock === null;
    }

    /** Texte du seuil par défaut, ex : « 3 × Sac de 25 kg » ou « 3 kg ». */
    public function defaultMinLabel(): string
    {
        $pack = $this->largestPackaging();
        if ($pack) {
            return self::DEFAULT_MIN_PACKS . ' × ' . ($pack->packaging->name ?? 'emballage');
        }

        return self::DEFAULT_MIN_PACKS . ' ' . \App\Support\Qty::displayUnit($this->unit->symbol ?? '')['label'];
    }

    /** Contenance d'un « emballage » pour le seuil par défaut (plus grand emballage, sinon 1 kg / 1 L / 1 pièce). */
    private function defaultMinUnit(): float
    {
        $pack = $this->largestPackaging();
        if ($pack && (float) $pack->quantity > 0) {
            return (float) $pack->quantity;
        }

        return (float) \App\Support\Qty::displayUnit($this->unit->symbol ?? '')['factor'];
    }

    private function largestPackaging()
    {
        return $this->mainPackaging();
    }

    /** Emballage principal d'achat : le plus grand dont la contenance est connue (sac, carton…). */
    public function mainPackaging(): ?ProductPackaging
    {
        return $this->productPackagings->filter(fn ($pp) => (float) $pp->quantity > 0)
            ->sortByDesc(fn ($pp) => (float) $pp->quantity)->first();
    }

    /** Quantité (unité de base) exprimée en emballage principal, ex : « 10 × Sac de 25 kg ». */
    public function inMainPackaging(float $base): ?string
    {
        $pack = $this->mainPackaging();
        if (!$pack || $base <= 0) {
            return null;
        }

        return \App\Support\Qty::num($base / (float) $pack->quantity) . ' × ' . ($pack->packaging->name ?? 'emballage');
    }

    /**
     * Décompose une quantité (unité de base) en emballages, du plus grand au plus petit,
     * le reste en vrac. Ex : 41 bouteilles → [3 × Carton de 12, 5 × Bouteille de 1 L].
     *
     * @return array<int, array{qty: float, label: string}>
     */
    public function stockBreakdown(float $base): array
    {
        $parts = [];
        if ($base <= 0) {
            return $parts;
        }
        $packs = $this->productPackagings->filter(fn ($pp) => (float) $pp->quantity > 0)
            ->sortByDesc(fn ($pp) => (float) $pp->quantity)->values();
        $rest = $base;
        foreach ($packs as $pp) {
            $size = (float) $pp->quantity;
            $n = floor(($rest + 1e-6) / $size);
            if ($n >= 1) {
                $parts[] = ['qty' => $n, 'label' => $pp->packaging->name ?? 'emballage'];
                $rest -= $n * $size;
            }
        }
        if ($rest > 1e-6) {
            $parts[] = ['qty' => null, 'label' => \App\Support\Qty::format($rest, $this->unit->symbol ?? '') . ' en vrac'];
        }

        return $parts;
    }

    /**
     * Emballage et quantité proposés pour commander $base (unité de base) :
     * l'emballage principal (sac, carton…) avec la quantité correspondante, sinon en vrac.
     *
     * @return array{packaging_id: ?int, quantity: float}
     */
    public function suggestOrderLine(float $base): array
    {
        $pick = $this->mainPackaging();

        if (!$pick) {
            return ['packaging_id' => null, 'quantity' => round($base, 2)];
        }

        // Arrondi vers le bas pour ne jamais dépasser le restant de la demande
        return ['packaging_id' => $pick->packaging_id, 'quantity' => floor($base / (float) $pick->quantity * 100) / 100];
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }

    public function recipesItems()
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function productPackagings()
    {
        return $this->hasMany(ProductPackaging::class);
    }
}
