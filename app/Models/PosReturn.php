<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosReturn extends Model
{
    protected $fillable = [
        'cash_register_id', 'pos_terminal_id',
        'reference', 'return_date', 'returned_by', 'notes',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    public function register(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class, 'cash_register_id');
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosReturnItem::class);
    }

    // ── Static helpers ─────────────────────────────────────────────────────

    public static function nextReference(): string
    {
        $year  = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'RET-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
