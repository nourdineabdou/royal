<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringInvoice extends Model
{
    protected $fillable = [
        'catering_contract_id',
        'client_id',
        'invoice_number',
        'period_year',
        'period_month',
        'period_start',
        'period_end',
        'total_amount',
        'paid_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function contract()
    {
        return $this->belongsTo(CateringContract::class, 'catering_contract_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(CateringInvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(CateringInvoicePayment::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }
}
