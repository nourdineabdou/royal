<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    protected $fillable = ['name', 'description', 'is_active', 'chart_of_account_id'];

    /** Mots-clés des modes de paiement autorisés en point de vente (POS/caisse). */
    public const POS_KEYWORDS = [
        'espece',
        'espèce',
        'bankili',
        'bankily',
        'sadad',
        'masrivi',
        'amanaty',
        'click',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    /** Types de paiement autorisés en point de vente, pour peupler les boutons/listes. */
    public static function posAllowed()
    {
        return static::where(function ($query) {
            foreach (self::POS_KEYWORDS as $keyword) {
                $query->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($keyword) . '%']);
            }
        })->orderBy('name')->get();
    }

    public static function isPosAllowed(int $paymentTypeId): bool
    {
        return static::where('id', $paymentTypeId)
            ->where(function ($query) {
                foreach (self::POS_KEYWORDS as $keyword) {
                    $query->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($keyword) . '%']);
                }
            })->exists();
    }
}
