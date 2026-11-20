<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Demande d'achat (DA).
 *
 * Circuit : draft (DA automatique pas encore envoyée)
 *   → pending      : envoyée par le magasinier, à approuver par le responsable achat
 *   → to_validate  : approuvée par l'acheteur (ou créée par lui), à valider par le responsable des achats
 *   → approved     : validée par la RH, le responsable achat peut commander (devis, BC par fournisseur)
 *   → partially_ordered / ordered
 *   → rejected     : refusée (motif obligatoire) · cancelled : annulée
 */
class PurchaseRequest extends Model
{
    public const STATUSES = [
        'draft'             => 'Brouillon',
        'pending'           => 'À approuver (acheteur)',
        'to_validate'       => 'À valider (resp. des achats)',
        'approved'          => 'Validée — à commander',
        'partially_ordered' => 'Partiellement commandée',
        'ordered'           => 'Entièrement commandée',
        'rejected'          => 'Refusée',
        'cancelled'         => 'Annulée',
    ];

    public const BADGES = [
        'draft'             => 'bg-slate-100 text-slate-600',
        'pending'           => 'bg-amber-100 text-amber-700',
        'to_validate'       => 'bg-violet-100 text-violet-700',
        'approved'          => 'bg-sky-100 text-sky-700',
        'partially_ordered' => 'bg-blue-100 text-blue-700',
        'ordered'           => 'bg-emerald-100 text-emerald-700',
        'rejected'          => 'bg-red-100 text-red-700',
        'cancelled'         => 'bg-red-100 text-red-700',
    ];

    /** Statuts où la DA représente un besoin en cours (compté comme « en commande »). */
    public const OPEN = ['pending', 'to_validate', 'approved', 'partially_ordered'];

    protected $fillable = [
        'reference', 'requested_by', 'status', 'auto_generated', 'notes',
        'achat_approved_by', 'achat_approved_at', 'rh_approved_by', 'rh_approved_at',
        'rejected_by', 'rejected_at', 'rejection_reason',
    ];

