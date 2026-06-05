<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'phone', 'email', 'address'];

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
