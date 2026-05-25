<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMealItem extends Model
{
    protected $fillable = ['event_meal_id', 'meal_id'];

    public function eventMeal() { return $this->belongsTo(EventMeal::class); }
    public function meal()      { return $this->belongsTo(Meal::class); }
}
