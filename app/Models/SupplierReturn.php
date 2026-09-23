<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierReturn extends Model
{
    protected $fillable = [
        'reference', 'purchase_order_id', 'stock_id', 'reason', 'total_amount', 'created_by', 'returned_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'returned_at'  => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SupplierReturnItem::class);
    }
}
