<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'chart_of_account_id', 'name', 'is_fixed_amount', 'fixed_amount', 'is_active',
    ];

    protected $casts = [
        'is_fixed_amount' => 'boolean',
        'is_active'        => 'boolean',
        'fixed_amount'     => 'decimal:2',
    ];

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
