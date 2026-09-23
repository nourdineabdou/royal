<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'employee_id', 'month', 'year',
        'base_salary', 'bonus', 'deduction', 'advance_deduction', 'payment_type_id',
        'net_salary', 'status', 'paid_at',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }
}
