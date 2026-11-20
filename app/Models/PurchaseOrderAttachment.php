<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Document joint à un bon de commande (devis, facture, BL…), stocké dans le disque privé. */
class PurchaseOrderAttachment extends Model
{
    public const TYPES = [
        'devis'   => 'Devis',
        'facture' => 'Facture',
        'bl'      => 'Bon de livraison',
        'autre'   => 'Autre',
    ];

    protected $fillable = ['purchase_order_id', 'type', 'path', 'original_name', 'mime', 'size', 'uploaded_by'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }
}
