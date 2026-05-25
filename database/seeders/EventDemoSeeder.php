<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Event;
use App\Models\Service;
use App\Models\EventServiceItem;
use App\Models\Meal;
use App\Models\EventMeal;
use App\Models\EventMealItem;
use App\Models\Option;
use App\Models\EventOption;
use App\Models\EventOptionItem;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeItem;

class EventDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Exemple de client
        $client = Client::firstOrCreate([
            'email' => 'client.demo@royalcomplex.com',
        ], [
            'name' => 'Société Démo',
            'phone' => '222000111',
            'company' => 'Société Démo',
        ]);

        // Exemple de stock
        $stock = Stock::firstOrCreate(['name' => 'Cuisine principale']);

        // Exemple de service
        $service = Service::firstOrCreate([
            'name' => 'Salle Prestige',
        ], [
            'price' => 5000,
            'type' => 'hall',
        ]);

        // Exemple de plat/repas avec recette
        $meal = Meal::firstOrCreate([
            'name' => 'Poulet Yassa',
        ], [
            'price' => 1200,
            'category_id' => 1,
        ]);
        $recipe = Recipe::firstOrCreate([
            'meal_id' => $meal->id
        ]);
        RecipeItem::firstOrCreate([
            'recipe_id' => $recipe->id,
            'product_id' => Product::firstOrCreate(['name' => 'Poulet'])->id,
        ], ['quantity' => 1]);
        RecipeItem::firstOrCreate([
            'recipe_id' => $recipe->id,
            'product_id' => Product::firstOrCreate(['name' => 'Oignons'])->id,
        ], ['quantity' => 0.2]);

        // Exemple d'événement complet
        $event = Event::create([
            'client_id' => $client->id,
            'event_type' => 'Mariage',
            'event_date' => now()->addDays(10),
            'guest_count' => 100,
            'stock_id' => $stock->id,
            'status' => 'draft',
            'total_amount' => 0,
        ]);
        // Lier un service
        EventServiceItem::create([
            'event_id' => $event->id,
            'service_id' => $service->id,
            'quantity' => 1,
            'price' => $service->price,
        ]);
        // Lier un repas
        $eventMeal = EventMeal::create([
            'event_id' => $event->id,
            'type' => 'dinner',
            'guest_count' => 100,
        ]);
        EventMealItem::create([
            'event_meal_id' => $eventMeal->id,
            'meal_id' => $meal->id,
        ]);

        // Décrémentation du stock pour chaque ingrédient de la recette du plat servi à l'événement
        if ($event->stock_id && $meal->recipe) {
            foreach ($meal->recipe->items as $item) {
                $product = $item->product;
                if ($product) {
                    $stockItem = $product->stockItems()->where('stock_id', $event->stock_id)->first();
                    if ($stockItem) {
                        $stockItem->decrement('quantity', $item->quantity * $event->guest_count);
                    }
                }
            }
        }
        // Lier une option
        $option = EventOption::create([
            'event_id' => $event->id,
            'name' => 'Décoration florale',
            'price' => 800,
            'quantity' => 1,
        ]);
        // Statut validé pour un autre événement
        $event2 = Event::create([
            'client_id' => $client->id,
            'event_type' => 'Séminaire',
            'event_date' => now()->addDays(20),
            'guest_count' => 50,
            'stock_id' => $stock->id,
            'status' => 'validated',
            'total_amount' => 0,
        ]);
    }
}
