<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name', 'legal_form', 'nif', 'rc',
        'address', 'phone', 'email', 'logo',
        'fiscal_year_start', 'fiscal_year_end',
        'tax_regime', 'bank_name', 'rib',
        'currency', 'status',
    ];

    protected $casts = [
        'fiscal_year_start' => 'date',
        'fiscal_year_end'   => 'date',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function activeSites(): HasMany
    {
        return $this->hasMany(Site::class)->where('status', 'active');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cashRegisters(): HasMany
    {
        return $this->hasMany(CashRegister::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function cateringContracts(): HasMany
    {
        return $this->hasMany(CateringContract::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
