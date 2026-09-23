<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierQuoteItem extends Model
{
    protected $fillable = ['supplier_quote_id', 'purchase_request_item_id', 'unit_price'];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function supplierQuote()
    {
        return $this->belongsTo(SupplierQuote::class);
    }

    public function requestItem()
    {
        return $this->belongsTo(PurchaseRequestItem::class, 'purchase_request_item_id');
    }
}
