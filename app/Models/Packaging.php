<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packaging extends Model
{
    protected $fillable = ['name', 'image', 'description'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productPackagings()
    {
        return $this->hasMany(ProductPackaging::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
