<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryAdjustment extends Model
{
    protected $fillable = ['employee_id', 'type', 'amount', 'reason', 'date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
