<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringInvoicePayment extends Model
{
    protected $fillable = [
        'catering_invoice_id',
        'payment_type_id',
        'amount',
        'payment_date',
        'status',
        'notes',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'validated_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(CateringInvoice::class, 'catering_invoice_id');
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
