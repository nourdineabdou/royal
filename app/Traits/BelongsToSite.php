<?php

namespace App\Traits;

use App\Models\Site;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait BelongsToSite
 *
 * À ajouter sur les modèles qui possèdent une colonne site_id.
 */
trait BelongsToSite
{
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeForSite($query, ?int $siteId = null)
    {
        $id = $siteId ?? session('current_site_id');

        if ($id) {
            return $query->where($this->getTable() . '.site_id', $id);
        }

        return $query;
    }

    protected static function bootBelongsToSite(): void
    {
        static::creating(function ($model) {
            if (empty($model->site_id) && session()->has('current_site_id')) {
                $model->site_id = session('current_site_id');
            }
        });
    }
}
