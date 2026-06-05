<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class CateringContract extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'start_date', 'end_date', 'guest_count',
        'active_days', 'has_breakfast', 'has_lunch', 'has_dinner', 'status',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'active_days'   => 'array',
        'has_breakfast' => 'boolean',
        'has_lunch'     => 'boolean',
        'has_dinner'    => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function prices()
    {
        return $this->hasMany(CateringContractPrice::class);
    }

    public function weeklyMenus()
    {
        return $this->hasMany(CateringWeeklyMenu::class);
    }

    public function invoices()
    {
        return $this->hasMany(CateringInvoice::class);
    }

    public function getPriceFor(string $type): float
    {
        return (float)($this->prices->firstWhere('type', $type)?->price ?? 0);
    }

    public function getActiveMealTypes(): array
    {
        $types = [];
        if ($this->has_breakfast) $types[] = 'breakfast';
        if ($this->has_lunch)     $types[] = 'lunch';
        if ($this->has_dinner)    $types[] = 'dinner';

        // Fallback legacy: déduire les types depuis les prix quand les booléens ne sont pas renseignés.
        if (empty($types)) {
            $priceTypes = $this->relationLoaded('prices')
                ? $this->prices->pluck('type')->all()
                : $this->prices()->pluck('type')->all();

            $allowed = ['breakfast', 'lunch', 'dinner'];
            $types = array_values(array_unique(array_values(array_filter($priceTypes, fn ($t) => in_array($t, $allowed, true)))));
        }

        // Dernier fallback: afficher les 3 types pour ne pas bloquer la programmation.
        if (empty($types)) {
            $types = ['breakfast', 'lunch', 'dinner'];
        }

        return $types;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Actif',
            'paused' => 'En pause',
            'ended'  => 'Terméiné',
            default  => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-100 text-emerald-700',
            'paused' => 'bg-amber-100 text-amber-700',
            'ended'  => 'bg-slate-100 text-slate-500',
            default  => 'bg-slate-100 text-slate-600',
        };
    }
}
