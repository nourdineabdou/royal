<?php

namespace App\Http\Controllers;

use App\Models\KitchenRequest;
use App\Models\KitchenRequestItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Support\Qty;
use App\Support\ScanCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sorties de stock vers les cuisines (procédure Complex Royal, workflow B) :
 *   1. la cuisine fait une demande (produits + quantités) ;
 *   2. le magasinier prépare et remet les quantités réelles (elles sont réservées) ;
 *   3. le cuisinier valide « je prends » → le stock diminue et un bon de sortie BS est créé.
 *   Une demande de cuisine n'est pas une sortie tant que le cuisinier n'a pas validé.
 */
class KitchenRequestController extends Controller
{
    // ═══ Côté cuisine ═══════════════════════════════════════════════════════

    public function index()
    {
        $this->anyPerm(['kitchen.requests.create', 'kitchen.requests.validate']);
        $user = auth()->user();

        // À confirmer : marchandise remise par le magasinier, en attente du cuisinier qui la prend
        // Chaque cuisinier ne voit que les sorties qui lui sont remises (l'admin voit tout)
        $toConfirm = $user->can('kitchen.requests.validate')
            ? KitchenRequest::with(['items.product.unit', 'requester', 'preparer', 'receiver'])->where('status', 'prepared')
                ->when(!$user->hasRole(['admin', 'super-admin']), fn ($q) => $q->where(fn ($w) => $w->whereNull('receiver_id')->orWhere('receiver_id', $user->id)))
                ->oldest('prepared_at')->get()
            : collect();

        $mine = KitchenRequest::with(['items.product.unit', 'validator'])
            ->where('requested_by', $user->id)
            ->where('status', '!=', 'prepared')
            ->latest()->limit(30)->get();

        return view('kitchen.index', compact('toConfirm', 'mine'));
    }

    public function create()
    {
        $this->perm('kitchen.requests.create');

        $catalog = ScanCatalog::all();
        $kitchens = KitchenRequest::knownKitchens();
        $lastKitchen = KitchenRequest::where('requested_by', auth()->id())->latest()->value('kitchen');

        return view('kitchen.create', compact('catalog', 'kitchens', 'lastKitchen'));
    }

    public function store(Request $request)
    {
        $this->perm('kitchen.requests.create');

        $data = $request->validate([
            'kitchen'            => 'required|string|max:100',
            'needed_on'          => 'nullable|date',
            'notes'              => 'nullable|string|max:500',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|numeric|gt:0|max:99999999', // unité de base
        ], [
            'kitchen.required' => 'Indiquez la cuisine.',
            'items.required'   => 'Ajoutez au moins un produit.',
        ]);

        $kr = DB::transaction(function () use ($data) {
            $kr = KitchenRequest::create([
                'kitchen'      => trim($data['kitchen']),
                'needed_on'    => $data['needed_on'] ?? null,
                'notes'        => $data['notes'] ?? null,
                'requested_by' => auth()->id(),
                'status'       => 'pending',
            ]);
            // Un même produit saisi deux fois est regroupé
            collect($data['items'])->groupBy('product_id')->each(function ($rows, $pid) use ($kr) {
                $kr->items()->create(['product_id' => $pid, 'requested_quantity' => round($rows->sum('quantity'), 2)]);
            });

            return $kr;
        });

        return redirect()->route('kitchen.requests.show', $kr)
            ->with('success', "Demande {$kr->reference} envoyée au magasin.");
    }

    public function show(KitchenRequest $kitchenRequest)
    {
        $this->anyPerm(['kitchen.requests.create', 'kitchen.requests.validate', 'kitchen.requests.prepare']);
        $kitchenRequest->load(['items.product.unit', 'requester', 'preparer', 'validator', 'receiver', 'stock']);

        return view('kitchen.show', ['kr' => $kitchenRequest]);
    }

