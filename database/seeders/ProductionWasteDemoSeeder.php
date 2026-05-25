<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Waste;
use App\Models\User;
use Carbon\Carbon;

class ProductionWasteDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Utilisateur validateur (production manager)
        $user = User::firstOrCreate([
            'email' => 'prod.manager@royalcomplex.com',
        ], [
            'name' => 'Production Manager',
            'password' => bcrypt('password'),
        ]);

        // Exemple de stock principal
        $stock = Stock::firstOrCreate(['name' => 'Stock Cuisine Principale']);

        // Sélectionner 2 produits à périmer/gâter
        $products = Product::inRandomOrder()->take(2)->get();
        foreach ($products as $product) {
            $qty = rand(3, 10);
            // Créer un enregistrement de perte/gaspillage
            $waste = Waste::create([
                'product_id' => $product->id,
                'stock_id' => $stock->id,
                'quantity' => $qty,
                'reason' => 'Produit périmé',
                'validated_at' => Carbon::now(),
                'validated_by' => $user->id,
            ]);
            // Ajuster le stock (sortie)
            $stockItem = StockItem::where('stock_id', $stock->id)->where('product_id', $product->id)->first();
            if ($stockItem) {
                $stockItem->decrement('quantity', $qty);
            }
            // Historique du mouvement de stock
            StockMovement::create([
                'product_id' => $product->id,
                'stock_id' => $stock->id,
                'type' => 'out',
                'quantity' => $qty,
                'origin_module' => 'production',
                'origin_type' => 'waste',
                'origin_id' => $waste->id,
                'user_id' => $user->id,
                'notes' => 'Sortie pour perte/gaspillage validée',
            ]);
        }
    }
}
