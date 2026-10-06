<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,      // 0. Sociétés & sites (doit être en premier)
            PermissionSeeder::class,   // 1. Rôles & permissions Spatie
            UserSeeder::class,         // 2. Super-admins (nourdine, saydou) + RH (salamata.ball)
            ReferenceDataSeeder::class,// 3. Unités, emballages, catégories, types de paiement, postes RH, types de chambre, services
        ]);

        // Jeux de données de démonstration (commandes, clients, contrats, réservations, etc.)
        // — désactivés pour l'environnement de production. Décommenter pour re-remplir
        // une base de test avec des exemples complets.
        // $this->call([
        //     CashierSeeder::class,
        //     StockSeeder::class,
        //     MenuSeeder::class,
        //     ClientSeeder::class,
        //     HRSeeder::class,
        //     HotelSeeder::class,
        //     SupplierSeeder::class,
        //     CateringSeeder::class,
        //     POSOrderSeeder::class,
        //     EventDemoSeeder::class,
        //     PaymentSessionDemoSeeder::class,
        //     PurchaseStockDemoSeeder::class,
        //     HRDemoSeeder::class,
        //     HRTransactionDemoSeeder::class,
        //     ProductionWasteDemoSeeder::class,
        //     CateringPOSDemoSeeder::class,
        // ]);
    }
}

