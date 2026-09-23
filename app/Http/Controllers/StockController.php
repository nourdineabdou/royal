<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockController extends Controller
{
    // ─── Dashboard: all stocks with module assignments ────────────────────────

    public function index()
    {
        $this->perm('stock.view');
        $stocks = Stock::withCount('items')
            ->with(['items' => fn($q) => $q->select('stock_id', DB::raw('SUM(quantity) as total_qty'))->groupBy('stock_id')])
            ->orderBy('name')
            ->get();

        // Module assignment status
        $linkedModules = Stock::whereNotNull('module')->pluck('module')->toArray();
        $missingModules = array_diff(array_keys(Stock::MODULES), $linkedModules);

        // Global stats
        $totalProducts  = StockItem::distinct('product_id')->count('product_id');
        $totalMovements = StockMovement::count();

        return view('stock.index', compact('stocks', 'missingModules', 'totalProducts', 'totalMovements'));
    }

    // ─── Show products inside a specific stock ────────────────────────────────

    public function show(Stock $stock, Request $request)
    {
        $this->perm('stock.view');
        $query = StockItem::with(['product.unit'])
            ->where('stock_id', $stock->id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('product', fn($q) => $q->where('name', 'LIKE', "%{$s}%"));
        }

        $items = $query->orderByDesc('quantity')->paginate(30)->withQueryString();

        return view('stock.show', compact('stock', 'items'));
    }

    // ─── Movements of a specific stock ───────────────────────────────────────

    public function movements(Stock $stock, Request $request)
    {
        $this->perm('stock.movements.view');
        $query = StockMovement::with(['product.unit', 'user', 'sourceStock', 'destinationStock'])
            ->where('stock_id', $stock->id);

        $this->applyMovementFilters($query, $request);

        $movements = $query->orderByDesc('created_at')->paginate(30)->withQueryString();
        $filters   = $request->only(['type', 'origin_module', 'from_date', 'to_date', 'product_id']);
        $products  = Product::orderBy('name')->get(['id', 'name']);

        return view('stock.movements', compact('stock', 'movements', 'filters', 'products'));
    }

    // ─── All movements across all stocks ─────────────────────────────────────

    public function allMovements(Request $request)
    {
        $this->perm('stock.movements.all');
        $cumule = $request->boolean('cumule');
        $filters = $request->only(['stock_id', 'type', 'origin_module', 'from_date', 'to_date', 'product_id']);
        $stocks    = Stock::orderBy('name')->get(['id', 'name', 'module']);
        $products  = Product::orderBy('name')->get(['id', 'name']);

        if ($cumule) {
            // Cumulé par produit
            $query = StockMovement::query();
            if ($request->filled('stock_id')) {
                $query->where('stock_id', $request->stock_id);
            }
            $this->applyMovementFilters($query, $request);
            $query->select('product_id', 'type', DB::raw('SUM(quantity) as total_quantity'));
            $query->groupBy('product_id', 'type');
            $query->with('product.unit');
            $cumuls = $query->get();
            return view('stock.all-movements', compact('cumuls', 'filters', 'stocks', 'products', 'cumule'));
        } else {
            $query = StockMovement::with(['product.unit', 'stock', 'user', 'sourceStock', 'destinationStock']);
            if ($request->filled('stock_id')) {
                $query->where('stock_id', $request->stock_id);
            }
            $this->applyMovementFilters($query, $request);
            $movements = $query->orderByDesc('created_at')->paginate(40)->withQueryString();
            return view('stock.all-movements', compact('movements', 'filters', 'stocks', 'products', 'cumule'));
        }
    }

    // ─── Assign a module to a stock ──────────────────────────────────────────

    public function assignModule(Request $request)
    {
        $this->perm('stock.assign-module');
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'module'   => 'nullable|in:' . implode(',', array_keys(Stock::MODULES)),
        ]);

        $module = $request->module ?: null;

        // Remove module from any other stock that has it
        if ($module) {
            Stock::where('module', $module)->update(['module' => null]);
        }

        Stock::findOrFail($request->stock_id)->update(['module' => $module]);

        $label = $module ? (Stock::MODULES[$module] . ' lié au stock sélectionné.') : 'Liaison retirée.';
        return back()->with('success', $label);
    }

    // ─── Transfer form ────────────────────────────────────────────────────────

    public function transfer()
    {
        $this->perm('stock.transfer');
        $stocks   = Stock::orderBy('name')->get();
        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('stock.transfer', compact('stocks', 'products'));
    }

    // ─── Execute a stock transfer ─────────────────────────────────────────────

    public function storeTransfer(Request $request)
    {
        $this->perm('stock.transfer');
        $request->validate([
            'destination_stock_id' => 'required|exists:stocks,id',
            'product_id'           => 'required|exists:products,id',
            'quantity'             => 'required|numeric|min:0.001',
            'notes'                => 'nullable|string|max:500',
        ]);

        $sourceId = (int)$request->source_stock_id;
        $destId   = (int)$request->destination_stock_id;
        $prodId   = (int)$request->product_id;
        $qty      = (float)$request->quantity;
        $notes    = $request->notes;

        try {
            DB::transaction(function () use ($sourceId, $destId, $prodId, $qty, $notes) {
                // Check source availability
                $sourceItem = StockItem::where('stock_id', $sourceId)
                    ->where('product_id', $prodId)
                    ->lockForUpdate()
                    ->first();

                $available = (float)($sourceItem?->quantity ?? 0);
                if ($available < $qty) {
                    $product = Product::find($prodId);
                    throw new \Exception(
                        "Stock insuffisant pour {$product?->name} : disponible {$available}, demandé {$qty}."
                    );
                }

                // Decrement source
                $sourceItem->decrement('quantity', $qty);

                // Increment destination (create if absent)
                $destItem = StockItem::firstOrCreate(
                    ['stock_id' => $destId, 'product_id' => $prodId],
                    ['quantity' => 0]
                );
                $destItem->increment('quantity', $qty);

                $commonData = [
                    'product_id'           => $prodId,
                    'quantity'             => $qty,
                    'origin_module'        => 'transfer',
                    'origin_type'          => 'transfer',
                    'source_stock_id'      => $sourceId,
                    'destination_stock_id' => $destId,
                    'user_id'              => auth()->id(),
                    'notes'                => $notes,
                ];

                // Out on source
                StockMovement::create(array_merge($commonData, [
                    'stock_id' => $sourceId,
                    'type'     => 'out',
                ]));

                // In on destination
                StockMovement::create(array_merge($commonData, [
                    'stock_id' => $destId,
                    'type'     => 'in',
                ]));
            });

            return redirect()->route('stock.index')->with('success', 'Transfert effectué avec succès.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ─── Add/adjust product quantity in a stock (manual entry) ───────────────

    public function addProduct(Request $request, Stock $stock)
    {
        $this->perm('stock.products.adjust');
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric',
            'type'       => 'required|in:in,out',
            'notes'      => 'nullable|string|max:500',
        ]);

        $prodId = (int)$request->product_id;
        $qty    = abs((float)$request->quantity);
        $type   = $request->type;

        try {
            DB::transaction(function () use ($stock, $prodId, $qty, $type, $request) {
                $item = StockItem::firstOrCreate(
                    ['stock_id' => $stock->id, 'product_id' => $prodId],
                    ['quantity' => 0]
                );

                if ($type === 'out') {
                    if ((float)$item->quantity < $qty) {
                        throw new \Exception('Quantité insuffisante dans ce stock.');
                    }
                    $item->decrement('quantity', $qty);
                } else {
                    $item->increment('quantity', $qty);
                }

                StockMovement::create([
                    'stock_id'      => $stock->id,
                    'product_id'    => $prodId,
                    'type'          => $type,
                    'quantity'      => $qty,
                    'origin_module' => 'manual',
                    'origin_type'   => 'manual',
                    'user_id'       => auth()->id(),
                    'notes'         => $request->notes ?? 'Ajustement manuel',
                ]);
            });

            return back()->with('success', 'Quantité mise à jour.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─── CSV Exports ──────────────────────────────────────────────────────────

    public function exportInventoryCsv(Request $request)
    {
        $this->perm('stock.view');
        $stocks = Stock::with(['items.product.unit'])->orderBy('name')->get();

        $filename = 'inventaire_global_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($stocks) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['Stock', 'Produit', 'Unité', 'Quantité'], ';');

            foreach ($stocks as $stock) {
                foreach ($stock->items as $item) {
                    fputcsv($out, [
                        $stock->name,
                        $item->product->name ?? '-',
                        $item->product->unit->symbol ?? '-',
                        number_format($item->quantity, 2, '.', ''),
                    ], ';');
                }
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportStockInventoryCsv(Request $request, Stock $stock)
    {
        $this->perm('stock.view');
        $stock->load('items.product.unit');

        $filename = 'inventaire_' . Str::slug($stock->name) . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($stock) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Produit', 'Unité', 'Quantité'], ';');

            foreach ($stock->items as $item) {
                fputcsv($out, [
                    $item->product->name ?? '-',
                    $item->product->unit->symbol ?? '-',
                    number_format($item->quantity, 2, '.', ''),
                ], ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportMovementsCsv(Request $request)
    {
        $this->perm('stock.movements.all');

        $query = StockMovement::with('product.unit', 'stock', 'user')
            ->orderBy('created_at', 'desc');
        $this->applyMovementFilters($query, $request);

        if ($request->filled('stock_id')) {
            $query->where('stock_id', $request->stock_id);
        }

        $movements = $query->get();
        $filename  = 'mouvements_stock_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($movements) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Stock', 'Produit', 'Unité', 'Type', 'Quantité', 'Module', 'Utilisateur', 'Notes'], ';');

            foreach ($movements as $m) {
                fputcsv($out, [
                    $m->created_at->format('d/m/Y H:i'),
                    $m->stock->name ?? '-',
                    $m->product->name ?? '-',
                    $m->product->unit->symbol ?? '-',
                    $m->type === 'in' ? 'Entrée' : 'Sortie',
                    number_format($m->quantity, 2, '.', ''),
                    $m->origin_module ?? '-',
                    $m->user->name ?? '-',
                    $m->notes ?? '',
                ], ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function applyMovementFilters($query, Request $request): void
    {
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('origin_module')) {
            $query->where('origin_module', $request->origin_module);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
    }
}
