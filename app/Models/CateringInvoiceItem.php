<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringInvoiceItem extends Model
{
    protected $fillable = [
        'catering_invoice_id',
        'pos_terminal_stock_item_id',
        'label',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(CateringInvoice::class, 'catering_invoice_id');
    }

    public function stockItem()
    {
        return $this->belongsTo(PosTerminalStockItem::class, 'pos_terminal_stock_item_id');
    }
}
