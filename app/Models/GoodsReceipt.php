<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = ['purchase_order_id', 'stock_id', 'received_at'];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }
}
