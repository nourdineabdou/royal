<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\SupplierPayment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\PaymentType;
use Carbon\Carbon;
use App\Models\Transaction;
class PurchaseController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard()
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
            'topSuppliers', 'recentOrders', 'recentReceipts'
        ));
    }

    // ─── Purchase Orders ──────────────────────────────────────────────────────

    public function orders(Request $request)
    {
        $this->perm('purchases.orders.view');
        $query = PurchaseOrder::with('supplier', 'items');

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

        $orders    = $query->latest()->paginate(20);
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.orders', compact('orders', 'suppliers'));
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
            'items'        => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
        ]);

        $order = PurchaseOrder::create([
            'supplier_id'      => $request->supplier_id,
            'reference'        => 'CMD-' . strtoupper(uniqid()),
            'total_amount'     => 0,
            'paid_amount'      => 0,
            'remaining_amount' => 0,
            'payment_status'   => 'unpaid',
            'status'           => 'pending',
        ]);

        foreach ($request->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id'        => $item['product_id'],
                'packaging_id'      => isset($item['packaging_id']) && $item['packaging_id'] ? (int)$item['packaging_id'] : null,
                'quantity'          => $item['quantity'],
                'price'             => null,
                'total'             => null,
            ]);
        }

        return redirect()->route('purchases.orders.show', $order)
                         ->with('success', 'Commande créée avec succès.');
    }

    public function showOrder(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.view');
        $order->load('supplier', 'items.product.unit', 'items.packaging', 'goodsReceipts.items.product', 'goodsReceipts.stock', 'supplierPayments.paymentType');
        $stocks       = Stock::all();
        $paymentTypes = PaymentType::all();

        return view('purchases.show', compact('order', 'stocks', 'paymentTypes'));
    }

    public function confirmOrder(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.confirm');
        $order->update(['status' => 'ordered']);

        return back()->with('success', 'Commande confirmée et envoyée au fournisseur.');
    }

    public function storeReceipt(Request $request, PurchaseOrder $order)
    {
        $this->perm('purchases.orders.receipt');
        $request->validate([
            'items'                  => 'required|array',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
        ]);

        // Validate against ordered quantities before writing any receipt row
        $orderItemsByProduct = $order->items()->with('packaging', 'product')->get()->keyBy('product_id');
        foreach ($request->items as $item) {
            $orderItem = $orderItemsByProduct[$item['product_id']] ?? null;
            if (!$orderItem) {
                return back()->withErrors(['items' => 'Un produit reçu ne fait pas partie de cette commande.'])->withInput();
            }

            $receivedInput = (float)$item['quantity'];
            $orderedInput  = (float)$orderItem->quantity;
            if ($receivedInput > $orderedInput) {
                return back()->withErrors([
                    'items' => "La quantité reçue pour {$orderItem->product->name} dépasse la quantité commandée ({$orderedInput})."
                ])->withInput();
            }
        }

        $receipt = GoodsReceipt::create([
            'purchase_order_id' => $order->id,
            'stock_id'          => $request->stock_id,
            'received_at'       => now(),
        ]);

        foreach ($request->items as $item) {
            $orderItem   = $orderItemsByProduct[$item['product_id']] ?? null;
            $receivedQty = (float)$item['quantity'];
            $unitPrice   = (float)$item['unit_price'];
            $lineTotal   = $receivedQty * $unitPrice;

            // Update the purchase order item with the price from the bon de livraison
            if ($orderItem) {
                $orderItem->update([
                    'price' => $unitPrice,
                    'total' => $lineTotal,
                ]);
            }

            // If the order was placed with a packaging, convert packagings → actual units for stock
            $stockQty = $receivedQty;
            if ($orderItem && $orderItem->packaging_id) {
                $pkgQtyPerUnit = \App\Models\ProductPackaging::where('product_id', $orderItem->product_id)
                                    ->where('packaging_id', $orderItem->packaging_id)
                                    ->value('quantity');
                if ($pkgQtyPerUnit) {
                    $stockQty = $receivedQty * (float)$pkgQtyPerUnit;
                }
            }

            GoodsReceiptItem::create([
                'goods_receipt_id' => $receipt->id,
                'product_id'       => $item['product_id'],
                'quantity'         => $stockQty,
            ]);

            // Update stock
            StockItem::updateOrCreate(
                ['stock_id' => $request->stock_id, 'product_id' => $item['product_id']],
                ['quantity' => \DB::raw('quantity + ' . $stockQty)]
            );
        }

        // Recalculate order total from items (prices now known from delivery note)
        $totalAmount = $order->items()->sum('total');
        $newRemaining = max(0, $totalAmount - $order->paid_amount);
        $payStatus = $newRemaining <= 0 && $totalAmount > 0 ? 'paid' : 'unpaid';

        $order->update([
            'total_amount'     => $totalAmount,
            'remaining_amount' => $newRemaining,
            'payment_status'   => $payStatus,
            'status'           => 'received',
        ]);

        return back()->with('success', 'Réception enregistrée et stock mis à jour.');
    }

    public function storePayment(Request $request, PurchaseOrder $order)
    {
        $this->perm('purchases.orders.payment');
        $request->validate([
            'amount'          => 'required|numeric|min:0.01|max:' . $order->remaining_amount,
        ]);

        SupplierPayment::create([
            'purchase_order_id' => $order->id,
            'payment_type_id'   => $request->payment_type_id,
            'amount'            => $request->amount,
            'paid_at'           => now(),
        ]);

        // Créer une transaction comptable pour le paiement fournisseur
        Transaction::create([
            'type'      => 'supplier_payment',
            'amount'    => $request->amount,
            'reference' => 'SUPP-PAY-' . $order->id,
            'date'      => now()->toDateString(),
            'module'    => 'purchase',
            'description' => 'Paiement fournisseur pour commande #' . $order->id,
        ]);

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
                        ->get();

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
        $ruptures = StockItem::with('product.unit', 'stock')
                        ->where('quantity', '<=', 0)
                        ->get();

        $lowStock = StockItem::with('product.unit', 'stock')
                        ->where('quantity', '>', 0)
                        ->where('quantity', '<=', 5)
                        ->get();

        $allProducts = Product::with('unit')->orderBy('name')->get();
        $suppliers   = Supplier::orderBy('name')->get();

        return view('purchases.stock-ruptures', compact('ruptures', 'lowStock', 'allProducts', 'suppliers'));
    }

    // ─── Print / PDF A4 ───────────────────────────────────────────────────────

    public function printCommande(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.view');
        $order->load('supplier', 'items.product.unit', 'items.packaging');
        $company = config('app.company');
        return view('purchases.print.commande', compact('order', 'company'));
    }

    public function printFacture(PurchaseOrder $order)
    {
        $this->perm('purchases.orders.view');
        $order->load('supplier', 'items.product.unit', 'items.packaging', 'goodsReceipts.items.product', 'supplierPayments.paymentType');
        $company = config('app.company');
        return view('purchases.print.facture', compact('order', 'company'));
    }

    public function printLivraison(PurchaseOrder $order, GoodsReceipt $receipt)
    {
        $this->perm('purchases.orders.view');
        $order->load('supplier', 'items.product.unit', 'items.packaging');
        $receipt->load('items.product.unit', 'stock');
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
