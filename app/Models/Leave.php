<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'employee_id', 'type', 'start_date', 'end_date', 'days', 'status', 'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
