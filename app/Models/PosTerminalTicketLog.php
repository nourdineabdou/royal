<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosTerminalTicketLog extends Model
{
    protected $fillable = [
        'pos_terminal_stock_item_id',
        'cash_register_id',
        'user_id',
        'ticket_code',
        'qty',
        'served_at',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'served_at' => 'datetime',
    ];

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(PosTerminalStockItem::class, 'pos_terminal_stock_item_id');
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class, 'cash_register_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
