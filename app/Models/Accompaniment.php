<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accompaniment extends Model
{
    protected $fillable = ['name', 'price'];

    public function meals()
    {
        return $this->belongsToMany(Meal::class, 'meal_accompaniments');
    }
}
