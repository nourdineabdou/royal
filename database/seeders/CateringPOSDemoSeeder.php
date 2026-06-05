<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Client;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\Meal;
use App\Models\Product;
use App\Models\CateringContract;
use App\Models\CateringWeeklyMenu;
use App\Models\CateringMenuDay;
use App\Models\CateringMenuMeal;
use App\Models\PosTransfer;
use App\Models\PosTransferItem;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

/**
 * Seeder de démonstration du workflow POS Catering :
 *
 * 1. Responsable production (crée les transferts)
 * 2. Caissier matin catering (ouvre session POS matin)
 * 3. Caissier soir catering  (ouvre session POS soir)
 * 4. Un transfert en attente (pending) prêt à être validé par le caissier
 */
class CateringPOSDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Créer les utilisateurs ─────────────────────────────────────────

        $roleManager  = Role::where('name', 'manager')->first();
        $roleCaissier = Role::where('name', 'caissier')->first();

        // Responsable production / catering (crée les transferts)
        $production = User::firstOrCreate(
            ['email' => 'production.catering@royalcomplex.com'],
            [
                'name'     => 'Responsable Production Catering',
                'password' => Hash::make('password'),
            ]
        );
        if ($roleManager && !$production->hasRole('manager')) {
            $production->assignRole($roleManager);
        }

        // Caissier matin catering
        $caissierMatin = User::firstOrCreate(
            ['email' => 'caissier.catering.matin@royalcomplex.com'],
            [
                'name'     => 'Caissier Catering Matin',
                'password' => Hash::make('password'),
            ]
        );
        if ($roleCaissier && !$caissierMatin->hasRole('caissier')) {
            $caissierMatin->assignRole($roleCaissier);
        }

        // Caissier soir catering
        $caissierSoir = User::firstOrCreate(
            ['email' => 'caissier.catering.soir@royalcomplex.com'],
            [
                'name'     => 'Caissier Catering Soir',
                'password' => Hash::make('password'),
            ]
        );
        if ($roleCaissier && !$caissierSoir->hasRole('caissier')) {
            $caissierSoir->assignRole($roleCaissier);
        }

        // ── 2. Récupérer données existantes (stock + client + contrat) ────────

        // Stock catering (production → POS)
        $stockProduction = Stock::where('module', 'catering')
            ->orWhere('name', 'like', '%catering%')
            ->orWhere('name', 'like', '%Catering%')
            ->first();

        if (!$stockProduction) {
            $stockProduction = Stock::first();
        }

        // Client catering avec contrat actif
        $client = Client::whereHas('cateringContracts', fn($q) => $q->where('status', 'active'))
            ->first();

        if (!$client) {
            $client = Client::first();
        }

        // Contrat actif
        $contrat = $client
            ? CateringContract::where('client_id', $client->id)
                ->where('status', 'active')
                ->first()
            : null;

        // ── 3. Créer un transfert en attente (pending) ────────────────────────

        // Vérifier qu'il n'existe pas déjà un transfert demo
        $existingTransfer = PosTransfer::where('notes', 'like', '%Demo CateringPOSDemo%')->first();

        if (!$existingTransfer) {
            // Récupérer les plats du jour (menu d'aujourd'hui si disponible)
            $todayMeals = collect();
            if ($contrat) {
                $today = Carbon::today();
                $menuDay = CateringMenuDay::whereHas('weeklyMenu', fn($q) =>
                    $q->where('catering_contract_id', $contrat->id)
                )->whereDate('date', $today)->first();

                if ($menuDay) {
                    $todayMeals = CateringMenuMeal::where('catering_menu_day_id', $menuDay->id)
                        ->with('items.meal')
                        ->get();
                }
            }

            // Récupérer des plats du menu pour les items du transfert
            $mealsForTransfer = Meal::take(3)->get();
            $productsForTransfer = Product::take(2)->get();

            // Référence auto
            $ref = 'TRNF-' . date('Y') . '-' . str_pad(
                PosTransfer::whereYear('created_at', date('Y'))->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $transfer = PosTransfer::create([
                'from_stock_id'          => $stockProduction?->id,
                'prepared_by'            => $production->id,
                'cash_register_id'       => null,   // sera assigné quand le caissier ouvre sa session
                'client_id'              => $client?->id,
                'catering_contract_id'   => $contrat?->id,
                'driver_name'            => 'Mohammed Ahmed',
                'reference'              => $ref,
                'transfer_date'          => Carbon::today(),
                'status'                 => 'pending',
                'notes'                  => 'Demo CateringPOSDemo — Transfert du matin pour validation caissier',
            ]);

            // Items contrat (repas du menu)
            if ($todayMeals->isNotEmpty()) {
                foreach ($todayMeals as $menuMeal) {
                    $label = match($menuMeal->type) {
                        'breakfast' => 'Petit-déjeuner',
                        'lunch'     => 'Déjeuner',
                        'dinner'    => 'Dîner',
                        default     => ucfirst($menuMeal->type),
                    };
                    PosTransferItem::create([
                        'pos_transfer_id' => $transfer->id,
                        'meal_id'         => $menuMeal->items->first()?->meal_id,
                        'label'           => $label . ' — ' . ($menuMeal->items->pluck('meal.name')->join(', ') ?: 'Repas'),
                        'quantity'        => $contrat->guest_count ?? 30,
                        'unit'            => 'portion',
                        'item_type'       => 'contract',
                        'unit_price'      => $contrat?->getPriceFor($menuMeal->type) ?? 1500,
                    ]);
                }
            } else {
                // Fallback : plats génériques du menu
                foreach ($mealsForTransfer as $meal) {
                    PosTransferItem::create([
                        'pos_transfer_id' => $transfer->id,
                        'meal_id'         => $meal->id,
                        'label'           => $meal->name,
                        'quantity'        => 30,
                        'unit'            => 'portion',
                        'item_type'       => 'contract',
                        'unit_price'      => 1500,
                    ]);
                }
            }

            // Items extras (produits vendables en plus)
            if ($productsForTransfer->isNotEmpty()) {
                foreach ($productsForTransfer->take(2) as $product) {
                    PosTransferItem::create([
                        'pos_transfer_id' => $transfer->id,
                        'product_id'      => $product->id,
                        'label'           => $product->name . ' (extra)',
                        'quantity'        => 20,
                        'unit'            => $product->unit?->abbreviation ?? 'unité',
                        'item_type'       => 'extra',
                        'unit_price'      => 500,
                    ]);
                }
            }

            $this->command->info("✅ Transfert créé : {$ref} ({$transfer->items->count()} articles)");
        } else {
            $this->command->info("ℹ️  Transfert demo déjà existant : {$existingTransfer->reference}");
        }

        $this->command->info('');
        $this->command->info('── Comptes de démonstration POS Catering ──────────────────────');
        $this->command->info('📦 Production (crée les transferts)');
        $this->command->info('   Email    : production.catering@royalcomplex.com');
        $this->command->info('   Password : password');
        $this->command->info('   URL      : /pos-transfer/create');
        $this->command->info('');
        $this->command->info('🌅 Caissier Matin (valide réception + service repas)');
        $this->command->info('   Email    : caissier.catering.matin@royalcomplex.com');
        $this->command->info('   Password : password');
        $this->command->info('   URL      : /cashier/open → choisir "POS Catering"');
        $this->command->info('');
        $this->command->info('🌆 Caissier Soir');
        $this->command->info('   Email    : caissier.catering.soir@royalcomplex.com');
        $this->command->info('   Password : password');
        $this->command->info('   URL      : /cashier/open → choisir "POS Catering"');
        $this->command->info('──────────────────────────────────────────────────────────────');
    }
}