    /** Le cuisinier prend la marchandise : c'est ici que le stock diminue. */
    public function take(KitchenRequest $kitchenRequest)
    {
        $this->perm('kitchen.requests.validate');
        abort_unless($kitchenRequest->canBeTakenBy(auth()->user()), 403, 'Cette sortie est remise à un autre cuisinier : c\'est lui qui doit la confirmer.');

        $belowMin = [];
        DB::transaction(function () use ($kitchenRequest, &$belowMin) {
            $kr = KitchenRequest::lockForUpdate()->with('items.product.unit')->findOrFail($kitchenRequest->id);
            if ($kr->status !== 'prepared') {
                throw ValidationException::withMessages(['status' => 'Cette demande n\'est plus à confirmer.']);
            }

            $bs = 'BS-' . str_pad((string) $kr->id, 5, '0', STR_PAD_LEFT);
            foreach ($kr->items as $item) {
                $qty = (float) $item->delivered_quantity;
                if ($qty <= 0) {
                    continue;
                }
                $stockItem = StockItem::where('stock_id', $kr->stock_id)->where('product_id', $item->product_id)->lockForUpdate()->first();
                if (!$stockItem || (float) $stockItem->quantity + 0.0001 < $qty) {
                    $unit = $item->product->unit->symbol ?? '';
                    throw ValidationException::withMessages(['stock' =>
                        "Stock insuffisant pour {$item->product->name} : disponible " . Qty::format($stockItem->quantity ?? 0, $unit)
                        . ', remis ' . Qty::format($qty, $unit) . '. Prévenez le magasinier.']);
                }
                $stockItem->decrement('quantity', $qty);

                StockMovement::create([
                    'stock_id'      => $kr->stock_id,
                    'product_id'    => $item->product_id,
                    'type'          => 'out',
                    'quantity'      => $qty,
                    'origin_module' => 'kitchen',
                    'origin_type'   => 'kitchen_request',
                    'origin_id'     => $kr->id,
                    'user_id'       => auth()->id(),
                    'notes'         => "{$bs} — {$kr->kitchen} — demande {$kr->reference}",
                ]);

                // Seuil minimum atteint dans le Stock Catering ? (la DA automatique sera mise à jour)
                if (app(\App\Services\StockAlertService::class)->isBelow($item->product)) {
                    $belowMin[] = $item->product->name;
                }
            }

            $kr->update([
                'status'       => 'validated',
                'validated_by' => auth()->id(),
                'validated_at' => now(),
                'bs_number'    => $bs,
            ]);
        });

        $msg = 'Sortie validée : bon de sortie BS-' . str_pad((string) $kitchenRequest->id, 5, '0', STR_PAD_LEFT) . ' créé.';
        if ($belowMin) {
            $msg .= ' Sous le seuil minimum : ' . implode(', ', array_unique($belowMin)) . ' (ajouté à la demande d\'achat automatique du magasinier).';
        }

        return redirect()->route('kitchen.requests.show', $kitchenRequest)->with('success', $msg);
    }

    /** Le cuisinier ne prend pas : quantités incorrectes, produit abîmé… la demande revient au magasinier. */
    public function contest(Request $request, KitchenRequest $kitchenRequest)
    {
        $this->perm('kitchen.requests.validate');
        $data = $request->validate(['contest_note' => 'required|string|max:500'], ['contest_note.required' => 'Expliquez ce qui ne va pas.']);
        abort_unless($kitchenRequest->status === 'prepared', 422, 'Cette demande n\'est plus à confirmer.');
        abort_unless($kitchenRequest->canBeTakenBy(auth()->user()), 403, 'Cette sortie est remise à un autre cuisinier.');

        $kitchenRequest->update([
            'status'       => 'pending',
            'contest_note' => $data['contest_note'],
            'prepared_by'  => null,
            'prepared_at'  => null,
        ]);

        return redirect()->route('kitchen.requests.index')->with('success', "Demande {$kitchenRequest->reference} renvoyée au magasinier.");
    }

