<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advance extends Model
{
    protected $fillable = [
        'employee_id', 'amount', 'repayment_percentage', 'remaining_balance', 'date', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'repayment_percentage' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /** Montant à déduire ce mois-ci pour un salaire de base donné (plafonné au solde restant). */
    public function monthlyDeductionFor(float $baseSalary): float
    {
        $target = round($baseSalary * (float) $this->repayment_percentage / 100, 2);
        return min((float) $this->remaining_balance, max(0, $target));
    }
}
