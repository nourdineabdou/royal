<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id', 'job_title_id', 'first_name', 'last_name',
        'phone', 'address', 'hire_date', 'salary_base', 'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary_base' => 'decimal:2',
    ];

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function advances()
    {
        return $this->hasMany(Advance::class);
    }

    public function salaryAdjustments()
    {
        return $this->hasMany(SalaryAdjustment::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
