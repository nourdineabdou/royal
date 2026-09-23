<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeContract extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'start_date', 'end_date', 'trial_period_end', 'salary', 'status', 'notes',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'end_date'          => 'date',
        'trial_period_end'  => 'date',
        'salary'            => 'decimal:2',
    ];

    public const TYPES = [
        'cdi'         => 'CDI',
        'cdd'         => 'CDD',
        'stage'       => 'Stage',
        'prestation'  => 'Prestation',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getIsEndingSoonAttribute(): bool
    {
        if (!$this->end_date || $this->status !== 'active') {
            return false;
        }
        return now()->diffInDays($this->end_date, false) <= 30 && now()->diffInDays($this->end_date, false) >= 0;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->status === 'active' && $this->end_date->isPast();
    }

    public function getIsInTrialAttribute(): bool
    {
        return $this->trial_period_end && $this->status === 'active' && $this->trial_period_end->isFuture();
    }
}
