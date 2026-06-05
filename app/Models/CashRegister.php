<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use BelongsToCompany, BelongsToSite;

    protected $fillable = [
        'company_id', 'site_id', 'pos_terminal_id',
        'user_id', 'module', 'label', 'type', 'client_id', 'stock_id',
        'shift', 'opening_balance', 'closing_balance', 'opened_at', 'closed_at',
        'status', 'declared_excess', 'closing_history', 'accounting_note', 'validated_by', 'validated_at',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'declared_excess' => 'decimal:2',
        'closing_history' => 'array',
        'opened_at'       => 'datetime',
        'closed_at'       => 'datetime',
        'validated_at'    => 'datetime',
    ];

    public function posTerminal()
    {
        return $this->belongsTo(PosTerminal::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function validator()
    {
        return $this->belongsTo(\App\Models\User::class, 'validated_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function posTransfers()
    {
        return $this->hasMany(PosTransfer::class);
    }

    public function pendingTransfers()
    {
        return $this->hasMany(PosTransfer::class)->where('status', 'pending');
    }

    /**
     * Retourne les transferts pending pour cette caisse catering_pos :
     * - soit liés directement à cette caisse
     * - soit en attente (cash_register_id null) pour le même client
     */
    public function getPendingTransfersAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        $query = PosTransfer::where('status', 'pending');

        if ($this->client_id) {
            $query->where(function ($q) {
                $q->where('cash_register_id', $this->id)
                  ->orWhere(function ($q2) {
                      $q2->whereNull('cash_register_id')
                         ->where('client_id', $this->client_id);
                  });
            });
        } else {
            $query->where('cash_register_id', $this->id);
        }

        return $query->with(['items', 'preparedBy'])->get();
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isCateringPos(): bool
    {
        return $this->type === 'catering_pos';
    }

    public function isOrdinary(): bool
    {
        return $this->type === 'ordinary';
    }
}
