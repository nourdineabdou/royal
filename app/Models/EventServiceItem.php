<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventServiceItem extends Model
{
    protected $fillable = ['event_id', 'service_id', 'quantity', 'price'];

    protected $casts = ['price' => 'decimal:2'];

    public function event()   { return $this->belongsTo(Event::class); }
    public function service() { return $this->belongsTo(Service::class); }

    public function getSubtotalAttribute(): float
    {
        return (float)$this->price * (int)$this->quantity;
    }
}