    protected $casts = [
        'auto_generated'    => 'boolean',
        'achat_approved_at' => 'datetime',
        'rh_approved_at'    => 'datetime',
        'rejected_at'       => 'datetime',
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function achatApprover()
    {
        return $this->belongsTo(User::class, 'achat_approved_by');
    }

    public function rhApprover()
    {
        return $this->belongsTo(User::class, 'rh_approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    /** Les BC (un par fournisseur) créés à partir de cette demande. */
    public function orders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /** Devis reçus des fournisseurs consultés pour cette demande, avant de choisir. */
    public function quotes()
    {
        return $this->hasMany(SupplierQuote::class);
    }

    /**
     * Demandé (DA) / commandé (BC) / reçu (BL), produit par produit, en unité de base,
     * tous BC et toutes réceptions de la DA confondus.
     *
     * État de chaque ligne :
     *  ok       : reçu = demandé
     *  waiting  : reçu < demandé, mais une livraison est encore attendue (BC ouvert)
     *  short    : reçu < demandé, plus rien n'est attendu → manque
     *  over     : reçu > demandé → surplus
     *  extra    : reçu alors que le produit n'était pas dans la DA
     *
     * @return array{lines: array<int, array>, status: string}
     */
    public function receptionComparison(): array
    {
        $this->loadMissing([
            'items.product.unit', 'items.product.productPackagings.packaging',
            'orders.items.packaging', 'orders.items.product.unit', 'orders.items.product.productPackagings.packaging',
            'orders.goodsReceipts.items.product.unit',
        ]);

        $orders = $this->orders->where('status', '!=', 'cancelled');
        $open   = $orders->whereIn('status', ['pending', 'ordered', 'partial']);
        $rows   = [];

        foreach ($this->items as $it) {
            $rows[$it->product_id] = ['product' => $it->product, 'requested' => (float) $it->quantity, 'ordered' => 0.0, 'received' => 0.0, 'open' => false];
        }
        foreach ($orders as $o) {
            foreach ($o->items as $oi) {
                $rows[$oi->product_id] ??= ['product' => $oi->product, 'requested' => 0.0, 'ordered' => 0.0, 'received' => 0.0, 'open' => false];
                $rows[$oi->product_id]['ordered'] += (float) $oi->quantity * ($oi->perPack() ?? 1);
                if ($open->contains('id', $o->id)) {
                    $rows[$oi->product_id]['open'] = true;
                }
            }
            foreach ($o->goodsReceipts as $gr) {
                foreach ($gr->items as $gi) {
                    $rows[$gi->product_id] ??= ['product' => $gi->product, 'requested' => 0.0, 'ordered' => 0.0, 'received' => 0.0, 'open' => false];
                    $rows[$gi->product_id]['received'] += (float) $gi->quantity;
                }
            }
        }

        $lines = [];
        foreach ($rows as $pid => $r) {
            $diff = round($r['received'] - $r['requested'], 2);
            $state = match (true) {
                $r['requested'] <= 0 && $r['received'] > 0 => 'extra',
                abs($diff) < 0.01                          => 'ok',
                $diff > 0                                  => 'over',
                $r['open']                                 => 'waiting',
                default                                    => 'short',
            };
            $lines[] = $r + ['product_id' => $pid, 'diff' => $diff, 'state' => $state];
        }

        $states = array_column($lines, 'state');
        $status = match (true) {
            (bool) array_intersect($states, ['short', 'over', 'extra']) => 'gap',
            in_array('waiting', $states, true)                          => 'waiting',
            default                                                     => 'ok',
        };

        return ['lines' => $lines, 'status' => $status];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::BADGES[$this->status] ?? 'bg-slate-100 text-slate-600';
    }

    /** Les commandes ne sont possibles qu'après la validation RH. */
    public function canBeOrdered(): bool
    {
        return in_array($this->status, ['approved', 'partially_ordered']);
    }

    /**
     * Statut d'une DA au moment où elle est envoyée : le responsable achat (droit « approuver »)
     * l'approuve en la créant, elle part directement à la validation RH.
     */
    /** Ce qui se passe ensuite, selon le statut de départ. */
    public static function nextStepText(string $status): string
    {
        return match ($status) {
            'approved'    => 'validée d’office (responsable des achats) : le bon de commande peut être fait.',
            'to_validate' => 'en attente de la validation du responsable des achats.',
            default       => 'en attente de l’approbation de l’acheteur.',
        };
    }

    public static function submittedStatusFor(?User $user): array
    {
        // Responsable des achats (ou Direction) : c'est lui qui valide → la DA est validée d'office
        if ($user?->can('purchases.requests.validate')) {
            return [
                'status'            => 'approved',
                'achat_approved_by' => $user->id, 'achat_approved_at' => now(),
                'rh_approved_by'    => $user->id, 'rh_approved_at'    => now(),
            ];
        }
        // Acheteur : approuvée par lui, elle part à la validation du responsable des achats
        if ($user?->can('purchases.requests.approve')) {
            return ['status' => 'to_validate', 'achat_approved_by' => $user->id, 'achat_approved_at' => now()];
        }

        return ['status' => 'pending'];
    }

    /**
     * Recalcule le statut après une commande : partiellement / entièrement commandée.
     * Tant que rien n'est commandé, une DA validée reste « validée ».
     */
    public function refreshStatus(): void
    {
        if (!in_array($this->status, ['approved', 'partially_ordered', 'ordered'])) {
            return;
        }

        $items = $this->items;
        $allOrdered = $items->every(fn ($i) => (float) $i->ordered_quantity >= (float) $i->quantity);
        $anyOrdered = $items->contains(fn ($i) => (float) $i->ordered_quantity > 0);

        $this->update([
            'status' => $allOrdered ? 'ordered' : ($anyOrdered ? 'partially_ordered' : 'approved'),
        ]);
    }
}
