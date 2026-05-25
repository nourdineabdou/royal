<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Transaction extends Model
{
    protected $fillable = ['type', 'module', 'amount', 'reference', 'date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];
}
