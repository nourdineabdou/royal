<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\Packaging;
use App\Models\Category;
use App\Models\PaymentType;
use App\Models\JobTitle;
use App\Models\RoomType;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Unités ───────────────────────────────────────────────────────────
        $units = [
            ['name' => 'Kilogramme',  'symbol' => 'kg'],
            ['name' => 'Gramme',       'symbol' => 'g'],
            ['name' => 'Litre',        'symbol' => 'L'],
            ['name' => 'Millilitre',   'symbol' => 'mL'],
            ['name' => 'Pièce',        'symbol' => 'pcs'],
            ['name' => 'Bouteille',    'symbol' => 'btl'],
            ['name' => 'Boîte',        'symbol' => 'bte'],
            ['name' => 'Sachet',       'symbol' => 'sac'],
            ['name' => 'Carton',       'symbol' => 'ctn'],
            ['name' => 'Douzaine',     'symbol' => 'dz'],
        ];
        foreach ($units as $u) {
            Unit::firstOrCreate(['name' => $u['name']], $u);
        }

        // ─── Emballages ───────────────────────────────────────────────────────
        $packagings = [
            ['name' => 'Sac 50 kg'],
            ['name' => 'Sac 25 kg'],
            ['name' => 'Sac 10 kg'],
            ['name' => 'Sac 5 kg'],
            ['name' => 'Paquet 1 kg'],
            ['name' => 'Paquet 500 g'],
            ['name' => 'Paquet 250 g'],
            ['name' => 'Bidon 20 L'],
            ['name' => 'Bidon 10 L'],
            ['name' => 'Bouteille 5 L'],
            ['name' => 'Bouteille 1.5 L'],
            ['name' => 'Bouteille 1 L'],
            ['name' => 'Bouteille 500 mL'],
            ['name' => 'Canette 33 cL'],
            ['name' => 'Pack 6 bouteilles'],
            ['name' => 'Pack 24 canettes'],
            ['name' => 'Carton 12 unités'],
            ['name' => 'Carton 20 unités'],
            ['name' => 'Bloc 2 kg'],
            ['name' => 'Plaquette 250 g'],
            ['name' => 'Pot 1 kg'],
            ['name' => 'Boîte 500 g'],
            ['name' => 'Boîte 250 g'],
        ];
        foreach ($packagings as $p) {
            Packaging::firstOrCreate(['name' => $p['name']]);
        }

        // ─── Catégories de plats ─────────────────────────────────────────────
        $categories = [
            'Pizzas', 'Burgers', 'Grillades', 'Pâtes & Riz',
            'Salades', 'Soupes', 'Desserts', 'Petit-déjeuner',
            'Poissons & Fruits de mer', 'Plats maghrébins', 'Boissons', 'Sandwichs',
        ];
        foreach ($categories as $c) {
            Category::firstOrCreate(['name' => $c]);
        }

        // ─── Types de paiement ────────────────────────────────────────────────
        $paymentTypes = [
            'Espèces', 'Carte bancaire', 'Virement bancaire',
            'Chèque', 'Mobile Money',
            'Masrivi', 'Sadad', 'Bankily', 'Click',
        ];
        foreach ($paymentTypes as $pt) {
            PaymentType::firstOrCreate(['name' => $pt]);
        }

        // ─── Postes (RH) ─────────────────────────────────────────────────────
        $jobTitles = [
            ['name' => 'Directeur Général',        'description' => 'Direction et gestion globale',              'base_salary' => 250000],
            ['name' => 'Directeur Restauration',   'description' => 'Gestion du département restauration',      'base_salary' => 150000],
            ['name' => 'Chef Cuisinier',            'description' => 'Responsable de la cuisine',               'base_salary' => 120000],
            ['name' => 'Sous-chef',                 'description' => 'Second de cuisine',                       'base_salary' => 90000],
            ['name' => 'Cuisinier',                 'description' => 'Préparation des plats',                   'base_salary' => 60000],
            ['name' => 'Pâtissier',                 'description' => 'Desserts et pâtisseries',                 'base_salary' => 70000],
            ['name' => 'Serveur',                   'description' => 'Service en salle',                        'base_salary' => 40000],
            ['name' => 'Caissier',                  'description' => 'Gestion de la caisse',                    'base_salary' => 45000],
            ['name' => 'Réceptionniste',            'description' => 'Accueil et réservations hôtel',           'base_salary' => 48000],
            ['name' => 'Femme de chambre',          'description' => 'Entretien des chambres',                  'base_salary' => 35000],
            ['name' => 'Responsable Catering',      'description' => 'Gestion des contrats de restauration',   'base_salary' => 80000],
            ['name' => 'Agent de sécurité',         'description' => 'Sécurité des locaux',                    'base_salary' => 38000],
            ['name' => 'Responsable Stock',         'description' => 'Gestion des stocks et approvisionnements', 'base_salary' => 65000],
            ['name' => 'Comptable',                 'description' => 'Gestion comptable et financière',         'base_salary' => 85000],
            ['name' => 'Plongeur',                  'description' => 'Vaisselle et propreté cuisine',           'base_salary' => 30000],
        ];
        foreach ($jobTitles as $jt) {
            JobTitle::firstOrCreate(['name' => $jt['name']], $jt);
        }

        // ─── Types de chambres ───────────────────────────────────────────────
        $roomTypes = [
            ['name' => 'Chambre Standard',   'description' => 'Chambre confortable avec lit double, TV, climatisation et salle de bain privée.',         'base_price' => 4500],
            ['name' => 'Chambre Deluxe',     'description' => 'Chambre spacieuse avec vue sur jardin, literie haut de gamme et minibar.',                'base_price' => 7500],
            ['name' => 'Suite Junior',       'description' => 'Suite avec salon séparé, baignoire et vue panoramique.',                                  'base_price' => 12000],
            ['name' => 'Suite Présidentielle','description' => 'Suite de luxe avec 2 chambres, salon, cuisine équipée et terrasse privée.',              'base_price' => 25000],
            ['name' => 'Appartement',        'description' => 'Appartement meublé idéal pour séjours longue durée, cuisine équipée et 2 salles de bain.', 'base_price' => 18000],
        ];
        foreach ($roomTypes as $rt) {
            RoomType::firstOrCreate(['name' => $rt['name']], $rt);
        }

        // ─── Services événementiel ────────────────────────────────────────────
        $services = [
            ['name' => 'Salle de conférence (100 pers.)',  'price' => 15000, 'type' => 'hall'],
            ['name' => 'Grande salle de réception',        'price' => 30000, 'type' => 'hall'],
            ['name' => 'Salle de réunion (20 pers.)',      'price' => 6000,  'type' => 'hall'],
            ['name' => 'Scène et sono',                    'price' => 8000,  'type' => 'equipment'],
            ['name' => 'Éclairage événementiel',           'price' => 5000,  'type' => 'equipment'],
            ['name' => 'Vidéoprojecteur + écran',          'price' => 3000,  'type' => 'equipment'],
            ['name' => 'Sonorisation DJ',                  'price' => 10000, 'type' => 'equipment'],
            ['name' => 'Décoration florale',               'price' => 12000, 'type' => 'logistic'],
            ['name' => 'Transport invités (bus)',          'price' => 8000,  'type' => 'logistic'],
            ['name' => 'Service photographe',              'price' => 6000,  'type' => 'logistic'],
            ['name' => 'Sécurité événement (équipe)',      'price' => 5000,  'type' => 'logistic'],
        ];
        foreach ($services as $s) {
            Service::firstOrCreate(['name' => $s['name']], $s);
        }

        $this->command->info('✅ Données de référence créées : unités, emballages, catégories, paiements, postes, chambres, services.');
    }
}
