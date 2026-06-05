<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use BelongsToCompany, BelongsToSite;

    protected $fillable = [
        'company_id',
        'site_id',
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
