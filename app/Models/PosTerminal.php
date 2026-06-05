<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Point de vente permanent configuré par l'admin.
 * Chaque terminal est lié à un ou deux caissiers (matin / soir).
 * Les sessions d'ouverture sont dans cash_registers (pos_terminal_id FK).
 */
class PosTerminal extends Model
{
    protected $fillable = [
        'label', 'type', 'module',
        'client_id', 'stock_id',
        'cashier_morning_id', 'cashier_evening_id',
        'is_active', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /** Caissier affecté au shift matin */
    public function cashierMorning()
    {
        return $this->belongsTo(User::class, 'cashier_morning_id');
    }

    /** Caissier affecté au shift soir */
    public function cashierEvening()
    {
        return $this->belongsTo(User::class, 'cashier_evening_id');
    }

    /** Sessions d'ouverture liées à ce terminal */
    public function sessions()
    {
        return $this->hasMany(CashRegister::class, 'pos_terminal_id');
    }

    /** Session actuellement ouverte (s'il y en a une) */
    public function activeSession()
    {
        return $this->hasOne(CashRegister::class, 'pos_terminal_id')->where('status', 'open');
    }

    /** Transferts (tous statuts) */
    public function transfers()
    {
        return $this->hasMany(PosTransfer::class, 'pos_terminal_id');
    }

    /** Transferts en attente ou en transit */
    public function pendingTransfers()
    {
        return $this->hasMany(PosTransfer::class, 'pos_terminal_id')
                    ->whereIn('status', ['pending', 'in_transit']);
    }

    /** Stock cumulé du terminal */
    public function stockItems()
    {
        return $this->hasMany(PosTerminalStockItem::class, 'pos_terminal_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isCateringPos(): bool
    {
        return $this->type === 'catering_pos';
    }

    public function isOrdinary(): bool
    {
        return $this->type === 'ordinary';
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'catering_pos' => 'Point de vente catering',
            default        => 'Caisse ordinaire',
        };
    }

    public function getModuleLabelAttribute(): string
    {
        return match($this->module) {
            'catering'   => 'Catering',
            'events'     => 'Événements',
            'residence'  => 'Résidence',
            default      => 'Restaurant',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'catering_pos' => 'fa-truck',
            default        => 'fa-cash-register',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'catering_pos' => 'amber',
            default        => 'indigo',
        };
    }

    /**
     * Trouve le terminal assigné à un utilisateur (peu importe le shift).
     */
    public static function forUser(int $userId): ?self
    {
        return static::where('is_active', true)
            ->where(function ($q) use ($userId) {
                $q->where('cashier_morning_id', $userId)
                  ->orWhere('cashier_evening_id', $userId);
            })
            ->with(['client', 'stock', 'activeSession'])
            ->first();
    }

    /**
     * Détermine le shift du caissier pour ce terminal.
     */
    public function shiftFor(int $userId): string
    {
        if ($this->cashier_morning_id === $userId) return 'morning';
        if ($this->cashier_evening_id === $userId) return 'evening';
        return 'morning';
    }
}
