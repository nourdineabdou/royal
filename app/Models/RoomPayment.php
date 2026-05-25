<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomPayment extends Model
{
    protected $fillable = ['booking_id', 'payment_type_id', 'cash_register_id', 'amount', 'paid_at'];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }
}
