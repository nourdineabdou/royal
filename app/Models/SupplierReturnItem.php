<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierReturnItem extends Model
{
    protected $fillable = [
        'supplier_return_id', 'order_item_id', 'product_id', 'quantity', 'stock_quantity', 'unit_price', 'total',
    ];

    protected $casts = [
        'quantity'       => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'unit_price'     => 'decimal:2',
        'total'          => 'decimal:2',
    ];

    public function supplierReturn()
    {
        return $this->belongsTo(SupplierReturn::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'order_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
