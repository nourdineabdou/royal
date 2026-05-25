<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventOption extends Model
{
    protected $fillable = ['event_id', 'name', 'price', 'quantity'];

    protected $casts = ['price' => 'decimal:2'];

    public function event() { return $this->belongsTo(Event::class); }
    public function items() { return $this->hasMany(EventOptionItem::class); }

    public function getSubtotalAttribute(): float
    {
        return (float)$this->price * (int)$this->quantity;
    }
}
