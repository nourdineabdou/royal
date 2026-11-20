<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderAttachment;
use App\Models\Stock;
use App\Services\AccountingEntryService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Bon de commande :
 *  - documents joints (devis, facture, BL) envoyés en photo ou en scan, sans saisie ligne par ligne ;
 *  - saisie des prix après coup, quand le devis ou la facture arrive.
 */
class PurchaseOrderDocumentController extends Controller
{
    public const FILE_RULES = 'file|mimes:jpg,jpeg,png,webp,heic,heif,pdf|max:15360'; // 15 Mo

    public function store(Request $request, PurchaseOrder $order)
    {
        $this->anyPerm(['purchases.orders.create', 'purchases.orders.receipt', 'purchases.orders.validate-invoice']);
        $data = $request->validate([
            'type'    => 'required|in:' . implode(',', array_keys(PurchaseOrderAttachment::TYPES)),
            'files'   => 'required|array|min:1|max:10',
            'files.*' => self::FILE_RULES,
        ], [
            'files.required' => 'Choisissez au moins un fichier (photo ou PDF).',
            'files.*.mimes'  => 'Formats acceptés : photo (JPG, PNG, HEIC) ou PDF.',
            'files.*.max'    => 'Un fichier dépasse 15 Mo.',
        ]);

        $n = self::storeFiles($order, $data['files'], $data['type']);

        return back()->with('success', $n . ' document(s) joint(s) au bon de commande.');
    }

    /** Enregistre des fichiers envoyés pour un BC (utilisé aussi à la création du BC). */
    public static function storeFiles(PurchaseOrder $order, array $files, string $type): int
    {
        $n = 0;
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }
            $order->attachments()->create([
                'type'          => $type,
                'path'          => $file->store('purchase-orders/' . $order->id, 'local'),
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 250),
                'mime'          => $file->getMimeType(),
                'size'          => $file->getSize(),
                'uploaded_by'   => auth()->id(),
            ]);
            $n++;
        }

        return $n;
    }

    /** Ouvre le document dans le navigateur (fichiers privés : jamais d'accès direct par URL publique). */
    public function show(PurchaseOrderAttachment $attachment)
    {
        $this->anyPerm(['purchases.orders.view', 'purchases.orders.receipt']);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404, 'Fichier introuvable.');

        return Storage::disk('local')->response($attachment->path, $attachment->original_name, [
            'Content-Type'        => $attachment->mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($attachment->original_name) . '"',
        ]);
    }

    public function destroy(PurchaseOrderAttachment $attachment)
    {
        $user = auth()->user();
        abort_unless($attachment->uploaded_by === $user?->id || $user?->can('purchases.orders.create') || $user?->hasRole(['admin', 'super-admin']), 403);

        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Document supprimé.');
    }

    /**
     * Saisie / correction des prix du BC (par emballage commandé), une fois le devis ou la facture reçu.
     * Verrouillée dès que la facture est validée par le responsable des achats.
     */
    public function updatePrices(Request $request, PurchaseOrder $order, AccountingEntryService $accounting)
    {
        $this->perm('purchases.orders.create');
        if ($order->invoice_validated_at) {
            return back()->with('error', 'La facture est déjà validée : les prix ne peuvent plus être modifiés.');
        }

        $data = $request->validate([
            'prices'   => 'required|array',
            'prices.*' => 'nullable|numeric|min:0|max:999999999',
        ]);

        DB::transaction(function () use ($order, $data, $accounting) {
            $order->load('items', 'goodsReceipts.items', 'goodsReceipts.stock');
            foreach ($order->items as $item) {
                if (!array_key_exists($item->id, $data['prices'])) {
                    continue;
                }
                $price = $data['prices'][$item->id];
                $price = ($price === null || $price === '') ? null : round((float) $price, 2);
                $item->update(['price' => $price, 'total' => $price !== null ? round($price * (float) $item->quantity, 2) : null]);
            }

            $total = round((float) $order->items()->sum('total'), 2);
            $paid = (float) $order->paid_amount;
            $order->update([
                'total_amount'     => $total,
                'remaining_amount' => max(0, round($total - $paid, 2)),
                'payment_status'   => $paid <= 0 ? 'unpaid' : ($paid + 0.001 >= $total ? 'paid' : 'partial'),
            ]);

            // Réceptions déjà faites sans prix : on passe maintenant l'écriture d'achat (une seule fois par BE)
            $prices = $order->items()->pluck('price', 'id');
            foreach ($order->goodsReceipts as $receipt) {
                $amount = $receipt->items->sum(fn ($ri) => (float) $ri->received_quantity * (float) ($prices[$ri->order_item_id] ?? 0));
                $accounting->postPurchaseReceipt($receipt, round($amount, 2), $receipt->stock?->module ?? 'default');
            }
        });

        return back()->with('success', 'Prix enregistrés : total du bon de commande ' . number_format((float) $order->fresh()->total_amount, 0, ',', ' ') . ' MRU.');
    }

    private function anyPerm(array $perms): void
    {
        abort_unless(auth()->user()?->canAny($perms), 403, 'Action non autorisée.');
    }
}
