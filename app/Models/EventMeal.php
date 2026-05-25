<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMeal extends Model
{
    protected $fillable = ['event_id', 'type', 'guest_count'];

    public function event() { return $this->belongsTo(Event::class); }
    public function items() { return $this->hasMany(EventMealItem::class); }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'breakfast' => 'Petit-déjeuner',
            'lunch'     => 'Déjeuner',
            'dinner'    => 'Dîner',
            default     => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'breakfast' => 'bg-amber-100 text-amber-700',
            'lunch'     => 'bg-orange-100 text-orange-700',
            'dinner'    => 'bg-indigo-100 text-indigo-700',
            default     => 'bg-slate-100 text-slate-600',
        };
    }
}
