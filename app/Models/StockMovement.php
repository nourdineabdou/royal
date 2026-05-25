<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'stock_id', 'type', 'quantity',
        'source_stock_id', 'destination_stock_id',
        'origin_module', 'origin_type', 'origin_id',
        'user_id', 'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function sourceStock()
    {
        return $this->belongsTo(Stock::class, 'source_stock_id');
    }

    public function destinationStock()
    {
        return $this->belongsTo(Stock::class, 'destination_stock_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in'       => 'Entrée',
            'out'      => 'Sortie',
            'transfer' => 'Transfert',
            default    => $this->type,
        };
    }

    public function getOriginModuleLabelAttribute(): string
    {
        return match ($this->origin_module) {
            'restaurant' => 'Restaurant',
            'catering'   => 'Catering',
            'events'     => 'Événements',
            'transfer'   => 'Transfert',
            'purchase'   => 'Achat',
            'manual'     => 'Manuel',
            default      => $this->origin_module ?? '—',
        };
    }
}
