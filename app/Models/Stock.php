<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['name', 'location', 'module'];

    /** The modules that must each be linked to exactly one stock. */
    public const MODULES = [
        'restaurant' => 'Restaurant',
        'catering'   => 'Catering',
        'events'     => 'Événements',
    ];

    public function items()
    {
        return $this->hasMany(StockItem::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /** Return the stock linked to a given module, or null. */
    public static function forModule(string $module): ?self
    {
        return static::where('module', $module)->first();
    }

    public function getModuleLabelAttribute(): string
    {
        return self::MODULES[$this->module] ?? ($this->module ?? '—');
    }
}
