<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_number',
        'server_id',
        'cashier_id',
        'cash_register_id',
        'total_amount',
        'status',
        'is_prepared',
        'sent_at',
        'paid_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'is_prepared' => 'boolean'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function server()
    {
        return $this->belongsTo(User::class, 'server_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }
}
