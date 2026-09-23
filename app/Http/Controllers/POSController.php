<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosTerminalStockItem;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Recipe;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AccountingEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    private const POS_MODULE = 'restaurant';

    /**
     * Affiche le POS avec toutes les catégories et plats
     */
    public function index()
    {
        $this->perm('pos.view');
        $activeRegister = CashRegister::with('user')
            ->where('module', self::POS_MODULE)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if (!$activeRegister) {
            return redirect()->route('cashier.open')->with('error', 'Aucune caisse restaurant ouverte. Ouvrez une session avant de prendre des commandes.');
        }

        $categories = Category::with('meals')->get();

        // Couleurs pour les catégories (jusqu'à 4)
        $categoryColors = [
            'bg-blue-500',
            'bg-green-500',
            'bg-purple-500',
            'bg-orange-500'
        ];

        return view('pos.index', compact('categories', 'categoryColors', 'activeRegister'));
    }

    /**
     * API: Récupère les plats avec détails pour le panier
     */
    public function getMeals()
    {
        $this->perm('pos.view');
        $meals = Meal::with(['category', 'accompaniments'])
            ->select('id', 'name', 'price', 'image', 'category_id')
            ->get();

        return response()->json($meals);
    }

    /**
     * API: Récupère les produits consommables (boissons, extras) vendables au POS restaurant,
     * au même titre qu'au POS catering — un produit "consommable" est vendable partout où le POS est utilisé.
     */
    public function getExtras()
    {
        $this->perm('pos.view');
        $products = Product::where('is_consumable', true)
            ->whereNotNull('sale_price')
            ->select('id', 'name', 'sale_price', 'unit_id')
            ->with('unit:id,name,symbol')
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    /**
     * API: Crée une commande et déduit le stock.
     * Une commande peut mélanger des plats (meal_id, cuisinés via recette) et des produits
     * consommables vendus tels quels (product_id, ex: boissons) — même logique que le POS catering.
     */
    public function createOrder(Request $request)
    {
        $this->perm('pos.orders.create');
        $activeRegister = CashRegister::where('module', self::POS_MODULE)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if (!$activeRegister) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune caisse restaurant ouverte. Ouvrez une session avant de créer une commande.'
            ], 422);
        }

        $validated = $request->validate([
            'items'                         => 'required|array|min:1',
            'items.*.meal_id'               => 'nullable|exists:meals,id',
            'items.*.product_id'            => 'nullable|exists:products,id',
            'items.*.quantity'              => 'required|integer|min:1',
            'items.*.accompaniments'        => 'nullable|array',
            'items.*.accompaniments.*'      => 'integer|exists:accompaniments,id',
            'customer_number'               => 'nullable|string|max:50',
            'total_amount'                  => 'required|numeric|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            if (empty($item['meal_id']) && empty($item['product_id'])) {
                return response()->json(['success' => false, 'message' => 'Article invalide dans le panier.'], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Recalculer le total côté serveur (ne jamais faire confiance au client)
            $serverTotal = 0;
            $mealPrices    = [];
            $productPrices = [];
            foreach ($validated['items'] as $item) {
                if (!empty($item['meal_id'])) {
                    $meal = Meal::findOrFail($item['meal_id']);
                    $mealPrices[$item['meal_id']] = $meal->price;
                    $serverTotal += $meal->price * $item['quantity'];
                } else {
                    $product = Product::findOrFail($item['product_id']);
                    if (!$product->is_consumable || $product->sale_price === null) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => "« {$product->name} » n'est pas vendable au POS."], 422);
                    }
                    $productPrices[$item['product_id']] = $product->sale_price;
                    $serverTotal += $product->sale_price * $item['quantity'];
                }
            }

            // Créer la commande
            $order = Order::create([
                'customer_number' => $validated['customer_number'] ?? null,
                'server_id'       => auth()->id() ?? 1,
                'cashier_id'      => $activeRegister->user_id,
                'cash_register_id'=> $activeRegister->id,
                'total_amount'    => $serverTotal,
                'status'          => 'pending',
                'is_prepared'     => false,
            ]);

            // Traiter chaque article de la commande
            foreach ($validated['items'] as $item) {
                if (!empty($item['meal_id'])) {
                    $meal = Meal::find($item['meal_id']);

                    $orderItem = OrderItem::create([
                        'order_id'   => $order->id,
                        'meal_id'    => $meal->id,
                        'quantity'   => $item['quantity'],
                        'price'      => $mealPrices[$item['meal_id']],  // prix de la DB
                        'cost_price' => $this->calculateRecipeCost($meal->id), // coût recette
                    ]);

                    // Sauvegarder les accompagnements sélectionnés
                    if (!empty($item['accompaniments'])) {
                        $orderItem->accompaniments()->sync($item['accompaniments']);
                    }

                    // Déduire le stock basé sur la recette
                    $this->deductStockFromRecipe($meal->id, $item['quantity'], $order->id);
                } else {
                    $product = Product::find($item['product_id']);

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'quantity'   => $item['quantity'],
                        'price'      => $productPrices[$item['product_id']],
                        'cost_price' => 0,
                    ]);

                    // Déduire directement le stock du produit (pas de recette, vendu tel quel)
                    $this->deductProductStock($product->id, $item['quantity'], $order->id);
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Commande créée avec succès',
                'order_id' => $order->id,
                'total'    => $serverTotal,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déduit le stock d'un produit consommable vendu tel quel (boisson, extra) au POS restaurant.
     */
    private function deductProductStock(int $productId, float $quantity, int $orderId = 0): void
    {
        $restaurantStock = Stock::forModule('restaurant');

        $stockItem = $restaurantStock
            ? StockItem::where('stock_id', $restaurantStock->id)->where('product_id', $productId)->first()
            : StockItem::where('product_id', $productId)->first();

        if (!$stockItem) {
            return;
        }

        $stockItem->quantity -= $quantity;
        $stockItem->save();

        StockMovement::create([
            'product_id'     => $productId,
            'stock_id'       => $stockItem->stock_id,
            'type'           => 'out',
            'quantity'       => $quantity,
            'origin_module'  => 'restaurant',
            'origin_type'    => 'order',
            'origin_id'      => $orderId ?: null,
            'user_id'        => auth()->id(),
            'notes'          => 'Vente restaurant (extra) — Commande #' . $orderId,
        ]);
    }

    /**
     * Déduit le stock basé sur la recette d'un plat.
     * Utilise le stock lié au module restaurant ; trace les mouvements.
     */
    private function deductStockFromRecipe(int $mealId, float $quantity, int $orderId = 0): void
    {
        $recipe = Recipe::where('meal_id', $mealId)->with('items')->first();
        if (!$recipe) {
            return;
        }

        // Stock lié au restaurant
        $restaurantStock = Stock::forModule('restaurant');

        foreach ($recipe->items as $ri) {
            $totalQty = $ri->quantity * $quantity;

            // Prendre le stock lié au restaurant en priorité, sinon le premier stock ayant ce produit
            $stockItem = $restaurantStock
                ? StockItem::where('stock_id', $restaurantStock->id)->where('product_id', $ri->product_id)->first()
                : StockItem::where('product_id', $ri->product_id)->first();

            if (!$stockItem) {
                continue;
            }

            $stockItem->quantity -= $totalQty;
            $stockItem->save();

            // Tracer le mouvement
            StockMovement::create([
                'product_id'     => $ri->product_id,
                'stock_id'       => $stockItem->stock_id,
                'type'           => 'out',
                'quantity'       => $totalQty,
                'origin_module'  => 'restaurant',
                'origin_type'    => 'order',
                'origin_id'      => $orderId ?: null,
                'user_id'        => auth()->id(),
                'notes'          => 'Vente restaurant — Commande #' . $orderId,
            ]);
        }
    }

    /**
     * Affiche la liste des commandes avec status
     */
    public function orders()
    {
        $this->perm('pos.orders.view');
        $orders = Order::with('items.meal')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('pos.orders', compact('orders'));
    }

    /**
     * Affiche les détails d'une commande
     */
    public function orderDetail($id)
    {
        $this->perm('pos.orders.view');
        $order = Order::with([
            'items.meal.category',
            'items.meal.recipe.items.product',
            'items.product.unit',
            'items.accompaniments'
        ])->findOrFail($id);

        return view('pos.order-detail', compact('order'));
    }

    /**
     * Marque une commande comme payée
     */
    public function markAsPaid($id)
    {
        $this->perm('pos.orders.mark-paid');
        $order = Order::findOrFail($id);
        $order->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        return redirect()->route('pos.order-detail', $id)
            ->with('success', 'Commande marquée comme payée');
    }

    /**
     * Annule une commande et restaure le stock
     */
    public function cancelOrder($id)
    {
        $this->perm('pos.orders.cancel');
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($id);

            if ($order->status === 'paid') {
                return back()->with('error', 'Impossible d\'annuler une commande payée');
            }

            // Restaurer le stock pour chaque article
            foreach ($order->items as $item) {
                // Cas restaurant: plat lié à une recette
                if ($item->meal_id) {
                    $this->restoreStockFromRecipe($item->meal_id, $item->quantity);
                    continue;
                }

                // Cas catering vente libre: restituer la quantité vendue au stock terminal
                if ($item->product_id) {
                    $terminalId = $order->cashRegister?->pos_terminal_id;
                    if ($terminalId) {
                        $terminalStockItem = PosTerminalStockItem::where('pos_terminal_id', $terminalId)
                            ->where('item_type', 'extra')
                            ->where('product_id', $item->product_id)
                            ->first();

                        if ($terminalStockItem) {
                            $newSold = max(0, (float) $terminalStockItem->quantity_sold - (float) $item->quantity);
                            $terminalStockItem->update(['quantity_sold' => $newSold]);
                        }
                    }
                }
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();

            return back()->with('success', 'Commande annulée et stock restauré');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Page caissier — liste en temps réel des commandes à encaisser
     */
    public function cashier()
    {
        $this->perm('pos.cashier');
        $currentUser = auth()->user();

        if (!$currentUser) {
            return redirect()->route('login');
        }

        $paymentTypes = $this->getPosPaymentTypes();
        $cashRegister = CashRegister::where('user_id', $currentUser->id)
            ->where('module', self::POS_MODULE)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$cashRegister) {
            return redirect()->to(url('/modules/pos'))->with('error', 'Aucune caisse restaurant active pour votre compte. La comptabilité doit ouvrir votre session.');
        }

        return view('pos.cashier', compact('paymentTypes', 'cashRegister'));
    }

    /**
     * API : liste des commandes actives pour le caissier (polling)
     */
    public function pendingOrders()
    {
        $this->perm('pos.pending-orders');
        $cashRegister = CashRegister::where('user_id', auth()->id())
            ->where('module', self::POS_MODULE)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$cashRegister) {
            return response()->json([]);
        }

        $orders = Order::with(['items.meal', 'items.product'])
            ->where('cash_register_id', $cashRegister->id)
            ->whereIn('status', ['pending', 'sent', 'paid'])
            ->orderByRaw("FIELD(status,'sent','pending','paid')")
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($order) {
                return [
                    'id'              => $order->id,
                    'customer_number' => $order->customer_number,
                    'total_amount'    => $order->total_amount,
                    'status'          => $order->status,
                    'is_prepared'     => $order->is_prepared,
                    'created_at'      => $order->created_at->format('H:i'),
                    'items_count'     => $order->items->sum('quantity'),
                    'items_summary'   => $order->items
                        ->map(fn($i) => (($i->meal?->name ?? $i->product?->name ?? $i->label ?? 'Article') . ' ×' . $i->quantity))
                        ->join(', '),
                    'paid_at'         => $order->paid_at?->format('H:i'),
                ];
            });

        return response()->json($orders);
    }

    /**
     * API : encaisser une commande
     */
    public function processPayment(Request $request, $id)
    {
        $this->perm('pos.orders.payment');
        $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'amount'          => 'required|numeric|min:0',
        ]);

        if (!$this->isAllowedPosPaymentType((int) $request->payment_type_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Mode de paiement non autorise en point de vente. Utilisez espece ou wallet (Bankili, Sadad, Masrivi, Amanaty).'
            ], 422);
        }

        $order = Order::findOrFail($id);

        $cashRegister = CashRegister::where('user_id', auth()->id())
            ->where('module', self::POS_MODULE)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$cashRegister) {
            return response()->json(['success' => false, 'message' => 'Aucune caisse active pour votre compte'], 422);
        }

        if ($order->cash_register_id !== $cashRegister->id) {
            return response()->json(['success' => false, 'message' => 'Commande non assignée à votre caisse'], 403);
        }

        if ($order->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Commande déjà encaissée'], 422);
        }
        if ($order->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Commande annulée'], 422);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'order_id'         => $order->id,
                'payment_type_id'  => $request->payment_type_id,
                'amount'           => $order->total_amount,
                'cash_register_id' => $cashRegister?->id,
            ]);

            Transaction::create([
                'type'      => 'sale',
                'amount'    => $order->total_amount,
                'reference' => 'POS-' . $order->id,
                'date'      => now()->toDateString(),
                'module'    => 'pos',
            ]);

            app(AccountingEntryService::class)->postSale($payment, self::POS_MODULE);

            $order->update([
                'status'     => 'paid',
                'cashier_id' => auth()->id(),
                'paid_at'    => now(),
            ]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Paiement enregistré',
                'order_id' => $order->id,
                'total'    => $order->total_amount,
                'change'   => max(0, $request->amount - $order->total_amount),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  TICKET DE CAISSE
    // ══════════════════════════════════════════════════════════════

    /**
     * Bon de commande cuisine (sans prix) — imprimable sur imprimante cuisine.
     */
    public function printKitchenTicket($id)
    {
        $this->perm('pos.orders.view');

        $order = Order::with(['items.meal', 'items.product', 'items.accompaniments', 'server'])
            ->findOrFail($id);

        $company = config('app.company');

        return view('pos.kitchen-ticket', compact('order', 'company'));
    }

    /**
     * Affiche le ticket imprimable pour une commande payée.
     */
    public function printReceipt(Request $request, $id)
    {
        $this->perm('pos.orders.payment');

        $order = Order::with(['items.meal', 'items.product', 'payment.paymentType', 'server', 'cashier', 'cashRegister'])
            ->findOrFail($id);

        // Monnaie rendue (passée en query string depuis la caisse)
        $received = (float) $request->query('received', $order->total_amount);
        $change   = max(0, $received - (float) $order->total_amount);

        $company = config('app.company');

        // Override logo/name with the module-specific branding
        $module     = $order->cashRegister?->module;
        $moduleCfg  = config("app.modules.{$module}");
        if ($module && $moduleCfg) {
            $company['logo'] = $moduleCfg['logo'];
            $company['name'] = $moduleCfg['name'];
        }

        return view('pos.receipt', compact('order', 'company', 'received', 'change'));
    }

    // ══════════════════════════════════════════════════════════════
    //  CATERING POS — Vente libre (boissons, desserts, extras)
    // ══════════════════════════════════════════════════════════════

    /**
     * Interface POS pour le catering — vente d'articles hors contrat
     */
    public function cateringIndex()
    {
        $this->perm('pos.view');

        $activeRegister = CashRegister::with(['user', 'posTerminal'])
            ->where('module', 'catering')
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        // Produits consommables disponibles dans le terminal de cette session
        $consumableStock = [];
        if ($activeRegister && $activeRegister->pos_terminal_id) {
            $consumableStock = PosTerminalStockItem::with('product.unit')
                ->where('pos_terminal_id', $activeRegister->pos_terminal_id)
                ->where('item_type', 'extra')
                ->whereNotNull('product_id')
                ->whereHas('product', fn($q) => $q->where('is_consumable', true))
                ->get()
                ->filter(fn($si) => $si->available_qty > 0)
                ->values();
        }

            $paymentTypes = $this->getPosPaymentTypes();

            return view('pos.catering', compact('activeRegister', 'consumableStock', 'paymentTypes'));
    }

    /**
     * API: Crée une commande catering hors contrat (boissons, extras…)
     * Chaque item référence un PosTerminalStockItem (produit consommable).
     * La quantité vendue est débitée du stock terminal en temps réel.
     */
    public function cateringCreateOrder(Request $request)
    {
        $this->perm('pos.orders.create');

        $activeRegister = CashRegister::with('posTerminal')
            ->where('module', 'catering')
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if (!$activeRegister) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune caisse catering ouverte.',
            ], 422);
        }

        $validated = $request->validate([
            'items'                         => 'required|array|min:1',
            'items.*.stock_item_id'         => 'required|exists:pos_terminal_stock_items,id',
            'items.*.quantity'              => 'required|integer|min:1',
            'customer_number'               => 'nullable|string|max:50',
            'payment_type_id'               => 'required|exists:payment_types,id',
        ]);

        if (!$this->isAllowedPosPaymentType((int) $validated['payment_type_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Mode de paiement non autorise en point de vente. Utilisez espece ou wallet (Bankili, Sadad, Masrivi, Amanaty).',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $serverTotal = 0;
            $lineItems   = [];

            foreach ($validated['items'] as $item) {
                $stockItem = PosTerminalStockItem::with('product')
                    ->where('id', $item['stock_item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($stockItem->pos_terminal_id !== $activeRegister->pos_terminal_id) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Article non autorisé pour ce terminal.',
                    ], 422);
                }

                if ($stockItem->item_type !== 'extra' || !$stockItem->product_id || !$stockItem->product?->is_consumable) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Article non vendable en vente libre.',
                    ], 422);
                }

                // Vérifier stock suffisant
                if ($stockItem->available_qty < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuffisant pour « {$stockItem->label} » (disponible : {$stockItem->available_qty})",
                    ], 422);
                }

                $price = $stockItem->product ? (float) $stockItem->product->sale_price : 0;
                $serverTotal += $price * $item['quantity'];

                $lineItems[] = [
                    'stockItem' => $stockItem,
                    'qty'       => $item['quantity'],
                    'price'     => $price,
                ];
            }

            $order = Order::create([
                'customer_number'  => $validated['customer_number'] ?? null,
                'server_id'        => auth()->id(),
                'cashier_id'       => auth()->id(),
                'cash_register_id' => $activeRegister->id,
                'total_amount'     => $serverTotal,
                'status'           => 'paid',
                'is_prepared'      => true, // produits prêts (pas de cuisine)
                'paid_at'          => now(),
            ]);

            foreach ($lineItems as $line) {
                $si = $line['stockItem'];

                OrderItem::create([
                    'order_id'   => $order->id,
                    'meal_id'    => null,
                    'product_id' => $si->product_id,
                    'label'      => $si->label,
                    'quantity'   => $line['qty'],
                    'price'      => $line['price'],
                    'cost_price' => 0,
                ]);

                // Déduire du stock terminal
                $si->increment('quantity_sold', $line['qty']);
            }

            $payment = Payment::create([
                'order_id'         => $order->id,
                'payment_type_id'  => $validated['payment_type_id'],
                'amount'           => $order->total_amount,
                'cash_register_id' => $activeRegister->id,
            ]);

            Transaction::create([
                'type'      => 'sale',
                'amount'    => $order->total_amount,
                'reference' => 'CAT-' . $order->id,
                'date'      => now()->toDateString(),
                'module'    => 'catering',
            ]);

            app(AccountingEntryService::class)->postSale($payment, 'catering');

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Commande créée avec succès',
                'order_id' => $order->id,
                'total'    => $serverTotal,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  COMPTABILITÉ CAISSE
    // ══════════════════════════════════════════════════════════════

    /**
     * Vue principale comptabilité — liste toutes les sessions de caisse
     */
    public function accountingIndex(Request $request)
    {
        $this->perm('pos.accounting.view');

        $filters = [
            'module' => $request->query('module'),
            'cashier_user_id' => $request->query('cashier_user_id'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ];

        $registers = CashRegister::with('user')
            ->withSum('payments', 'amount')
            ->withCount('payments')
            ->when($filters['module'], fn($q, $v) => $q->where('module', $v))
            ->when($filters['cashier_user_id'], fn($q, $v) => $q->where('user_id', $v))
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('opened_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('opened_at', '<=', $v))
            ->orderByDesc('opened_at')
            ->paginate(25);

        $cashiers = User::role('caissier')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Dashboard: aujourd'hui seulement
        $today = now()->toDateString();

        $recentTransactions = Transaction::orderByDesc('date')
            ->whereDate('date', $today)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $recentPaidOrders = Order::with(['items.meal', 'cashRegister.user', 'payment.paymentType'])
            ->where('status', 'paid')
            ->when($filters['module'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('module', $v));
            })
            ->when($filters['cashier_user_id'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('user_id', $v));
            })
            ->whereDate('paid_at', $today)
            ->orderByDesc('paid_at')
            ->limit(50)
            ->get();

        // Mouvements de stock du jour
        $recentStockMovements = StockMovement::with(['product.unit', 'stock', 'user'])
            ->whereNotNull('origin_module')
            ->when($filters['module'], fn($q, $v) => $q->where('origin_module', $v))
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $stats = DB::table('cash_registers')
            ->when($filters['module'], fn($q, $v) => $q->where('module', $v))
            ->when($filters['cashier_user_id'], fn($q, $v) => $q->where('user_id', $v))
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('opened_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('opened_at', '<=', $v))
            ->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status='open'      THEN 1 ELSE 0 END) as open,
            SUM(CASE WHEN status='closed'    THEN 1 ELSE 0 END) as closed,
            SUM(CASE WHEN status='validated' THEN 1 ELSE 0 END) as validated,
            SUM(CASE WHEN status='flagged'   THEN 1 ELSE 0 END) as flagged
        ")->first();

        return view('pos.accounting', compact('registers', 'stats', 'cashiers', 'recentTransactions', 'recentPaidOrders', 'recentStockMovements', 'filters'));
    }

    private function getPosPaymentTypes()
    {
        return PaymentType::posAllowed();
    }

    private function isAllowedPosPaymentType(int $paymentTypeId): bool
    {
        return PaymentType::isPosAllowed($paymentTypeId);
    }

    public function accountingTraces(Request $request)
    {
        $this->perm('pos.accounting.traces.view');

        $filters = [
            'cashier_user_id' => $request->query('cashier_user_id'),
            'from_date'       => $request->query('from_date'),
            'to_date'         => $request->query('to_date'),
        ];

        $cashiers = User::role('caissier')->orderBy('name')->get(['id', 'name']);

        // Transactions comptables
        $transactions = Transaction::orderByDesc('date')
            ->when($filters['type'], fn($q, $v) => $q->where('type', $v))
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('date', '<=', $v))
            ->orderByDesc('id')
            ->paginate(50, ['*'], 'tx_page');

        // Ventes détaillées
        $orders = Order::with(['items.meal', 'cashRegister.user', 'payment.paymentType'])
            ->where('status', 'paid')
            ->when($filters['module'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('module', $v));
            })
            ->when($filters['cashier_user_id'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('user_id', $v));
            })
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('paid_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('paid_at', '<=', $v))
            ->orderByDesc('paid_at')
            ->paginate(50, ['*'], 'order_page');

        // Mouvements de stock
        $stockMovements = StockMovement::with(['product.unit', 'stock', 'user'])
            ->whereNotNull('origin_module')
            ->when($filters['module'], fn($q, $v) => $q->where('origin_module', $v))
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->paginate(50, ['*'], 'sm_page');

        abort(404); // Vue supprimée, accès interdit depuis POS
    }

    public function exportAccountingTracesFullCsv(Request $request)
    {
        $this->perm('pos.accounting.traces.export');

        $filters = [
            'module'          => $request->query('module'),
            'type'            => $request->query('type'),
            'cashier_user_id' => $request->query('cashier_user_id'),
            'from_date'       => $request->query('from_date'),
            'to_date'         => $request->query('to_date'),
        ];

        $transactions = Transaction::orderByDesc('date')
            ->when($filters['type'], fn($q, $v) => $q->where('type', $v))
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('date', '<=', $v))
            ->orderByDesc('id')
            ->get();

        $orders = Order::with(['items.meal', 'cashRegister.user', 'payment.paymentType'])
            ->where('status', 'paid')
            ->when($filters['module'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('module', $v));
            })
            ->when($filters['cashier_user_id'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('user_id', $v));
            })
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('paid_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('paid_at', '<=', $v))
            ->orderByDesc('paid_at')
            ->get();

        $stockMovements = StockMovement::with(['product.unit', 'stock', 'user'])
            ->whereNotNull('origin_module')
            ->when($filters['module'], fn($q, $v) => $q->where('origin_module', $v))
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->get();

        $filename = 'traces_comptables_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($transactions, $orders, $stockMovements, $filters) {
            $fh = fopen('php://output', 'w');
            fwrite($fh, "\xEF\xBB\xBF");

            // Header info
            fputcsv($fh, ['Complex Royal — Traces Comptables Globales']);
            fputcsv($fh, ['Généré le', now()->format('d/m/Y H:i')]);
            if ($filters['module']) fputcsv($fh, ['Module filtré', ucfirst($filters['module'])]);
            if ($filters['from_date']) fputcsv($fh, ['Du', $filters['from_date']]);
            if ($filters['to_date']) fputcsv($fh, ['Au', $filters['to_date']]);
            fputcsv($fh, []);

            // --- TRANSACTIONS ---
            fputcsv($fh, ['=== TRANSACTIONS COMPTABLES ===']);
            fputcsv($fh, ['ID', 'Date', 'Type', 'Reference', 'Montant (MRU)']);
            foreach ($transactions as $t) {
                fputcsv($fh, [
                    $t->id,
                    \Carbon\Carbon::parse($t->date)->format('d/m/Y'),
                    match($t->type) {
                        'sale' => 'Vente',
                        'purchase' => 'Achat',
                        'salary' => 'Salaire',
                        'expense' => 'Dépense',
                        default => ucfirst($t->type),
                    },
                    $t->reference ?? '',
                    number_format((float)$t->amount, 2, '.', ''),
                ]);
            }

            fputcsv($fh, []);

            // --- VENTES ---
            fputcsv($fh, ['=== VENTES DÉTAILLÉES ===']);
            fputcsv($fh, ['Commande #', 'Module', 'Caissier', 'Articles', 'Mode paiement', 'Montant (MRU)', 'Payé le']);
            foreach ($orders as $o) {
                fputcsv($fh, [
                    $o->id,
                    ucfirst($o->cashRegister?->module ?? ''),
                    $o->cashRegister?->user?->name ?? '',
                    $o->items->map(fn($i) => ($i->meal->name ?? 'Article') . ' ×' . $i->quantity)->join(' | '),
                    $o->payment?->paymentType?->name ?? '',
                    number_format((float)$o->total_amount, 2, '.', ''),
                    $o->paid_at?->format('d/m/Y H:i') ?? '',
                ]);
            }

            fputcsv($fh, []);

            // --- STOCK ---
            fputcsv($fh, ['=== MOUVEMENTS DE STOCK ===']);
            fputcsv($fh, ['Date', 'Stock', 'Produit', 'Unité', 'Module', 'Type', 'Quantité', 'Référence', 'Opérateur']);
            foreach ($stockMovements as $mv) {
                fputcsv($fh, [
                    $mv->created_at->format('d/m/Y H:i'),
                    $mv->stock?->name ?? '',
                    $mv->product?->name ?? '',
                    $mv->product?->unit?->name ?? '',
                    $mv->origin_module_label,
                    match($mv->type) {
                        'out' => 'Sortie',
                        'in' => 'Entrée',
                        default => 'Transfert',
                    },
                    ($mv->type === 'out' ? '-' : '+') . number_format((float)$mv->quantity, 2, '.', ''),
                    $mv->notes ?? ($mv->origin_id ? '#' . $mv->origin_id : ''),
                    $mv->user?->name ?? '',
                ]);
            }

            fclose($fh);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportAccountingSessionsCsv(Request $request)
    {
        $this->perm('pos.accounting.sessions.export');

        $filters = [
            'module' => $request->query('module'),
            'cashier_user_id' => $request->query('cashier_user_id'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ];

        $rows = CashRegister::with(['user', 'validator'])
            ->withSum('payments', 'amount')
            ->with(['payments.paymentType'])
            ->when($filters['module'], fn($q, $v) => $q->where('module', $v))
            ->when($filters['cashier_user_id'], fn($q, $v) => $q->where('user_id', $v))
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('opened_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('opened_at', '<=', $v))
            ->orderByDesc('opened_at')
            ->get();

        $callback = function () use ($rows) {
            $fh = fopen('php://output', 'w');
            fwrite($fh, "\xEF\xBB\xBF");

            fputcsv($fh, [
                'Session', 'Module', 'Caissier', 'Poste', 'Ouverture', 'Fermeture',
                'Statut', 'Fond ouverture', 'Total encaisse', 'Solde reel', 'Ecart',
                'Excedent declare', 'Valide par', 'Valide le', 'Note'
            ]);

            foreach ($rows as $r) {
                $cashTotal = (float) $r->payments->filter(function ($p) {
                    $name = mb_strtolower($p->paymentType?->name ?? '');
                    return str_contains($name, 'espèce') || str_contains($name, 'espece')
                        || str_contains($name, 'cash') || str_contains($name, 'liquide');
                })->sum('amount');

                $expected = (float) $r->opening_balance + $cashTotal;
                $difference = $r->closing_balance !== null ? (float) $r->closing_balance - $expected : null;

                fputcsv($fh, [
                    $r->id,
                    $r->module,
                    $r->user?->name,
                    $r->shift,
                    $r->opened_at?->format('Y-m-d H:i:s'),
                    $r->closed_at?->format('Y-m-d H:i:s'),
                    $r->status,
                    (float) $r->opening_balance,
                    (float) ($r->payments_sum_amount ?? 0),
                    $r->closing_balance !== null ? (float) $r->closing_balance : '',
                    $difference,
                    $r->declared_excess,
                    $r->validator?->name,
                    $r->validated_at?->format('Y-m-d H:i:s'),
                    $r->accounting_note,
                ]);
            }

            fclose($fh);
        };

        return response()->streamDownload($callback, 'accounting_sessions.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportAccountingTracesCsv(Request $request)
    {
        $this->perm('pos.accounting.traces.export');

        $filters = [
            'module' => $request->query('module'),
            'cashier_user_id' => $request->query('cashier_user_id'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ];

        $transactions = Transaction::orderByDesc('date')
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('date', '<=', $v))
            ->limit(500)
            ->get();

        $orders = Order::with(['items.meal', 'cashRegister.user', 'payment.paymentType'])
            ->where('status', 'paid')
            ->when($filters['module'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('module', $v));
            })
            ->when($filters['cashier_user_id'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('user_id', $v));
            })
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('paid_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('paid_at', '<=', $v))
            ->orderByDesc('paid_at')
            ->limit(500)
            ->get();

        $callback = function () use ($transactions, $orders) {
            $fh = fopen('php://output', 'w');
            fwrite($fh, "\xEF\xBB\xBF");

            fputcsv($fh, ['TRANSACTIONS']);
            fputcsv($fh, ['ID', 'Date', 'Type', 'Reference', 'Montant']);
            foreach ($transactions as $t) {
                fputcsv($fh, [
                    $t->id,
                    $t->date,
                    $t->type,
                    $t->reference,
                    (float) $t->amount,
                ]);
            }

            fputcsv($fh, []);
            fputcsv($fh, ['VENTES DETAILLEES']);
            fputcsv($fh, ['Commande', 'Module', 'Caissier', 'Articles', 'Paiement', 'Total', 'Paye le']);
            foreach ($orders as $o) {
                fputcsv($fh, [
                    $o->id,
                    $o->cashRegister?->module,
                    $o->cashRegister?->user?->name,
                    $o->items->map(fn($i) => ($i->meal->name ?? 'Article') . ' x' . $i->quantity)->join(', '),
                    $o->payment?->paymentType?->name,
                    (float) $o->total_amount,
                    $o->paid_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($fh);
        };

        return response()->streamDownload($callback, 'accounting_traces.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportAccountingSessionsPdf(Request $request)
    {
        $this->perm('pos.accounting.sessions.export');

        $filters = [
            'module' => $request->query('module'),
            'cashier_user_id' => $request->query('cashier_user_id'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ];

        $rows = CashRegister::with(['user'])
            ->withSum('payments', 'amount')
            ->when($filters['module'], fn($q, $v) => $q->where('module', $v))
            ->when($filters['cashier_user_id'], fn($q, $v) => $q->where('user_id', $v))
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('opened_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('opened_at', '<=', $v))
            ->orderByDesc('opened_at')
            ->get();

        return response()->view('pos.exports.accounting-sessions-pdf', [
            'rows' => $rows,
            'filters' => $filters,
        ]);
    }

    public function exportAccountingTracesPdf(Request $request)
    {
        $this->perm('pos.accounting.traces.export');

        $filters = [
            'module' => $request->query('module'),
            'cashier_user_id' => $request->query('cashier_user_id'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ];

        $transactions = Transaction::orderByDesc('date')
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('date', '<=', $v))
            ->limit(500)
            ->get();

        $orders = Order::with(['items.meal', 'cashRegister.user', 'payment.paymentType'])
            ->where('status', 'paid')
            ->when($filters['module'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('module', $v));
            })
            ->when($filters['cashier_user_id'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('user_id', $v));
            })
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('paid_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('paid_at', '<=', $v))
            ->orderByDesc('paid_at')
            ->limit(500)
            ->get();

        abort(404); // Vue PDF supprimée, accès interdit depuis POS
    }

    /**
     * Détail d'une session de caisse pour la comptabilité
     */
    public function accountingDetail($id)
    {
        $this->perm('pos.accounting.register.view-detail');

        $register = CashRegister::with(['user', 'validator'])->findOrFail($id);

        $payments = Payment::with(['order.items.meal', 'paymentType'])
            ->where('cash_register_id', $id)
            ->orderBy('created_at')
            ->get();

        $byType = $payments->groupBy('payment_type_id')->map(function ($group) {
            return [
                'name'  => $group->first()->paymentType?->name ?? 'Inconnu',
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ];
        })->sortByDesc('total');

        $systemTotal = (float) $payments->sum('amount');

        $cashTotal = (float) $payments->filter(function ($p) {
            $name = mb_strtolower($p->paymentType?->name ?? '');
            return str_contains($name, 'espèce') || str_contains($name, 'espece')
                || str_contains($name, 'cash') || str_contains($name, 'liquide');
        })->sum('amount');

        $expectedCash = (float) ($register->opening_balance ?? 0) + $cashTotal;

        $difference = $register->closing_balance !== null
            ? (float) $register->closing_balance - $expectedCash
            : null;

        abort(404); // Vue supprimée, accès interdit depuis POS
    }

    /**
     * API : ouvrir une nouvelle caisse
     */
    public function openRegister(Request $request)
    {
        $this->perm('pos.accounting.register.open');

        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'shift'           => 'required|in:morning,evening',
            'module'          => 'required|in:restaurant,catering,events,residence',
            'cashier_user_id' => 'required|exists:users,id',
        ]);

        $cashier = User::findOrFail($request->cashier_user_id);
        if (!$cashier->hasRole('caissier')) {
            return response()->json([
                'success' => false,
                'message' => 'L\'utilisateur sélectionné n\'a pas le rôle caissier.'
            ], 422);
        }

        // Vérifie qu'aucune session n'est déjà ouverte pour ce module
        $existing = CashRegister::where('module', $request->module)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Une caisse est déjà ouverte pour le module ' . $request->module . ' (session #' . $existing->id . ').'
            ], 422);
        }

        // Vérifie qu'on n'ouvre pas deux fois le même poste dans la même journée
        $todayShift = CashRegister::where('module', $request->module)
            ->where('shift', $request->shift)
            ->whereDate('opened_at', today())
            ->whereIn('status', ['closed', 'validated', 'flagged'])
            ->first();

        if ($todayShift) {
            $shiftLabel = $request->shift === 'morning' ? 'matin' : 'soir';
            return response()->json([
                'success' => false,
                'message' => 'Le poste "' . $shiftLabel . '" a déjà été utilisé aujourd\'hui pour le module ' . $request->module . ' (session #' . $todayShift->id . '). Impossible d\'ouvrir une deuxième session pour ce poste.',
            ], 422);
        }

        $register = CashRegister::create([
            'user_id'         => $cashier->id,
            'module'          => $request->module,
            'shift'           => $request->shift,
            'opening_balance' => $request->opening_balance,
            'opened_at'       => now(),
            'status'          => 'open',
        ]);

        return response()->json(['success' => true, 'register_id' => $register->id]);
    }

    /**
     * API : fermer la caisse (soumis par le caissier)
     */
    public function submitClosing(Request $request, $id)
    {
        $this->perm('pos.accounting.register.close');

        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'declared_excess' => 'nullable|numeric',
        ]);

        $register = CashRegister::findOrFail($id);

        if ($register->status !== 'open') {
            return response()->json(['success' => false, 'message' => 'Caisse déjà fermée'], 422);
        }

        $register->update([
            'closing_balance' => $request->closing_balance,
            'declared_excess' => $request->declared_excess,
            'closed_at'       => now(),
            'status'          => 'closed',
            'closing_history' => [[
                'old_balance'    => null,
                'new_balance'    => (float) $request->closing_balance,
                'old_excess'     => null,
                'new_excess'     => $request->declared_excess !== null ? (float) $request->declared_excess : null,
                'note'           => 'Fermeture initiale',
                'changed_by'     => auth()->id(),
                'changed_by_name'=> auth()->user()?->name ?? '—',
                'changed_at'     => now()->toDateTimeString(),
            ]],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * API : modifier le solde déclaré d'une session fermée (avec trace)
     */
    public function updateClosingBalance(Request $request, $id)
    {
        $this->perm('pos.accounting.register.close');

        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'declared_excess' => 'nullable|numeric',
            'edit_note'       => 'nullable|string|max:500',
        ]);

        $register = CashRegister::findOrFail($id);

        if ($register->status !== 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'La modification du solde est possible uniquement sur une session en statut « fermée ».',
            ], 422);
        }

        $history   = $register->closing_history ?? [];
        $history[] = [
            'old_balance'    => (float) $register->closing_balance,
            'new_balance'    => (float) $request->closing_balance,
            'old_excess'     => $register->declared_excess !== null ? (float) $register->declared_excess : null,
            'new_excess'     => $request->declared_excess !== null ? (float) $request->declared_excess : null,
            'note'           => $request->edit_note ?: null,
            'changed_by'     => auth()->id(),
            'changed_by_name'=> auth()->user()?->name ?? '—',
            'changed_at'     => now()->toDateTimeString(),
        ];

        $register->update([
            'closing_balance' => $request->closing_balance,
            'declared_excess' => $request->declared_excess,
            'closing_history' => $history,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * API : annuler la fermeture d'une caisse (réouvrir)
     */
    public function reopenRegister(Request $request, $id)
    {
        $this->perm('pos.accounting.register.close');

        $register = CashRegister::findOrFail($id);

        if ($register->status !== 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Seule une session avec le statut "fermée" peut être réouverte.',
            ], 422);
        }

        $register->update([
            'status'          => 'open',
            'closed_at'       => null,
            'closing_balance' => null,
            'declared_excess' => null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * API : valider ou signaler un problème (soumis par le comptable)
     */
    public function validateRegister(Request $request, $id)
    {
        $this->perm('pos.accounting.register.validate');

        $request->validate([
            'action'          => 'required|in:validate,flag',
            'accounting_note' => 'nullable|string|max:1000',
        ]);

        $register = CashRegister::findOrFail($id);

        if ($register->status === 'open') {
            return response()->json(['success' => false, 'message' => 'La caisse est toujours ouverte'], 422);
        }

        $register->update([
            'status'          => $request->action === 'validate' ? 'validated' : 'flagged',
            'accounting_note' => $request->accounting_note,
            'validated_by'    => auth()->id(),
            'validated_at'    => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════

    /**
     * Restaure le stock après annulation
     */
    private function restoreStockFromRecipe($mealId, $quantity)
    {
        $recipe = Recipe::where('meal_id', $mealId)->first();

        if (!$recipe) {
            return;
        }

        $recipeItems = $recipe->items;

        foreach ($recipeItems as $item) {
            $totalQuantityToRestore = $item->quantity * $quantity;

            $stockItem = StockItem::where('product_id', $item->product_id)->first();

            if ($stockItem) {
                $stockItem->quantity += $totalQuantityToRestore;
                $stockItem->save();
            }
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  RENTABILITÉ / BÉNÉFICES
    // ══════════════════════════════════════════════════════════════

    /**
     * Calcule le coût de revient d'un repas à partir de sa recette
     * en utilisant le dernier prix d'achat reçu de chaque ingrédient.
     */
    private function calculateRecipeCost(int $mealId): float
    {
        $recipe = Recipe::where('meal_id', $mealId)->with('items')->first();
        if (!$recipe) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($recipe->items as $ri) {
            $lastPurchasePrice = PurchaseOrderItem::where('product_id', $ri->product_id)
                ->whereHas('purchaseOrder', fn($q) => $q->where('status', 'received'))
                ->latest()
                ->value('price') ?? 0.0;

            $total += (float) $ri->quantity * (float) $lastPurchasePrice;
        }

        return round($total, 2);
    }

    /**
     * Rapport de rentabilité des repas — par jour / semaine / mois / année.
     */
    public function profitabilityReport(Request $request)
    {
        $this->perm('pos.accounting.profitability');

        $period    = $request->get('period', 'day');
        $dateInput = $request->get('date', now()->toDateString());

        $date = \Carbon\Carbon::parse($dateInput);

        switch ($period) {
            case 'week':
                $start = $date->copy()->startOfWeek();
                $end   = $date->copy()->endOfWeek();
                $label = 'Semaine du ' . $start->format('d/m/Y') . ' au ' . $end->format('d/m/Y');
                break;
            case 'month':
                $start = $date->copy()->startOfMonth();
                $end   = $date->copy()->endOfMonth();
                $label = $date->translatedFormat('F Y');
                break;
            case 'year':
                $start = $date->copy()->startOfYear();
                $end   = $date->copy()->endOfYear();
                $label = $date->format('Y');
                break;
            default: // day
                $start = $date->copy()->startOfDay();
                $end   = $date->copy()->endOfDay();
                $label = $date->format('d/m/Y');
                break;
        }

        // Détail par repas
        $rows = DB::table('order_items as oi')
            ->join('orders as o',  'o.id', '=', 'oi.order_id')
            ->join('meals as m',   'm.id', '=', 'oi.meal_id')
            ->whereBetween('o.paid_at', [$start, $end])
            ->where('o.status', 'paid')
            ->groupBy('oi.meal_id', 'm.name')
            ->select(
                'm.id as meal_id',
                'm.name as meal_name',
                DB::raw('SUM(oi.quantity) as qty_sold'),
                DB::raw('SUM(oi.price * oi.quantity) as revenue'),
                DB::raw('SUM(oi.cost_price * oi.quantity) as total_cost'),
                DB::raw('SUM((oi.price - oi.cost_price) * oi.quantity) as profit'),
                DB::raw('AVG(oi.price) as avg_price'),
                DB::raw('AVG(oi.cost_price) as avg_cost')
            )
            ->orderByDesc('profit')
            ->get();

        $totalRevenue = $rows->sum('revenue');
        $totalCost    = $rows->sum('total_cost');
        $totalProfit  = $rows->sum('profit');
        $margin       = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0;

        // Évolution journalière pour le graphique
        $daily = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween('o.paid_at', [$start, $end])
            ->where('o.status', 'paid')
            ->groupBy(DB::raw('DATE(o.paid_at)'))
            ->select(
                DB::raw('DATE(o.paid_at) as day'),
                DB::raw('SUM(oi.price * oi.quantity) as revenue'),
                DB::raw('SUM(oi.cost_price * oi.quantity) as cost'),
                DB::raw('SUM((oi.price - oi.cost_price) * oi.quantity) as profit')
            )
            ->orderBy('day')
            ->get();

        return view('pos.profitability', compact(
            'rows', 'totalRevenue', 'totalCost', 'totalProfit', 'margin',
            'period', 'dateInput', 'label', 'daily'
        ));
    }
}
