<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenRequestItem extends Model
{
    protected $fillable = ['kitchen_request_id', 'product_id', 'requested_quantity', 'delivered_quantity'];

    protected $casts = [
        'requested_quantity' => 'decimal:2',
        'delivered_quantity' => 'decimal:2',
    ];

    public function request()
    {
        return $this->belongsTo(KitchenRequest::class, 'kitchen_request_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
