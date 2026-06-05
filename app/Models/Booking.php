<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use BelongsToCompany, BelongsToSite;

    protected $fillable = [
        'company_id', 'site_id',
        'room_id', 'customer_name', 'customer_phone', 'identity_number',
        'num_guests', 'check_in', 'check_out', 'total_amount', 'paid_amount',
        'status', 'notes', 'transaction_id',
    ];

    protected $casts = [
        'check_in'     => 'date',
        'check_out'    => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function details()
    {
        return $this->hasMany(BookingDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(RoomPayment::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float)$this->total_amount - (float)$this->paid_amount);
    }

    public function getNightsAttribute(): int
    {
        return $this->check_in->diffInDays($this->check_out);
    }
}
