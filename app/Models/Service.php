<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'price', 'type'];

    protected $casts = ['price' => 'decimal:2'];

    public function eventServiceItems()
    {
        return $this->hasMany(EventServiceItem::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'hall'      => 'Salle',
            'equipment' => 'Équipement',
            'logistic'  => 'Logistique',
            default     => $this->type,
        };
    }
}
