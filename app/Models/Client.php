<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'company', 'notes'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function cateringContracts()
    {
        return $this->hasMany(CateringContract::class);
    }
}
