<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringMenuMealItem extends Model
{
    protected $fillable = ['catering_menu_meal_id', 'meal_id'];

    public function menuMeal()
    {
        return $this->belongsTo(CateringMenuMeal::class, 'catering_menu_meal_id');
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }
}
