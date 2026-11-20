<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Demande de cuisine au magasin.
 *
 * pending   : demandée par la cuisine, à préparer par le magasinier
 * prepared  : remise par le magasinier (quantités réservées), en attente de validation du cuisinier
 * validated : le cuisinier a pris la marchandise → stock diminué, bon de sortie (BS) créé
 * cancelled : annulée par la cuisine avant préparation
 */
class KitchenRequest extends Model
{
    public const STATUSES = [
        'pending'   => 'À préparer',
        'prepared'  => 'À confirmer par la cuisine',
        'validated' => 'Sortie validée',
        'cancelled' => 'Annulée',
    ];

    protected $fillable = [
        'kitchen', 'stock_id', 'status', 'needed_on', 'notes', 'requested_by',
        'prepared_by', 'prepared_at', 'receiver_id', 'validated_by', 'validated_at', 'contest_note', 'bs_number',
    ];

    protected $casts = [
        'needed_on'    => 'date',
        'prepared_at'  => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(KitchenRequestItem::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function preparer()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /** Cuisinier qui reçoit la marchandise, choisi par le magasinier. */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /** Seul le cuisinier choisi confirme la sortie (l'admin peut dépanner). Anciennes sorties sans réceptionnaire : tout cuisinier. */
    public function canBeTakenBy(?User $user): bool
    {
        if (!$user || $this->status !== 'prepared' || !$user->can('kitchen.requests.validate')) {
            return false;
        }

        return $this->receiver_id === null || $this->receiver_id === $user->id || $user->hasRole(['admin', 'super-admin']);
    }

    /** Les cuisiniers parmi lesquels le magasinier choisit le réceptionnaire. */
    public static function cooks()
    {
        return User::role('cuisinier')->orderBy('name')->get(['id', 'name']);
    }

    /** N° de la demande, ex : DC-00012. */
    public function getReferenceAttribute(): string
    {
        return 'DC-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Quantités déjà remises mais pas encore validées par une cuisine (réservées), par produit. */
    public static function reservedQuantities(int $stockId, ?int $exceptRequestId = null): array
    {
        return KitchenRequestItem::query()
            ->join('kitchen_requests', 'kitchen_requests.id', '=', 'kitchen_request_items.kitchen_request_id')
            ->where('kitchen_requests.status', 'prepared')
            ->where('kitchen_requests.stock_id', $stockId)
            ->when($exceptRequestId, fn ($q) => $q->where('kitchen_requests.id', '!=', $exceptRequestId))
            ->groupBy('kitchen_request_items.product_id')
            ->selectRaw('kitchen_request_items.product_id, SUM(kitchen_request_items.delivered_quantity) as qty')
            ->pluck('qty', 'product_id')
            ->map(fn ($q) => (float) $q)
            ->all();
    }

    /** Cuisines déjà utilisées (suggestions). */
    public static function knownKitchens(): array
    {
        return static::query()->distinct()->orderBy('kitchen')->pluck('kitchen')
            ->merge(['Cuisine TCN', 'Cuisine principale', 'Pain'])
            ->unique()->values()->all();
    }
}
