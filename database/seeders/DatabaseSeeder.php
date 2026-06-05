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
            UserSeeder::class,         // 2. Super-admin nourdine@gmail.com
            CashierSeeder::class,      // 2b. Caissiers pour chaque module et shift
            ReferenceDataSeeder::class,// 3. Unités, emballages, catégories, types de paiement, fonctions, types de chambre, services
            StockSeeder::class,        // 4. 4 stocks + 57 produits + emballages + inventaire initial
            MenuSeeder::class,         // 5. 30 plats + accompagnements + recettes
            ClientSeeder::class,       // 6. 10 clients (entreprises + particuliers)
            HRSeeder::class,           // 7. 18 employés dont 6 avec compte utilisateur
            HotelSeeder::class,        // 8. 27 chambres + 8 réservations + caisse
            SupplierSeeder::class,     // 9. 5 fournisseurs + 6 bons de commande + réceptions
            CateringSeeder::class,     // 10. 4 contrats catering + menus hebdo + codes repas
            POSOrderSeeder::class,     // 11. 18 commandes restaurant (15 payées + 3 en cours)
            EventDemoSeeder::class,    // 12. Exemples complets d'événements (client, service, repas, recette, option)
            PaymentSessionDemoSeeder::class, // 13. Paiements et sessions de caisse pour event, catering, résidence
            PurchaseStockDemoSeeder::class,  // 14. Achats pour chaque stock avec paiement et remplissage
            HRDemoSeeder::class,             // 15. Cas RH : employé, contrat, congé, absence, paie
            HRTransactionDemoSeeder::class,  // 16. Transactions de paie RH
            ProductionWasteDemoSeeder::class, // 17. Démonstration gestion produits périmés/gâtés (production)
            CateringPOSDemoSeeder::class,     // 18. Démo workflow POS Catering (2 caissiers + transfert en attente)
        ]);
    }
}

