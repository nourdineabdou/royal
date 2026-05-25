<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Meal;
use App\Models\Accompaniment;
use App\Models\MealAccompaniment;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Accompagnements ───────────────────────────────────────────────────
        $accompaniments = [
            // Sauces (type multiple = peut en choisir plusieurs) - prix individuel
            ['name' => 'Sauce ketchup',        'type' => 'multiple', 'price' => 0],
            ['name' => 'Sauce mayonnaise',      'type' => 'multiple', 'price' => 0],
            ['name' => 'Sauce harissa',         'type' => 'multiple', 'price' => 0],
            ['name' => 'Sauce barbecue',        'type' => 'multiple', 'price' => 0],
            ['name' => 'Sauce fromage',         'type' => 'multiple', 'price' => 50],
            ['name' => 'Sauce César',           'type' => 'multiple', 'price' => 50],
            ['name' => 'Vinaigrette huile/citron', 'type' => 'multiple', 'price' => 0],
            // Garnitures (type single = 1 seule au choix)
            ['name' => 'Frites',               'type' => 'single',   'price' => 150],
            ['name' => 'Salade verte',          'type' => 'single',   'price' => 100],
            ['name' => 'Riz blanc',             'type' => 'single',   'price' => 100],
            ['name' => 'Légumes sautés',        'type' => 'single',   'price' => 150],
            ['name' => 'Pain pita',             'type' => 'single',   'price' => 80],
            // Boissons (single)
            ['name' => 'Eau minérale 50cL',     'type' => 'single',   'price' => 100],
            ['name' => 'Jus d\'orange frais',   'type' => 'single',   'price' => 200],
            ['name' => 'Café',                  'type' => 'single',   'price' => 150],
            ['name' => 'Thé à la menthe',       'type' => 'single',   'price' => 120],
            ['name' => 'Coca-Cola 33cL',        'type' => 'single',   'price' => 200],
        ];

        $accModels = [];
        foreach ($accompaniments as $a) {
            $accModels[$a['name']] = Accompaniment::firstOrCreate(['name' => $a['name']], $a);
        }

        // ─── Plats par catégorie ───────────────────────────────────────────────
        // Photos Unsplash (URLs stables pour démo)
        $catPizzas    = Category::where('name', 'Pizzas')->first();
        $catBurgers   = Category::where('name', 'Burgers')->first();
        $catGrillades = Category::where('name', 'Grillades')->first();
        $catPates     = Category::where('name', 'Pâtes & Riz')->first();
        $catSalades   = Category::where('name', 'Salades')->first();
        $catSoupes    = Category::where('name', 'Soupes')->first();
        $catDesserts  = Category::where('name', 'Desserts')->first();
        $catPetitDej  = Category::where('name', 'Petit-déjeuner')->first();
        $catPoissons  = Category::where('name', 'Poissons & Fruits de mer')->first();
        $catMaghreb   = Category::where('name', 'Plats maghrébins')->first();
        $catBoissons  = Category::where('name', 'Boissons')->first();

        // Format : ['name', 'price', category, 'image_url', [accompaniment_names], [recipe_items: [product, qty_per_portion]]]
        $meals = [

            // ══════════════════ PIZZAS ══════════════════════════════════════════
            [
                'name'  => 'Pizza Margherita',
                'price' => 850,
                'cat'   => $catPizzas,
                'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80',
                'acc'   => ['Sauce ketchup', 'Sauce harissa', 'Coca-Cola 33cL', 'Eau minérale 50cL'],
                'recipe'=> [
                    ['Farine de blé', 0.200], ['Levure boulangère', 0.005], ['Sel', 0.005],
                    ['Huile d\'olive', 0.030], ['Sauce tomate cuisinée', 0.120],
                    ['Mozzarella', 0.150], ['Tomates fraîches', 0.100],
                ],
            ],
            [
                'name'  => 'Pizza Pepperoni',
                'price' => 1050,
                'cat'   => $catPizzas,
                'image' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&q=80',
                'acc'   => ['Sauce ketchup', 'Sauce harissa', 'Coca-Cola 33cL'],
                'recipe'=> [
                    ['Farine de blé', 0.200], ['Levure boulangère', 0.005], ['Sel', 0.005],
                    ['Huile d\'olive', 0.030], ['Sauce tomate cuisinée', 0.120],
                    ['Mozzarella', 0.150], ['Pepperoni', 0.100], ['Poivrons', 0.080],
                ],
            ],
            [
                'name'  => 'Pizza Quatre Fromages',
                'price' => 1100,
                'cat'   => $catPizzas,
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&q=80',
                'acc'   => ['Sauce harissa', 'Eau minérale 50cL'],
                'recipe'=> [
                    ['Farine de blé', 0.200], ['Levure boulangère', 0.005], ['Sel', 0.005],
                    ['Huile d\'olive', 0.030], ['Sauce tomate cuisinée', 0.080],
                    ['Mozzarella', 0.100], ['Fromage fondu', 0.100],
                    ['Fromage blanc', 0.060], ['Crème fraîche', 0.050],
                ],
            ],
            [
                'name'  => 'Pizza Végétarienne',
                'price' => 900,
                'cat'   => $catPizzas,
                'image' => 'https://images.unsplash.com/photo-1571997478779-2adcbbe9ab2f?w=600&q=80',
                'acc'   => ['Sauce ketchup', 'Sauce harissa', 'Salade verte'],
                'recipe'=> [
                    ['Farine de blé', 0.200], ['Levure boulangère', 0.005], ['Sel', 0.005],
                    ['Huile d\'olive', 0.030], ['Sauce tomate cuisinée', 0.120],
                    ['Mozzarella', 0.120], ['Poivrons', 0.100], ['Champignons', 0.080],
                    ['Tomates fraîches', 0.080], ['Olives noires', 0.040],
                ],
            ],
            [
                'name'  => 'Pizza Poulet Barbecue',
                'price' => 1150,
                'cat'   => $catPizzas,
                'image' => 'https://images.unsplash.com/photo-1520201163981-8cc95007dd2a?w=600&q=80',
                'acc'   => ['Sauce barbecue', 'Sauce fromage', 'Coca-Cola 33cL'],
                'recipe'=> [
                    ['Farine de blé', 0.200], ['Levure boulangère', 0.005], ['Sel', 0.005],
                    ['Huile d\'olive', 0.030], ['Sauce tomate cuisinée', 0.080],
                    ['Mozzarella', 0.130], ['Filets de poulet', 0.120],
                    ['Poivrons', 0.060], ['Oignons', 0.050],
                ],
            ],

            // ══════════════════ BURGERS ═════════════════════════════════════════
            [
                'name'  => 'Burger Classique',
                'price' => 650,
                'cat'   => $catBurgers,
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80',
                'acc'   => ['Sauce ketchup', 'Sauce mayonnaise', 'Frites', 'Coca-Cola 33cL'],
                'recipe'=> [
                    ['Farine de blé', 0.100], ['Viande de bœuf hachée', 0.180],
                    ['Fromage fondu', 0.040], ['Tomates fraîches', 0.060],
                    ['Salade verte', 0.030], ['Oignons', 0.040],
                    ['Huile végétale', 0.020], ['Sel', 0.005], ['Poivre noir', 0.002],
                ],
            ],
            [
                'name'  => 'Double Smash Burger',
                'price' => 850,
                'cat'   => $catBurgers,
                'image' => 'https://images.unsplash.com/photo-1550317138-10000687a72b?w=600&q=80',
                'acc'   => ['Sauce ketchup', 'Sauce mayonnaise', 'Sauce barbecue', 'Frites'],
                'recipe'=> [
                    ['Farine de blé', 0.120], ['Viande de bœuf hachée', 0.300],
                    ['Fromage fondu', 0.080], ['Tomates fraîches', 0.080],
                    ['Salade verte', 0.040], ['Oignons', 0.050], ['Beurre', 0.020],
                    ['Sel', 0.006], ['Poivre noir', 0.003],
                ],
            ],
            [
                'name'  => 'Burger Poulet Croustillant',
                'price' => 700,
                'cat'   => $catBurgers,
                'image' => 'https://images.unsplash.com/photo-1598182198871-d3f4ab4fd181?w=600&q=80',
                'acc'   => ['Sauce mayonnaise', 'Sauce harissa', 'Frites', 'Jus d\'orange frais'],
                'recipe'=> [
                    ['Farine de blé', 0.100], ['Filets de poulet', 0.160],
                    ['Fromage fondu', 0.040], ['Tomates fraîches', 0.060],
                    ['Salade verte', 0.030], ['Œufs', 0.060], ['Paprika', 0.005],
                    ['Huile végétale', 0.050], ['Sel', 0.005],
                ],
            ],

            // ══════════════════ GRILLADES ═══════════════════════════════════════
            [
                'name'  => 'Poulet Grillé Entier',
                'price' => 1200,
                'cat'   => $catGrillades,
                'image' => 'https://images.unsplash.com/photo-1518492104633-130d0cc84637?w=600&q=80',
                'acc'   => ['Sauce harissa', 'Frites', 'Salade verte', 'Pain pita'],
                'recipe'=> [
                    ['Poulet entier', 1.000], ['Ail', 0.020], ['Citron', 0.100],
                    ['Huile d\'olive', 0.040], ['Paprika', 0.010], ['Cumin', 0.008],
                    ['Sel', 0.015], ['Poivre noir', 0.005],
                ],
            ],
            [
                'name'  => 'Brochettes de Bœuf',
                'price' => 900,
                'cat'   => $catGrillades,
                'image' => 'https://images.unsplash.com/photo-1546964124-0cce460537df?w=600&q=80',
                'acc'   => ['Sauce harissa', 'Sauce barbecue', 'Frites', 'Pain pita'],
                'recipe'=> [
                    ['Viande de bœuf hachée', 0.250], ['Oignons', 0.080],
                    ['Poivrons', 0.060], ['Cumin', 0.008], ['Paprika', 0.008],
                    ['Sel', 0.010], ['Poivre noir', 0.004], ['Huile végétale', 0.020],
                ],
            ],
            [
                'name'  => 'Mixed Grill Royale',
                'price' => 1800,
                'cat'   => $catGrillades,
                'image' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?w=600&q=80',
                'acc'   => ['Sauce harissa', 'Sauce mayonnaise', 'Frites', 'Salade verte'],
                'recipe'=> [
                    ['Filets de poulet', 0.150], ['Merguez', 0.120],
                    ['Viande d\'agneau', 0.150], ['Poivrons', 0.080],
                    ['Oignons', 0.060], ['Ail', 0.015], ['Citron', 0.080],
                    ['Huile d\'olive', 0.040], ['Sel', 0.012], ['Cumin', 0.010],
                ],
            ],
            [
                'name'  => 'Côtelettes d\'Agneau Grillées',
                'price' => 1500,
                'cat'   => $catGrillades,
                'image' => 'https://images.unsplash.com/photo-1432139555190-58524dae6a55?w=600&q=80',
                'acc'   => ['Sauce harissa', 'Frites', 'Légumes sautés', 'Pain pita'],
                'recipe'=> [
                    ['Viande d\'agneau', 0.350], ['Ail', 0.020], ['Citron', 0.100],
                    ['Cumin', 0.010], ['Paprika', 0.010], ['Sel', 0.012],
                    ['Poivre noir', 0.005], ['Huile d\'olive', 0.040],
                ],
            ],

            // ══════════════════ PÂTES & RIZ ════════════════════════════════════
            [
                'name'  => 'Spaghetti Bolognaise',
                'price' => 750,
                'cat'   => $catPates,
                'image' => 'https://images.unsplash.com/photo-1555949258-eb67b1ef0ceb?w=600&q=80',
                'acc'   => ['Sauce fromage', 'Salade verte', 'Eau minérale 50cL'],
                'recipe'=> [
                    ['Pâtes spaghetti', 0.120], ['Viande de bœuf hachée', 0.120],
                    ['Tomates fraîches', 0.100], ['Concentré de tomate', 0.040],
                    ['Oignons', 0.060], ['Ail', 0.010], ['Huile d\'olive', 0.030],
                    ['Sel', 0.008], ['Poivre noir', 0.003],
                ],
            ],
            [
                'name'  => 'Penne à la Crème',
                'price' => 700,
                'cat'   => $catPates,
                'image' => 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=600&q=80',
                'acc'   => ['Salade verte', 'Eau minérale 50cL'],
                'recipe'=> [
                    ['Pâtes penne', 0.120], ['Filets de poulet', 0.100],
                    ['Crème fraîche', 0.100], ['Fromage fondu', 0.040],
                    ['Champignons', 0.060], ['Beurre', 0.020],
                    ['Sel', 0.007], ['Poivre noir', 0.003],
                ],
            ],
            [
                'name'  => 'Riz au Poulet Épicé',
                'price' => 650,
                'cat'   => $catPates,
                'image' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=600&q=80',
                'acc'   => ['Salade verte', 'Sauce harissa', 'Eau minérale 50cL'],
                'recipe'=> [
                    ['Riz long grain', 0.150], ['Filets de poulet', 0.150],
                    ['Oignons', 0.060], ['Tomates fraîches', 0.080],
                    ['Poivrons', 0.050], ['Curcuma', 0.005], ['Cumin', 0.005],
                    ['Huile végétale', 0.030], ['Sel', 0.008],
                ],
            ],

            // ══════════════════ SALADES ══════════════════════════════════════════
            [
                'name'  => 'Salade César au Poulet',
                'price' => 600,
                'cat'   => $catSalades,
                'image' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=600&q=80',
                'acc'   => ['Sauce César', 'Eau minérale 50cL'],
                'recipe'=> [
                    ['Salade verte', 0.120], ['Filets de poulet', 0.100],
                    ['Fromage fondu', 0.040], ['Tomates fraîches', 0.060],
                    ['Citron', 0.040], ['Huile d\'olive', 0.030], ['Sel', 0.005],
                ],
            ],
            [
                'name'  => 'Salade Marocaine',
                'price' => 450,
                'cat'   => $catSalades,
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80',
                'acc'   => ['Vinaigrette huile/citron', 'Pain pita'],
                'recipe'=> [
                    ['Tomates fraîches', 0.120], ['Poivrons', 0.080],
                    ['Oignons', 0.040], ['Citron', 0.050],
                    ['Huile d\'olive', 0.025], ['Sel', 0.004], ['Cumin', 0.003],
                ],
            ],

            // ══════════════════ SOUPES ══════════════════════════════════════════
            [
                'name'  => 'Soupe Harira',
                'price' => 350,
                'cat'   => $catSoupes,
                'image' => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&q=80',
                'acc'   => ['Pain pita', 'Citron', 'Eau minérale 50cL'],
                'recipe'=> [
                    ['Viande de bœuf hachée', 0.080], ['Tomates fraîches', 0.100],
                    ['Concentré de tomate', 0.040], ['Oignons', 0.060],
                    ['Farine de blé', 0.030], ['Citron', 0.050],
                    ['Cannelle', 0.003], ['Cumin', 0.005], ['Sel', 0.008],
                    ['Huile végétale', 0.020],
                ],
            ],
            [
                'name'  => 'Velouté de Légumes',
                'price' => 300,
                'cat'   => $catSoupes,
                'image' => 'https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?w=600&q=80',
                'acc'   => ['Crème fraîche', 'Pain pita'],
                'recipe'=> [
                    ['Pommes de terre', 0.150], ['Oignons', 0.060],
                    ['Poivrons', 0.060], ['Beurre', 0.020], ['Crème fraîche', 0.060],
                    ['Sel', 0.007], ['Poivre noir', 0.003],
                ],
            ],

            // ══════════════════ POISSONS ════════════════════════════════════════
            [
                'name'  => 'Poisson à la Mauritanienne',
                'price' => 1100,
                'cat'   => $catPoissons,
                'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80',
                'acc'   => ['Riz blanc', 'Salade verte', 'Sauce harissa'],
                'recipe'=> [
                    ['Poisson (capitaine)', 0.400], ['Tomates fraîches', 0.120],
                    ['Oignons', 0.080], ['Poivrons', 0.080], ['Ail', 0.015],
                    ['Citron', 0.080], ['Huile végétale', 0.040],
                    ['Cumin', 0.008], ['Paprika', 0.008], ['Sel', 0.012],
                ],
            ],

            // ══════════════════ PLATS MAGHRÉBINS ══════════════════════════════
            [
                'name'  => 'Couscous Royal',
                'price' => 1200,
                'cat'   => $catMaghreb,
                'image' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=600&q=80',
                'acc'   => ['Sauce harissa', 'Eau minérale 50cL', 'Thé à la menthe'],
                'recipe'=> [
                    ['Semoule fine', 0.200], ['Viande d\'agneau', 0.200],
                    ['Merguez', 0.080], ['Oignons', 0.080], ['Tomates fraîches', 0.100],
                    ['Poivrons', 0.080], ['Pommes de terre', 0.150],
                    ['Cumin', 0.008], ['Paprika', 0.008], ['Curcuma', 0.006],
                    ['Huile végétale', 0.040], ['Sel', 0.012],
                ],
            ],
            [
                'name'  => 'Tajine de Poulet aux Olives',
                'price' => 950,
                'cat'   => $catMaghreb,
                'image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&q=80',
                'acc'   => ['Riz blanc', 'Pain pita', 'Thé à la menthe'],
                'recipe'=> [
                    ['Poulet entier', 0.500], ['Oignons', 0.100], ['Ail', 0.020],
                    ['Olives noires', 0.080], ['Citron', 0.100], ['Tomates fraîches', 0.100],
                    ['Huile d\'olive', 0.050], ['Curcuma', 0.008], ['Cumin', 0.008],
                    ['Cannelle', 0.003], ['Sel', 0.012], ['Poivre noir', 0.004],
                ],
            ],
            [
                'name'  => 'Méchoui d\'Agneau',
                'price' => 2200,
                'cat'   => $catMaghreb,
                'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80',
                'acc'   => ['Sauce harissa', 'Salade verte', 'Riz blanc', 'Thé à la menthe'],
                'recipe'=> [
                    ['Viande d\'agneau', 0.500], ['Beurre', 0.060], ['Ail', 0.030],
                    ['Cumin', 0.012], ['Paprika', 0.012], ['Sel', 0.015],
                    ['Poivre noir', 0.006], ['Citron', 0.100],
                ],
            ],

            // ══════════════════ PETIT-DÉJEUNER ══════════════════════════════════
            [
                'name'  => 'Petit-déjeuner Continental',
                'price' => 600,
                'cat'   => $catPetitDej,
                'image' => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?w=600&q=80',
                'acc'   => ['Café', 'Thé à la menthe', 'Jus d\'orange frais'],
                'recipe'=> [
                    ['Farine de blé', 0.080], ['Beurre', 0.040], ['Œufs', 0.120],
                    ['Lait entier', 0.200], ['Sucre blanc', 0.020],
                    ['Fromage blanc', 0.060], ['Café moulu', 0.015],
                ],
            ],
            [
                'name'  => 'Omelette au Fromage',
                'price' => 450,
                'cat'   => $catPetitDej,
                'image' => 'https://images.unsplash.com/photo-1510511459019-5dda7724fd87?w=600&q=80',
                'acc'   => ['Sauce harissa', 'Pain pita', 'Thé à la menthe'],
                'recipe'=> [
                    ['Œufs', 0.180], ['Fromage fondu', 0.060], ['Beurre', 0.020],
                    ['Tomates fraîches', 0.060], ['Sel', 0.004], ['Poivre noir', 0.002],
                ],
            ],
            [
                'name'  => 'Pancakes au Miel',
                'price' => 500,
                'cat'   => $catPetitDej,
                'image' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=600&q=80',
                'acc'   => ['Café', 'Jus d\'orange frais', 'Thé à la menthe'],
                'recipe'=> [
                    ['Farine de blé', 0.120], ['Œufs', 0.120], ['Lait entier', 0.150],
                    ['Sucre blanc', 0.030], ['Beurre', 0.030], ['Levure chimique', 0.005],
                    ['Sel', 0.002],
                ],
            ],

            // ══════════════════ DESSERTS ════════════════════════════════════════
            [
                'name'  => 'Tiramisu Maison',
                'price' => 450,
                'cat'   => $catDesserts,
                'image' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=600&q=80',
                'acc'   => ['Café', 'Thé à la menthe'],
                'recipe'=> [
                    ['Mascarpone', 0.120], ['Œufs', 0.120], ['Sucre blanc', 0.050],
                    ['Café moulu', 0.020], ['Cacao en poudre', 0.015],
                    ['Farine de blé', 0.060], ['Sucre vanillé', 0.010],
                ],
            ],
            [
                'name'  => 'Moelleux au Chocolat',
                'price' => 380,
                'cat'   => $catDesserts,
                'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&q=80',
                'acc'   => ['Café', 'Crème fraîche'],
                'recipe'=> [
                    ['Chocolat noir', 0.100], ['Beurre', 0.080], ['Sucre blanc', 0.060],
                    ['Œufs', 0.120], ['Farine de blé', 0.040],
                    ['Cacao en poudre', 0.020],
                ],
            ],
            [
                'name'  => 'Cheesecake aux Fruits',
                'price' => 400,
                'cat'   => $catDesserts,
                'image' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=600&q=80',
                'acc'   => ['Café', 'Thé à la menthe'],
                'recipe'=> [
                    ['Fromage blanc', 0.200], ['Crème fraîche', 0.100],
                    ['Sucre blanc', 0.080], ['Œufs', 0.120],
                    ['Beurre', 0.060], ['Farine de blé', 0.080], ['Gélatine', 0.005],
                ],
            ],

            // ══════════════════ BOISSONS ════════════════════════════════════════
            [
                'name'  => 'Jus d\'Orange Pressé',
                'price' => 250,
                'cat'   => $catBoissons,
                'image' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?w=600&q=80',
                'acc'   => [],
                'recipe'=> [['Jus d\'orange', 0.300], ['Sucre blanc', 0.010]],
            ],
            [
                'name'  => 'Thé à la Menthe Royale',
                'price' => 180,
                'cat'   => $catBoissons,
                'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=600&q=80',
                'acc'   => [],
                'recipe'=> [['Thé en sachets', 3], ['Sucre blanc', 0.030], ['Eau minérale', 0.400]],
            ],
            [
                'name'  => 'Café Arabe',
                'price' => 200,
                'cat'   => $catBoissons,
                'image' => 'https://images.unsplash.com/photo-1495774856032-8b90bbb32b32?w=600&q=80',
                'acc'   => [],
                'recipe'=> [['Café moulu', 0.015], ['Eau minérale', 0.080], ['Sucre blanc', 0.010]],
            ],
        ];

        // Créer les plats + accompagnements + recettes
        foreach ($meals as $data) {
            $meal = Meal::firstOrCreate(
                ['name' => $data['name']],
                [
                    'price'       => $data['price'],
                    'category_id' => $data['cat']?->id,
                    'image'       => $data['image'],
                ]
            );

            // Accompagnements
            foreach ($data['acc'] as $accName) {
                if (isset($accModels[$accName])) {
                    MealAccompaniment::firstOrCreate([
                        'meal_id'           => $meal->id,
                        'accompaniment_id'  => $accModels[$accName]->id,
                    ]);
                }
            }

            // Recette
            $recipe = Recipe::firstOrCreate(['meal_id' => $meal->id]);
            foreach ($data['recipe'] as [$productName, $qty]) {
                $product = Product::where('name', $productName)->first();
                if ($product) {
                    RecipeItem::firstOrCreate(
                        ['recipe_id' => $recipe->id, 'product_id' => $product->id],
                        ['quantity'  => $qty]
                    );
                }
            }
        }

        $this->command->info('✅ Menu créé : ' . Meal::count() . ' plats, ' . Accompaniment::count() . ' accompagnements, ' . Recipe::count() . ' recettes.');
    }
}
