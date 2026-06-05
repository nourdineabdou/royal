<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'meal_id', 'product_id', 'label', 'quantity', 'price', 'cost_price'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** Nom affiché (plat ou produit ou label libre) */
    public function getDisplayNameAttribute(): string
    {
        return $this->label ?? $this->meal?->name ?? $this->product?->name ?? '?';
    }

    public function accompaniments()
    {
        return $this->belongsToMany(Accompaniment::class, 'order_item_accompaniments');
    }
}
