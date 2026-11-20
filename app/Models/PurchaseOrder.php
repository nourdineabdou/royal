<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'supplier_id', 'purchase_request_id', 'event_id', 'is_direct', 'reference', 'total_amount', 'paid_amount',
        'remaining_amount', 'payment_status', 'status', 'invoice_validated_at', 'invoice_validated_by',
    ];

    protected $casts = [
        'total_amount'         => 'decimal:2',
        'paid_amount'          => 'decimal:2',
        'remaining_amount'     => 'decimal:2',
        'invoice_validated_at' => 'datetime',
        'is_direct'            => 'boolean',
    ];

    /** Origine du BC, pour filtrer les réceptions. */
    public const ORIGINS = [
        'request' => 'Sur demande d\'achat',
        'direct'  => 'Réception directe',
        'manual'  => 'BC sans demande d\'achat',
        'event'   => 'Événement',
    ];

    public function getOriginAttribute(): string
    {
        return match (true) {
            (bool) $this->purchase_request_id => 'request',
            (bool) $this->is_direct           => 'direct',
            (bool) $this->event_id            => 'event',
            default                            => 'manual',
        };
    }

    /** Filtre « origine » appliqué à une requête sur purchase_orders. */
    public function scopeOfOrigin($q, string $origin)
    {
        return match ($origin) {
            'request' => $q->whereNotNull('purchase_request_id'),
            'direct'  => $q->whereNull('purchase_request_id')->where('is_direct', true),
            'event'   => $q->whereNull('purchase_request_id')->where('is_direct', false)->whereNotNull('event_id'),
            'manual'  => $q->whereNull('purchase_request_id')->where('is_direct', false)->whereNull('event_id'),
            default   => $q,
        };
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function invoiceValidatedBy()
    {
        return $this->belongsTo(User::class, 'invoice_validated_by');
    }

    /** La demande d'achat d'origine, si ce BC en découle (voir PurchaseRequest). */
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    /** Devis, facture, BL… joints au bon de commande (photo ou scan). */
    public function attachments()
    {
        return $this->hasMany(PurchaseOrderAttachment::class)->latest();
    }

    /** Vrai si au moins une ligne n'a pas encore de prix (BC créé avant de connaître les prix). */
    public function hasMissingPrices(): bool
    {
        return $this->items->contains(fn ($i) => $i->price === null);
    }

    public function supplierReturns()
    {
        return $this->hasMany(SupplierReturn::class);
    }

    public function isReceived()
    {
        return $this->status === 'received';
    }
}
