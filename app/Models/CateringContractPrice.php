<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringContractPrice extends Model
{
    protected $fillable = ['catering_contract_id', 'type', 'price'];

    protected $casts = ['price' => 'decimal:2'];

    public function contract()
    {
        return $this->belongsTo(CateringContract::class, 'catering_contract_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'breakfast' => 'Petit-déjeuner',
            'lunch'     => 'Déjeuner',
            'dinner'    => 'Dîner',
            default     => $this->type,
        };
    }
}
