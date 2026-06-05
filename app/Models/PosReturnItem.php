<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosReturnItem extends Model
{
    protected $fillable = [
        'pos_return_id', 'pos_transfer_item_id', 'pos_terminal_stock_item_id',
        'label', 'quantity', 'unit', 'item_type',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function posReturn(): BelongsTo
    {
        return $this->belongsTo(PosReturn::class);
    }

    public function transferItem(): BelongsTo
    {
        return $this->belongsTo(PosTransferItem::class, 'pos_transfer_item_id');
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(PosTerminalStockItem::class, 'pos_terminal_stock_item_id');
    }
}
