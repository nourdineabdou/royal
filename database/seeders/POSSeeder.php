<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Meal;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Stock;
use App\Models\StockItem;
use Illuminate\Database\Seeder;

class POSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les unités
        $kg = Unit::firstOrCreate(['name' => 'Kilogramme']);
        $unit = Unit::firstOrCreate(['name' => 'Unité']);
        $liter = Unit::firstOrCreate(['name' => 'Litre']);

        // Créer les catégories
        $burgers = Category::firstOrCreate(['name' => 'Burgers']);
        $pizzas = Category::firstOrCreate(['name' => 'Pizzas']);
        $sandwiches = Category::firstOrCreate(['name' => 'Sandwiches']);
        $desserts = Category::firstOrCreate(['name' => 'Desserts']);

        // Créer les produits (ingrédients)
        $pain = Product::firstOrCreate(['name' => 'Pain Burger', 'unit_id' => $kg->id]);
        $fromage = Product::firstOrCreate(['name' => 'Fromage', 'unit_id' => $kg->id]);
        $tomate = Product::firstOrCreate(['name' => 'Tomate', 'unit_id' => $kg->id]);
        $salade = Product::firstOrCreate(['name' => 'Salade', 'unit_id' => $kg->id]);
        $beurre = Product::firstOrCreate(['name' => 'Beurre', 'unit_id' => $kg->id]);
        $oignon = Product::firstOrCreate(['name' => 'Oignon', 'unit_id' => $kg->id]);

        // Créer le stock principal
        $stock = Stock::firstOrCreate(['name' => 'Stock Principal', 'location' => 'Cuisine']);

        // Ajouter les produits au stock
        StockItem::firstOrCreate(
            ['stock_id' => $stock->id, 'product_id' => $pain->id],
            ['quantity' => 100]
        );
        StockItem::firstOrCreate(
            ['stock_id' => $stock->id, 'product_id' => $fromage->id],
            ['quantity' => 50]
        );
        StockItem::firstOrCreate(
            ['stock_id' => $stock->id, 'product_id' => $tomate->id],
            ['quantity' => 80]
        );
        StockItem::firstOrCreate(
            ['stock_id' => $stock->id, 'product_id' => $salade->id],
            ['quantity' => 60]
        );
        StockItem::firstOrCreate(
            ['stock_id' => $stock->id, 'product_id' => $beurre->id],
            ['quantity' => 30]
        );
        StockItem::firstOrCreate(
            ['stock_id' => $stock->id, 'product_id' => $oignon->id],
            ['quantity' => 40]
        );

        // Créer les plats BURGERS
        $burgerClassique = Meal::firstOrCreate([
            'name' => 'Burger Classique',
            'price' => 8.50,
            'category_id' => $burgers->id
        ]);

        $burgerDeluxe = Meal::firstOrCreate([
            'name' => 'Burger Deluxe',
            'price' => 11.50,
            'category_id' => $burgers->id
        ]);

        // Créer les plats PIZZAS
        $pizzaMargherita = Meal::firstOrCreate([
            'name' => 'Pizza Margherita',
            'price' => 10.00,
            'category_id' => $pizzas->id
        ]);

        // Créer les plats SANDWICHES
        $sandwichOmelet = Meal::firstOrCreate([
            'name' => 'Sandwich Omelette',
            'price' => 6.50,
            'category_id' => $sandwiches->id
        ]);

        // Créer les plats DESSERTS (sans recette pour tester)
        $tiramisu = Meal::firstOrCreate([
            'name' => 'Tiramisu',
            'price' => 5.50,
            'category_id' => $desserts->id
        ]);

        // Créer les recettes
        // BURGER CLASSIQUE
        if (!$burgerClassique->recipe) {
            $recipeBurger = Recipe::create(['meal_id' => $burgerClassique->id]);
            RecipeItem::create(['recipe_id' => $recipeBurger->id, 'product_id' => $pain->id, 'quantity' => 2]);
            RecipeItem::create(['recipe_id' => $recipeBurger->id, 'product_id' => $fromage->id, 'quantity' => 1]);
            RecipeItem::create(['recipe_id' => $recipeBurger->id, 'product_id' => $tomate->id, 'quantity' => 0.5]);
            RecipeItem::create(['recipe_id' => $recipeBurger->id, 'product_id' => $salade->id, 'quantity' => 0.3]);
        }

        // BURGER DELUXE
        if (!$burgerDeluxe->recipe) {
            $recipeBurgerDeluxe = Recipe::create(['meal_id' => $burgerDeluxe->id]);
            RecipeItem::create(['recipe_id' => $recipeBurgerDeluxe->id, 'product_id' => $pain->id, 'quantity' => 2]);
            RecipeItem::create(['recipe_id' => $recipeBurgerDeluxe->id, 'product_id' => $fromage->id, 'quantity' => 2]);
            RecipeItem::create(['recipe_id' => $recipeBurgerDeluxe->id, 'product_id' => $tomate->id, 'quantity' => 1]);
            RecipeItem::create(['recipe_id' => $recipeBurgerDeluxe->id, 'product_id' => $salade->id, 'quantity' => 0.5]);
            RecipeItem::create(['recipe_id' => $recipeBurgerDeluxe->id, 'product_id' => $oignon->id, 'quantity' => 0.3]);
        }

        // PIZZA MARGHERITA
        if (!$pizzaMargherita->recipe) {
            $recipePizza = Recipe::create(['meal_id' => $pizzaMargherita->id]);
            RecipeItem::create(['recipe_id' => $recipePizza->id, 'product_id' => $pain->id, 'quantity' => 1]);
            RecipeItem::create(['recipe_id' => $recipePizza->id, 'product_id' => $fromage->id, 'quantity' => 1.5]);
            RecipeItem::create(['recipe_id' => $recipePizza->id, 'product_id' => $tomate->id, 'quantity' => 1]);
        }

        // SANDWICH OMELETTE
        if (!$sandwichOmelet->recipe) {
            $recipeSandwich = Recipe::create(['meal_id' => $sandwichOmelet->id]);
            RecipeItem::create(['recipe_id' => $recipeSandwich->id, 'product_id' => $pain->id, 'quantity' => 1]);
            RecipeItem::create(['recipe_id' => $recipeSandwich->id, 'product_id' => $beurre->id, 'quantity' => 0.2]);
            RecipeItem::create(['recipe_id' => $recipeSandwich->id, 'product_id' => $oignon->id, 'quantity' => 0.2]);
        }

        // TIRAMISU - Pas de recette (test)

        echo "✅ Données de test du POS créées avec succès!\n";
        echo "📊 Résumé:\n";
        echo "  • 4 Catégories\n";
        echo "  • 5 Plats\n";
        echo "  • 6 Ingrédients\n";
        echo "  • 4 Recettes\n";
        echo "  • 1 Stock avec quantités initiales\n";
    }
}
