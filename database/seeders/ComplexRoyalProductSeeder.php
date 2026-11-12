<?php

namespace Database\Seeders;

use App\Models\Packaging;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPackaging;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComplexRoyalProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['CR-001', 'Riz cassé', 'Céréales & féculents', 'g', 'Sac de 25 kg', 25000],
            ['CR-002', 'Riz Basmati', 'Céréales & féculents', 'g', 'Carton de 5 kg', 5000],
            ['CR-003', 'Sac emporté', 'Emballages & jetables', 'pcs', 'Carton de 300 pcs', 300],
            ['CR-004', 'Derwile (sachets)', 'Pâtes & céréales', 'mL', 'Carton de 20 L', 20000],
            ['CR-005', 'Pâtes macaroni', 'Pâtes & céréales', 'g', 'Carton de 12 paquets de 500 g', 6000, 'Paquet de 500 g', 500],
            ['CR-006', 'Zyt Zytoune (huile olive)', 'Huiles & condiments', 'mL', 'Carton de 20 L', 20000],
            ['CR-007', 'Derwile (huile)', 'Huiles & condiments', 'mL', 'Carton de 15 L', 15000],
            ['CR-008', 'Tomate concentrée', 'Conserves & sauces', 'g', 'Carton de 6 boîtes de 2 kg', 12000, 'Boîte de 2 kg', 2000],
            ['CR-009', 'Spaghetti', 'Pâtes & céréales', 'g', 'Carton de 20 paquets de 500 g', 10000, 'Paquet de 500 g', 500],
            ['CR-010', 'Mayonnaise Rosa', 'Conserves & sauces', 'g', 'Carton de 6 boîtes de 1 kg', 6000, 'Boîte de 1 kg', 1000],
            ['CR-011', 'Thon', 'Conserves & sauces', 'g', 'Carton de 12 boîtes de 1 kg', 12000, 'Boîte de 1 kg', 1000],
            ['CR-012', 'Cornichon', 'Conserves & sauces', 'g', 'Carton de 72 boîtes de 300 g', 21600, 'Boîte de 300 g', 300],
            ['CR-013', 'Nesquik', 'Boissons & petit déjeuner', 'g', 'Carton de 12 boîtes de 390 g', 4680, 'Boîte de 390 g', 390],
            ['CR-014', 'Quaker', 'Boissons & petit déjeuner', 'g', 'Carton de 24 boîtes de 500 g', 12000, 'Boîte de 500 g', 500],
            ['CR-015', 'Sel Le Melt (nom à vérifier)', 'Épices & assaisonnements', 'g', 'Carton de 12 boîtes de 500 g', 6000, 'Boîte de 500 g', 500],
            ['CR-016', 'Kapi moutarde', 'Conserves & sauces', 'mL', 'Carton de 6 boîtes de 1 L', 6000, 'Boîte de 1 L', 1000],
            ['CR-017', 'Farine', 'Céréales & féculents', 'g', 'Sac de 5 kg', 5000],
            ['CR-018', 'Sucre', 'Sucre & pâtisserie', 'g', 'Sac de 5 kg', 5000],
            ['CR-019', 'Ketchup (catsup)', 'Conserves & sauces', 'g', 'Bouteille de 650 g', 650],
            ['CR-020', 'Golerella (nom à vérifier)', 'Conserves & sauces', 'g', 'Boîte de 160 g', 160],
            ['CR-021', 'Paidemi (nom à vérifier)', 'Épices & assaisonnements', 'pcs', 'Carton de 2 sachets', null],
            ['CR-022', 'Pain tacos 30 cm', 'Boulangerie & tortillas', 'pcs', 'Carton de 8 sachets', null],
            ['CR-023', 'Pain tacos 25 cm', 'Boulangerie & tortillas', 'pcs', 'Carton de 8 sachets', null],
            ['CR-024', 'Pain tacos 20 cm', 'Boulangerie & tortillas', 'pcs', 'Carton de 8 sachets', null],
            ['CR-025', 'Bicarbonate', 'Épices & assaisonnements', 'g', 'Carton de 100 boîtes de 90 g', 9000, 'Boîte de 90 g', 90],
            ['CR-026', 'Sucre vanilline', 'Sucre & pâtisserie', 'pcs', 'Paquet de 100 sachets', null],
            ['CR-027', 'Sucre bûchettes', 'Sucre & pâtisserie', 'g', 'Sachet de 5 kg', 5000],
            ['CR-028', 'Papier essuie-tout', 'Emballages & jetables', 'pcs', 'Carton de 8 rouleaux', 8, 'Rouleau', 1],
            ['CR-029', 'Barquette jetable', 'Emballages & jetables', 'pcs', 'Carton de 140 sachets', null],
            ['CR-030', 'Emporté soupe', 'Emballages & jetables', 'pcs', 'Carton de 1000 pcs', 1000],
            ['CR-031', 'Chimousse (nom à vérifier)', 'Emballages & jetables', 'pcs', 'Carton de 30 sachets', null],
            ['CR-032', 'Petite cuillère jetable', 'Emballages & jetables', 'pcs', 'Carton de 40 paquets de 50 pcs', 2000, 'Paquet de 50 pcs', 50],
            ['CR-033', 'Papier aluminium foil', 'Emballages & jetables', 'pcs', 'Carton de 6 paquets', null],
            ['CR-034', 'Papier cling film', 'Emballages & jetables', 'pcs', 'Carton de 6 paquets', null],
            ['CR-035', 'Javel', 'Entretien & hygiène', 'mL', 'Carton de 12 bouteilles de 1 L', 12000, 'Bouteille de 1 L', 1000],
            ['CR-036', 'Madame (produit à vérifier)', 'Entretien & hygiène', 'mL', 'Carton de 12 bouteilles de 1 L', 12000, 'Bouteille de 1 L', 1000],
            ['CR-037', 'Oumo Clean', 'Entretien & hygiène', 'pcs', 'Carton de 156 sachets', null],
            ['CR-038', 'Vinaigre d’alcool', 'Huiles & condiments', 'mL', 'Carton de 12 bouteilles de 1 L', 12000, 'Bouteille de 1 L', 1000],
            ['CR-039', 'Ananas (conserve)', 'Conserves & sauces', 'g', 'Carton de 24 boîtes de 340 g', 8160, 'Boîte de 340 g', 340],
            ['CR-040', 'Sauce Biggy', 'Conserves & sauces', 'g', 'Bouteille de 950 g', 950],
            ['CR-041', 'Gants', 'Emballages & jetables', 'pcs', 'Carton de 24 paquets', null],
            ['CR-042', 'Maggi', 'Épices & assaisonnements', 'pcs', 'Paquet', null],
            ['CR-043', 'Boîte de lait', 'Boissons & petit déjeuner', 'g', 'Boîte de 1 kg', 1000],
            ['CR-044', 'Mayonnaise Goya', 'Conserves & sauces', 'g', 'Boîte de 500 g', 500],
            ['CR-045', 'Haricots blancs', 'Conserves & sauces', 'g', 'Carton de 12 boîtes de 400 g', 4800, 'Boîte de 400 g', 400],
            ['CR-046', 'Macédoine', 'Conserves & sauces', 'g', 'Carton de 12 boîtes de 400 g', 4800, 'Boîte de 400 g', 400],
            ['CR-047', 'Petits pois', 'Conserves & sauces', 'g', 'Carton de 12 boîtes de 400 g', 4800, 'Boîte de 400 g', 400],
            ['CR-048', 'Maïs', 'Conserves & sauces', 'g', 'Carton de 12 boîtes de 400 g', 4800, 'Boîte de 400 g', 400],
            ['CR-049', 'Olives vertes', 'Conserves & sauces', 'pcs', 'Carton de 12 bouteilles', null],
            ['CR-050', 'Olives noires', 'Conserves & sauces', 'pcs', 'Carton de 12 bouteilles', null],
            ['CR-051', 'Essence banane', 'Arômes & pâtisserie', 'mL', 'Paquet de 12 bouteilles de 30 mL', 360, 'Bouteille de 30 mL', 30],
            ['CR-052', 'Essence coco', 'Arômes & pâtisserie', 'mL', 'Paquet de 12 bouteilles de 30 mL', 360, 'Bouteille de 30 mL', 30],
            ['CR-053', 'Thé 2020', 'Boissons & petit déjeuner', 'g', 'Carton de 200 paquets de 25 g', 5000, 'Paquet de 25 g', 25],
        ];

        DB::transaction(function () use ($products): void {
            $units = [
                'g' => ['name' => 'Gramme', 'symbol' => 'g'],
                'mL' => ['name' => 'Millilitre', 'symbol' => 'mL'],
                'pcs' => ['name' => 'Pièce', 'symbol' => 'pcs'],
            ];

            $unitIds = [];
            foreach ($units as $symbol => $unit) {
                $unitIds[$symbol] = Unit::firstOrCreate(
                    ['symbol' => $symbol],
                    ['name' => $unit['name']]
                )->id;
            }

            $retainedProductIds = [];
            $categoryNames = [];
            $packagingNames = [];

            foreach ($products as $row) {
                [$reference, $name, $categoryName, $unitSymbol, $mainPackagingName, $mainQuantity] = $row;
                $categoryNames[] = $categoryName;
                $packagingNames[] = $mainPackagingName;

                $category = ProductCategory::firstOrCreate(['name' => $categoryName]);
                $mainPackaging = Packaging::firstOrCreate(['name' => $mainPackagingName]);
                $unitId = $unitIds[$unitSymbol];

                $product = Product::where('reference', $reference)->first()
                    ?? Product::where('name', $name)->first()
                    ?? new Product;

                $product->fill([
                    'name' => $name,
                    'reference' => $reference,
                    'unit_id' => $unitId,
                    'product_category_id' => $category->id,
                    'packaging_id' => $mainPackaging->id,
                    'is_bulk' => false,
                ]);
                $product->save();
                $retainedProductIds[] = $product->id;

                $this->saveProductPackaging($product->id, $mainPackaging->id, $mainQuantity);

                if (isset($row[6], $row[7])) {
                    $packagingNames[] = $row[6];
                    $innerPackaging = Packaging::firstOrCreate(['name' => $row[6]]);
                    $this->saveProductPackaging($product->id, $innerPackaging->id, $row[7]);
                }
            }

            $this->deleteObsoleteProducts($retainedProductIds);
            $this->deleteObsoletePackagings(array_unique($packagingNames));
            ProductCategory::whereNotIn('name', array_unique($categoryNames))->delete();
        });

        $this->command?->info('Catalogue Complex Royal : '.count($products).' produits importés.');
    }

    private function saveProductPackaging(int $productId, int $packagingId, ?int $quantity): void
    {
        if ($quantity === null || $quantity <= 0) {
            return;
        }

        ProductPackaging::updateOrCreate(
            ['product_id' => $productId, 'packaging_id' => $packagingId],
            ['quantity' => $quantity]
        );
    }

    private function deleteObsoleteProducts(array $retainedProductIds): void
    {
        $obsoleteProductIds = Product::whereNotIn('id', $retainedProductIds)->pluck('id')->all();
        if ($obsoleteProductIds === []) {
            return;
        }

        $transferItemIds = Schema::hasTable('pos_transfer_items')
            ? DB::table('pos_transfer_items')->whereIn('product_id', $obsoleteProductIds)->pluck('id')->all()
            : [];
        $terminalStockItemIds = Schema::hasTable('pos_terminal_stock_items')
            ? DB::table('pos_terminal_stock_items')->whereIn('product_id', $obsoleteProductIds)->pluck('id')->all()
            : [];

        if (Schema::hasTable('pos_return_items')) {
            if ($transferItemIds !== []) {
                DB::table('pos_return_items')->whereIn('pos_transfer_item_id', $transferItemIds)->delete();
            }

            if ($terminalStockItemIds !== []) {
                DB::table('pos_return_items')->whereIn('pos_terminal_stock_item_id', $terminalStockItemIds)->delete();
            }
        }

        foreach ([
            'supplier_return_items',
            'goods_receipt_items',
            'purchase_order_items',
            'purchase_request_items',
            'event_stock_usages',
            'recipe_items',
            'wastes',
            'stock_movements',
            'stock_items',
            'order_items',
            'pos_transfer_items',
            'pos_terminal_stock_items',
            'product_packagings',
        ] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'product_id')) {
                DB::table($table)->whereIn('product_id', $obsoleteProductIds)->delete();
            }
        }

        Product::whereIn('id', $obsoleteProductIds)->delete();
    }

    private function deleteObsoletePackagings(array $retainedPackagingNames): void
    {
        $retainedPackagingIds = Packaging::whereIn('name', $retainedPackagingNames)->pluck('id')->all();
        ProductPackaging::whereNotIn('packaging_id', $retainedPackagingIds)->delete();

        if ($retainedPackagingIds === []) {
            Packaging::query()->delete();

            return;
        }

        Packaging::whereNotIn('id', $retainedPackagingIds)->delete();
    }
}
