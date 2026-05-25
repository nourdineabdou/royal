<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'payment_type_id', 'amount', 'cash_register_id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
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
