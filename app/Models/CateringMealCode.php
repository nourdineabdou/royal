<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringMealCode extends Model
{
    protected $fillable = [
        'catering_menu_meal_id', 'code', 'is_used', 'used_at', 'validated_by',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function menuMeal()
    {
        return $this->belongsTo(CateringMenuMeal::class, 'catering_menu_meal_id');
    }

    public function consumption()
    {
        return $this->hasOne(CateringConsumption::class);
    }

    public function validatedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'validated_by');
    }
}