    /**
     * Annulation :
     *  - la cuisine annule sa demande tant qu'elle n'est pas préparée ;
     *  - le magasinier annule une demande ou une remise pas encore validée (les quantités réservées sont libérées).
     */
    public function cancel(KitchenRequest $kitchenRequest)
    {
        $user = auth()->user();
        $isStore = $user?->can('kitchen.requests.prepare');
        abort_unless(
            $kitchenRequest->requested_by === $user?->id || $isStore || $user?->hasRole(['admin', 'super-admin']),
            403
        );
        $allowed = $isStore ? ['pending', 'prepared'] : ['pending'];
        abort_unless(in_array($kitchenRequest->status, $allowed), 422, 'Cette demande ne peut plus être annulée.');
        $kitchenRequest->update(['status' => 'cancelled']);

        return redirect()->route($isStore ? 'stock.kitchen-requests.index' : 'kitchen.requests.index')
            ->with('success', "Demande {$kitchenRequest->reference} annulée.");
    }

    /** Le magasinier initie la sortie (feuille manuscrite, cuisinier au comptoir) : elle part directement « à confirmer ». */
    public function createFromStore()
    {
        $this->perm('kitchen.requests.prepare');

        $stocks = Stock::orderBy('name')->get(['id', 'name', 'module']);
        $available = [];
        foreach ($stocks as $s) {
            $reserved = KitchenRequest::reservedQuantities($s->id);
            foreach (StockItem::where('stock_id', $s->id)->pluck('quantity', 'product_id') as $pid => $q) {
                $available[$s->id][$pid] = max(0, (float) $q - ($reserved[$pid] ?? 0));
            }
        }

        // Magasin proposé : celui qui contient le plus de produits en stock
        $defaultStock = collect($available)->map(fn ($p) => count(array_filter($p, fn ($q) => $q > 0)))->sortDesc()->keys()->first()
            ?? $stocks->first()?->id;

        return view('stock.kitchen-requests.create', [
            'cooks'          => KitchenRequest::cooks(),
            'packagingNames' => \App\Models\Packaging::orderBy('name')->pluck('name'),
            'canLink'        => (bool) auth()->user()?->can('products.edit'),
            'catalog'      => ScanCatalog::all(),
            'kitchens'     => KitchenRequest::knownKitchens(),
            'stocks'       => $stocks,
            'available'    => $available,
            'defaultStock' => $defaultStock,
        ]);
    }

