<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'phone', 'email', 'company', 'notes'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function cateringContracts()
    {
        return $this->hasMany(CateringContract::class);
    }
}
