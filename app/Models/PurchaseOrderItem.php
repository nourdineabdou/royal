<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id', 'product_id', 'packaging_id', 'quantity', 'price', 'total'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price'    => 'decimal:2',
        'total'    => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class);
    }

    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class, 'order_item_id');
    }

    /** Quantité déjà reçue (cumulée sur tous les BL), dans l'unité de la ligne de commande (ex: colis). */
    public function getReceivedQuantityAttribute(): float
    {
        return (float) $this->goodsReceiptItems()->sum('received_quantity');
    }

    public function getRemainingQuantityAttribute(): float
    {
        return max(0, (float) $this->quantity - $this->received_quantity);
    }

    public function supplierReturnItems()
    {
        return $this->hasMany(SupplierReturnItem::class, 'order_item_id');
    }

    /** Quantité déjà retournée au fournisseur (cumulée), dans l'unité de la ligne de commande. */
    public function getReturnedQuantityAttribute(): float
    {
        return (float) $this->supplierReturnItems()->sum('quantity');
    }

    /** Ce qui peut encore être retourné = ce qui a été reçu moins ce qui a déjà été retourné. */
    public function getReturnableQuantityAttribute(): float
    {
        return max(0, $this->received_quantity - $this->returned_quantity);
    }

    public function productPackaging()
    {
        return $this->hasOne(ProductPackaging::class, 'product_id', 'product_id')
                    ->where('packaging_id', $this->packaging_id);
    }

    /** Contenance d'un emballage de la ligne, en unité de base (null si commande en vrac). */
    public function perPack(): ?float
    {
        if (!$this->packaging_id) {
            return null;
        }
        $pp = $this->product && $this->product->relationLoaded('productPackagings')
            ? $this->product->productPackagings->firstWhere('packaging_id', $this->packaging_id)
            : null;
        $q = $pp ? $pp->quantity : $this->packaging_qty_per_unit;

        return (float) $q > 0 ? (float) $q : null;
    }

    /** Quantité de la ligne en emballages, ex : « 10 × Sac de 25 kg » (ou « 15 kg » en vrac). */
    public function qtyLabel(?float $qty = null): string
    {
        $qty ??= (float) $this->quantity;
        if ($this->packaging_id) {
            return \App\Support\Qty::num($qty) . ' × ' . ($this->packaging->name ?? 'emballage');
        }

        return \App\Support\Qty::format($qty, $this->product->unit->symbol ?? '');
    }

    /** Équivalent en unité de base, ex : « 250 kg » (null si commande en vrac ou contenance inconnue). */
    public function baseLabel(?float $qty = null): ?string
    {
        $per = $this->perPack();
        if (!$per) {
            return null;
        }

        return \App\Support\Qty::format(($qty ?? (float) $this->quantity) * $per, $this->product->unit->symbol ?? '');
    }

    public function getPackagingQtyPerUnitAttribute()
    {
        if (!$this->packaging_id) return null;
        return ProductPackaging::where('product_id', $this->product_id)
                               ->where('packaging_id', $this->packaging_id)
                               ->value('quantity');
    }
}
