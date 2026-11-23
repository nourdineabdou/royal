<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Demande de cuisine au magasin.
 *
 * Approbation du responsable des achats : seulement si un produit dépasse le seuil (30 % par défaut) de son stock
 * (thresholdPercent(), modifiable dans l'application). Il voit le stock, ce qui sort et ce qui reste, et peut corriger les quantités.
 *
 * to_approve (prepared_at vide)  : demande de la cuisine importante, à approuver AVANT d'aller au magasinier
 * pending    : à préparer par le magasinier (demande normale, ou importante déjà approuvée)
 * to_approve (prepared_at rempli): sortie préparée par le magasinier, importante, à approuver avant la remise
 * prepared   : remise, en attente de la validation du cuisinier (« j'ai reçu »)
 * validated  : le cuisinier a pris la marchandise → stock diminué, bon de sortie (BS) créé
 * cancelled  : annulée
 */
class KitchenRequest extends Model
{
    public const STATUSES = [
        'pending'    => 'À préparer',
        'to_approve' => 'À approuver (resp. achats)',
        'prepared'   => 'À confirmer par la cuisine',
        'validated'  => 'Sortie validée',
        'cancelled'  => 'Annulée',
    ];

    /** Statuts où les quantités remises sont réservées (préparées, pas encore sorties du stock). */
    public const RESERVING_STATUSES = ['to_approve', 'prepared'];

    protected $fillable = [
        'kitchen', 'stock_id', 'status', 'needed_on', 'notes', 'requested_by',
        'prepared_by', 'prepared_at', 'receiver_id', 'approved_by', 'approved_at',
        'validated_by', 'validated_at', 'contest_note', 'bs_number',
    ];

    protected $casts = [
        'needed_on'    => 'date',
        'prepared_at'  => 'datetime',
        'approved_at'  => 'datetime',
        'validated_at' => 'datetime',
    ];

    /** Responsable des achats qui a approuvé la sortie. */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

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

    /**
     * Seuil d'approbation : une sortie (ou une demande) qui dépasse ce pourcentage du stock d'un produit
     * doit être approuvée par le responsable des achats. En dessous, pas d'approbation.
     * Modifiable à tout moment depuis la page « Sorties à approuver » (paramètre en base).
     */
    public const THRESHOLD_SETTING = 'kitchen.approval_threshold_percent';
    public const DEFAULT_THRESHOLD_PERCENT = 30;

    /** Seuil en pourcentage, ex : 30. */
    public static function thresholdPercent(): float
    {
        return (float) Setting::get(self::THRESHOLD_SETTING, self::DEFAULT_THRESHOLD_PERCENT);
    }

    /** « 30 % », « 12,5 % » */
    public static function thresholdLabel(): string
    {
        return rtrim(rtrim(number_format(self::thresholdPercent(), 2, ',', ''), '0'), ',') . ' %';
    }

    public static function exceedsThreshold(float $qty, float $inStock): bool
    {
        return $qty > 0 && $qty > $inStock * self::thresholdPercent() / 100 + 0.0001;
    }

    /** Avant préparation (demande de la cuisine) / après préparation (sortie du magasinier). */
    public function isPrepared(): bool
    {
        return $this->prepared_at !== null;
    }

    /** « Riz 5 kg, Huile 2 L » : quantités remises si la sortie est préparée, sinon quantités demandées. */
    public function summary(string $separator = ', '): string
    {
        $column = $this->isPrepared() ? 'delivered_quantity' : 'requested_quantity';

        return $this->items->filter(fn ($i) => (float) $i->{$column} > 0)
            ->map(fn ($i) => ($i->product->name ?? '—') . ' ' . \App\Support\Qty::format($i->{$column}, $i->product->unit->symbol ?? ''))
            ->implode($separator);
    }

    /** Stock physique par produit : dans un magasin, ou tous magasins confondus si $stockId est null. */
    public static function stockQuantities(?int $stockId, $productIds): array
    {
        return StockItem::query()
            ->when($stockId, fn ($q) => $q->where('stock_id', $stockId))
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->pluck('qty', 'product_id')
            ->map(fn ($q) => (float) $q)
            ->all();
    }

    /** Quantités déjà remises mais pas encore validées par une cuisine (réservées), par produit ($stockId null = tous magasins). */
    public static function reservedQuantities(?int $stockId, ?int $exceptRequestId = null): array
    {
        return KitchenRequestItem::query()
            ->join('kitchen_requests', 'kitchen_requests.id', '=', 'kitchen_request_items.kitchen_request_id')
            ->whereIn('kitchen_requests.status', self::RESERVING_STATUSES)
            ->when($stockId, fn ($q) => $q->where('kitchen_requests.stock_id', $stockId))
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
