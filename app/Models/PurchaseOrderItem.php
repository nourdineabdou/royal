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

    public function getPackagingQtyPerUnitAttribute()
    {
        if (!$this->packaging_id) return null;
        return ProductPackaging::where('product_id', $this->product_id)
                               ->where('packaging_id', $this->packaging_id)
                               ->value('quantity');
    }
}
