<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosTransfer extends Model
{
    protected $fillable = [
        'from_stock_id', 'prepared_by', 'cash_register_id', 'pos_terminal_id',
        'client_id', 'catering_contract_id',
        'driver_name', 'reference', 'transfer_date', 'status',
        'printed_at', 'driver_signed_at',
        'cashier_validated_at', 'cashier_validated_by', 'notes',
    ];

    protected $casts = [
        'transfer_date'        => 'date',
        'printed_at'           => 'datetime',
        'driver_signed_at'     => 'datetime',
        'cashier_validated_at' => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    public function items(): HasMany
    {
        return $this->hasMany(PosTransferItem::class);
    }

    public function contractItems(): HasMany
    {
        return $this->hasMany(PosTransferItem::class)->where('item_type', 'contract');
    }

    public function extraItems(): HasMany
    {
        return $this->hasMany(PosTransferItem::class)->where('item_type', 'extra');
    }

    public function fromStock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'from_stock_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function posTerminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function cateringContract(): BelongsTo
    {
        return $this->belongsTo(CateringContract::class);
    }

    public function cashierValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_validated_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForRegister($query, int $registerId)
    {
        return $query->where('cash_register_id', $registerId);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Génère la prochaine référence TRNF-YYYY-NNNN
     */
    public static function nextReference(): string
    {
        $year  = date('Y');
        $last  = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'TRNF-' . $year . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getTotalExtraAmountAttribute(): float
    {
        return (float) $this->extraItems->sum(fn ($i) => $i->unit_price * $i->sold_qty);
    }
}
