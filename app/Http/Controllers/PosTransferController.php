<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CateringContract;
use App\Models\PosTerminal;
use App\Models\PosTerminalStockItem;
use App\Models\PosReturn;
use App\Models\PosReturnItem;
use App\Models\CateringMenuDay;
use App\Models\CateringMenuMeal;
use App\Models\CateringMenuMealItem;
use App\Models\Client;
use App\Models\Meal;
use App\Models\Packaging;
use App\Models\PosTransfer;
use App\Models\PosTransferItem;
use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gestion des transferts production → point de vente catering.
 */
class PosTransferController extends Controller
{
    // ── Production : créer un transfert ───────────────────────────────────

    /**
     * Formulaire de création d'un transfert.
     * La production choisit : client → contrat → le système suggère les plats du jour.
     */
    public function create()
    {
        $this->perm('production.transfers.create');

        // Clients entreprise uniquement (catering B2B)
        $clients = Client::whereNotNull('company')
            ->where('company', '!=', '')
            ->with('cateringContracts')
            ->orderBy('company')
            ->orderBy('name')
            ->get();

        // Terminaux distants catering uniquement (pas les caisses ordinaires)
        $terminals    = PosTerminal::where('is_active', true)
            ->where('type', 'catering_pos')
            ->where('module', 'catering')
            ->whereNotNull('client_id')
            ->whereNotNull('stock_id')
            ->with(['client', 'stock', 'cashierMorning', 'cashierEvening', 'activeSession'])
            ->orderBy('label')
            ->get();

        // Stock source dédié catering
        $allStocks = Stock::where('module', 'catering')->orderBy('name')->get();

        $products = Product::with(['packaging', 'productPackagings.packaging'])
            ->where('is_consumable', true)
            ->orderBy('name')
            ->get();

        $meals = Meal::with('recipe.items.product')->orderBy('name')->get();

        return view('pos-transfer.create', compact(
            'clients', 'terminals', 'allStocks', 'products', 'meals'
        ));
    }

