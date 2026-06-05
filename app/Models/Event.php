<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'event_type', 'event_date', 'guest_count',
        'stock_id', 'total_amount', 'status',
        'validated_at', 'completed_at', 'transaction_id',
    ];

    protected $casts = [
        'event_date'   => 'date',
        'total_amount' => 'decimal:2',
        'validated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function client()       { return $this->belongsTo(Client::class); }
    public function stock()        { return $this->belongsTo(Stock::class); }
    public function transaction()  { return $this->belongsTo(Transaction::class); }
    public function serviceItems() { return $this->hasMany(EventServiceItem::class); }
    public function eventMeals()   { return $this->hasMany(EventMeal::class); }
    public function options()      { return $this->hasMany(EventOption::class); }
    public function stockUsages()  { return $this->hasMany(EventStockUsage::class); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'       => 'Brouillon',
            'validated'   => 'Validé',
            'in_progress' => 'En cours',
            'completed'   => 'Clôturé',
            'cancelled'   => 'Annulé',
            default       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'       => 'bg-slate-100 text-slate-600',
            'validated'   => 'bg-blue-100 text-blue-700',
            'in_progress' => 'bg-amber-100 text-amber-700',
            'completed'   => 'bg-emerald-100 text-emerald-700',
            'cancelled'   => 'bg-red-100 text-red-600',
            default       => 'bg-slate-100 text-slate-600',
        };
    }
}
