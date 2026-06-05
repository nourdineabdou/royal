<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use BelongsToCompany, BelongsToSite;

    protected $fillable = ['company_id', 'site_id', 'room_type_id', 'number', 'floor', 'status'];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking()
    {
        return $this->hasOne(Booking::class)->whereIn('status', ['pending', 'checked_in']);
    }
}
