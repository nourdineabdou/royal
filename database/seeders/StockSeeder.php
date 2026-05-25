<?php

namespace Database\Seeders;

use App\Models\Stock;
use App\Models\Unit;
use App\Models\Packaging;
use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 4 Stocks ─────────────────────────────────────────────────────────
        $stockCuisine  = Stock::firstOrCreate(['name' => 'Stock Cuisine Principale'], ['location' => 'Cuisine centrale — Bâtiment A']);
        $stockBar      = Stock::firstOrCreate(['name' => 'Stock Bar & Boissons'],      ['location' => 'Bar & Lounge — Rez-de-chaussée']);
        $stockReserve  = Stock::firstOrCreate(['name' => 'Stock Réserve Sèche'],       ['location' => 'Réserve générale — Sous-sol']);
        $stockCatering = Stock::firstOrCreate(['name' => 'Stock Catering'],            ['location' => 'Cuisine catering — Bâtiment B']);

        // ─── Unités (déjà créées par ReferenceDataSeeder) ─────────────────────
        $kg  = Unit::where('symbol', 'kg')->first();
        $g   = Unit::where('symbol', 'g')->first();
        $L   = Unit::where('symbol', 'L')->first();
        $mL  = Unit::where('symbol', 'mL')->first();
        $pcs = Unit::where('symbol', 'pcs')->first();
        $btl = Unit::where('symbol', 'btl')->first();

        // ─── Emballages ────────────────────────────────────────────────────────
        $sac50kg   = Packaging::where('name', 'Sac 50 kg')->first();
        $sac25kg   = Packaging::where('name', 'Sac 25 kg')->first();
        $sac10kg   = Packaging::where('name', 'Sac 10 kg')->first();
        $sac5kg    = Packaging::where('name', 'Sac 5 kg')->first();
        $paq1kg    = Packaging::where('name', 'Paquet 1 kg')->first();
        $paq500g   = Packaging::where('name', 'Paquet 500 g')->first();
        $paq250g   = Packaging::where('name', 'Paquet 250 g')->first();
        $bidon20L  = Packaging::where('name', 'Bidon 20 L')->first();
        $bidon10L  = Packaging::where('name', 'Bidon 10 L')->first();
        $btl5L     = Packaging::where('name', 'Bouteille 5 L')->first();
        $btl15L    = Packaging::where('name', 'Bouteille 1.5 L')->first();
        $btl1L     = Packaging::where('name', 'Bouteille 1 L')->first();
        $btl500mL  = Packaging::where('name', 'Bouteille 500 mL')->first();
        $canette   = Packaging::where('name', 'Canette 33 cL')->first();
        $pack6     = Packaging::where('name', 'Pack 6 bouteilles')->first();
        $pack24    = Packaging::where('name', 'Pack 24 canettes')->first();
        $bloc2kg   = Packaging::where('name', 'Bloc 2 kg')->first();
        $plaq250g  = Packaging::where('name', 'Plaquette 250 g')->first();
        $pot1kg    = Packaging::where('name', 'Pot 1 kg')->first();
        $bte500g   = Packaging::where('name', 'Boîte 500 g')->first();
        $bte250g   = Packaging::where('name', 'Boîte 250 g')->first();

        // ─── Produits + Emballages ─────────────────────────────────────────────
        // (is_bulk = true → produit vendu au poids/volume sans emballage fixe)

        $products = [
            // ── Céréales & farines ──────────────────────────────────────────
            ['name' => 'Farine de blé',         'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$sac50kg, 50], [$sac25kg, 25], [$paq1kg, 1]]],
            ['name' => 'Semoule fine',           'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$sac25kg, 25], [$paq1kg, 1], [$paq500g, 0.5]]],
            ['name' => 'Riz long grain',         'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$sac50kg, 50], [$sac5kg, 5], [$paq1kg, 1]]],
            ['name' => 'Pâtes spaghetti',        'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq500g, 0.5], [$paq1kg, 1]]],
            ['name' => 'Pâtes penne',            'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq500g, 0.5], [$paq1kg, 1]]],

            // ── Sucre & produits sucrés ─────────────────────────────────────
            ['name' => 'Sucre blanc',            'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$sac50kg, 50], [$paq1kg, 1], [$paq500g, 0.5]]],
            ['name' => 'Sucre glace',            'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq1kg, 1], [$paq500g, 0.5]]],

            // ── Matières grasses ────────────────────────────────────────────
            ['name' => 'Huile végétale',         'is_bulk' => true,  'unit' => $L,  'packagings' => [[$bidon20L, 20], [$bidon10L, 10], [$btl5L, 5], [$btl1L, 1]]],
            ['name' => 'Huile d\'olive',         'is_bulk' => false, 'unit' => $L,  'packagings' => [[$btl1L, 1], [$btl500mL, 0.5]]],
            ['name' => 'Beurre',                 'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$plaq250g, 0.25], [$paq1kg, 1]]],
            ['name' => 'Margarine',              'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$pot1kg, 1], [$plaq250g, 0.25]]],

            // ── Produits laitiers ───────────────────────────────────────────
            ['name' => 'Lait entier',            'is_bulk' => true,  'unit' => $L,  'packagings' => [[$btl1L, 1], [$pack6, 6]]],
            ['name' => 'Crème fraîche',          'is_bulk' => false, 'unit' => $L,  'packagings' => [[$btl500mL, 0.5], [$btl1L, 1]]],
            ['name' => 'Fromage blanc',          'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$pot1kg, 1], [$bte500g, 0.5]]],
            ['name' => 'Fromage fondu',          'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$bloc2kg, 2], [$bte250g, 0.25]]],
            ['name' => 'Mozzarella',             'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq500g, 0.5], [$paq250g, 0.25]]],

            // ── Protéines & viandes ─────────────────────────────────────────
            ['name' => 'Viande de bœuf hachée', 'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Viande d\'agneau',       'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Poulet entier',          'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Filets de poulet',       'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Merguez',                'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq500g, 0.5]]],
            ['name' => 'Poisson (capitaine)',    'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Thon en boîte',          'is_bulk' => false, 'unit' => $pcs,'packagings' => [[$bte250g, 1]]],
            ['name' => 'Œufs',                   'is_bulk' => false, 'unit' => $pcs,'packagings' => [[$paq500g, 30]]],  // paquet de 30

            // ── Légumes & fruits ────────────────────────────────────────────
            ['name' => 'Tomates fraîches',       'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Oignons',                'is_bulk' => true,  'unit' => $kg, 'packagings' => [[$sac10kg, 10]]],
            ['name' => 'Pommes de terre',        'is_bulk' => true,  'unit' => $kg, 'packagings' => [[$sac25kg, 25], [$sac10kg, 10]]],
            ['name' => 'Poivrons',               'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Champignons',            'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Salade verte',           'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Ail',                    'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Citron',                 'is_bulk' => true,  'unit' => $kg, 'packagings' => []],
            ['name' => 'Olives noires',          'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$bte500g, 0.5], [$pot1kg, 1]]],
            ['name' => 'Concentré de tomate',    'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$bte500g, 0.5], [$bte250g, 0.25]]],

            // ── Épices & condiments ─────────────────────────────────────────
            ['name' => 'Sel',                    'is_bulk' => true,  'unit' => $kg, 'packagings' => [[$sac10kg, 10], [$paq1kg, 1]]],
            ['name' => 'Poivre noir',            'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq250g, 0.25]]],
            ['name' => 'Cumin',                  'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq250g, 0.25]]],
            ['name' => 'Paprika',                'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq250g, 0.25]]],
            ['name' => 'Curcuma',                'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq250g, 0.25]]],
            ['name' => 'Cannelle',               'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$bte250g, 0.25]]],
            ['name' => 'Laurier',                'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$bte250g, 0.25]]],
            ['name' => 'Levure boulangère',      'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq500g, 0.5]]],
            ['name' => 'Levure chimique',        'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$bte250g, 0.25]]],

            // ── Boissons ────────────────────────────────────────────────────
            ['name' => 'Café moulu',             'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq250g, 0.25], [$paq1kg, 1]]],
            ['name' => 'Thé en sachets',         'is_bulk' => false, 'unit' => $pcs,'packagings' => [[$bte250g, 100]]],  // boîte 100 sachets
            ['name' => 'Eau minérale',           'is_bulk' => true,  'unit' => $L,  'packagings' => [[$btl15L, 1.5], [$btl1L, 1], [$pack6, 9]]],  // pack 6×1.5L
            ['name' => 'Jus d\'orange',          'is_bulk' => false, 'unit' => $L,  'packagings' => [[$btl1L, 1], [$pack6, 6]]],
            ['name' => 'Coca-Cola',              'is_bulk' => false, 'unit' => $L,  'packagings' => [[$canette, 0.33], [$btl15L, 1.5], [$pack24, 7.92]]],
            ['name' => 'Fanta Orange',           'is_bulk' => false, 'unit' => $L,  'packagings' => [[$canette, 0.33], [$btl15L, 1.5], [$pack24, 7.92]]],
            ['name' => 'Sprite',                 'is_bulk' => false, 'unit' => $L,  'packagings' => [[$canette, 0.33], [$btl15L, 1.5]]],

            // ── Ingrédients pizza & pâtisserie ──────────────────────────────
            ['name' => 'Sauce tomate cuisinée',  'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$bte500g, 0.5], [$pot1kg, 1]]],
            ['name' => 'Pepperoni',              'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq500g, 0.5]]],
            ['name' => 'Jambon',                 'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq500g, 0.5], [$bloc2kg, 2]]],
            ['name' => 'Chocolat noir',          'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq500g, 0.5], [$paq250g, 0.25]]],
            ['name' => 'Cacao en poudre',        'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$bte250g, 0.25], [$bte500g, 0.5]]],
            ['name' => 'Sucre vanillé',          'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq250g, 0.25]]],
            ['name' => 'Gélatine',               'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$paq250g, 0.25]]],
            ['name' => 'Mascarpone',             'is_bulk' => false, 'unit' => $kg, 'packagings' => [[$bte500g, 0.5]]],
        ];

        // Créer produits + leurs emballages
        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['name' => $data['name']],
                ['is_bulk' => $data['is_bulk'], 'unit_id' => $data['unit']->id]
            );
            foreach ($data['packagings'] as [$packaging, $qty]) {
                if ($packaging) {
                    ProductPackaging::firstOrCreate(
                        ['product_id' => $product->id, 'packaging_id' => $packaging->id],
                        ['quantity' => $qty]
                    );
                }
            }
        }

        // ─── Remplissage des stocks ────────────────────────────────────────────
        // Niveaux initiaux réalistes par stock (product_name => quantité)
        $inventairesCuisine = [
            'Farine de blé'          => 150,
            'Semoule fine'           => 80,
            'Riz long grain'         => 120,
            'Pâtes spaghetti'        => 40,
            'Pâtes penne'            => 35,
            'Sucre blanc'            => 60,
            'Huile végétale'         => 80,
            'Huile d\'olive'         => 20,
            'Beurre'                 => 15,
            'Margarine'              => 12,
            'Lait entier'            => 50,
            'Crème fraîche'          => 25,
            'Fromage blanc'          => 18,
            'Fromage fondu'          => 20,
            'Mozzarella'             => 25,
            'Viande de bœuf hachée'  => 30,
            'Viande d\'agneau'       => 25,
            'Poulet entier'          => 40,
            'Filets de poulet'       => 35,
            'Merguez'                => 15,
            'Poisson (capitaine)'    => 20,
            'Thon en boîte'          => 48,
            'Œufs'                   => 360,
            'Tomates fraîches'       => 50,
            'Oignons'                => 40,
            'Pommes de terre'        => 60,
            'Poivrons'               => 20,
            'Champignons'            => 15,
            'Salade verte'           => 12,
            'Ail'                    => 10,
            'Citron'                 => 15,
            'Olives noires'          => 8,
            'Concentré de tomate'    => 20,
            'Sel'                    => 25,
            'Poivre noir'            => 3,
            'Cumin'                  => 3,
            'Paprika'                => 3,
            'Curcuma'                => 3,
            'Cannelle'               => 2,
            'Levure boulangère'      => 5,
            'Sauce tomate cuisinée'  => 30,
            'Pepperoni'              => 12,
            'Jambon'                 => 10,
            'Chocolat noir'          => 8,
            'Cacao en poudre'        => 5,
            'Mascarpone'             => 6,
            'Café moulu'             => 8,
        ];

        $inventairesBar = [
            'Eau minérale'    => 200,
            'Jus d\'orange'   => 60,
            'Coca-Cola'       => 144,
            'Fanta Orange'    => 96,
            'Sprite'          => 72,
            'Café moulu'      => 5,
            'Thé en sachets'  => 400,
            'Lait entier'     => 20,
            'Sucre blanc'     => 15,
            'Citron'          => 5,
        ];

        $inventairesReserve = [
            'Farine de blé'       => 300,
            'Riz long grain'      => 200,
            'Sucre blanc'         => 150,
            'Huile végétale'      => 200,
            'Pâtes spaghetti'     => 80,
            'Pâtes penne'         => 80,
            'Sel'                 => 50,
            'Concentré de tomate' => 60,
            'Thon en boîte'       => 120,
            'Eau minérale'        => 300,
            'Coca-Cola'           => 288,
            'Fanta Orange'        => 192,
        ];

        $inventairesCatering = [
            'Riz long grain'         => 80,
            'Farine de blé'          => 50,
            'Semoule fine'           => 60,
            'Poulet entier'          => 30,
            'Filets de poulet'       => 25,
            'Viande de bœuf hachée'  => 20,
            'Tomates fraîches'       => 30,
            'Oignons'                => 25,
            'Pommes de terre'        => 40,
            'Huile végétale'         => 40,
            'Beurre'                 => 8,
            'Lait entier'            => 30,
            'Œufs'                   => 240,
            'Sel'                    => 10,
            'Poivre noir'            => 2,
            'Cumin'                  => 2,
            'Curcuma'                => 2,
            'Café moulu'             => 3,
            'Thé en sachets'         => 200,
            'Sucre blanc'            => 20,
            'Eau minérale'           => 100,
        ];

        $stockInventaires = [
            $stockCuisine->id  => $inventairesCuisine,
            $stockBar->id      => $inventairesBar,
            $stockReserve->id  => $inventairesReserve,
            $stockCatering->id => $inventairesCatering,
        ];

        foreach ($stockInventaires as $stockId => $inventaire) {
            foreach ($inventaire as $productName => $qty) {
                $product = Product::where('name', $productName)->first();
                if ($product) {
                    StockItem::firstOrCreate(
                        ['stock_id' => $stockId, 'product_id' => $product->id],
                        ['quantity' => $qty]
                    );
                    // Enregistrer un mouvement d'entrée initial
                    StockMovement::firstOrCreate(
                        ['product_id' => $product->id, 'stock_id' => $stockId, 'type' => 'in', 'quantity' => $qty],
                    );
                }
            }
        }

        $this->command->info('✅ Stocks créés : 4 stocks, ' . Product::count() . ' produits, ' . StockItem::count() . ' articles en stock.');
    }
}
