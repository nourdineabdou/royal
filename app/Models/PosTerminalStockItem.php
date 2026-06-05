<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosTerminalStockItem extends Model
{
    protected $fillable = [
        'pos_terminal_id', 'label', 'item_type',
        'meal_id', 'product_id',
        'quantity_received', 'quantity_served', 'quantity_sold', 'quantity_returned',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:3',
        'quantity_served'   => 'decimal:3',
        'quantity_sold'     => 'decimal:3',
        'quantity_returned' => 'decimal:3',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ticketLogs(): HasMany
    {
        return $this->hasMany(PosTerminalTicketLog::class, 'pos_terminal_stock_item_id');
    }

    // ── Accesseurs ─────────────────────────────────────────────────────────

    /** Quantité disponible (reçue - servie - vendue - retournée) */
    public function getAvailableQtyAttribute(): float
    {
        return max(0,
            (float) $this->quantity_received
            - (float) $this->quantity_served
            - (float) $this->quantity_sold
            - (float) $this->quantity_returned
        );
    }

    public function getIsContractAttribute(): bool
    {
        return $this->item_type === 'contract';
    }

    public function getIsExtraAttribute(): bool
    {
        return $this->item_type === 'extra';
    }
}
