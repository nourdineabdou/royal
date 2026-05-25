<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPackaging extends Model
{
    protected $fillable = ['product_id', 'packaging_id', 'quantity'];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class);
    }
}
