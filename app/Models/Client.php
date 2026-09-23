<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'phone', 'email', 'company', 'notes', 'address', 'latitude', 'longitude'];

    protected $casts = [
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function getHasLocationAttribute(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function cateringContracts()
    {
        return $this->hasMany(CateringContract::class);
    }

    public function cateringInvoices()
    {
        return $this->hasMany(CateringInvoice::class);
    }
}
