<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosTransferItem extends Model
{
    protected $fillable = [
        'pos_transfer_id', 'meal_id', 'product_id', 'packaging_id',
        'label', 'quantity', 'unit', 'item_type', 'unit_price',
        'served_qty', 'sold_qty',
    ];

    protected $casts = [
        'quantity'   => 'decimal:3',
        'unit_price' => 'decimal:2',
        'served_qty' => 'decimal:3',
        'sold_qty'   => 'decimal:3',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(PosTransfer::class, 'pos_transfer_id');
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function packaging(): BelongsTo
    {
        return $this->belongsTo(Packaging::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Quantité restante (ni servie ni vendue) */
    public function getRemainingQtyAttribute(): float
    {
        return (float) $this->quantity - (float) $this->served_qty - (float) $this->sold_qty;
    }

    /** Montant encaissé pour cet article (extras uniquement) */
    public function getSoldAmountAttribute(): float
    {
        return (float) $this->unit_price * (float) $this->sold_qty;
    }
}
