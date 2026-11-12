<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'reference',
        'barcode',
        'is_bulk',
        'is_consumable',
        'sale_price',
        'unit_id',
        'packaging_id',
        'product_category_id',
    ];

    protected $casts = [
        'is_bulk' => 'boolean',
        'is_consumable' => 'boolean',
        'sale_price' => 'decimal:2',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
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
