<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventStockUsage extends Model
{
    protected $fillable = ['event_id', 'product_id', 'quantity'];

    protected $casts = ['quantity' => 'decimal:2'];

    public function event()   { return $this->belongsTo(Event::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
