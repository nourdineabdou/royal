<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'type', 'module', 'amount', 'reference', 'date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];
}
