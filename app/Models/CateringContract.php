<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringContract extends Model
{
    protected $fillable = [
        'client_id', 'start_date', 'end_date', 'guest_count',
        'active_days', 'has_breakfast', 'has_lunch', 'has_dinner', 'status',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'active_days'   => 'array',
        'has_breakfast' => 'boolean',
        'has_lunch'     => 'boolean',
        'has_dinner'    => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function prices()
    {
        return $this->hasMany(CateringContractPrice::class);
    }

    public function weeklyMenus()
    {
        return $this->hasMany(CateringWeeklyMenu::class);
    }

    public function getPriceFor(string $type): float
    {
        return (float)($this->prices->firstWhere('type', $type)?->price ?? 0);
    }

    public function getActiveMealTypes(): array
    {
        $types = [];
        if ($this->has_breakfast) $types[] = 'breakfast';
        if ($this->has_lunch)     $types[] = 'lunch';
        if ($this->has_dinner)    $types[] = 'dinner';
        return $types;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Actif',
            'paused' => 'En pause',
            'ended'  => 'Terméiné',
            default  => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-100 text-emerald-700',
            'paused' => 'bg-amber-100 text-amber-700',
            'ended'  => 'bg-slate-100 text-slate-500',
            default  => 'bg-slate-100 text-slate-600',
        };
    }
}
