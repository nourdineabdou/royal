<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'user_id', 'module', 'shift', 'opening_balance', 'closing_balance', 'opened_at', 'closed_at',
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
}
