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
 *   2. le magasinier prépare les quantités réelles (elles sont réservées) ;
 *   2b. le responsable des achats approuve : il voit le stock, ce qui sort et ce qui reste,
 *       et peut corriger les quantités (ou renvoyer la sortie au magasinier) ;
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

        // Mes demandes, et les sorties que le magasinier a saisies pour moi
        $isAdmin = $user->hasRole(['admin', 'super-admin']);
        $mineQuery = fn () => KitchenRequest::with(['items.product.unit', 'validator', 'approver', 'preparer', 'receiver', 'items.siteAllocations.site'])
            ->when(!$isAdmin, fn ($q) => $q->where(fn ($w) => $w->where('requested_by', $user->id)->orWhere('receiver_id', $user->id)));

        // En cours : pas encore chez moi (à approuver, chez le magasinier), ou remises à un autre cuisinier
        $inProgress = $mineQuery()->whereIn('status', ['to_approve', 'pending', 'prepared'])
            ->whereNotIn('id', $toConfirm->pluck('id'))
            ->latest()->get();
        $history = $mineQuery()->whereIn('status', ['validated', 'cancelled'])
            ->latest('updated_at')->limit(20)->get();

        return view('kitchen.index', compact('toConfirm', 'inProgress', 'history'));
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
            // Un même produit saisi deux fois est regroupé
            $lines = collect($data['items'])->groupBy('product_id')->map(fn ($rows) => round($rows->sum('quantity'), 2));

            // Demande importante (> seuil % du stock d'un produit, tous magasins) : le responsable des achats
            // l'approuve d'abord, ensuite seulement elle arrive chez le magasinier.
            $inStock = KitchenRequest::stockQuantities(null, $lines->keys());
            $big = $lines->contains(fn ($qty, $pid) => KitchenRequest::exceedsThreshold($qty, $inStock[$pid] ?? 0));

            $kr = KitchenRequest::create([
                'kitchen'      => trim($data['kitchen']),
                'needed_on'    => $data['needed_on'] ?? null,
                'notes'        => $data['notes'] ?? null,
                'requested_by' => auth()->id(),
                'status'       => $big ? 'to_approve' : 'pending',
            ]);
            foreach ($lines as $pid => $qty) {
                $kr->items()->create(['product_id' => $pid, 'requested_quantity' => $qty]);
            }

            return $kr;
        });

        return redirect()->route('kitchen.requests.show', $kr)
            ->with('success', $kr->status === 'to_approve'
                ? "Demande {$kr->reference} envoyée : quantité importante (plus de " . KitchenRequest::thresholdLabel() . " du stock), le responsable des achats doit l'approuver avant le magasin."
                : "Demande {$kr->reference} envoyée au magasin.");
    }

    public function show(KitchenRequest $kitchenRequest)
    {
        $this->anyPerm(['kitchen.requests.create', 'kitchen.requests.validate', 'kitchen.requests.prepare', 'kitchen.requests.approve']);
        $kitchenRequest->load(['items.product.unit', 'items.siteAllocations.site', 'requester', 'preparer', 'approver', 'validator', 'receiver', 'stock']);

        return view('kitchen.show', [
            'kr'    => $kitchenRequest,
            'sites' => $kitchenRequest->status === 'prepared' ? $kitchenRequest->receivingSites(auth()->user()) : collect(),
        ]);
    }

    /** Le cuisinier prend la marchandise : c'est ici que le stock diminue. */
    public function take(Request $request, KitchenRequest $kitchenRequest)
    {
        $this->perm('kitchen.requests.validate');
        abort_unless($kitchenRequest->canBeTakenBy(auth()->user()), 403, 'Cette sortie est remise à un autre cuisinier : c\'est lui qui doit la confirmer.');

        $request->validate([
            'sites'     => 'nullable|array',
            'sites.*'   => 'array',
            'sites.*.*' => 'nullable|numeric|min:0|max:99999999', // unité de base
        ]);
        $sitesInput = (array) $request->input('sites', []);

        $belowMin = [];
        DB::transaction(function () use ($kitchenRequest, $sitesInput, &$belowMin) {
            $kr = KitchenRequest::lockForUpdate()->with(['items.product.unit', 'receiver'])->findOrFail($kitchenRequest->id);
            if ($kr->status !== 'prepared') {
                throw ValidationException::withMessages(['status' => 'Cette demande n\'est plus à confirmer.']);
            }

            // Sites du cuisinier : un seul → tout lui est attribué ; plusieurs → répartition obligatoire.
            $siteIds = $kr->receivingSites(auth()->user())->pluck('id')->all();
            $siteErrors = [];

            $bs = 'BS-' . str_pad((string) $kr->id, 5, '0', STR_PAD_LEFT);
            foreach ($kr->items as $item) {
                $qty = (float) $item->delivered_quantity;
                if ($qty <= 0) {
                    continue;
                }

                if (count($siteIds) === 1) {
                    $item->siteAllocations()->create(['site_id' => $siteIds[0], 'quantity' => $qty]);
                } elseif (count($siteIds) > 1) {
                    $split = collect($sitesInput[$item->id] ?? [])
                        ->map(fn ($q) => round((float) $q, 2))
                        ->filter(fn ($q, $sid) => $q > 0 && in_array((int) $sid, $siteIds, true));
                    if (abs($split->sum() - $qty) > 0.01) {
                        $unit = $item->product->unit->symbol ?? '';
                        $siteErrors[] = "{$item->product->name} : reçu " . Qty::format($qty, $unit) . ', réparti ' . Qty::format($split->sum(), $unit);
                        continue;
                    }
                    foreach ($split as $sid => $q) {
                        $item->siteAllocations()->create(['site_id' => (int) $sid, 'quantity' => $q]);
                    }
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

            if ($siteErrors) {
                throw ValidationException::withMessages(['sites' => array_merge(
                    ['Répartissez tout ce que vous recevez entre vos sites :'], $siteErrors)]);
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
            'contest_note' => 'Cuisine : ' . $data['contest_note'],
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
        $allowed = $isStore ? ['pending', 'to_approve', 'prepared'] : ['pending'];
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
            $big = false;
            foreach ($lines as $pid => $qty) {
                $inStock = (float) StockItem::where('stock_id', $stockId)->where('product_id', $pid)->lockForUpdate()->value('quantity');
                $free = $inStock - ($reserved[$pid] ?? 0);
                if ($qty > $free + 0.0001) {
                    $unit = $products[$pid]->unit->symbol ?? '';
                    $errors[] = "{$products[$pid]->name} : disponible " . Qty::format(max(0, $free), $unit) . ', remis ' . Qty::format($qty, $unit);
                }
                $big = $big || KitchenRequest::exceedsThreshold($qty, $inStock);
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
                // Sortie importante (> seuil % du stock d'un produit) : le responsable des achats approuve d'abord.
                // Sinon elle part directement au cuisinier, qui confirme la réception.
                'status'       => $big ? 'to_approve' : 'prepared',
            ]);
            foreach ($lines as $pid => $qty) {
                $kr->items()->create(['product_id' => $pid, 'requested_quantity' => $qty, 'delivered_quantity' => $qty]);
            }

            return $kr;
        });

        $kr->load('receiver');

        return redirect()->route('stock.kitchen-requests.index')
            ->with('success', $kr->status === 'to_approve'
                ? "Sortie {$kr->reference} pour {$kr->receiver->name} : quantité importante (plus de " . KitchenRequest::thresholdLabel() . " du stock), le responsable des achats doit l'approuver, puis le cuisinier la confirme."
                : "Sortie {$kr->reference} remise à {$kr->receiver->name} (« {$kr->kitchen} ») : il doit se connecter et la confirmer pour qu'elle sorte du stock.");
    }

    public function printBs(KitchenRequest $kitchenRequest)
    {
        $this->anyPerm(['kitchen.requests.create', 'kitchen.requests.validate', 'kitchen.requests.prepare', 'kitchen.requests.approve']);
        abort_unless(in_array($kitchenRequest->status, ['prepared', 'validated']), 404);
        $kitchenRequest->load(['items.product.unit', 'items.siteAllocations.site', 'requester', 'preparer', 'approver', 'validator', 'receiver', 'stock']);

        return view('kitchen.print-bs', ['kr' => $kitchenRequest]);
    }

    // ═══ Côté magasin ═══════════════════════════════════════════════════════

    public function queue()
    {
        $this->perm('kitchen.requests.prepare');

        $base = KitchenRequest::with(['items.product.unit', 'requester', 'preparer', 'approver', 'validator', 'receiver', 'stock']);
        $pending   = (clone $base)->where('status', 'pending')->oldest()->get();
        $toApprove = (clone $base)->where('status', 'to_approve')->oldest('updated_at')->get();
        $prepared  = (clone $base)->where('status', 'prepared')->oldest('prepared_at')->get();
        $validated = (clone $base)->where('status', 'validated')->latest('validated_at')->limit(20)->get();

        return view('stock.kitchen-requests.index', compact('pending', 'toApprove', 'prepared', 'validated'));
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
            $big = false;

            foreach ($kr->items as $item) {
                $qty = round((float) ($data['delivered'][$item->id] ?? 0), 2);
                $inStock = (float) StockItem::where('stock_id', $stockId)->where('product_id', $item->product_id)->value('quantity');
                $free = $inStock - ($reserved[$item->product_id] ?? 0);
                if ($qty > $free + 0.0001) {
                    $unit = $item->product->unit->symbol ?? '';
                    $errors[] = "{$item->product->name} : disponible " . Qty::format(max(0, $free), $unit) . ', remis ' . Qty::format($qty, $unit);
                }
                // Au-delà du seuil : approbation nécessaire, sauf si le responsable des achats
                // a déjà approuvé cette quantité sur la demande de la cuisine.
                $alreadyApproved = $kr->approved_at && $qty <= (float) $item->requested_quantity + 0.0001;
                $big = $big || (KitchenRequest::exceedsThreshold($qty, $inStock) && !$alreadyApproved);
                $item->update(['delivered_quantity' => $qty]);
                $any = $any || $qty > 0;
            }

            if ($errors) {
                throw ValidationException::withMessages(['delivered' => array_merge(['Stock insuffisant :'], $errors)]);
            }
            if (!$any) {
                throw ValidationException::withMessages(['delivered' => 'Aucune quantité remise.']);
            }

            $kr->update(array_merge([
                'status'       => $big ? 'to_approve' : 'prepared',
                'stock_id'     => $stockId,
                'prepared_by'  => auth()->id(),
                'prepared_at'  => now(),
                'receiver_id'  => (int) $data['receiver_id'],
                'contest_note' => null,
            ], $big ? ['approved_by' => null, 'approved_at' => null] : []));
        });

        $status = $kitchenRequest->fresh()->status;

        return redirect()->route('stock.kitchen-requests.index')
            ->with('success', $status === 'to_approve'
                ? "Demande {$kitchenRequest->reference} préparée : quantité importante (plus de " . KitchenRequest::thresholdLabel() . " du stock), en attente de l'approbation du responsable des achats."
                : "Demande {$kitchenRequest->reference} remise : en attente de la validation du cuisinier.");
    }

    // ═══ Consommation par site ══════════════════════════════════════════════

    /** Ce que chaque site a reçu (sorties validées, réparties par le cuisinier), sur une période. */
    public function siteConsumption(Request $request)
    {
        $this->anyPerm(['stock.movements.all', 'kitchen.requests.approve']);

        $filters = $request->validate([
            'from'    => 'nullable|date',
            'to'      => 'nullable|date',
            'site_id' => 'nullable|integer|exists:sites,id',
        ]);
        $from = \Carbon\Carbon::parse($filters['from'] ?? now()->startOfMonth())->startOfDay();
        $to   = \Carbon\Carbon::parse($filters['to'] ?? now())->endOfDay();
        $siteId = $filters['site_id'] ?? null;

        $rows = \App\Models\KitchenRequestItemSite::query()
            ->join('kitchen_request_items as i', 'i.id', '=', 'kitchen_request_item_sites.kitchen_request_item_id')
            ->join('kitchen_requests as r', 'r.id', '=', 'i.kitchen_request_id')
            ->join('sites as s', 's.id', '=', 'kitchen_request_item_sites.site_id')
            ->join('products as p', 'p.id', '=', 'i.product_id')
            ->leftJoin('units as u', 'u.id', '=', 'p.unit_id')
            ->where('r.status', 'validated')
            ->whereBetween('r.validated_at', [$from, $to])
            ->when($siteId, fn ($q) => $q->where('kitchen_request_item_sites.site_id', $siteId))
            ->groupBy('s.id', 's.name', 'p.id', 'p.name', 'u.symbol')
            ->orderBy('s.name')->orderBy('p.name')
            ->selectRaw('s.id as site_id, s.name as site_name, p.id as product_id, p.name as product_name, u.symbol as unit,
                         SUM(kitchen_request_item_sites.quantity) as qty, COUNT(DISTINCT r.id) as exits')
            ->get()
            ->groupBy('site_name');

        // Sorties validées sur la période sans site (cuisinier rattaché à aucun site)
        $unassigned = KitchenRequestItem::query()
            ->join('kitchen_requests as r', 'r.id', '=', 'kitchen_request_items.kitchen_request_id')
            ->where('r.status', 'validated')->whereBetween('r.validated_at', [$from, $to])
            ->where('kitchen_request_items.delivered_quantity', '>', 0)
            ->whereDoesntHave('siteAllocations')
            ->distinct()->count('r.id');

        return view('stock.site-consumption', [
            'rows'       => $rows,
            'unassigned' => $siteId ? 0 : $unassigned,
            'sites'      => \App\Models\Site::active()->orderBy('name')->get(['id', 'name']),
            'from'       => $from, 'to' => $to, 'siteId' => $siteId,
        ]);
    }

    // ═══ Côté responsable des achats : approbation des sorties ══════════════

    /** Sorties importantes à approuver (+ réglage du seuil pour la Direction). */
    public function approvals()
    {
        $this->anyPerm(['kitchen.requests.approve', 'kitchen.requests.threshold']);

        $toApprove = KitchenRequest::with(['items.product.unit', 'requester', 'preparer', 'receiver', 'stock'])
            ->where('status', 'to_approve')->oldest('updated_at')->get();
        $approved = KitchenRequest::with(['items.product.unit', 'approver', 'receiver', 'stock'])
            ->where('status', '!=', 'to_approve')
            ->whereNotNull('approved_at')->latest('approved_at')->limit(20)->get();
        $thresholdSetting = \App\Models\Setting::with('updater')->where('key', KitchenRequest::THRESHOLD_SETTING)->first();

        return view('stock.kitchen-requests.approvals', compact('toApprove', 'approved', 'thresholdSetting'));
    }

    /**
     * Change le seuil d'approbation (% du stock). Effet immédiat sur les nouvelles demandes et sorties ;
     * celles déjà en attente d'approbation restent à approuver.
     */
    public function updateThreshold(Request $request)
    {
        $this->perm('kitchen.requests.threshold');
        $data = $request->validate([
            'threshold' => 'required|numeric|min:1|max:100',
        ], [
            'threshold.required' => 'Indiquez le pourcentage.',
            'threshold.min'      => 'Le seuil doit être d\'au moins 1 %.',
            'threshold.max'      => 'Le seuil ne peut pas dépasser 100 %.',
        ]);

        \App\Models\Setting::set(KitchenRequest::THRESHOLD_SETTING, round((float) $data['threshold'], 2));

        return redirect()->route('stock.kitchen-requests.approvals')
            ->with('success', 'Seuil d\'approbation fixé à ' . KitchenRequest::thresholdLabel() . ' du stock.');
    }

    /** Pour chaque produit : en stock, ce qui sort, ce qui reste — quantités modifiables. */
    public function approveForm(KitchenRequest $kitchenRequest)
    {
        $this->perm('kitchen.requests.approve');
        abort_unless($kitchenRequest->status === 'to_approve', 404);
        $kitchenRequest->load(['items.product.unit', 'requester', 'preparer', 'receiver', 'stock']);

        return view('stock.kitchen-requests.approve', [
            'kr'    => $kitchenRequest,
            'lines' => $this->stockLines($kitchenRequest),
        ]);
    }

    public function approve(Request $request, KitchenRequest $kitchenRequest)
    {
        $this->perm('kitchen.requests.approve');

        $data = $request->validate([
            'qty'   => 'required|array',
            'qty.*' => 'nullable|numeric|min:0|max:99999999', // unité de base
        ]);

        [$changed, $prepared] = DB::transaction(function () use ($data, $kitchenRequest) {
            $kr = KitchenRequest::lockForUpdate()->with('items.product.unit')->findOrFail($kitchenRequest->id);
            if ($kr->status !== 'to_approve') {
                throw ValidationException::withMessages(['status' => 'Cette sortie n\'est plus à approuver.']);
            }

            // Avant préparation (demande de la cuisine) : on approuve les quantités demandées, la demande
            // part ensuite au magasinier. Après préparation : on approuve ce qui sort du magasin choisi.
            $prepared = $kr->isPrepared();
            $column = $prepared ? 'delivered_quantity' : 'requested_quantity';
            $reserved = $prepared ? KitchenRequest::reservedQuantities($kr->stock_id, $kr->id) : [];
            $errors = [];
            $any = false;
            $changed = false;

            foreach ($kr->items as $item) {
                $qty = round((float) ($data['qty'][$item->id] ?? $item->{$column} ?? 0), 2);
                if ($prepared) {
                    $inStock = (float) StockItem::where('stock_id', $kr->stock_id)->where('product_id', $item->product_id)->lockForUpdate()->value('quantity');
                    $free = $inStock - ($reserved[$item->product_id] ?? 0);
                    if ($qty > $free + 0.0001) {
                        $unit = $item->product->unit->symbol ?? '';
                        $errors[] = "{$item->product->name} : disponible " . Qty::format(max(0, $free), $unit) . ', sortie ' . Qty::format($qty, $unit);
                    }
                }
                if (abs($qty - (float) $item->{$column}) > 0.0001) {
                    $changed = true;
                    $item->update([$column => $qty]);
                }
                $any = $any || $qty > 0;
            }

            if ($errors) {
                throw ValidationException::withMessages(['qty' => array_merge(['Stock insuffisant :'], $errors)]);
            }
            if (!$any) {
                throw ValidationException::withMessages(['qty' => 'Aucune quantité : utilisez plutôt « Refuser ».']);
            }

            $kr->update([
                'status'      => $prepared ? 'prepared' : 'pending', // → cuisinier / → magasinier
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return [$changed, $prepared];
        });

        return redirect()->route('stock.kitchen-requests.approvals')
            ->with('success', ($prepared ? 'Sortie ' : 'Demande ') . "{$kitchenRequest->reference} approuvée"
                . ($changed ? ' avec quantités modifiées' : '')
                . ($prepared ? ' : le cuisinier peut la confirmer.' : ' : elle part au magasinier, qui fera la sortie.'));
    }

    /**
     * Le responsable des achats refuse :
     *  - une demande de la cuisine (pas encore au magasin) est refusée (annulée) avec le motif ;
     *  - une sortie préparée revient au magasinier avec le motif.
     */
    public function reject(Request $request, KitchenRequest $kitchenRequest)
    {
        $this->perm('kitchen.requests.approve');
        $data = $request->validate(['contest_note' => 'required|string|max:500'], ['contest_note.required' => 'Indiquez le motif.']);
        abort_unless($kitchenRequest->status === 'to_approve', 422, 'Cette sortie n\'est plus à approuver.');

        if (!$kitchenRequest->isPrepared()) {
            $kitchenRequest->update([
                'status'       => 'cancelled',
                'contest_note' => 'Refusée par le resp. achats : ' . $data['contest_note'],
            ]);

            return redirect()->route('stock.kitchen-requests.approvals')
                ->with('success', "Demande {$kitchenRequest->reference} refusée : le cuisinier verra le motif.");
        }

        $kitchenRequest->update([
            'status'       => 'pending',
            'contest_note' => 'Resp. achats : ' . $data['contest_note'],
            'prepared_by'  => null,
            'prepared_at'  => null,
        ]);

        return redirect()->route('stock.kitchen-requests.approvals')
            ->with('success', "Sortie {$kitchenRequest->reference} renvoyée au magasinier.");
    }

    /**
     * En stock, déjà réservé par d'autres sorties, pour chaque ligne.
     * Sortie préparée : dans le magasin choisi. Demande pas encore préparée : tous magasins confondus.
     */
    private function stockLines(KitchenRequest $kr): array
    {
        $stockId = $kr->isPrepared() ? (int) $kr->stock_id : null;
        $reserved = KitchenRequest::reservedQuantities($stockId, $kr->id);
        $inStock = KitchenRequest::stockQuantities($stockId, $kr->items->pluck('product_id'));

        $lines = [];
        foreach ($kr->items as $item) {
            $lines[$item->id] = [
                'in_stock' => (float) ($inStock[$item->product_id] ?? 0),
                'reserved' => (float) ($reserved[$item->product_id] ?? 0),
            ];
        }

        return $lines;
    }

    private function anyPerm(array $perms): void
    {
        abort_unless(auth()->user()?->canAny($perms), 403, 'Action non autorisée.');
    }
}
