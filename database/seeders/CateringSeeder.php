<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\CateringContract;
use App\Models\CateringContractPrice;
use App\Models\CateringWeeklyMenu;
use App\Models\CateringMenuDay;
use App\Models\CateringMenuMeal;
use App\Models\CateringMenuMealItem;
use App\Models\CateringMealCode;
use App\Models\CateringConsumption;
use App\Models\Meal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CateringSeeder extends Seeder
{
    public function run(): void
    {
        $caissier = User::whereHas('roles', fn($q) => $q->where('name', 'caissier'))->first()
                 ?? User::first();

        // ─── Contrats de restauration ──────────────────────────────────────────

        // Contrat 1 — SOMELEC (50 convives, lun-ven, déjeuner + dîner)
        $clientSomelec = Client::where('company', 'SOMELEC')->first();

        // Contrat 2 — SNPT (30 convives, lun-ven, matin + déj)
        $clientSNPT = Client::where('company', 'SNPT')->first();

        // Contrat 3 — BIM (30 convives, lun-sam, déjeuner seulement)
        $clientBIM = Client::where('company', 'BIM (Banque pour le Commerce)')->first();

        // Contrat 4 — Mauritel (terminé)
        $clientMauritel = Client::where('company', 'Mauritel')->first();

        if (!$clientSomelec || !$clientSNPT || !$clientBIM) {
            $this->command->warn('⚠️ Clients manquants. Lancez ClientSeeder d\'abord.');
            return;
        }

        // --- Contrat 1 : SOMELEC ---
        $contract1 = CateringContract::firstOrCreate(
            ['client_id' => $clientSomelec->id, 'start_date' => '2026-01-01'],
            [
                'end_date'       => '2026-12-31',
                'guest_count'    => 50,
                'active_days'    => json_encode([1, 2, 3, 4, 5]),
                'has_breakfast'  => false,
                'has_lunch'      => true,
                'has_dinner'     => true,
                'status'         => 'active',
            ]
        );
        CateringContractPrice::firstOrCreate(['catering_contract_id' => $contract1->id, 'type' => 'lunch'],  ['price' => 600]);
        CateringContractPrice::firstOrCreate(['catering_contract_id' => $contract1->id, 'type' => 'dinner'], ['price' => 650]);

        // --- Contrat 2 : SNPT ---
        $contract2 = CateringContract::firstOrCreate(
            ['client_id' => $clientSNPT->id, 'start_date' => '2026-02-01'],
            [
                'end_date'       => '2026-07-31',
                'guest_count'    => 30,
                'active_days'    => json_encode([1, 2, 3, 4, 5]),
                'has_breakfast'  => true,
                'has_lunch'      => true,
                'has_dinner'     => false,
                'status'         => 'active',
            ]
        );
        CateringContractPrice::firstOrCreate(['catering_contract_id' => $contract2->id, 'type' => 'breakfast'], ['price' => 300]);
        CateringContractPrice::firstOrCreate(['catering_contract_id' => $contract2->id, 'type' => 'lunch'],     ['price' => 550]);

        // --- Contrat 3 : BIM ---
        $contract3 = CateringContract::firstOrCreate(
            ['client_id' => $clientBIM->id, 'start_date' => '2026-03-01'],
            [
                'end_date'       => '2026-08-31',
                'guest_count'    => 30,
                'active_days'    => json_encode([1, 2, 3, 4, 5, 6]),
                'has_breakfast'  => false,
                'has_lunch'      => true,
                'has_dinner'     => false,
                'status'         => 'active',
            ]
        );
        CateringContractPrice::firstOrCreate(['catering_contract_id' => $contract3->id, 'type' => 'lunch'], ['price' => 500]);

        // --- Contrat 4 : Mauritel (terminé) ---
        if ($clientMauritel) {
            $contract4 = CateringContract::firstOrCreate(
                ['client_id' => $clientMauritel->id, 'start_date' => '2025-07-01'],
                [
                    'end_date'       => '2026-01-31',
                    'guest_count'    => 40,
                    'active_days'    => json_encode([1, 2, 3, 4, 5]),
                    'has_breakfast'  => true,
                    'has_lunch'      => true,
                    'has_dinner'     => false,
                    'status'         => 'ended',
                ]
            );
            CateringContractPrice::firstOrCreate(['catering_contract_id' => $contract4->id, 'type' => 'breakfast'], ['price' => 280]);
            CateringContractPrice::firstOrCreate(['catering_contract_id' => $contract4->id, 'type' => 'lunch'],     ['price' => 520]);
        }

        // ─── Menus hebdomadaires + jours + repas + codes ─────────────────────
        // On génère 2 semaines de menus pour le contrat SOMELEC (le plus complet)
        $this->genererSemaines($contract1, 2, $caissier);

        // 1 semaine pour SNPT
        $this->genererSemaines($contract2, 1, $caissier);

        // 1 semaine pour BIM
        $this->genererSemaines($contract3, 1, $caissier);

        $this->command->info('✅ Catering créé : '
            . CateringContract::count()    . ' contrats, '
            . CateringWeeklyMenu::count()  . ' menus hebdo, '
            . CateringMenuMeal::count()    . ' séances repas, '
            . CateringMealCode::count()    . ' codes générés, '
            . CateringConsumption::count() . ' consommations.'
        );
    }

    /**
     * Génère $nbSemaines semaines de menus pour un contrat donné.
     * La première semaine est passée (avec des validations), la dernière est en cours.
     */
    private function genererSemaines(CateringContract $contract, int $nbSemaines, ?User $caissier): void
    {
        // Plats disponibles par type de repas
        $platsMatin  = Meal::whereHas('category', fn($q) => $q->where('name', 'Petit-déjeuner'))->pluck('id')->toArray();
        $platsDeج  = Meal::whereHas('category', fn($q) => $q->whereIn('name', ['Plats maghrébins', 'Pâtes & Riz', 'Grillades', 'Poissons & Fruits de mer']))->pluck('id')->toArray();
        $platsSoir   = Meal::whereHas('category', fn($q) => $q->whereIn('name', ['Grillades', 'Pizzas', 'Pâtes & Riz', 'Plats maghrébins']))->pluck('id')->toArray();

        // Fallback si catégories vides
        $allMeals = Meal::pluck('id')->toArray();
        if (empty($platsMatin)) $platsMatin = $allMeals;
        if (empty($platsDeج))  $platsDeج  = $allMeals;
        if (empty($platsSoir))  $platsSoir  = $allMeals;

        $activeDays = json_decode($contract->active_days, true) ?? [1, 2, 3, 4, 5];

        for ($s = $nbSemaines - 1; $s >= 0; $s--) {
            // Lundi de la semaine (s=0 = cette semaine, s=1 = semaine passée)
            $monday = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks($s);

            $menu = CateringWeeklyMenu::firstOrCreate(
                ['catering_contract_id' => $contract->id, 'week_start_date' => $monday->format('Y-m-d')]
            );

            foreach ($activeDays as $dayNum) {
                // $dayNum 1=lundi, 7=dimanche (ISO 8601)
                // active_days uses ISO: 1=Monday … 7=Sunday
                $date = $monday->copy()->addDays($dayNum - 1);

                $menuDay = CateringMenuDay::firstOrCreate(
                    ['catering_weekly_menu_id' => $menu->id, 'date' => $date->format('Y-m-d')]
                );

                $typesRepas = [];
                if ($contract->has_breakfast) $typesRepas[] = ['type' => 'breakfast', 'plats' => $platsMatin];
                if ($contract->has_lunch)     $typesRepas[] = ['type' => 'lunch',     'plats' => $platsDeج];
                if ($contract->has_dinner)    $typesRepas[] = ['type' => 'dinner',    'plats' => $platsSoir];

                foreach ($typesRepas as $repas) {
                    $menuMeal = CateringMenuMeal::firstOrCreate(
                        ['catering_menu_day_id' => $menuDay->id, 'type' => $repas['type']],
                        ['quantity' => $contract->guest_count]
                    );

                    // Associer 2-3 plats variés à ce repas
                    $selectedMeals = array_slice($repas['plats'], 0, min(2, count($repas['plats'])));
                    foreach ($selectedMeals as $mealId) {
                        CateringMenuMealItem::firstOrCreate(
                            ['catering_menu_meal_id' => $menuMeal->id, 'meal_id' => $mealId]
                        );
                    }

                    // Générer les codes (1 code par convive)
                    if (CateringMealCode::where('catering_menu_meal_id', $menuMeal->id)->count() === 0) {
                        $codes = [];
                        for ($i = 0; $i < $contract->guest_count; $i++) {
                            $codes[] = [
                                'catering_menu_meal_id' => $menuMeal->id,
                                'code'                  => strtoupper(Str::random(3)) . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                                'is_used'               => false,
                                'created_at'            => now(),
                                'updated_at'            => now(),
                            ];
                        }
                        // Insertion par batch pour performance
                        foreach (array_chunk($codes, 50) as $batch) {
                            CateringMealCode::insert($batch);
                        }
                    }

                    // Pour les semaines passées : valider ~80% des codes
                    if ($s > 0 && $date->isPast() && $caissier) {
                        $mealCodes = CateringMealCode::where('catering_menu_meal_id', $menuMeal->id)
                            ->where('is_used', false)->get();
                        $nbAValider = (int)($mealCodes->count() * 0.80);
                        foreach ($mealCodes->take($nbAValider) as $code) {
                            $code->update([
                                'is_used'      => true,
                                'used_at'      => $date->copy()->setTime(rand(7, 19), rand(0, 59)),
                                'validated_by' => $caissier->id,
                            ]);
                            CateringConsumption::firstOrCreate(
                                ['catering_meal_code_id' => $code->id],
                                [
                                    'consumed_at' => $code->used_at,
                                    'user_id'     => $caissier->id,
                                ]
                            );

                            // Décrémentation du stock pour chaque plat consommé
                            $stock = \App\Models\Stock::forModule('catering');
                            foreach ($menuMeal->items as $menuMealItem) {
                                $meal = $menuMealItem->meal;
                                if ($meal && $meal->recipe) {
                                    foreach ($meal->recipe->items as $item) {
                                        $product = $item->product;
                                        if ($product) {
                                            $stockItem = $product->stockItems()->where('stock_id', $stock ? $stock->id : null)->first();
                                            if ($stockItem) {
                                                $stockItem->decrement('quantity', $item->quantity);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
