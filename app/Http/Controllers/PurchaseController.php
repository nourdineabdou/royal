<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\SupplierPayment;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\PaymentType;
use Carbon\Carbon;
use App\Models\Transaction;
use App\Services\AccountingEntryService;
use App\Services\StockRequirementService;
class PurchaseController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard(StockRequirementService $stockRequirementService)
    {
        $this->perm('purchases.dashboard');
        $now   = Carbon::now();
        $month = $now->month;
        $year  = $now->year;

        // KPI cards
        $totalSuppliers    = Supplier::count();
        $pendingOrders     = PurchaseOrder::where('status', 'pending')->count();
        $orderedOrders     = PurchaseOrder::where('status', 'ordered')->count();
        $receivedOrders    = PurchaseOrder::where('status', 'received')->count();
        $cancelledOrders   = PurchaseOrder::where('status', 'cancelled')->count();

        $monthlyPurchases  = PurchaseOrder::whereMonth('created_at', $month)
                                ->whereYear('created_at', $year)
                                ->sum('total_amount');

        $unpaidAmount      = PurchaseOrder::where('payment_status', '!=', 'paid')
                                ->sum('remaining_amount');

        $partialOrders     = PurchaseOrder::where('payment_status', 'partial')->count();

        // Stock ruptures (quantity = 0)
        $stockRuptures = StockItem::with('product.unit', 'stock')
                            ->where('quantity', '<=', 0)
                            ->get();

        $lowStockItems = StockItem::with('product.unit', 'stock')
                            ->where('quantity', '>', 0)
                            ->where('quantity', '<=', 5)
                            ->get();

        $totalRuptures = $stockRuptures->count() + $lowStockItems->count();

        // Besoins de production catering du jour non couverts par le stock
        $cateringNeeds = $stockRequirementService->cateringNeedsForDate($now);
        $cateringShortfall = collect($cateringNeeds['comparison'])->filter(fn ($row) => $row['missing'] > 0);
        $cateringShortfallProducts = $cateringShortfall->isNotEmpty()
            ? Product::with('unit')->whereIn('id', $cateringShortfall->keys())->get()->keyBy('id')
            : collect();

        // Monthly chart data (last 6 months)
        $chartLabels  = [];
        $chartTotals  = [];
        $chartPaid    = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $chartLabels[] = $date->translatedFormat('M Y');
            $chartTotals[] = PurchaseOrder::whereMonth('created_at', $date->month)
                                ->whereYear('created_at', $date->year)
                                ->sum('total_amount');
            $chartPaid[]   = PurchaseOrder::whereMonth('created_at', $date->month)
                                ->whereYear('created_at', $date->year)
                                ->sum('paid_amount');
        }

        // Top suppliers this month
        $topSuppliers = Supplier::withCount('purchaseOrders')
                            ->withSum('purchaseOrders', 'total_amount')
                            ->orderByDesc('purchase_orders_sum_total_amount')
                            ->take(5)
                            ->get();

        // Recent orders
        $recentOrders = PurchaseOrder::with('supplier')
                            ->latest()
                            ->take(8)
                            ->get();

        // Recent receipts
        $recentReceipts = GoodsReceipt::with('purchaseOrder.supplier', 'stock')
                            ->latest()
                            ->take(5)
                            ->get();

        return view('purchases.dashboard', compact(
            'totalSuppliers', 'pendingOrders', 'orderedOrders', 'receivedOrders',
            'cancelledOrders', 'monthlyPurchases', 'unpaidAmount', 'partialOrders',
            'stockRuptures', 'lowStockItems', 'totalRuptures',
            'chartLabels', 'chartTotals', 'chartPaid',
            'topSuppliers', 'recentOrders', 'recentReceipts',
            'cateringShortfall', 'cateringShortfallProducts'
        ));
    }

    // ─── Purchase Orders ──────────────────────────────────────────────────────

    /**
     * API : dernier prix payé pour un produit chez un fournisseur donné (+ un petit historique),
     * pour pré-remplir le prix unitaire quand on commande — comme le fait Odoo avec les tarifs fournisseur.
     */
    public function priceHistory(Request $request)
    {
        $this->perm('purchases.orders.view');
        $request->validate([
            'product_id'  => 'required|exists:products,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $query = PurchaseOrderItem::where('product_id', $request->product_id)
            ->whereNotNull('price')
            ->whereHas('purchaseOrder', function ($q) use ($request) {
                if ($request->supplier_id) {
                    $q->where('supplier_id', $request->supplier_id);
                }
            })
            ->with('purchaseOrder.supplier')
            ->latest('created_at');

        $history = $query->take(5)->get()->map(fn ($item) => [
            'price'    => (float) $item->price,
            'date'     => $item->created_at->format('d/m/Y'),
            'supplier' => $item->purchaseOrder->supplier->name ?? 'Fournisseur non défini',
        ]);

        return response()->json([
            'last_price' => $history->first()['price'] ?? null,
            'history'    => $history,
        ]);
    }

    public function orders(Request $request)
    {
        $this->perm('purchases.orders.view');
        $request->validate([
            'todo'    => 'nullable|in:to_confirm,to_receive,to_check,to_pay',
            'invoice' => 'nullable|in:to_check,validated',
            'from'    => 'nullable|date',
            'to'      => 'nullable|date|after_or_equal:from',
        ], ['to.after_or_equal' => 'La date de fin doit être après la date de début.']);

        // « À traiter » : ce qui attend une action, avec le nombre pour chaque pastille
        $todo = [
            'to_confirm' => fn ($q) => $q->where('status', 'pending'),
            'to_receive' => fn ($q) => $q->whereIn('status', ['ordered', 'partial']),
            'to_check'   => fn ($q) => $q->whereIn('status', ['partial', 'received'])->whereNull('invoice_validated_at'),
            'to_pay'     => fn ($q) => $q->whereNotNull('invoice_validated_at')->where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled'),
        ];
        $period = fn ($q) => $q
            ->when($request->from, fn ($x, $d) => $x->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($x, $d) => $x->whereDate('created_at', '<=', $d));
        $todoCounts = collect($todo)->map(fn ($f) => $f($period(PurchaseOrder::query()))->count());

        $query = $period(PurchaseOrder::with('supplier', 'purchaseRequest', 'items'));
        if ($request->todo) {
            $todo[$request->todo]($query);
        }
        if ($request->invoice === 'to_check') {
            $query->whereIn('status', ['partial', 'received'])->whereNull('invoice_validated_at');
        } elseif ($request->invoice === 'validated') {
            $query->whereNotNull('invoice_validated_at');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->packaging_type === 'packaged') {
            $query->whereHas('items', function ($q) {
                $q->whereNotNull('packaging_id');
            });
        }
        if ($request->packaging_type === 'bulk') {
            $query->whereDoesntHave('items', function ($q) {
                $q->whereNotNull('packaging_id');
            });
        }

        $orders    = $query->latest()->paginate(20)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.orders', compact('orders', 'suppliers', 'todoCounts'));
    }

    public function createOrder()
    {
        $this->perm('purchases.orders.create');
        $suppliers    = Supplier::orderBy('name')->get();
        $products     = Product::with('unit', 'productPackagings.packaging')->orderBy('name')->get();
        $paymentTypes = PaymentType::orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products', 'paymentTypes'));
    }

    public function storeOrder(Request $request)
    {
        $this->perm('purchases.orders.create');
        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'nullable|numeric|min:0',
        ]);

        $totalAmount = 0;
        foreach ($request->items as $item) {
            $price = isset($item['unit_price']) && $item['unit_price'] !== '' ? (float) $item['unit_price'] : null;
            $totalAmount += ($price ?? 0) * (float) $item['quantity'];
        }

        $order = PurchaseOrder::create([
            'supplier_id'      => $request->supplier_id,
            'reference'        => 'CMD-' . strtoupper(uniqid()),
            'total_amount'     => $totalAmount,
            'paid_amount'      => 0,
            'remaining_amount' => $totalAmount,
            'payment_status'   => 'unpaid',
            // Créé par le responsable des achats : déjà confirmé. Créé par l'acheteur : à confirmer.
            'status'           => auth()->user()->can('purchases.orders.confirm') ? 'ordered' : 'pending',
        ]);

        foreach ($request->items as $item) {
            $price = isset($item['unit_price']) && $item['unit_price'] !== '' ? (float) $item['unit_price'] : null;
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id'        => $item['product_id'],
                'packaging_id'      => isset($item['packaging_id']) && $item['packaging_id'] ? (int)$item['packaging_id'] : null,
                'quantity'          => $item['quantity'],
                'price'             => $price,
                'total'             => $price !== null ? $price * (float) $item['quantity'] : null,
            ]);
        }

        return redirect()->route('purchases.orders.show', $order)
                         ->with('success', "Commande {$order->reference} créée" . ($order->status === 'ordered'
                             ? ' et confirmée : elle part au magasinier pour la réception.'
                             : ' : en attente de la confirmation du responsable des achats.'));
    }

    public function showOrder(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.view');
        $order->load('supplier', 'purchaseRequest', 'invoiceValidatedBy', 'items.product.unit', 'items.packaging', 'goodsReceipts.items.product.unit', 'goodsReceipts.stock', 'goodsReceipts.receivedBy', 'attachments.uploader', 'items.product.productPackagings', 'supplierPayments.paymentType', 'supplierReturns.items.product');
        $stocks       = Stock::all();
        $paymentTypes = PaymentType::all();
        $suppliers    = Supplier::orderBy('name')->get();

        return view('purchases.show', compact('order', 'stocks', 'paymentTypes', 'suppliers'));
    }

    public function confirmOrder(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.confirm');
        $order->update(['status' => 'ordered']);

        return back()->with('success', 'Commande confirmée et envoyée au fournisseur.');
    }

    /**
     * Contrôle comptable de la facture fournisseur — doit être fait avant d'autoriser le paiement.
     */
    public function validateInvoice(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.validate-invoice');

        if (!in_array($order->status, ['received', 'partial'])) {
            return back()->with('error', 'La commande doit être au moins partiellement reçue avant de valider la facture.');
        }

        // Sans prix, le montant à payer serait faux : le responsable achat doit d'abord les saisir
        $order->loadMissing('items');
        if ($order->hasMissingPrices()) {
            return back()->with('error', 'Des prix manquent sur ce bon de commande : le responsable achat doit les saisir (devis ou facture) avant la validation de la facture.');
        }

        if ($order->invoice_validated_at) {
            return back()->with('error', 'Cette facture est déjà validée.');
        }

        $order->update([
            'invoice_validated_at' => now(),
            'invoice_validated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Facture validée — le paiement peut maintenant être enregistré.');
    }

    /**
     * Retour fournisseur (avoir) : marchandise reçue mais renvoyée (défectueuse, erreur de livraison...).
     * Réduit le stock, réduit ce qu'on doit au fournisseur, et passe une écriture comptable inverse de la réception.
     */
    public function createReturn(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.receipt');
        $order->load('items.product.unit', 'items.packaging');

        $returnableItems = $order->items->filter(fn ($i) => $i->returnable_quantity > 0.001)->values();
        $stocks = Stock::all();

        return view('purchases.return-create', compact('order', 'returnableItems', 'stocks'));
    }

    public function storeReturn(Request $request, PurchaseOrder $order)
    {
        $this->perm('purchases.orders.receipt');
        $validated = $request->validate([
            'reason'                => 'nullable|string',
            'stock_id'              => 'required|exists:stocks,id',
            'items'                 => 'required|array|min:1',
            'items.*.order_item_id' => 'required|integer',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.01',
        ]);

        $orderItemsById = $order->items()->with('packaging', 'product')->get()->keyBy('id');

        foreach ($validated['items'] as $item) {
            $orderItem = $orderItemsById[$item['order_item_id']] ?? null;
            if (!$orderItem || $orderItem->product_id != $item['product_id']) {
                return back()->withErrors(['items' => 'Un produit retourné ne fait pas partie de cette commande.'])->withInput();
            }
            if ((float) $item['quantity'] > $orderItem->returnable_quantity + 0.001) {
                return back()->withErrors([
                    'items' => "La quantité retournée pour {$orderItem->product->name} dépasse ce qui a été reçu et pas déjà retourné ({$orderItem->returnable_quantity})."
                ])->withInput();
            }
        }

        $return = SupplierReturn::create([
            'reference'         => 'RET-' . strtoupper(uniqid()),
            'purchase_order_id' => $order->id,
            'stock_id'          => $validated['stock_id'],
            'reason'            => $validated['reason'] ?? null,
            'created_by'        => auth()->id(),
            'returned_at'       => now(),
        ]);

        $returnTotal = 0;

        foreach ($validated['items'] as $item) {
            $orderItem = $orderItemsById[$item['order_item_id']];
            $qty       = (float) $item['quantity'];
            $unitPrice = (float) ($orderItem->price ?? 0);
            $lineTotal = $qty * $unitPrice;
            $returnTotal += $lineTotal;

            $stockQty = $qty;
            if ($orderItem->packaging_id) {
                $pkgQtyPerUnit = \App\Models\ProductPackaging::where('product_id', $orderItem->product_id)
                    ->where('packaging_id', $orderItem->packaging_id)
                    ->value('quantity');
                if ($pkgQtyPerUnit) {
                    $stockQty = $qty * (float) $pkgQtyPerUnit;
                }
            }

            SupplierReturnItem::create([
                'supplier_return_id' => $return->id,
                'order_item_id'      => $orderItem->id,
                'product_id'         => $item['product_id'],
                'quantity'           => $qty,
                'stock_quantity'     => $stockQty,
                'unit_price'         => $unitPrice,
                'total'              => $lineTotal,
            ]);

            StockItem::where('stock_id', $validated['stock_id'])->where('product_id', $item['product_id'])
                ->update(['quantity' => \DB::raw('GREATEST(0, quantity - ' . $stockQty . ')')]);

            StockMovement::create([
                'product_id'    => $item['product_id'],
                'stock_id'      => $validated['stock_id'],
                'type'          => 'out',
                'quantity'      => $stockQty,
                'origin_module' => 'purchase',
                'origin_type'   => 'supplier_return',
                'origin_id'     => $return->id,
                'user_id'       => auth()->id(),
                'notes'         => 'Retour fournisseur — ' . $return->reference,
            ]);
        }

        $return->update(['total_amount' => $returnTotal]);

        // Réduit d'autant ce qu'on doit au fournisseur pour ce BC.
        $newTotal     = max(0, $order->total_amount - $returnTotal);
        $newRemaining = max(0, $newTotal - $order->paid_amount);
        $order->update([
            'total_amount'     => $newTotal,
            'remaining_amount' => $newRemaining,
            'payment_status'   => $newRemaining <= 0 && $newTotal > 0 ? 'paid' : ($order->paid_amount > 0 ? 'partial' : 'unpaid'),
        ]);

        $stockModule = Stock::find($validated['stock_id'])?->module ?? 'default';
        app(AccountingEntryService::class)->postSupplierReturn($return, $stockModule);

        return redirect()->route('purchases.orders.show', $order)->with('success', 'Retour fournisseur enregistré.');
    }

    public function storePayment(Request $request, PurchaseOrder $order)
    {
        $this->perm('purchases.orders.payment');

        if (!$order->invoice_validated_at) {
            return back()->with('error', 'La facture doit d\'abord être validée par la comptabilité (contrôle comptable).');
        }

        $request->validate([
            'amount'          => 'required|numeric|min:0.01|max:' . $order->remaining_amount,
        ]);

        $supplierPayment = SupplierPayment::create([
            'purchase_order_id' => $order->id,
            'payment_type_id'   => $request->payment_type_id,
            'amount'            => $request->amount,
            'paid_at'           => now(),
        ]);

        // Créer une transaction comptable pour le paiement fournisseur
        // ('purchase' — même famille que la réception ; la table transactions n'a pas de type 'supplier_payment')
        Transaction::create([
            'type'      => 'purchase',
            'amount'    => $request->amount,
            'reference' => 'SUPP-PAY-' . $order->id,
            'date'      => now()->toDateString(),
            'module'    => 'purchase',
            'description' => 'Paiement fournisseur pour commande #' . $order->id,
        ]);

        app(AccountingEntryService::class)->postSupplierPayment($supplierPayment);

        $newPaid      = $order->paid_amount + $request->amount;
        $newRemaining = $order->total_amount - $newPaid;
        $payStatus    = $newRemaining <= 0 ? 'paid' : 'partial';

        $order->update([
            'paid_amount'      => $newPaid,
            'remaining_amount' => max(0, $newRemaining),
            'payment_status'   => $payStatus,
        ]);

        return back()->with('success', 'Paiement enregistré.');
    }

    public function cancelOrder(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.cancel');
        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Commande annulée.');
    }

    // ─── Suppliers ────────────────────────────────────────────────────────────

    public function suppliers()
    {
        $this->perm('purchases.suppliers.view');
        $suppliers = Supplier::withCount('purchaseOrders')
                        ->withSum('purchaseOrders', 'total_amount')
                        ->withSum('purchaseOrders', 'remaining_amount')
                        ->orderBy('name')
                        ->paginate(20)
                        ->withQueryString();

        return view('purchases.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $this->perm('purchases.suppliers.create');
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);
        Supplier::create($request->only('name', 'phone', 'email', 'address'));

        return back()->with('success', 'Fournisseur ajouté.');
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $this->perm('purchases.suppliers.edit');
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);
        $supplier->update($request->only('name', 'phone', 'email', 'address'));

        return back()->with('success', 'Fournisseur mis à jour.');
    }

    public function destroySupplier(Supplier $supplier)
    {
        $this->perm('purchases.suppliers.delete');
        $supplier->delete();

        return back()->with('success', 'Fournisseur supprimé.');
    }

    // ─── Stock Ruptures ───────────────────────────────────────────────────────

    public function stockRuptures()
    {
        $this->perm('purchases.stock-ruptures.view');

        // Seuil minimum propre à chaque produit, comparé au Stock Catering ; la DA automatique est remise à jour
        $service = app(\App\Services\StockAlertService::class);
        $draft = app(\App\Services\AutoPurchaseRequestService::class)->sync();
        $alerts = $service->alerts();
        $ref = $service->referenceStock();
        $noThreshold = Product::whereNull('min_stock')->count();

        return view('purchases.stock-ruptures', compact('alerts', 'noThreshold', 'draft', 'ref'));
    }

    // ─── Print / PDF A4 ───────────────────────────────────────────────────────

    public function printCommande(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.view');
        $order->load('supplier', 'items.product.unit', 'items.product.productPackagings', 'items.packaging');
        $company = config('app.company');
        return view('purchases.print.commande', compact('order', 'company'));
    }

    public function printFacture(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.view');
        $order->load('supplier', 'items.product.unit', 'items.product.productPackagings', 'items.packaging', 'goodsReceipts.items.product.unit', 'goodsReceipts.items.orderItem.packaging', 'supplierPayments.paymentType');
        $company = config('app.company');
        return view('purchases.print.facture', compact('order', 'company'));
    }

    public function printLivraison(PurchaseOrder $order, GoodsReceipt $receipt)
    {
        $this->perm('purchases.orders.view');
        $order->load('supplier', 'items.product.unit', 'items.product.productPackagings', 'items.packaging');
        $receipt->load('items.product.unit', 'items.product.productPackagings', 'items.orderItem.packaging', 'stock');
        $company = config('app.company');
        return view('purchases.print.livraison', compact('order', 'receipt', 'company'));
    }

    public function printPaiement(PurchaseOrder $order, SupplierPayment $payment)
    {
        $this->perm('purchases.orders.view');
        $order->load('supplier', 'supplierPayments.paymentType');
        $payment->load('paymentType');
        $company = config('app.company');
        return view('purchases.print.paiement', compact('order', 'payment', 'company'));
    }
}
