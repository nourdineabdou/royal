<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Waste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionWasteController extends Controller
{
    public function index()
    {
        $this->perm('production.waste.view');
        $wastes = Waste::with('product', 'stock', 'user')->latest()->paginate(20);
        return view('production.waste.index', compact('wastes'));
    }

    public function create()
    {
        $this->perm('production.waste.create');
        $products = Product::all();
        $stocks = Stock::all();
        return view('production.waste.create', compact('products', 'stocks'));
    }

    public function store(Request $request)
    {
        $this->perm('production.waste.create');
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock_id' => 'required|exists:stocks,id',
            'quantity' => 'required|numeric|min:1',
            'reason' => 'required|string|max:255',
            'packaging_id' => 'nullable|integer',
        ]);
        $user = Auth::user();
        $waste = Waste::create([
            'product_id' => $request->product_id,
            'stock_id' => $request->stock_id,
            'quantity' => $request->quantity,
            'reason' => $request->reason,
            'validated_at' => now(),
            'validated_by' => $user->id,
            'packaging_id' => $request->packaging_id ?? null,
        ]);
        // Ajustement du stock (optionnel : filtrer aussi par packaging_id si la structure le permet)
        $stockItemQuery = StockItem::where('stock_id', $request->stock_id)
            ->where('product_id', $request->product_id);
        if ($request->filled('packaging_id') && $request->packaging_id > 0 && \Schema::hasColumn('stock_items', 'packaging_id')) {
            $stockItemQuery->where('packaging_id', $request->packaging_id);
        }
        $stockItem = $stockItemQuery->first();
        if ($stockItem) {
            $stockItem->decrement('quantity', $request->quantity);
        }
        // Historique du mouvement
        StockMovement::create([
            'product_id' => $request->product_id,
            'stock_id' => $request->stock_id,
            'type' => 'out',
            'quantity' => $request->quantity,
            'origin_module' => 'production',
            'origin_type' => 'waste',
            'origin_id' => $waste->id,
            'user_id' => $user->id,
            'notes' => $request->reason,
        ]);
        return redirect()->route('production.waste.index')->with('success', 'Sortie de stock pour perte/gaspillage enregistrée.');
    }
}
