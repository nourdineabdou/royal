<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packaging extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productPackagings()
    {
        return $this->hasMany(ProductPackaging::class);
    }
}
