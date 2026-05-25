<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringWeeklyMenu extends Model
{
    protected $fillable = ['catering_contract_id', 'week_start_date'];

    protected $casts = ['week_start_date' => 'date'];

    public function contract()
    {
        return $this->belongsTo(CateringContract::class, 'catering_contract_id');
    }

    public function days()
    {
        return $this->hasMany(CateringMenuDay::class)->orderBy('date');
    }

    public function getWeekLabelAttribute(): string
    {
        return 'Semaine du ' . $this->week_start_date->format('d/m/Y')
            . ' au ' . $this->week_start_date->copy()->addDays(6)->format('d/m/Y');
    }
}