    public function storeFromStore(Request $request)
    {
        $this->perm('kitchen.requests.prepare');

        $data = $request->validate([
            'kitchen'            => 'nullable|string|max:100', // déduite du cuisinier si absente,
            'stock_id'           => 'required|exists:stocks,id',
            'needed_on'          => 'nullable|date',
            'notes'              => 'nullable|string|max:500',
            'receiver_id'        => ['required', \Illuminate\Validation\Rule::exists('model_has_roles', 'model_id')
                ->where('model_type', \App\Models\User::class)
                ->where('role_id', \Spatie\Permission\Models\Role::where('name', 'cuisinier')->value('id'))],
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|numeric|gt:0|max:99999999', // unité de base
        ], [
            'receiver_id.required' => 'Choisissez le cuisinier qui reçoit la marchandise.',
            'receiver_id.exists'   => 'Choisissez un cuisinier de la liste.',
            'items.required'   => 'Ajoutez au moins un produit.',
        ]);

        $kr = DB::transaction(function () use ($data) {
            $stockId = (int) $data['stock_id'];
            $reserved = KitchenRequest::reservedQuantities($stockId);
            $lines = collect($data['items'])->groupBy('product_id')->map(fn ($rows) => round($rows->sum('quantity'), 2));
            $products = Product::with('unit')->whereIn('id', $lines->keys())->get()->keyBy('id');

            $errors = [];
            foreach ($lines as $pid => $qty) {
                $free = (float) StockItem::where('stock_id', $stockId)->where('product_id', $pid)->lockForUpdate()->value('quantity') - ($reserved[$pid] ?? 0);
                if ($qty > $free + 0.0001) {
                    $unit = $products[$pid]->unit->symbol ?? '';
                    $errors[] = "{$products[$pid]->name} : disponible " . Qty::format(max(0, $free), $unit) . ', remis ' . Qty::format($qty, $unit);
                }
            }
            if ($errors) {
                throw ValidationException::withMessages(['items' => array_merge(['Stock insuffisant :'], $errors)]);
            }

            $kr = KitchenRequest::create([
                // Cuisine : celle saisie, sinon la dernière utilisée par ce cuisinier, sinon « Cuisine »
                'kitchen'      => trim((string) ($data['kitchen'] ?? '')) ?: (KitchenRequest::where('requested_by', $data['receiver_id'])->whereNotNull('kitchen')->latest()->value('kitchen') ?: 'Cuisine'),
                'stock_id'     => $stockId,
                'needed_on'    => $data['needed_on'] ?? null,
                'notes'        => $data['notes'] ?? null,
                'requested_by' => auth()->id(),
                'prepared_by'  => auth()->id(),
                'prepared_at'  => now(),
                'receiver_id'  => (int) $data['receiver_id'],
                'status'       => 'prepared', // remise faite : en attente de la validation du cuisinier
            ]);
            foreach ($lines as $pid => $qty) {
                $kr->items()->create(['product_id' => $pid, 'requested_quantity' => $qty, 'delivered_quantity' => $qty]);
            }

            return $kr;
        });

        $kr->load('receiver');

        return redirect()->route('stock.kitchen-requests.index')
            ->with('success', "Sortie {$kr->reference} remise à {$kr->receiver->name} (« {$kr->kitchen} ») : il doit se connecter et la confirmer pour qu'elle sorte du stock.");
    }

    public function printBs(KitchenRequest $kitchenRequest)
    {
        $this->anyPerm(['kitchen.requests.create', 'kitchen.requests.validate', 'kitchen.requests.prepare']);
        abort_unless(in_array($kitchenRequest->status, ['prepared', 'validated']), 404);
        $kitchenRequest->load(['items.product.unit', 'requester', 'preparer', 'validator', 'receiver', 'stock']);

        return view('kitchen.print-bs', ['kr' => $kitchenRequest]);
    }

    // ═══ Côté magasin ═══════════════════════════════════════════════════════

    public function queue()
    {
        $this->perm('kitchen.requests.prepare');

        $base = KitchenRequest::with(['items.product.unit', 'requester', 'preparer', 'validator', 'receiver', 'stock']);
        $pending   = (clone $base)->where('status', 'pending')->oldest()->get();
        $prepared  = (clone $base)->where('status', 'prepared')->oldest('prepared_at')->get();
        $validated = (clone $base)->where('status', 'validated')->latest('validated_at')->limit(20)->get();

        return view('stock.kitchen-requests.index', compact('pending', 'prepared', 'validated'));
    }