    /**
     * API : quand on sélectionne un client, retourne ses contrats actifs + plats du jour.
     */
    public function getClientContracts(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'transfer_date' => 'nullable|date',
            'contract_id' => 'nullable|exists:catering_contracts,id',
        ]);

        $client = Client::findOrFail($request->client_id);
        if (blank($client->company)) {
            return response()->json([
                'contracts' => [],
                'programmed_meals' => [],
                'error' => 'Ce client n\'est pas un client entreprise catering.',
            ], 422);
        }

        $date = Carbon::parse($request->input('transfer_date', today()))->startOfDay();

        $contracts = CateringContract::where('client_id', $client->id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->with('prices')
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'label'      => 'Contrat du ' . $c->start_date->format('d/m/Y') . ' au ' . $c->end_date->format('d/m/Y'),
                'guest_count' => $c->guest_count,
            ]);

        $programmedMeals = $this->getProgrammedMeals($client->id, $date, $request->integer('contract_id') ?: null);

        return response()->json([
            'contracts' => $contracts,
            'programmed_meals' => $programmedMeals,
        ]);
    }

    /**
     * API : plats programmés pour un contrat à une date donnée.
     */
    public function getContractMeals(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contract_id' => 'nullable|exists:catering_contracts,id',
            'date' => 'nullable|date',
        ]);

        $date = Carbon::parse($request->date ?? today());
        $meals = $this->getProgrammedMeals($request->integer('client_id'), $date, $request->integer('contract_id') ?: null);

        return response()->json(['programmed_meals' => $meals]);
    }

    /**
     * Enregistre le transfert.
     */
    public function store(Request $request)
    {
        $this->perm('production.transfers.create');

        $request->validate([
            'cash_register_id'     => 'nullable|exists:cash_registers,id',
            'pos_terminal_id'      => 'nullable|exists:pos_terminals,id',
            'client_id'            => 'required|exists:clients,id',
            'catering_contract_id' => 'nullable|exists:catering_contracts,id',
            'from_stock_id'        => 'required|exists:stocks,id',
            'driver_name'          => 'nullable|string|max:100',
            'transfer_date'        => 'required|date',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.item_type'    => 'required|in:contract,extra',
            'items.*.label'        => 'required|string',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.unit'         => 'nullable|string',
            'items.*.unit_price'   => 'nullable|numeric|min:0',
            'items.*.meal_id'      => 'nullable|exists:meals,id',
            'items.*.product_id'   => 'nullable|exists:products,id',
            'items.*.packaging_id' => 'nullable|exists:packagings,id',
        ]);

        $client = Client::findOrFail($request->client_id);
        if (blank($client->company)) {
            return back()->withInput()->with('error', 'Seuls les clients entreprise sont autorisés pour un transfert catering.');
        }

        $terminal = PosTerminal::findOrFail($request->pos_terminal_id);
        if (!$terminal->is_active || $terminal->type !== 'catering_pos' || $terminal->module !== 'catering') {
            return back()->withInput()->with('error', 'Le terminal choisi doit être un point de vente distant catering actif.');
        }
        if ((int) $terminal->client_id !== (int) $request->client_id) {
            return back()->withInput()->with('error', 'Le terminal sélectionné ne correspond pas au client du transfert.');
        }

        $fromStock = Stock::findOrFail($request->from_stock_id);
        if ($fromStock->module !== 'catering') {
            return back()->withInput()->with('error', 'Le stock source doit être le stock dédié au module catering.');
        }

        if ($request->filled('catering_contract_id')) {
            $contract = CateringContract::findOrFail($request->catering_contract_id);
            $transferDate = Carbon::parse($request->transfer_date)->toDateString();
            if ((int) $contract->client_id !== (int) $request->client_id) {
                return back()->withInput()->with('error', 'Le contrat sélectionné n\'appartient pas au client choisi.');
            }
            if ($contract->status !== 'active' || $transferDate < $contract->start_date->toDateString() || $transferDate > $contract->end_date->toDateString()) {
                return back()->withInput()->with('error', 'Le contrat n\'est pas actif pour la date de transfert demandée.');
            }
        }

        $mealIds = collect($request->items)->pluck('meal_id')->filter()->unique()->values();
        $productIds = collect($request->items)->pluck('product_id')->filter()->unique()->values();

        $mealsById = Meal::with('recipe.items.product')->whereIn('id', $mealIds)->get()->keyBy('id');
        $productsById = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $requiredByProduct = [];
        $validationErrors = [];

        // Sécurité métier + préparation déduction stock (produits et recettes des plats)
        foreach ($request->items as $row => $item) {
            $isExtra = ($item['item_type'] ?? null) === 'extra';
            $isContract = ($item['item_type'] ?? null) === 'contract';
            $qty = (float) ($item['quantity'] ?? 0);
            $productId = $item['product_id'] ?? null;
            $mealId = $item['meal_id'] ?? null;

            if ($qty <= 0) {
                $validationErrors[] = 'Ligne ' . ($row + 1) . ': quantité invalide.';
                continue;
            }

            if ($isContract && !$mealId) {
                $validationErrors[] = 'Ligne ' . ($row + 1) . ': un article contrat doit être lié à un plat programmé.';
                continue;
            }

            if ($isExtra && !$productId && !$mealId) {
                $validationErrors[] = 'Ligne ' . ($row + 1) . ': un extra doit être lié à un produit ou à un plat.';
                continue;
            }

            if ($productId) {
                $product = $productsById->get((int) $productId);
                if (!$product || !$product->is_consumable) {
                    $validationErrors[] = 'Ligne ' . ($row + 1) . ': seul un produit consommable peut être transféré.';
                    continue;
                }

                $baseQty = $qty;
                if (!empty($item['packaging_id'])) {
                    $pp = ProductPackaging::where('product_id', $product->id)
                        ->where('packaging_id', $item['packaging_id'])
                        ->first();
                    if (!$pp) {
                        $validationErrors[] = 'Ligne ' . ($row + 1) . ': emballage invalide pour ce produit.';
                        continue;
                    }
                    $baseQty = $baseQty * (float) $pp->quantity;
                }

                $requiredByProduct[$product->id] = ($requiredByProduct[$product->id] ?? 0) + $baseQty;
            }

            if ($mealId) {
                $meal = $mealsById->get((int) $mealId);
                if (!$meal) {
                    $validationErrors[] = 'Ligne ' . ($row + 1) . ': plat introuvable.';
                    continue;
                }

                $recipeItems = $meal->recipe?->items;
                if (!$recipeItems || $recipeItems->isEmpty()) {
                    $validationErrors[] = 'Ligne ' . ($row + 1) . ': le plat "' . $meal->name . '" n\'a pas de recette, impossible de déduire le stock.';
                    continue;
                }

                foreach ($recipeItems as $ri) {
                    $need = (float) $ri->quantity * $qty;
                    $requiredByProduct[$ri->product_id] = ($requiredByProduct[$ri->product_id] ?? 0) + $need;
                }
            }
        }

        if (!empty($validationErrors)) {
            return back()->withInput()->with('error', implode(' ', $validationErrors));
        }

        $availableByProduct = StockItem::where('stock_id', $fromStock->id)
            ->whereIn('product_id', array_keys($requiredByProduct))
            ->pluck('quantity', 'product_id');

        $insufficient = [];
        foreach ($requiredByProduct as $productId => $requiredQty) {
            $availableQty = (float) ($availableByProduct[$productId] ?? 0);
            if ($availableQty + 0.000001 < $requiredQty) {
                $productName = $productsById->get($productId)?->name
                    ?? Product::find($productId)?->name
                    ?? ('Produit #' . $productId);
                $insufficient[] = $productName . ' (requis: ' . number_format($requiredQty, 3, '.', '') . ', dispo: ' . number_format($availableQty, 3, '.', '') . ')';
            }
        }

        if (!empty($insufficient)) {
            return back()->withInput()->with('error', 'Stock catering insuffisant: ' . implode(' ; ', $insufficient));
        }

        DB::transaction(function () use ($request) {
            $transfer = PosTransfer::create([
                'from_stock_id'        => $request->from_stock_id,
                'prepared_by'          => auth()->id(),
                'cash_register_id'     => $request->cash_register_id,
                'pos_terminal_id'      => $request->pos_terminal_id,
                'client_id'            => $request->client_id,
                'catering_contract_id' => $request->catering_contract_id,
                'driver_name'          => $request->driver_name,
                'reference'            => PosTransfer::nextReference(),
                'transfer_date'        => $request->transfer_date,
                'status'               => 'pending',
                'notes'                => $request->notes,
            ]);

            foreach ($request->items as $item) {
                PosTransferItem::create([
                    'pos_transfer_id' => $transfer->id,
                    'meal_id'         => $item['meal_id'] ?? null,
                    'product_id'      => $item['product_id'] ?? null,
                    'packaging_id'    => $item['packaging_id'] ?? null,
                    'label'           => $item['label'],
                    'quantity'        => $item['quantity'],
                    'unit'            => $item['unit'] ?? null,
                    'item_type'       => $item['item_type'],
                    'unit_price'      => $item['unit_price'] ?? 0,
                ]);
            }

            $requirements = $this->buildStockRequirements($request->items);
            foreach ($requirements as $productId => $qty) {
                $si = StockItem::where('stock_id', $request->from_stock_id)
                    ->where('product_id', $productId)
                    ->first();
                if ($si) {
                    $si->decrement('quantity', $qty);
                }

                StockMovement::create([
                    'product_id'    => $productId,
                    'stock_id'      => $request->from_stock_id,
                    'type'          => 'out',
                    'quantity'      => $qty,
                    'origin_module' => 'catering',
                    'origin_type'   => 'pos_transfer',
                    'origin_id'     => $transfer->id,
                    'user_id'       => auth()->id(),
                    'notes'         => 'Transfert POS ' . $transfer->reference . ' (déduction recettes incluse)',
                ]);
            }

            $this->_transferId = $transfer->id;
        });

        return redirect()->route('pos-transfer.print', $this->_transferId ?? 0)
            ->with('success', 'Transfert créé. Imprimer le bon de transfert.');
    }

    private int $_transferId = 0;

    // ── Impression PDF ─────────────────────────────────────────────────────

    /**
     * Génère le bon de transfert PDF (avec emplacements signatures).
     */
    public function print($id)
    {
        $transfer = PosTransfer::with([
            'items.meal', 'items.product', 'items.packaging',
            'fromStock', 'preparedBy', 'client',
            'cateringContract', 'cashRegister.user',
            'posTerminal.cashierMorning', 'posTerminal.cashierEvening',
        ])->findOrFail($id);

        // Marquer comme imprimé + in_transit
        if ($transfer->status === 'pending') {
            $transfer->update([
                'status'     => 'in_transit',
                'printed_at' => now(),
            ]);
        }

        return view('pos-transfer.print', compact('transfer'));
    }

    // ── Caissier : voir les transferts en attente ─────────────────────────

    /**
     * Liste des transferts en attente pour la caisse du caissier connecté.
     * Recherche par pos_terminal_id (nouvelle méthode) OU par cash_register_id / client_id (ancienne méthode).
     */
    public function pending($registerId)
    {
        $register = CashRegister::with(['posTerminal'])->findOrFail($registerId);

        if ($register->user_id !== auth()->id() && !auth()->user()->hasRole(['admin', 'accountant', 'super-admin'])) {
            abort(403);
        }

        $transfers = PosTransfer::with(['items', 'preparedBy', 'client', 'posTerminal'])
            ->whereIn('status', ['pending', 'in_transit'])
            ->where(function ($q) use ($registerId, $register) {
                // Méthode 1 : ciblé par terminal
                if ($register->pos_terminal_id) {
                    $q->where('pos_terminal_id', $register->pos_terminal_id);
                }
                // Méthode 2 : directement sur la session
                $q->orWhere('cash_register_id', $registerId);
                // Méthode 3 : pending sans caisse mais même client
                if ($register->client_id) {
                    $q->orWhere(function ($q2) use ($register) {
                        $q2->whereNull('cash_register_id')
                           ->whereNull('pos_terminal_id')
                           ->where('client_id', $register->client_id);
                    });
                }
            })
            ->orderByDesc('transfer_date')
            ->get();

        return view('pos-transfer.pending', compact('register', 'transfers'));
    }

    /**
     * Le caissier valide la réception d'un transfert.
     * Met à jour le stock cumulé du terminal.
     */
    public function validate(Request $request, $id)
    {
        $transfer = PosTransfer::with(['items', 'cashRegister', 'posTerminal'])->findOrFail($id);

        // Trouver la session du caissier
        $registerId = $request->input('register_id');
        $register = $transfer->cashRegister;

        if ($register) {
            if ($register->user_id !== auth()->id()) abort(403);
        } else {
            $register = CashRegister::where('user_id', auth()->id())->where('status', 'open')->first();
            if (!$register) abort(403, 'Aucune session ouverte.');
            $transfer->update(['cash_register_id' => $register->id]);
        }

        if (!in_array($transfer->status, ['pending', 'in_transit'])) {
            return back()->with('error', 'Ce transfert ne peut plus être validé.');
        }

        $transfer->update([
            'status'               => 'validated',
            'cashier_validated_at' => now(),
            'cashier_validated_by' => auth()->id(),
        ]);

        // ── Cumuler dans pos_terminal_stock_items ─────────────────────────
        $terminalId = $transfer->pos_terminal_id ?? $register->pos_terminal_id;

        if ($terminalId) {
            foreach ($transfer->items as $item) {
                $key = [
                    'pos_terminal_id' => $terminalId,
                    'item_type'       => $item->item_type,
                    'meal_id'         => $item->meal_id,
                    'product_id'      => $item->product_id,
                ];
                $stockItem = PosTerminalStockItem::firstOrCreate($key, ['label' => $item->label]);

                // Conversion emballage → unité de base
                // Ex : 2 caisses × 24 bts/caisse = 48 unités enregistrées
                $baseQty = (float) $item->quantity;
                if ($item->product_id && $item->packaging_id) {
                    $pp = ProductPackaging::where('product_id', $item->product_id)
                                         ->where('packaging_id', $item->packaging_id)
                                         ->first();
                    if ($pp) {
                        $baseQty = $baseQty * (float) $pp->quantity;
                    }
                }
                $stockItem->increment('quantity_received', $baseQty);
            }
        }

        // ── Créditer aussi le stock physique si product_id ─────────────────
        $posStockId = $register->stock_id;
        if ($posStockId) {
            foreach ($transfer->items as $item) {
                if ($item->product_id) {
                    // Même conversion emballage → base
                    $baseQty = (float) $item->quantity;
                    if ($item->packaging_id) {
                        $pp = ProductPackaging::where('product_id', $item->product_id)
                                             ->where('packaging_id', $item->packaging_id)
                                             ->first();
                        if ($pp) $baseQty = $baseQty * (float) $pp->quantity;
                    }

                    StockItem::updateOrCreate(
                        ['stock_id' => $posStockId, 'product_id' => $item->product_id],
                        []
                    )->increment('quantity', $baseQty);

                    StockMovement::create([
                        'product_id'    => $item->product_id,
                        'stock_id'      => $posStockId,
                        'type'          => 'in',
                        'quantity'      => $baseQty,
                        'origin_module' => 'catering',
                        'origin_type'   => 'pos_transfer',
                        'origin_id'     => $transfer->id,
                        'user_id'       => auth()->id(),
                        'notes'         => 'Réception POS ' . $transfer->reference,
                    ]);
                }
            }
        }

        return back()->with('success', 'Transfert #' . $transfer->reference . ' validé. Stock mis à jour.');
    }

    // ── Caissier : servir un plat contrat ─────────────────────────────────

    /**
     * Le caissier décrémente la quantité servie d'un article contrat.
     */
    public function serveItem(Request $request, $itemId)
    {
        $item = PosTransferItem::with('transfer.cashRegister')->findOrFail($itemId);

        if ($item->transfer->cashRegister->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate(['qty' => 'required|numeric|min:0.5']);

        $newServed = $item->served_qty + $request->qty;
        if ($newServed > $item->quantity) {
            return response()->json(['error' => 'Quantité servie dépasse la quantité transférée.'], 422);
        }

        $item->increment('served_qty', $request->qty);

        return response()->json(['success' => true, 'remaining' => $item->quantity - $newServed]);
    }

    /**
     * Le caissier enregistre la vente d'un article extra (encaissement).
     */
    public function sellItem(Request $request, $itemId)
    {
        $item = PosTransferItem::with('transfer.cashRegister')->findOrFail($itemId);

        if ($item->transfer->cashRegister->user_id !== auth()->id()) {
            abort(403);
        }

        if ($item->item_type !== 'extra') {
            return response()->json(['error' => 'Cet article fait partie du contrat, pas de vente.'], 422);
        }

        $request->validate(['qty' => 'required|numeric|min:0.5']);

        $newSold = $item->sold_qty + $request->qty;
        if ($newSold > $item->quantity) {
            return response()->json(['error' => 'Quantité vendue dépasse le stock disponible.'], 422);
        }

        $item->increment('sold_qty', $request->qty);

        $amount = $item->unit_price * $request->qty;

        return response()->json([
            'success'   => true,
            'amount'    => $amount,
            'remaining' => $item->quantity - $newSold,
        ]);
    }

    // ── Caissier : retour de marchandises ─────────────────────────────────

    /**
     * Formulaire de retour (depuis la page session).
     */
    public function createReturn($registerId)
    {
        $register = CashRegister::with(['posTerminal'])->findOrFail($registerId);
        if ($register->user_id !== auth()->id()) abort(403);

        // Articles du terminal disponibles pour retour
        $terminalId  = $register->pos_terminal_id;
        $stockItems  = $terminalId
            ? PosTerminalStockItem::where('pos_terminal_id', $terminalId)
                ->get()
                ->filter(fn($s) => $s->available_qty > 0)
            : collect();

        return view('pos-transfer.return-form', compact('register', 'stockItems'));
    }

    /**
     * Enregistre le retour et met à jour le stock.
     */
    public function storeReturn(Request $request, $registerId)
    {
        $register = CashRegister::findOrFail($registerId);
        if ($register->user_id !== auth()->id()) abort(403);

        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.stock_item_id' => 'required|exists:pos_terminal_stock_items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($request, $register, &$posReturn) {
            $posReturn = PosReturn::create([
                'cash_register_id' => $register->id,
                'pos_terminal_id'  => $register->pos_terminal_id,
                'reference'        => PosReturn::nextReference(),
                'return_date'      => now()->toDateString(),
                'returned_by'      => auth()->id(),
                'notes'            => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                $stockItem = PosTerminalStockItem::findOrFail($itemData['stock_item_id']);
                $qty = min($itemData['quantity'], $stockItem->available_qty);
                if ($qty <= 0) continue;

                PosReturnItem::create([
                    'pos_return_id'             => $posReturn->id,
                    'pos_terminal_stock_item_id' => $stockItem->id,
                    'label'                     => $stockItem->label,
                    'quantity'                  => $qty,
                    'item_type'                 => $stockItem->item_type,
                ]);

                $stockItem->increment('quantity_returned', $qty);
            }
        });

        return redirect()->route('pos-transfer.print-return', $posReturn->id)
            ->with('success', 'Retour enregistré.');
    }

    /**
     * Imprime le bon de retour.
     */
    public function printReturn($id)
    {
        $posReturn = PosReturn::with([
            'items.stockItem', 'returnedBy', 'register', 'terminal.client',
        ])->findOrFail($id);

        if ($posReturn->returned_by !== auth()->id() && !auth()->user()->hasRole(['admin', 'accountant', 'super-admin'])) {
            abort(403);
        }

        return view('pos-transfer.print-return', compact('posReturn'));
    }

    // ── Helpers privés ─────────────────────────────────────────────────────

    /**
     * Retourne les plats programmés pour un client à une date donnée.
     */
    private function getProgrammedMeals(int $clientId, Carbon $date, ?int $contractId = null): array
    {
        $query = CateringMenuDay::whereHas('weeklyMenu.contract', function ($q) use ($clientId, $contractId) {
            $q->where('client_id', $clientId)
              ->where('status', 'active');
            if ($contractId) {
                $q->where('catering_contracts.id', $contractId);
            }
        })->where('date', $date->toDateString())
          ->with(['meals.items.meal', 'weeklyMenu.contract.prices']);

        $days = $query->get();

        $grouped = [];
        $labels = [
            'breakfast' => 'Petit-dejeuner',
            'lunch' => 'Dejeuner',
            'dinner' => 'Diner',
        ];

        foreach ($days as $day) {
            foreach ($day->meals as $meal) {
                $type = $meal->type;
                if (!isset($grouped[$type])) {
                    $price = (float) ($day->weeklyMenu->contract->prices->firstWhere('type', $type)?->price ?? 0);
                    $grouped[$type] = [
                        'type' => $type,
                        'type_label' => $labels[$type] ?? ucfirst($type),
                        'guest_count' => (int) $meal->quantity,
                        'unit_price' => $price,
                        'items' => [],
                    ];
                }

                foreach ($meal->items as $mealItem) {
                    $grouped[$type]['items'][] = [
                        'id' => $mealItem->meal_id,
                        'name' => $mealItem->meal->name ?? 'Plat',
                    ];
                }
            }
        }

        return array_values($grouped);
    }

    /**
     * Calcule les quantités à déduire du stock catering en tenant compte des recettes des plats.
     */
    private function buildStockRequirements(array $items): array
    {
        $requirements = [];

        $mealIds = collect($items)->pluck('meal_id')->filter()->unique()->values();
        $productIds = collect($items)->pluck('product_id')->filter()->unique()->values();

        $mealsById = Meal::with('recipe.items')->whereIn('id', $mealIds)->get()->keyBy('id');
        $productsById = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            if (!empty($item['product_id'])) {
                $productId = (int) $item['product_id'];
                $baseQty = $qty;
                if (!empty($item['packaging_id'])) {
                    $pp = ProductPackaging::where('product_id', $productId)
                        ->where('packaging_id', $item['packaging_id'])
                        ->first();
                    if ($pp) {
                        $baseQty = $baseQty * (float) $pp->quantity;
                    }
                }
                if ($productsById->has($productId)) {
                    $requirements[$productId] = ($requirements[$productId] ?? 0) + $baseQty;
                }
            }

            if (!empty($item['meal_id'])) {
                $meal = $mealsById->get((int) $item['meal_id']);
                if (!$meal || !$meal->recipe) {
                    continue;
                }

                foreach ($meal->recipe->items as $ri) {
                    $requirements[$ri->product_id] = ($requirements[$ri->product_id] ?? 0) + ((float) $ri->quantity * $qty);
                }
            }
        }

        return $requirements;
    }
}
