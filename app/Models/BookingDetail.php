<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    protected $fillable = ['booking_id', 'title', 'description', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
