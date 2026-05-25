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
