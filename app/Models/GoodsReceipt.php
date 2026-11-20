<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'purchase_order_id', 'stock_id', 'supplier_delivery_note', 'received_by', 'received_at', 'notes',
    ];

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

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /** Numéro interne de réception, ex : REC-00042. */
    public function getReferenceAttribute(): string
    {
        return 'REC-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
