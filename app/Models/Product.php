<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Packaging;

class Product extends Model
{
    protected $fillable = ['name', 'is_bulk', 'unit_id', 'packaging_id'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }

    public function recipesItems()
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function productPackagings()
    {
        return $this->hasMany(ProductPackaging::class);
    }
}
