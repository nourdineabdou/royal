<?php

namespace App\Services;

use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

/**
 * Tient à jour UNE demande d'achat brouillon (« DA automatique ») contenant tous les produits
 * du Stock Catering passés sous leur seuil et qui ne sont pas déjà en commande.
 *
 * - un produit qui passe sous le seuil est ajouté avec la quantité proposée ;
 * - un produit qui n'en a plus besoin (stock remonté, commande passée) est retiré ;
 * - une quantité modifiée par le magasinier dans le brouillon n'est jamais écrasée.
 * Le magasinier relit le brouillon et l'envoie : il devient une DA normale (« En attente »).
 */
class AutoPurchaseRequestService
{
    public function __construct(private StockAlertService $alerts)
    {
    }

    public function draft(): ?PurchaseRequest
    {
        return PurchaseRequest::where('auto_generated', true)->where('status', 'draft')->latest('id')->first();
    }

    public function sync(): ?PurchaseRequest
    {
        $needed = $this->alerts->alerts()->filter(fn ($a) => $a['needs_order'])->keyBy(fn ($a) => $a['product']->id);

        return DB::transaction(function () use ($needed) {
            $draft = PurchaseRequest::where('auto_generated', true)->where('status', 'draft')->lockForUpdate()->latest('id')->first();

            // Les lignes ajoutées à la main restent : le brouillon n'est supprimé que s'il ne contient plus rien d'utile
            $manual = $draft ? $draft->items()->where('added_manually', true)->exists() : false;
            if ($needed->isEmpty() && !$manual) {
                $draft?->delete();

                return null;
            }

            $draft ??= $this->newDraft();

            $existing = $draft->items()->get()->keyBy('product_id');

            // Retirer ce qui n'est plus nécessaire (sauf les lignes ajoutées à la main)
            $existing->each(function ($item, $pid) use ($needed) {
                if (!$needed->has($pid) && !$item->added_manually) {
                    $item->delete();
                }
            });

            // Ajouter les nouveaux produits sous le seuil (sans toucher aux quantités déjà présentes)
            foreach ($needed as $pid => $a) {
                if (!$existing->has($pid)) {
                    $draft->items()->create([
                        'product_id' => $pid,
                        'quantity'   => $a['suggested'],
                        'notes'      => $a['suggested_label'],
                    ]);
                }
            }

            return $draft->fresh('items');
        });
    }

    /** Le brouillon en cours, créé s'il n'existe pas (pour y ajouter des produits à la main). */
    public function openDraft(): PurchaseRequest
    {
        return DB::transaction(fn () => PurchaseRequest::where('auto_generated', true)->where('status', 'draft')->lockForUpdate()->latest('id')->first()
            ?? $this->newDraft());
    }

    private function newDraft(): PurchaseRequest
    {
        return PurchaseRequest::create([
            'reference'      => 'DA-' . now()->format('ymd') . '-AUTO' . strtoupper(substr(uniqid(), -3)),
            'requested_by'   => null,
            'status'         => 'draft',
            'auto_generated' => true,
            'notes'          => 'Préparée automatiquement : produits du Stock Catering sous leur seuil minimum.',
        ]);
    }

    /** Appel « sans risque » : une erreur ici ne doit jamais bloquer une opération de stock. */
    public function syncQuietly(): void
    {
        try {
            $this->sync();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