    public function prepare(KitchenRequest $kitchenRequest)
    {
        $this->perm('kitchen.requests.prepare');
        abort_unless($kitchenRequest->status === 'pending', 404);
        $kitchenRequest->load(['items.product.unit', 'requester']);

        $stocks = Stock::orderBy('name')->get(['id', 'name', 'module']);
        $productIds = $kitchenRequest->items->pluck('product_id');

        // Disponible = stock − déjà remis aux autres cuisines et pas encore validé
        $available = [];
        foreach ($stocks as $s) {
            $reserved = KitchenRequest::reservedQuantities($s->id, $kitchenRequest->id);
            $qty = StockItem::where('stock_id', $s->id)->whereIn('product_id', $productIds)->pluck('quantity', 'product_id');
            foreach ($productIds as $pid) {
                $available[$s->id][$pid] = max(0, (float) ($qty[$pid] ?? 0) - ($reserved[$pid] ?? 0));
            }
        }

        // Magasin proposé : celui qui couvre le mieux la demande
        $defaultStock = collect($stocks)->sortByDesc(function ($s) use ($kitchenRequest, $available) {
            return $kitchenRequest->items->filter(fn ($i) => ($available[$s->id][$i->product_id] ?? 0) >= (float) $i->requested_quantity)->count();
        })->first()?->id;

        $cooks = KitchenRequest::cooks();

        return view('stock.kitchen-requests.prepare', [
            'cooks'           => $cooks,
            'defaultReceiver' => $kitchenRequest->receiver_id
                ?? ($cooks->contains('id', $kitchenRequest->requested_by) ? $kitchenRequest->requested_by : null),
            'kr'           => $kitchenRequest,
            'stocks'       => $stocks,
            'available'    => $available,
            'defaultStock' => $defaultStock,
        ]);
    }

    public function storePrepare(Request $request, KitchenRequest $kitchenRequest)
    {
        $this->perm('kitchen.requests.prepare');

        $data = $request->validate([
            'stock_id'    => 'required|exists:stocks,id',
            'delivered'   => 'required|array',
            'delivered.*' => 'nullable|numeric|min:0|max:99999999', // unité de base
            'receiver_id'        => ['required', \Illuminate\Validation\Rule::exists('model_has_roles', 'model_id')
                ->where('model_type', \App\Models\User::class)
                ->where('role_id', \Spatie\Permission\Models\Role::where('name', 'cuisinier')->value('id'))],
        ], [
            'receiver_id.required' => 'Choisissez le cuisinier qui reçoit la marchandise.',
            'receiver_id.exists'   => 'Choisissez un cuisinier de la liste.',
        ]);

        DB::transaction(function () use ($data, $kitchenRequest) {
            $kr = KitchenRequest::lockForUpdate()->with('items.product.unit')->findOrFail($kitchenRequest->id);
            if ($kr->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Cette demande a déjà été préparée.']);
            }
            $stockId = (int) $data['stock_id'];
            $reserved = KitchenRequest::reservedQuantities($stockId, $kr->id);
            $errors = [];
            $any = false;

            foreach ($kr->items as $item) {
                $qty = round((float) ($data['delivered'][$item->id] ?? 0), 2);
                $inStock = (float) StockItem::where('stock_id', $stockId)->where('product_id', $item->product_id)->value('quantity');
                $free = $inStock - ($reserved[$item->product_id] ?? 0);
                if ($qty > $free + 0.0001) {
                    $unit = $item->product->unit->symbol ?? '';
                    $errors[] = "{$item->product->name} : disponible " . Qty::format(max(0, $free), $unit) . ', remis ' . Qty::format($qty, $unit);
                }
                $item->update(['delivered_quantity' => $qty]);
                $any = $any || $qty > 0;
            }

            if ($errors) {
                throw ValidationException::withMessages(['delivered' => array_merge(['Stock insuffisant :'], $errors)]);
            }
            if (!$any) {
                throw ValidationException::withMessages(['delivered' => 'Aucune quantité remise.']);
            }

            $kr->update([
                'status'       => 'prepared',
                'stock_id'     => $stockId,
                'prepared_by'  => auth()->id(),
                'prepared_at'  => now(),
                'receiver_id'  => (int) $data['receiver_id'],
                'contest_note' => null,
            ]);
        });

        return redirect()->route('stock.kitchen-requests.index')
            ->with('success', "Demande {$kitchenRequest->reference} remise : en attente de la validation du cuisinier.");
    }

    private function anyPerm(array $perms): void
    {
        abort_unless(auth()->user()?->canAny($perms), 403, 'Action non autorisée.');
    }
}
