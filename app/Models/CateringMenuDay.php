<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringMenuDay extends Model
{
    protected $fillable = ['catering_weekly_menu_id', 'date'];

    protected $casts = ['date' => 'date'];

    public function weeklyMenu()
    {
        return $this->belongsTo(CateringWeeklyMenu::class, 'catering_weekly_menu_id');
    }

    public function meals()
    {
        return $this->hasMany(CateringMenuMeal::class)->orderBy('type');
    }

    public function getDayLabelAttribute(): string
    {
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $idx  = (int)$this->date->dayOfWeekIso - 1;
        return ($days[$idx] ?? $this->date->format('l')) . ' ' . $this->date->format('d/m');
    }
}
