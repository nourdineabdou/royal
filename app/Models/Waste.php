<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    protected $fillable = [
        'product_id',
        'stock_id',
        'quantity',
        'reason',
        'validated_at',
        'validated_by',
        'packaging_id',
    ];
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function stock()
    {
        return $this->belongsTo(\App\Models\Stock::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'validated_by');
    }
}
