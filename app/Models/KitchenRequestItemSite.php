<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Part d'un produit reçu par un cuisinier, attribuée à un site (consommation par site). */
class KitchenRequestItemSite extends Model
{
    protected $fillable = ['kitchen_request_item_id', 'site_id', 'quantity'];

    protected $casts = ['quantity' => 'decimal:2'];

    public function item()
    {
        return $this->belongsTo(KitchenRequestItem::class, 'kitchen_request_item_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
