<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\CateringContract;
use App\Models\CateringContractPrice;
use App\Models\CateringWeeklyMenu;
use App\Models\CateringMenuDay;
use App\Models\CateringMenuMeal;
use App\Models\CateringMenuMealItem;
use App\Models\CateringMealCode;
use App\Models\CateringConsumption;
use App\Models\Transaction;
use App\Models\Meal;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CateringController extends Controller
{
    // â”€â”€â”€ Dashboard â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function dashboard()
    {
        $this->perm('catering.dashboard');
        $totalContracts  = CateringContract::count();
        $activeContracts = CateringContract::where('status', 'active')->count();
        $totalCodes      = CateringMealCode::count();
        $usedCodes       = CateringMealCode::where('is_used', true)->count();
        $todayValidations = CateringConsumption::whereDate('consumed_at', today())->count();

        $monthRevenue = Transaction::where('type', 'sale')
            ->where('reference', 'like', 'CAT-%')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('D d/m');
            $chartData[]   = CateringConsumption::whereDate('consumed_at', $date)->count();
        }

        $recentConsumptions = CateringConsumption::with([
            'mealCode.menuMeal.menuDay.weeklyMenu.contract.client',
            'mealCode.menuMeal',
            'user',
        ])->latest('consumed_at')->take(10)->get();

        $activeContractsList = CateringContract::with('client')
            ->where('status', 'active')
            ->withCount('weeklyMenus')
            ->latest()->take(6)->get();

        return view('catering.dashboard', compact(
            'totalContracts', 'activeContracts', 'totalCodes', 'usedCodes',
            'todayValidations', 'monthRevenue', 'chartLabels', 'chartData',
            'recentConsumptions', 'activeContractsList'
        ));
    }

    // â”€â”€â”€ Clients â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function clients()
    {
        $this->perm('catering.clients.view');
        $clients = Client::withCount('cateringContracts')->orderBy('name')->paginate(20);
        return view('catering.clients', compact('clients'));
    }

    public function storeClient(Request $request)
    {
        $this->perm('catering.clients.create');
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'notes'   => 'nullable|string',
        ]);
        Client::create($request->only('name', 'phone', 'email', 'company', 'notes'));
        return back()->with('success', 'Client créé.');
    }

    public function updateClient(Request $request, Client $client)
    {
        $this->perm('catering.clients.edit');
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'notes'   => 'nullable|string',
        ]);
        $client->update($request->only('name', 'phone', 'email', 'company', 'notes'));
        return back()->with('success', 'Client mis à jour.');
    }

    public function destroyClient(Client $client)
    {
        $this->perm('catering.clients.delete');
        if ($client->cateringContracts()->exists()) {
            return back()->with('error', 'Impossible : des contrats exist pour ce client.');
        }
        $client->delete();
        return back()->with('success', 'Client supprimÃ©.');
    }

    // â”€â”€â”€ Contracts â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function contracts()
    {
        $this->perm('catering.contracts.view');
        $contracts = CateringContract::with('client')
            ->withCount('weeklyMenus')
            ->latest()->paginate(20);
        $clients = Client::orderBy('name')->get();
        return view('catering.contracts', compact('contracts', 'clients'));
    }

    public function storeContract(Request $request)
    {
        $this->perm('catering.contracts.create');
        $request->validate([
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'guest_count' => 'required|integer|min:1',
            'active_days' => 'required|array|min:1',
            'status'      => 'required|in:active,paused,ended',
        ]);

        if (!$request->has('has_breakfast') && !$request->has('has_lunch') && !$request->has('has_dinner')) {
            return back()->withErrors(['meal' => 'SÃ©lectionnez au moins un type de repas.'])->withInput();
        }

        DB::transaction(function () use ($request) {
            $contract = CateringContract::create([
                'client_id'     => $request->client_id,
                'start_date'    => $request->start_date,
                'end_date'      => $request->end_date,
                'guest_count'   => $request->guest_count,
                'active_days'   => $request->active_days,
                'has_breakfast' => $request->boolean('has_breakfast'),
                'has_lunch'     => $request->boolean('has_lunch'),
                'has_dinner'    => $request->boolean('has_dinner'),
                'status'        => $request->status,
            ]);

            foreach (['breakfast', 'lunch', 'dinner'] as $type) {
                $price = $request->input("price_{$type}");
                if ($price !== null && $price !== '') {
                    $contract->prices()->create(['type' => $type, 'price' => (float)$price]);
                }
            }
        });

        return redirect()->route('catering.contracts')->with('success', 'Contrat crÃ©Ã© avec succÃ¨s.');
    }

    public function showContract(CateringContract $contract)
    {
        $this->perm('catering.contracts.view');
        $contract->load([
            'client',
            'prices',
            'weeklyMenus.days.meals.items.meal',
            'weeklyMenus.days.meals.codes',
        ]);
        $allMeals = Meal::orderBy('name')->get();
        return view('catering.show', compact('contract', 'allMeals'));
    }

    public function updateContract(Request $request, CateringContract $contract)
    {
        $this->perm('catering.contracts.edit');
        $request->validate([
            'end_date'    => 'required|date|after_or_equal:start_date',
            'guest_count' => 'required|integer|min:1',
            'active_days' => 'required|array|min:1',
            'status'      => 'required|in:active,paused,ended',
        ]);

        DB::transaction(function () use ($request, $contract) {
            $contract->update([
                'start_date'    => $request->start_date,
                'end_date'      => $request->end_date,
                'guest_count'   => $request->guest_count,
                'active_days'   => $request->active_days,
                'has_breakfast' => $request->boolean('has_breakfast'),
                'has_lunch'     => $request->boolean('has_lunch'),
                'has_dinner'    => $request->boolean('has_dinner'),
                'status'        => $request->status,
            ]);

            $contract->prices()->delete();
            foreach (['breakfast', 'lunch', 'dinner'] as $type) {
                $price = $request->input("price_{$type}");
                if ($price !== null && $price !== '') {
                    $contract->prices()->create(['type' => $type, 'price' => (float)$price]);
                }
            }
        });

        return back()->with('success', 'Contrat mis Ã  jour.');
    }

    public function destroyContract(CateringContract $contract)
    {
        $this->perm('catering.contracts.delete');
        $contract->delete();
        return redirect()->route('catering.contracts')->with('success', 'Contrat supprimÃ©.');
    }

    // â”€â”€â”€ Weekly Menu â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function weeklyMenuCreate(CateringContract $contract)
    {
        $this->perm('catering.weekly-menu.create');
        $contract->load('client', 'prices');
        $allMeals = Meal::orderBy('name')->get();
        return view('catering.weekly-menu-create', compact('contract', 'allMeals'));
    }

    public function weeklyMenuStore(Request $request, CateringContract $contract)
    {
        $this->perm('catering.weekly-menu.create');
        $data = $request->validate([
            'week_start' => 'required|date',
            'days'       => 'nullable|array',
        ]);

        $weekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY)->startOfDay();

        if ($contract->weeklyMenus()->where('week_start_date', $weekStart)->exists()) {
            return response()->json(['error' => 'Un menu existe dÃ©jÃ  pour cette semaine.'], 422);
        }

        try {
            DB::transaction(function () use ($data, $contract, $weekStart) {
                $menu = CateringWeeklyMenu::create([
                    'catering_contract_id' => $contract->id,
                    'week_start_date'      => $weekStart,
                ]);

                foreach (($data['days'] ?? []) as $dateStr => $mealTypes) {
                    if (empty($mealTypes)) continue;

                    $day = CateringMenuDay::create([
                        'catering_weekly_menu_id' => $menu->id,
                        'date'                    => $dateStr,
                    ]);

                    foreach ($mealTypes as $type => $mealData) {
                        if (!in_array($type, ['breakfast', 'lunch', 'dinner'])) continue;
                        $mealIds = array_filter((array)($mealData['meal_ids'] ?? []));

                        $menuMeal = CateringMenuMeal::create([
                            'catering_menu_day_id' => $day->id,
                            'type'                 => $type,
                            'quantity'             => $contract->guest_count,
                        ]);

                        foreach ($mealIds as $mealId) {
                            CateringMenuMealItem::create([
                                'catering_menu_meal_id' => $menuMeal->id,
                                'meal_id'               => (int)$mealId,
                            ]);
                        }

                        // Generate unique codes
                        for ($i = 0; $i < $contract->guest_count; $i++) {
                            do {
                                $code = 'CAT-' . strtoupper(Str::random(3)) . '-' . strtoupper(Str::random(4));
                            } while (CateringMealCode::where('code', $code)->exists());

                            CateringMealCode::create([
                                'catering_menu_meal_id' => $menuMeal->id,
                                'code'                  => $code,
                            ]);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success'  => true,
            'redirect' => route('catering.contracts.show', $contract),
        ]);
    }

    public function weeklyMenuShow(CateringWeeklyMenu $menu)
    {
        $this->perm('catering.weekly-menu.view');
        $menu->load([
            'contract.client',
            'contract.prices',
            'days.meals.items.meal',
            'days.meals.codes',
        ]);
        return view('catering.weekly-menu-show', compact('menu'));
    }

    public function weeklyMenuDestroy(CateringWeeklyMenu $menu)
    {
        $this->perm('catering.weekly-menu.delete');
        $usedCount = CateringMealCode::whereHas('menuMeal.menuDay', fn($q) =>
            $q->where('catering_weekly_menu_id', $menu->id))
            ->where('is_used', true)->count();

        if ($usedCount > 0) {
            return back()->with('error', "{$usedCount} code(s) dÃ©jÃ  utilisÃ©(s) â€” suppression impossible.");
        }
        $contractId = $menu->catering_contract_id;
        $menu->delete();
        return redirect()->route('catering.contracts.show', $contractId)->with('success', 'Menu supprimÃ©.');
    }

    public function printCodes(CateringMenuMeal $meal)
    {
        $this->perm('catering.meals.print-codes');
        $meal->load(['menuDay.weeklyMenu.contract.client', 'codes', 'items.meal']);
        return view('catering.print-codes', compact('meal'));
    }

    // â”€â”€â”€ Code Validation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function validatePage()
    {
        $this->perm('catering.validate');
        $recentValidations = CateringConsumption::with([
            'mealCode.menuMeal.menuDay.weeklyMenu.contract.client',
            'mealCode.menuMeal',
            'user',
        ])->latest('consumed_at')->take(15)->get();

        return view('catering.validate', compact('recentValidations'));
    }

    public function checkCode(Request $request)
    {
        $this->perm('catering.validate');
        $request->validate(['code' => 'required|string']);
        $code = strtoupper(trim($request->code));

        $mealCode = CateringMealCode::with([
            'menuMeal.menuDay.weeklyMenu.contract.client',
            'menuMeal.menuDay.weeklyMenu.contract.prices',
            'menuMeal.items.meal',
        ])->where('code', $code)->first();

        if (!$mealCode) {
            return response()->json(['status' => 'not_found', 'message' => 'Code introuvable.']);
        }

        if ($mealCode->is_used) {
            return response()->json([
                'status'  => 'used',
                'message' => 'Code dÃ©jÃ  utilisÃ© le ' . $mealCode->used_at?->format('d/m/Y Ã  H:i') . '.',
                'client'  => $mealCode->menuMeal?->menuDay?->weeklyMenu?->contract?->client?->name ?? 'â€”',
            ]);
        }

        $meal     = $mealCode->menuMeal;
        $contract = $meal->menuDay->weeklyMenu->contract;
        $price    = $contract->getPriceFor($meal->type);

        return response()->json([
            'status'    => 'valid',
            'code_id'   => $mealCode->id,
            'code'      => $mealCode->code,
            'client'    => $contract->client->name ?? 'â€”',
            'company'   => $contract->client->company ?? '',
            'date'      => $meal->menuDay->date->format('d/m/Y'),
            'meal_type' => $meal->type_label,
            'type_icon' => $meal->type_icon,
            'dishes'    => $meal->items->map(fn($i) => $i->meal->name)->join(', ') ?: 'â€”',
            'price'     => number_format($price, 0, ',', ' ') . ' MRU',
            'price_raw' => $price,
        ]);
    }

    public function confirmCode(Request $request)
    {
        $this->perm('catering.validate');
        $request->validate(['code_id' => 'required|exists:catering_meal_codes,id']);

        $mealCode = CateringMealCode::with([
            'menuMeal.menuDay.weeklyMenu.contract.client',
            'menuMeal.menuDay.weeklyMenu.contract.prices',
            'menuMeal.items.meal.recipe.items',
        ])->findOrFail($request->code_id);

        if ($mealCode->is_used) {
            return response()->json(['error' => 'Code dÃ©jÃ  validÃ©.'], 422);
        }

        $contract = $mealCode->menuMeal->menuDay->weeklyMenu->contract;
        $price    = $contract->getPriceFor($mealCode->menuMeal->type);

        try {
            DB::transaction(function () use ($mealCode, $contract, $price) {
                $transaction = Transaction::create([
                    'type'      => 'sale',
                    'amount'    => $price,
                    'reference' => 'CAT-' . $mealCode->code,
                    'date'      => today(),
                    'module'    => 'catering',
                ]);

                CateringConsumption::create([
                    'catering_meal_code_id' => $mealCode->id,
                    'consumed_at'           => now(),
                    'user_id'               => auth()->id(),
                    'transaction_id'        => $transaction->id,
                ]);

                $mealCode->update([
                    'is_used'      => true,
                    'used_at'      => now(),
                    'validated_by' => auth()->id(),
                ]);

                // Décrémenter le stock lié au catering (1 portion par repas)
                $cateringStock = Stock::forModule('catering');
                if ($cateringStock) {
                    foreach ($mealCode->menuMeal->items as $menuItem) {
                        $recipe = $menuItem->meal?->recipe;
                        if (!$recipe) continue;
                        foreach ($recipe->items as $ri) {
                            $stockItem = StockItem::where('stock_id', $cateringStock->id)
                                ->where('product_id', $ri->product_id)
                                ->first();
                            if (!$stockItem) continue;
                            $stockItem->decrement('quantity', $ri->quantity);
                            StockMovement::create([
                                'product_id'    => $ri->product_id,
                                'stock_id'      => $cateringStock->id,
                                'type'          => 'out',
                                'quantity'      => $ri->quantity,
                                'origin_module' => 'catering',
                                'origin_type'   => 'catering_consumption',
                                'origin_id'     => $mealCode->id,
                                'user_id'       => auth()->id(),
                                'notes'         => 'Consommation catering — Code ' . $mealCode->code,
                            ]);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Repas validÃ© pour ' . ($contract->client->name ?? 'â€”') . '.',
        ]);
    }

    public function recentValidationsJson()
    {
        $this->perm('catering.validate');
        $items = CateringConsumption::with([
            'mealCode.menuMeal.menuDay.weeklyMenu.contract.client',
            'mealCode.menuMeal',
        ])->latest('consumed_at')->take(20)->get()
            ->map(fn($c) => [
                'code'      => $c->mealCode->code ?? 'â€”',
                'client'    => $c->mealCode?->menuMeal?->menuDay?->weeklyMenu?->contract?->client?->name ?? 'â€”',
                'meal_type' => $c->mealCode?->menuMeal?->type_label ?? 'â€”',
                'time'      => $c->consumed_at?->format('H:i'),
            ]);

        return response()->json(['items' => $items]);
    }

    // â”€â”€â”€ Consumptions History â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function consumptions(Request $request)
    {
        $this->perm('catering.consumptions.view');
        $query = CateringConsumption::with([
            'mealCode.menuMeal.menuDay.weeklyMenu.contract.client',
            'mealCode.menuMeal',
            'transaction',
            'user',
        ]);

        if ($request->client_id) {
            $query->whereHas('mealCode.menuMeal.menuDay.weeklyMenu.contract',
                fn($q) => $q->where('client_id', $request->client_id));
        }
        if ($request->date) {
            $query->whereDate('consumed_at', $request->date);
        }

        $consumptions = $query->latest('consumed_at')->paginate(30)->withQueryString();
        $clients      = Client::orderBy('name')->get();

        return view('catering.consumptions', compact('consumptions', 'clients'));
    }
}
