<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringConsumption extends Model
{
    protected $fillable = [
        'catering_meal_code_id', 'consumed_at', 'user_id', 'transaction_id',
    ];

    protected $casts = [
        'consumed_at' => 'datetime',
    ];

    public function mealCode()
    {
        return $this->belongsTo(CateringMealCode::class, 'catering_meal_code_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
