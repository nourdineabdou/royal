<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockRequirementService;
use Illuminate\Http\Request;

class ProductionPlanningController extends Controller
{
    public function cateringToday(Request $request, StockRequirementService $service)
    {
        $this->perm('production.dashboard');

        $date = $request->query('date') ? \Carbon\Carbon::parse($request->query('date')) : today();

        $needs = $service->cateringNeedsForDate($date);
        $byContract = $needs['byContract'];
        $cateringStock = $needs['cateringStock'];
        $comparison = $needs['comparison'];
        $missingRecipeMeals = $needs['missingRecipeMeals'];

        $transferSuggestions = [];
        if ($cateringStock) {
            foreach ($comparison as $productId => $row) {
                if ($row['missing'] > 0) {
                    $transferSuggestions[$productId] = $service->findTransferSources($productId, $cateringStock->id);
                }
            }
        }

        $products = Product::with('unit')->whereIn('id', array_keys($needs['required']))->get()->keyBy('id');

        return view('modules.production.catering-today', compact(
            'date', 'byContract', 'cateringStock', 'comparison', 'transferSuggestions', 'missingRecipeMeals', 'products'
        ));
    }
}
