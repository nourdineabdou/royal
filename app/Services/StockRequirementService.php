<?php

namespace App\Services;

use App\Models\CateringMenuDay;
use App\Models\Meal;
use App\Models\Stock;
use App\Models\StockItem;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Calcule les besoins en produits à partir des recettes des plats,
 * les compare à un stock donné, et suggère des sources de transfert.
 */
class StockRequirementService
{
    /**
     * @param array<int,float> $mealIdToQty [meal_id => quantité à produire]
     * @return array{required: array<int,float>, missingRecipeMealIds: int[]}
     */
    public function requirementsForMealQuantities(array $mealIdToQty): array
    {
        $required = [];
        $missingRecipeMealIds = [];

        if (empty($mealIdToQty)) {
            return ['required' => $required, 'missingRecipeMealIds' => $missingRecipeMealIds];
        }

        $meals = Meal::with('recipe.items')->whereIn('id', array_keys($mealIdToQty))->get()->keyBy('id');

        foreach ($mealIdToQty as $mealId => $qty) {
            $meal = $meals->get($mealId);
            $recipe = $meal?->recipe;

            if (!$recipe || $recipe->items->isEmpty()) {
                $missingRecipeMealIds[] = $mealId;
                continue;
            }

            foreach ($recipe->items as $item) {
                $pid = $item->product_id;
                $required[$pid] = ($required[$pid] ?? 0.0) + (float)$item->quantity * (float)$qty;
            }
        }

        return ['required' => $required, 'missingRecipeMealIds' => $missingRecipeMealIds];
    }

    /**
     * @param array<int,float> $required [product_id => quantité nécessaire]
     * @return array<int,array{product_id:int,needed:float,available:float,missing:float}>
     */
    public function compareAgainstStock(array $required, int $stockId): array
    {
        if (empty($required)) {
            return [];
        }

        $available = StockItem::where('stock_id', $stockId)
            ->whereIn('product_id', array_keys($required))
            ->pluck('quantity', 'product_id');

        $result = [];
        foreach ($required as $productId => $needed) {
            $avail = (float)($available[$productId] ?? 0);
            $result[$productId] = [
                'product_id' => $productId,
                'needed'     => $needed,
                'available'  => $avail,
                'missing'    => max(0.0, $needed - $avail),
            ];
        }

        return $result;
    }

    /**
     * Regroupe les plats programmés (menus hebdomadaires catering, contrats actifs)
     * pour une date donnée, par contrat/créneau, et calcule les besoins produits
     * comparés au stock catering.
     *
     * @return array{byContract: array, required: array<int,float>, missingRecipeMeals: Collection,
     *               cateringStock: ?Stock, comparison: array}
     */
    public function cateringNeedsForDate(CarbonInterface $date): array
    {
        $days = CateringMenuDay::with(['meals.items.meal', 'weeklyMenu.contract.client'])
            ->whereDate('date', $date)
            ->whereHas('weeklyMenu.contract', fn ($q) => $q->where('status', 'active'))
            ->get();

        $byContract = [];
        $mealIdToQty = [];

        foreach ($days as $day) {
            $contract = $day->weeklyMenu->contract;
            foreach ($day->meals as $menuMeal) {
                $byContract[$contract->id]['contract'] ??= $contract;
                $byContract[$contract->id]['slots'][] = [
                    'type'     => $menuMeal->type,
                    'quantity' => $menuMeal->quantity,
                    'dishes'   => $menuMeal->items->pluck('meal')->filter(),
                ];

                foreach ($menuMeal->items as $item) {
                    if (!$item->meal_id) continue;
                    $mealIdToQty[$item->meal_id] = ($mealIdToQty[$item->meal_id] ?? 0) + (float)$menuMeal->quantity;
                }
            }
        }

        $mealNames = Meal::whereIn('id', array_keys($mealIdToQty))->pluck('name', 'id');

        $breakdown = $this->requirementsForMealQuantities($mealIdToQty);
        $required = $breakdown['required'];
        $missingRecipeMeals = collect($breakdown['missingRecipeMealIds'])
            ->map(fn ($id) => $mealNames[$id] ?? '#' . $id)
            ->values();

        $cateringStock = Stock::forModule('catering');
        $comparison = $cateringStock ? $this->compareAgainstStock($required, $cateringStock->id) : [];

        return compact('byContract', 'required', 'missingRecipeMeals', 'cateringStock', 'comparison');
    }

    /**
     * Autres stocks disposant de ce produit (pour proposer un transfert),
     * triés par quantité disponible décroissante.
     */
    public function findTransferSources(int $productId, int $excludeStockId): Collection
    {
        return StockItem::with('stock')
            ->where('product_id', $productId)
            ->where('stock_id', '!=', $excludeStockId)
            ->where('quantity', '>', 0)
            ->orderByDesc('quantity')
            ->get()
            ->filter(fn ($item) => $item->stock !== null);
    }
}
