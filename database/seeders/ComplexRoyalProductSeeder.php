<?php

namespace Database\Seeders;

use App\Models\Packaging;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPackaging;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            ['CR-054', 'Champignons en conserve', 'Conserves & sauces', 'g', 'Boîte (contenance à vérifier)', null],
            ['CR-055', 'ADJA (assaisonnement liquide)', 'Épices & assaisonnements', 'mL', 'Carton de 12 bouteilles de 1 L', 12000, 'Bouteille de 1 L', 1000],
            ['CR-056', 'Mayonnaise Cheffin', 'Conserves & sauces', 'g', 'Carton de 4 sacs de 5 kg', 20000, 'Sac de 5 kg', 5000],
            ['CR-057', 'Tayba (produit à identifier)', 'Autres / à identifier', 'g', 'Sac de 5 kg', 5000],
            ['CR-058', 'Mini thon (petit format)', 'Conserves & sauces', 'pcs', 'Carton (contenu à vérifier)', null],
            ['CR-059', 'Café (dosettes / sachets)', 'Boissons & petit déjeuner', 'g', 'Carton (contenu à vérifier)', null],
            ['CR-060', 'Equal (nom à vérifier)', 'Autres / à identifier', 'pcs', 'Carton de 1000 sachets', 1000],
            ['CR-061', 'Lait (désignation incomplète)', 'Boissons & petit déjeuner', 'pcs', 'Pièce (contenu à vérifier)', null],
            ['CR-062', 'Tiges à brochettes en bois', 'Emballages & jetables', 'pcs', 'Paquet (contenu à vérifier)', null],
            ['CR-063', 'Fleur d’oranger', 'Arômes & pâtisserie', 'mL', 'Flacon de 100 mL', 100],
            ['CR-064', 'Papier cuisson (feuilles 38 × 42 cm)', 'Emballages & jetables', 'pcs', 'Tube (contenu à vérifier)', null],
            ['CR-065', 'Nutella chocolat (pot 750 g)', 'Conserves & sauces', 'g', 'Carton (contenu à vérifier)', null, 'Pot de 750 g', 750],
            ['CR-066', 'Confiture (pot 500 g)', 'Conserves & sauces', 'g', 'Carton (contenu à vérifier)', null, 'Pot de 500 g', 500],
            ['CR-067', 'Madar liquide (850 g)', 'Entretien & hygiène', 'g', 'Bouteille de 850 g', 850],
            ['CR-068', 'Savon liquide mains', 'Entretien & hygiène', 'mL', 'Carton de 12 bouteilles de 400 mL', 4800, 'Bouteille de 400 mL', 400],
            ['CR-069', 'OVI (produit à identifier)', 'Entretien & hygiène', 'g', 'Carton (contenu à vérifier)', null, 'Bouteille de 300 g', 300],
            ['CR-070', 'Glow (boisson ou produit liquide)', 'Autres / à identifier', 'mL', 'Bouteille de 1,5 L', 1500],
            ['CR-071', 'Petit lait (boisson)', 'Boissons & petit déjeuner', 'mL', 'Carton de 24 pièces de 200 mL', 4800, 'Pièce de 200 mL', 200],
            ['CR-072', 'Frites surgelées (Damaco)', 'Surgelés', 'g', 'Carton de 4 sachets de 2,5 kg', 10000, 'Sachet de 2,5 kg', 2500],
            ['CR-073', 'Produit en sac (nom à vérifier)', 'Autres / à identifier', 'g', 'Sac de 25 kg', 25000],
            ['CR-074', 'Pommes de terre', 'Fruits & légumes frais', 'g', 'Sac de 25 kg', 25000],
            ['CR-075', 'Cuisses de poulet (CaseFarms USA, 10 kg)', 'Viandes & volailles', 'g', 'Carton de 10 kg', 10000],
            ['CR-076', 'Viande avec os', 'Viandes & volailles', 'g', 'Kilogramme', 1000],
            ['CR-077', 'Viande sans os', 'Viandes & volailles', 'g', 'Kilogramme', 1000],
            ['CR-078', 'Pommes (fruit)', 'Fruits & légumes frais', 'pcs', 'Pièce', 1],
            ['CR-079', 'Yaourts', 'Produits laitiers & œufs', 'pcs', 'Pièce', 1],
            ['CR-080', 'Raïb / rayeb', 'Produits laitiers & œufs', 'pcs', 'Pièce', 1],
            ['CR-081', 'Beurre (marque non précisée)', 'Produits laitiers & œufs', 'pcs', 'Carton (contenu à vérifier)', null],
            ['CR-082', 'Fromage cheddar', 'Produits laitiers & œufs', 'pcs', 'Carton de 36 pièces', 36, 'Pièce', 1],
            ['CR-083', 'Beurre Président (200 g)', 'Produits laitiers & œufs', 'pcs', 'Carton (contenu à vérifier)', null, 'Pièce de 200 g', 1],
            ['CR-084', 'Jus de goyave (YESSI, 1 L)', 'Boissons & petit déjeuner', 'mL', 'Carton de 12 bouteilles de 1 L', 12000, 'Bouteille de 1 L', 1000],
            ['CR-085', 'Jus d’orange (Juper, 1 L)', 'Boissons & petit déjeuner', 'mL', 'Carton (nombre de bouteilles à vérifier)', null, 'Bouteille de 1 L', 1000],
            ['CR-086', 'Eau embouteillée', 'Boissons & petit déjeuner', 'mL', 'Carton (contenance à vérifier)', null],
            ['CR-087', 'ORO (boisson)', 'Boissons & petit déjeuner', 'mL', 'Carton de 12 bouteilles de 750 mL', 9000, 'Bouteille de 750 mL', 750],
            ['CR-088', 'Œufs frais (cartons de plateaux)', 'Produits laitiers & œufs', 'pcs', 'Carton (contenu à vérifier)', null],
            ['CR-089', 'Carottes', 'Fruits & légumes frais', 'g', 'Kilogramme', 1000],
            ['CR-090', 'Poivrons assortis (rouges / jaunes / verts)', 'Fruits & légumes frais', 'g', 'Kilogramme', 1000],
            ['CR-091', 'Fromage gouda', 'Produits laitiers & œufs', 'g', 'Carton (poids à vérifier)', null],
            ['CR-092', 'Crème liquide de cuisine (1 L)', 'Produits laitiers & œufs', 'mL', 'Carton de 12 briques de 1 L', 12000, 'Brique de 1 L', 1000],
            ['CR-093', 'Filet de poulet (Lezita, 1 kg)', 'Viandes & volailles', 'g', 'Carton de 12 pièces de 1 kg', 12000, 'Pièce de 1 kg', 1000],
            ['CR-094', 'Bouillon Jumbo (tablettes)', 'Épices & assaisonnements', 'pcs', 'Tablette (conditionnement à vérifier)', null],
            ['CR-095', 'Fourchettes jetables', 'Emballages & jetables', 'pcs', 'Paquet de 50 pièces', 50, 'Pièce', 1],
        ];

        /*
         * Produits « consommables » = vendus tels quels au POS catering (is_consumable + sale_price).
         * Tout le catalogue ci-dessus est de la matière première, du jetable ou de l'entretien :
         * rien n'est vendu directement au client, donc aucun produit n'est consommable par défaut.
         * Pour en rendre un vendable, ajoutez sa référence et son prix de vente (MRU, par unité de base).
         * Exemple : 'CR-053' => 10.00,
         */
        $consumables = [
        ];

        $createdProductCount = 0;
        $existingProductCount = 0;

        DB::transaction(function () use ($products, $consumables, &$createdProductCount, &$existingProductCount): void {
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

            foreach ($products as $row) {
                [$reference, $name, $categoryName, $unitSymbol, $mainPackagingName, $mainQuantity] = $row;
                $unitId = $unitIds[$unitSymbol];

                $product = Product::where('reference', $reference)->first();
                if ($product === null) {
                    $product = Product::where('name', $name)->first();
                }

                if ($product === null) {
                    $category = ProductCategory::firstOrCreate(['name' => $categoryName]);
                    $mainPackaging = Packaging::firstOrCreate(['name' => $mainPackagingName]);

                    $product = new Product;
                    $product->fill([
                        'name' => $name,
                        'reference' => $reference,
                        'unit_id' => $unitId,
                        'product_category_id' => $category->id,
                        'packaging_id' => $mainPackaging->id,
                        'is_bulk' => false,
                        'is_consumable' => array_key_exists($reference, $consumables),
                        'sale_price' => $consumables[$reference] ?? null,
                    ]);
                    $product->save();
                    $createdProductCount++;
                } else {
                    $existingProductCount++;
                    $mainPackaging = Packaging::firstOrCreate(['name' => $mainPackagingName]);
                }

                $this->saveProductPackaging($product->id, $mainPackaging->id, $mainQuantity);

                if (isset($row[6], $row[7])) {
                    $innerPackaging = Packaging::firstOrCreate(['name' => $row[6]]);
                    $this->saveProductPackaging($product->id, $innerPackaging->id, $row[7]);
                }
            }
        });

        $this->command?->info(
            'Catalogue Complex Royal : '.$createdProductCount.' produits ajoutés, '
            .$existingProductCount.' déjà présents conservés.'
        );
    }

    private function saveProductPackaging(int $productId, int $packagingId, ?int $quantity): void
    {
        if ($quantity === null || $quantity <= 0) {
            return;
        }

        ProductPackaging::firstOrCreate(
            ['product_id' => $productId, 'packaging_id' => $packagingId],
            ['quantity' => $quantity]
        );
    }
}
