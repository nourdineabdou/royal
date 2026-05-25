<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address'];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(SupplierPayment::class, PurchaseOrder::class);
    }

    public function getTotalDebtAttribute()
    {
        return $this->purchaseOrders()->sum('remaining_amount');
    }
}
