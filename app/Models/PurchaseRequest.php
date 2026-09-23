<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $fillable = ['reference', 'requested_by', 'status', 'notes'];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
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
     * Recalcule le statut global à partir de l'état des lignes :
     * draft (pas encore soumise) reste inchangé, sinon pending/partially_ordered/ordered.
     */
    public function refreshStatus(): void
    {
        if ($this->status === 'draft' || $this->status === 'cancelled') {
            return;
        }

        $items = $this->items;
        $allOrdered = $items->every(fn ($i) => (float) $i->ordered_quantity >= (float) $i->quantity);
        $anyOrdered = $items->contains(fn ($i) => (float) $i->ordered_quantity > 0);

        $this->update([
            'status' => $allOrdered ? 'ordered' : ($anyOrdered ? 'partially_ordered' : 'pending'),
        ]);
    }
}
