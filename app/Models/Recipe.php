<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['meal_id'];

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public function items()
    {
        return $this->hasMany(RecipeItem::class);
    }
}
