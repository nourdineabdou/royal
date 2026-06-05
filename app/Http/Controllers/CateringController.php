<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\CateringContract;
use App\Models\CateringContractPrice;
use App\Models\CateringInvoice;
use App\Models\CateringInvoicePayment;
use App\Models\CateringWeeklyMenu;
use App\Models\CateringMenuDay;
use App\Models\CateringMenuMeal;
use App\Models\CateringMenuMealItem;
use App\Models\PaymentType;
use App\Models\PosTerminalTicketLog;
use App\Models\Transaction;
use App\Models\Meal;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\StockMovement;
use Carbon\Carbon;

class CateringController extends Controller
{
    // أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬ Dashboard أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬

    public function dashboard()
    {
        $this->perm('catering.dashboard');
        $totalContracts  = CateringContract::count();
        $activeContracts = CateringContract::where('status', 'active')->count();

        $monthRevenue = Transaction::where('type', 'sale')
            ->where('module', 'catering')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $activeContractsList = CateringContract::with('client')
            ->where('status', 'active')
            ->withCount('weeklyMenus')
            ->latest()->take(6)->get();

        // Transferts POS rأ©cents
        $recentTransfers = \App\Models\PosTransfer::with(['client', 'cashRegister.user'])
            ->latest()->take(10)->get();

        return view('catering.dashboard', compact(
            'totalContracts', 'activeContracts',
            'monthRevenue', 'activeContractsList', 'recentTransfers'
        ));
    }

    // أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬ Clients أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬

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
        return back()->with('success', 'Client crأ©أ©.');
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
        return back()->with('success', 'Client mis أ  jour.');
    }

    public function destroyClient(Client $client)
    {
        $this->perm('catering.clients.delete');
        if ($client->cateringContracts()->exists()) {
            return back()->with('error', 'Impossible : des contrats exist pour ce client.');
        }
        $client->delete();
        return back()->with('success', 'Client supprimأƒآ©.');
    }

    // أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬ Contracts أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬

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
            return back()->withErrors(['meal' => 'Sأƒآ©lectionnez au moins un type de repas.'])->withInput();
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

        return redirect()->route('catering.contracts')->with('success', 'Contrat crأƒآ©أƒآ© avec succأƒآ¨s.');
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

        return back()->with('success', 'Contrat mis أƒآ  jour.');
    }

    public function destroyContract(CateringContract $contract)
    {
        $this->perm('catering.contracts.delete');
        $contract->delete();
        return redirect()->route('catering.contracts')->with('success', 'Contrat supprimأƒآ©.');
    }

    // أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬ Weekly Menu أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬أ¢â€‌â‚¬

    public function weeklyMenuCreate(CateringContract $contract)
    {
        $this->perm('catering.weekly-menu.create');
        $contract->load('client', 'prices');
        $allMeals = Meal::orderBy('name')->get();
        $weekStartInput = request('week_start');
        $prefillWeekStart = $weekStartInput
            ? Carbon::parse($weekStartInput)->startOfWeek(Carbon::MONDAY)->toDateString()
            : now()->startOfWeek(Carbon::MONDAY)->toDateString();

        $existingMenu = CateringWeeklyMenu::with(['days.meals.items'])
            ->where('catering_contract_id', $contract->id)
            ->whereDate('week_start_date', $prefillWeekStart)
            ->first();

        $prefillDays = [];
        if ($existingMenu) {
            foreach ($existingMenu->days as $day) {
                $date = $day->date->toDateString();
                if (!isset($prefillDays[$date])) {
                    $prefillDays[$date] = [];
                }

                foreach ($day->meals as $meal) {
                    $prefillDays[$date][$meal->type] = [
                        'meal_ids' => $meal->items->pluck('meal_id')->map(fn($id) => (int) $id)->values()->all(),
                    ];
                }
            }
        }

        $weekStart = Carbon::parse($prefillWeekStart)->startOfWeek(Carbon::MONDAY);
        $dayNames = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];

        $rawActiveDays = array_values(array_filter(array_map('intval', (array) $contract->active_days), fn ($d) => $d >= 1 && $d <= 7));
        $activeDays = !empty($rawActiveDays) ? array_values(array_unique($rawActiveDays)) : [1, 2, 3, 4, 5];

        $weekDays = collect(range(0, 6))
            ->map(function ($offset) use ($weekStart, $contract, $dayNames, $activeDays) {
                $date = $weekStart->copy()->addDays($offset);
                $isoDay = $date->dayOfWeekIso;

                $inContractPeriod = $date->toDateString() >= $contract->start_date->toDateString()
                    && $date->toDateString() <= $contract->end_date->toDateString();
                $isActiveDay = in_array($isoDay, $activeDays, true);

                if (!$inContractPeriod || !$isActiveDay) {
                    return null;
                }

                return [
                    'date' => $date->toDateString(),
                    'iso_day' => $isoDay,
                    'label' => $dayNames[$isoDay] ?? $date->translatedFormat('l'),
                    'human_label' => $date->translatedFormat('d/m/Y'),
                ];
            })
            ->filter()
            ->values();

        $labels = ['breakfast' => 'Petit-déjeuner', 'lunch' => 'Déjeuner', 'dinner' => 'Dîner'];
        $mealTypeDefs = collect($contract->getActiveMealTypes())
            ->filter(fn ($t) => isset($labels[$t]))
            ->values()
            ->map(fn ($t) => ['key' => $t, 'label' => $labels[$t]])
            ->all();

        return view('catering.weekly-menu-create', compact(
            'contract',
            'allMeals',
            'prefillWeekStart',
            'prefillDays',
            'weekDays',
            'mealTypeDefs'
        ));
    }

    public function weeklyPlanningIndex(Request $request)
    {
        $this->perm('catering.weekly-menu.view');

        $today = now();
        $defaultWeekStart = $today->isWeekend()
            ? $today->copy()->next(Carbon::MONDAY)
            : $today->copy()->startOfWeek(Carbon::MONDAY);

        $weekStart = Carbon::parse($request->input('week_start', $defaultWeekStart->toDateString()))
            ->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $days = collect(range(0, 6))->map(function ($i) use ($weekStart) {
            $date = $weekStart->copy()->addDays($i);
            return [
                'date' => $date->toDateString(),
                'iso_day' => $date->dayOfWeekIso,
                'label' => $date->translatedFormat('D d/m'),
            ];
        });

        $contracts = CateringContract::with(['client', 'prices'])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $weekEnd->toDateString())
            ->whereDate('end_date', '>=', $weekStart->toDateString())
            ->whereHas('client', function ($q) {
                $q->whereNotNull('company')->where('company', '!=', '');
            })
            ->orderByDesc('id')
            ->get();

        $weeklyMenus = CateringWeeklyMenu::with(['days.meals.items.meal'])
            ->whereIn('catering_contract_id', $contracts->pluck('id'))
            ->whereDate('week_start_date', $weekStart->toDateString())
            ->get()
            ->keyBy('catering_contract_id');

        $contractsOverview = $contracts->map(function ($contract) use ($days, $weeklyMenus) {
            $activeMealTypes = $contract->getActiveMealTypes();
            $expectedPerDay = count($activeMealTypes);
            $menu = $weeklyMenus->get($contract->id);
            $expectedTotal = 0;
            $programmedTotal = 0;
            $activeDays = array_map('intval', (array) $contract->active_days);

            $byDate = [];
            foreach ($days as $d) {
                $date = $d['date'];
                $inContractPeriod = $date >= $contract->start_date->toDateString()
                    && $date <= $contract->end_date->toDateString();
                $activeDay = in_array($d['iso_day'], $activeDays, true);
                $expected = ($inContractPeriod && $activeDay) ? $expectedPerDay : 0;

                $programmed = [];
                if ($menu) {
                    $menuDay = $menu->days->first(function ($day) use ($date) {
                        return $day->date->toDateString() === $date;
                    });

                    if ($menuDay) {
                        foreach ($menuDay->meals as $meal) {
                            if (!in_array($meal->type, $activeMealTypes, true)) {
                                continue;
                            }

                            $programmed[] = [
                                'type' => $meal->type,
                                'label' => $meal->type_label,
                                'items_count' => $meal->items->count(),
                            ];
                        }
                    }
                }

                $byDate[$date] = [
                    'expected_count' => $expected,
                    'programmed' => $programmed,
                ];

                $expectedTotal += $expected;
                $programmedTotal += min($expected, count($programmed));
            }

            $completionPct = $expectedTotal > 0
                ? (int) round(($programmedTotal / $expectedTotal) * 100)
                : 100;

            return [
                'contract' => $contract,
                'expected_per_day' => $expectedPerDay,
                'by_date' => $byDate,
                'has_week_menu' => (bool) $menu,
                'expected_total' => $expectedTotal,
                'programmed_total' => $programmedTotal,
                'completion_pct' => $completionPct,
            ];
        });

        return view('catering.planning.index', compact(
            'weekStart',
            'weekEnd',
            'days',
            'contractsOverview'
        ));
    }

    public function weeklyMenuStore(Request $request, CateringContract $contract)
    {
        $this->perm('catering.weekly-menu.create');
        $data = $request->validate([
            'week_start' => 'required|date',
            'days'       => 'nullable|array',
        ]);

        $weekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY)->startOfDay();

        try {
            DB::transaction(function () use ($data, $contract, $weekStart) {
                $menu = CateringWeeklyMenu::firstOrCreate([
                    'catering_contract_id' => $contract->id,
                    'week_start_date'      => $weekStart,
                ]);

                // Remplace toute la programmation de la semaine pour permettre la réédition.
                foreach ($menu->days as $existingDay) {
                    foreach ($existingDay->meals as $existingMeal) {
                        $existingMeal->items()->delete();
                        $existingMeal->delete();
                    }
                    $existingDay->delete();
                }

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

                        // (codes repas supprimأ©s â€” utiliser les transferts POS)
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

    public function weeklyMenuTemplate(Request $request, CateringContract $contract)
    {
        $this->perm('catering.weekly-menu.view');

        $data = $request->validate([
            'week_start' => 'required|date',
        ]);

        $currentWeekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY)->startOfDay();
        $previousWeekStart = $currentWeekStart->copy()->subWeek();

        $previousMenu = CateringWeeklyMenu::with(['days.meals.items'])
            ->where('catering_contract_id', $contract->id)
            ->whereDate('week_start_date', $previousWeekStart->toDateString())
            ->first();

        if (!$previousMenu) {
            return response()->json([
                'exists' => false,
                'message' => 'Aucune programmation sur la semaine precedente.',
            ]);
        }

        $days = [];

        foreach ($previousMenu->days as $day) {
            $targetDate = $currentWeekStart->copy()->addDays($day->date->dayOfWeekIso - 1)->toDateString();

            if (!isset($days[$targetDate])) {
                $days[$targetDate] = [];
            }

            foreach ($day->meals as $meal) {
                $days[$targetDate][$meal->type] = [
                    'meal_ids' => $meal->items->pluck('meal_id')->map(fn($id) => (int) $id)->values()->all(),
                ];
            }
        }

        return response()->json([
            'exists' => true,
            'from_week_start' => $previousWeekStart->toDateString(),
            'days' => $days,
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
        $contractId = $menu->catering_contract_id;
        $menu->delete();
        return redirect()->route('catering.contracts.show', $contractId)->with('success', 'Menu supprimأ©.');
    }

    // â”€â”€â”€ Code Validation

    public function validatePage()
    {
        $this->perm('catering.validate');

        // Liste des transferts POS validأ©s rأ©cents
        $recentTransfers = \App\Models\PosTransfer::with(['client', 'cashRegister.user', 'items'])
            ->where('status', 'validated')
            ->latest()->take(15)->get();

        $openRegister = \App\Models\CashRegister::where('module', 'catering')
            ->where('status', 'open')
            ->first();

        return view('catering.validate', compact('recentTransfers', 'openRegister'));
    }

    public function billingIndex(Request $request)
    {
        $this->perm('catering.contracts.view');

        $year = (int) ($request->input('year') ?: now()->year);
        $month = (int) ($request->input('month') ?: now()->month);

        $invoices = CateringInvoice::with(['client', 'contract'])
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->string('status')->toString());
            })
            ->latest('period_year')
            ->latest('period_month')
            ->paginate(20)
            ->withQueryString();

        $contracts = CateringContract::with('client')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();

        $monthlyTotals = [
            'billed' => CateringInvoice::where('period_year', $year)->where('period_month', $month)->sum('total_amount'),
            'paid' => CateringInvoice::where('period_year', $year)->where('period_month', $month)->sum('paid_amount'),
        ];

        return view('catering.billing.index', compact('invoices', 'contracts', 'year', 'month', 'monthlyTotals'));
    }

    public function generateMonthlyInvoice(Request $request)
    {
        $this->perm('catering.contracts.edit');

        $data = $request->validate([
            'contract_id' => 'required|exists:catering_contracts,id',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $contract = CateringContract::with(['client', 'prices'])->findOrFail($data['contract_id']);

        $invoiceExists = CateringInvoice::where('catering_contract_id', $contract->id)
            ->where('period_year', $data['year'])
            ->where('period_month', $data['month'])
            ->exists();

        if ($invoiceExists) {
            return back()->with('error', 'Une facture existe deja pour ce contrat et ce mois.');
        }

        $periodStart = Carbon::create($data['year'], $data['month'], 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $effectiveStart = $periodStart->copy()->max($contract->start_date->copy()->startOfDay());
        $effectiveEnd = $periodEnd->copy()->min($contract->end_date->copy()->endOfDay());

        if ($effectiveStart->gt($effectiveEnd)) {
            return back()->with('error', 'Ce contrat est hors periode pour le mois selectionne.');
        }

        $baseUnitPrice = (float) ($contract->prices()->avg('price') ?? 0);

        $logs = PosTerminalTicketLog::query()
            ->with('stockItem')
            ->whereBetween('served_at', [$effectiveStart, $effectiveEnd])
            ->whereHas('stockItem', function ($q) {
                $q->where('item_type', 'contract');
            })
            ->whereHas('stockItem.terminal', function ($q) use ($contract) {
                $q->where('client_id', $contract->client_id);
            })
            ->get();

        if ($logs->isEmpty()) {
            return back()->with('error', 'Aucune consommation trouvee pour generer la facture de ce mois.');
        }

        $grouped = $logs->groupBy('pos_terminal_stock_item_id');
        $invoiceNumber = sprintf('CAT-INV-%04d%02d-%05d', $data['year'], $data['month'], $contract->id);

        DB::transaction(function () use ($contract, $data, $effectiveStart, $effectiveEnd, $grouped, $baseUnitPrice, $invoiceNumber) {
            $invoice = CateringInvoice::create([
                'catering_contract_id' => $contract->id,
                'client_id' => $contract->client_id,
                'invoice_number' => $invoiceNumber,
                'period_year' => $data['year'],
                'period_month' => $data['month'],
                'period_start' => $effectiveStart->toDateString(),
                'period_end' => $effectiveEnd->toDateString(),
                'status' => 'issued',
                'total_amount' => 0,
                'paid_amount' => 0,
            ]);

            $total = 0;

            foreach ($grouped as $stockItemId => $rows) {
                $qty = (float) $rows->sum('qty');
                if ($qty <= 0) {
                    continue;
                }

                $stockItem = $rows->first()->stockItem;
                $unitPrice = $baseUnitPrice;
                $lineTotal = round($qty * $unitPrice, 2);
                $total += $lineTotal;

                $invoice->items()->create([
                    'pos_terminal_stock_item_id' => $stockItemId,
                    'label' => $stockItem?->item_label ?: 'Repas catering',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $invoice->update(['total_amount' => round($total, 2)]);
        });

        return back()->with('success', 'Facture mensuelle generee avec succes.');
    }

    public function showInvoice(CateringInvoice $invoice)
    {
        $this->perm('catering.contracts.view');

        $invoice->load([
            'client',
            'contract',
            'items',
            'payments.paymentType',
            'payments.creator',
            'payments.validator',
        ]);

        $paymentTypes = PaymentType::query()
            ->orderBy('name')
            ->get();

        $canValidatePayment = auth()->user()?->hasRole(['accountant', 'admin', 'super-admin']);

        return view('catering.billing.show', compact('invoice', 'paymentTypes', 'canValidatePayment'));
    }

    public function addInvoicePayment(Request $request, CateringInvoice $invoice)
    {
        $this->perm('catering.contracts.edit');

        $data = $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $remaining = max(0, (float) $invoice->total_amount - (float) $invoice->paid_amount);
        if ((float) $data['amount'] > $remaining) {
            return back()->with('error', 'Le montant depasse le reste a payer.');
        }

        $invoice->payments()->create([
            'payment_type_id' => $data['payment_type_id'],
            'amount' => (float) $data['amount'],
            'payment_date' => $data['payment_date'],
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Paiement enregistre en attente de validation comptable.');
    }

    public function validateInvoicePayment(CateringInvoice $invoice, CateringInvoicePayment $payment)
    {
        $this->perm('catering.contracts.view');

        if (!auth()->user()?->hasRole(['accountant', 'admin', 'super-admin'])) {
            abort(403, 'Validation reservee a la comptabilite.');
        }

        if ((int) $payment->catering_invoice_id !== (int) $invoice->id) {
            abort(404);
        }

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Ce paiement est deja traite.');
        }

        DB::transaction(function () use ($invoice, $payment) {
            $payment->update([
                'status' => 'validated',
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ]);

            $validatedTotal = (float) $invoice->payments()
                ->where('status', 'validated')
                ->sum('amount');

            $status = 'issued';
            if ($validatedTotal > 0 && $validatedTotal < (float) $invoice->total_amount) {
                $status = 'partial';
            }
            if ($validatedTotal >= (float) $invoice->total_amount) {
                $status = 'paid';
            }

            $invoice->update([
                'paid_amount' => round($validatedTotal, 2),
                'status' => $status,
            ]);

            $txRef = sprintf('CATINVPAY-%d-%d', $invoice->id, $payment->id);
            $exists = Transaction::where('reference', $txRef)->exists();
            if (!$exists) {
                Transaction::create([
                    'type' => 'sale',
                    'module' => 'catering',
                    'amount' => $payment->amount,
                    'reference' => $txRef,
                    'date' => $payment->payment_date,
                ]);
            }
        });

        return back()->with('success', 'Paiement valide par la comptabilite.');
    }
}
