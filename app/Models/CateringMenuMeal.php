<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringMenuMeal extends Model
{
    protected $fillable = ['catering_menu_day_id', 'type', 'quantity'];

    public function menuDay()
    {
        return $this->belongsTo(CateringMenuDay::class, 'catering_menu_day_id');
    }

    public function items()
    {
        return $this->hasMany(CateringMenuMealItem::class);
    }

    public function codes()
    {
        return $this->hasMany(CateringMealCode::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'breakfast' => 'Petit-déjeuner',
            'lunch'     => 'Déjeuner',
            'dinner'    => 'Dîner',
            default     => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'breakfast' => 'bg-yellow-100 text-yellow-700',
            'lunch'     => 'bg-blue-100 text-blue-700',
            'dinner'    => 'bg-indigo-100 text-indigo-700',
            default     => 'bg-slate-100 text-slate-600',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'breakfast' => '🌅',
            'lunch'     => '☀️',
            'dinner'    => '🌙',
            default     => '🍴',
        };
    }
}


