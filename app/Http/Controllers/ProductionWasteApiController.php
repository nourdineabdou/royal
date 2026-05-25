<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\ProductPackaging;

class ProductionWasteApiController extends Controller
{
    public function productInfo(Request $request)
    {
        $product = Product::with(['packaging', 'unit', 'productPackagings.packaging'])->find($request->product_id);
        $stockItem = StockItem::where('product_id', $request->product_id)
            ->where('stock_id', $request->stock_id)
            ->first();
        // Liste des emballages possibles (inclut vrac si is_bulk)
        $packagings = [];
        if ($product) {
            if ($product->is_bulk) {
                $packagings[] = [
                    'id' => 0,
                    'name' => 'Vrac',
                    'is_bulk' => true,
                ];
            }
            foreach ($product->productPackagings as $pp) {
                $packagings[] = [
                    'id' => $pp->packaging->id,
                    'name' => $pp->packaging->name,
                    'is_bulk' => false,
                ];
            }
        }
        // Affichage principal (si un seul emballage)
        if (count($packagings) === 1) {
            $packaging = $packagings[0]['name'];
        } elseif ($product?->packaging?->name) {
            $packaging = $product->packaging->name;
        } elseif ($product?->is_bulk) {
            $packaging = 'Vrac';
        } else {
            $packaging = 'Aucun emballage';
        }
        $unit = $product?->unit?->name ?? '-';
        return response()->json([
            'packaging' => $packaging,
            'unit' => $unit,
            'quantity' => $stockItem?->quantity,
            'packagings' => $packagings,
        ]);
    }
}
