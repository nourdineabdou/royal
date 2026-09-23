<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function edit(Meal $meal)
    {
        $this->perm('meals.edit');

        $meal->load('recipe.items.product.unit');
        $products = Product::with('unit')->orderBy('name')->get();

        return view('meals.recipe', compact('meal', 'products'));
    }

    public function update(Request $request, Meal $meal)
    {
        $this->perm('meals.edit');

        $validated = $request->validate([
            'product_id'   => 'array',
            'product_id.*' => 'required|exists:products,id',
            'quantity'     => 'array',
            'quantity.*'   => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($meal, $validated) {
            $recipe = Recipe::firstOrCreate(['meal_id' => $meal->id]);
            $recipe->items()->delete();

            $productIds = $validated['product_id'] ?? [];
            $quantities = $validated['quantity'] ?? [];

            foreach ($productIds as $i => $productId) {
                RecipeItem::create([
                    'recipe_id'  => $recipe->id,
                    'product_id' => $productId,
                    'quantity'   => $quantities[$i],
                ]);
            }
        });

        return redirect()->route('meals.show', $meal)->with('success', 'Recette mise à jour.');
    }
}
