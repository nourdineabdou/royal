<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'meal_id', 'quantity', 'price', 'cost_price'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public function accompaniments()
    {
        return $this->belongsToMany(Accompaniment::class, 'order_item_accompaniments');
    }
}
