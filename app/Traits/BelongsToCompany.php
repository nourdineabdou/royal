<?php

namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait BelongsToCompany
 *
 * À ajouter sur tout modèle qui possède une colonne company_id.
 * Fournit :
 *   - La relation company()
 *   - Le scope scopeForCompany($query, $companyId)
 *   - L'auto-remplissage de company_id à la création si l'utilisateur
 *     connecté a une company courante en session
 */
trait BelongsToCompany
{
    // ── Relation ───────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /**
     * Filtre sur une société donnée.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int|null  $companyId  Si null, utilise la société en session
     */
    public function scopeForCompany($query, ?int $companyId = null)
    {
        $id = $companyId ?? session('current_company_id');

        if ($id) {
            return $query->where($this->getTable() . '.company_id', $id);
        }

        return $query;
    }

    // ── Boot : auto-fill company_id ────────────────────────────────────────

    protected static function bootBelongsToCompany(): void
    {
        static::creating(function ($model) {
            if (empty($model->company_id) && session()->has('current_company_id')) {
                $model->company_id = session('current_company_id');
            }
        });
    }
}
