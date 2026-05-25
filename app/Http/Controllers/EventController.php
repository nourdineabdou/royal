<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Event;
use App\Models\Service;
use App\Models\EventServiceItem;
use App\Models\EventMeal;
use App\Models\EventMealItem;
use App\Models\EventOption;
use App\Models\EventOptionItem;
use App\Models\EventStockUsage;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Meal;
use App\Models\Recipe;
use App\Models\Stock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class EventController extends Controller
{
    /**
     * Génère et télécharge le PDF de l'événement
     */
    public function generatePdf(Event $event)
    {
        $event->load(['client', 'serviceItems.service']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('event.validated_pdf', [
            'event' => $event,
            'services' => $event->serviceItems->map(function($item) { return $item->service; })
        ]);
        return $pdf->download('evenement_'.$event->id.'.pdf');
    }
    // ─── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $this->perm('events.dashboard');
        $totalEvents    = Event::count();
        $draftCount     = Event::where('status', 'draft')->count();
        $validatedCount = Event::where('status', 'validated')->count();
        $completedCount = Event::where('status', 'completed')->count();
        $cancelledCount = Event::where('status', 'cancelled')->count();

        $monthRevenue = Event::where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('total_amount');

        $upcomingEvents = Event::with('client')
            ->whereIn('status', ['draft', 'validated', 'in_progress'])
            ->where('event_date', '>=', today())
            ->orderBy('event_date')
            ->take(8)
            ->get();

        $recentCompleted = Event::with('client')
            ->where('status', 'completed')
            ->latest('completed_at')
            ->take(5)
            ->get();

        $chartLabels = [];
        $chartData   = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartLabels[] = now()->setMonth($m)->format('M');
            $chartData[]   = Event::whereYear('created_at', now()->year)
                ->whereMonth('created_at', $m)->count();
        }

        return view('event.dashboard', compact(
            'totalEvents', 'draftCount', 'validatedCount', 'completedCount', 'cancelledCount',
            'monthRevenue', 'upcomingEvents', 'recentCompleted', 'chartLabels', 'chartData'
        ));
    }

    // ─── Clients ───────────────────────────────────────────────────────────────

    public function clients()
    {
        $this->perm('events.clients.view');
        $clients = Client::withCount('events')->latest()->paginate(20);
        return view('event.clients', compact('clients'));
    }

    public function storeClient(Request $request)
    {
        $this->perm('events.clients.create');
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'notes'   => 'nullable|string',
        ]);
        Client::create($request->only('name', 'phone', 'email', 'company', 'notes'));
        return back()->with('success', 'Client ajouté avec succès.');
    }

    public function updateClient(Request $request, Client $client)
    {
        $this->perm('events.clients.edit');
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
        $this->perm('events.clients.delete');
        if ($client->events()->exists()) {
            return back()->with('error', 'Impossible de supprimer : ce client a des événements associés.');
        }
        $client->delete();
        return back()->with('success', 'Client supprimé.');
    }

    // ─── Service Catalog ───────────────────────────────────────────────────────

    public function services()
    {
        $this->perm('events.services.view');
        $services = Service::withCount('eventServiceItems')->latest()->paginate(20);
        return view('event.services', compact('services'));
    }

    public function storeService(Request $request)
    {
        $this->perm('events.services.create');
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'type'  => 'required|in:hall,equipment,logistic',
        ]);
        Service::create($request->only('name', 'price', 'type'));
        return back()->with('success', 'Service ajouté au catalogue.');
    }

    public function updateService(Request $request, Service $service)
    {
        $this->perm('events.services.edit');
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'type'  => 'required|in:hall,equipment,logistic',
        ]);
        $service->update($request->only('name', 'price', 'type'));
        return back()->with('success', 'Service mis à jour.');
    }

    public function destroyService(Service $service)
    {
        $this->perm('events.services.delete');
        $service->delete();
        return back()->with('success', 'Service supprimé du catalogue.');
    }

    // ─── Events CRUD ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->perm('events.view');
        $query = Event::with('client')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas('client', fn($q2) => $q2->where('name', 'LIKE', "%{$s}%")
                                                      ->orWhere('company', 'LIKE', "%{$s}%"))
                  ->orWhere('event_type', 'LIKE', "%{$s}%");
            });
        }

        $events = $query->paginate(20)->withQueryString();
        return view('event.index', compact('events'));
    }

    public function create()
    {
        $this->perm('events.create');
        $clients    = Client::orderBy('name')->get();
        $stocks     = Stock::orderBy('name')->get();
        $eventTypes = ['Mariage', 'Baptême', 'Anniversaire', 'Conférence', 'Séminaire', 'Gala', 'Autre'];
        return view('event.create', compact('clients', 'stocks', 'eventTypes'));
    }

    public function store(Request $request)
    {
        $this->perm('events.create');
        $request->validate([
        ]);

        $event = Event::create([
            'client_id'    => $request->client_id,
            'event_type'   => $request->event_type,
            'event_date'   => $request->event_date,
            'guest_count'  => $request->guest_count,
            'stock_id'     => $request->stock_id,
            'status'       => 'draft',
            'total_amount' => 0,
        ]);

        return redirect()->route('event.show', $event)
            ->with('success', 'Événement créé. Ajoutez maintenant les services, repas et options.');
    }

    public function show(Event $event)
    {
        $this->perm('events.view');
        $event->load([
            'client',
            'stock',
            'transaction',
            'serviceItems.service',
            'eventMeals.items.meal',
            'options.items.recipe.meal',
            'stockUsages.product.unit',
        ]);

        $clients           = Client::orderBy('name')->get();
        $availableServices = Service::orderBy('name')->get();
        $availableMeals    = Meal::orderBy('name')->get();
        $availableRecipes  = Recipe::with('meal')->get();
        $stocks            = Stock::orderBy('name')->get();

        // Stock requirements preview (computed server-side)
        $stockRequirements = [];
        $stockOk           = true;
        if ($event->stock_id) {
            $required = $this->computeStockRequirements($event);
            foreach ($required as $productId => $qty) {
                $product   = Product::with('unit')->find($productId);
                $stockItem = StockItem::where('stock_id', $event->stock_id)
                    ->where('product_id', $productId)->first();
                $available = (float)($stockItem?->quantity ?? 0);
                $ok        = $available >= $qty;
                if (!$ok) $stockOk = false;
                $stockRequirements[] = compact('product', 'qty', 'available', 'ok');
            }
            usort($stockRequirements, fn($a, $b) => $a['ok'] <=> $b['ok']);
        }

        // Computed totals
        $servicesTotal = $event->serviceItems->sum(fn($si) => (float)$si->price * (int)$si->quantity);
        $mealsTotal    = 0.0;
        foreach ($event->eventMeals as $em) {
            $mealsTotal += (int)$em->guest_count * (float)$em->items->sum(fn($mi) => (float)($mi->meal?->price ?? 0));
        }
        $optionsTotal  = $event->options->sum(fn($opt) => (float)$opt->price * (int)$opt->quantity);
        $computedTotal = $servicesTotal + $mealsTotal + $optionsTotal;

        return view('event.show', compact(
            'event', 'clients', 'availableServices', 'availableMeals', 'availableRecipes', 'stocks',
            'stockRequirements', 'stockOk', 'servicesTotal', 'mealsTotal', 'optionsTotal', 'computedTotal'
        ));
    }

    public function update(Request $request, Event $event)
    {
        $this->perm('events.edit');
        if ($event->status === 'completed') {
            return back()->with('error', 'Impossible de modifier un événement déjà clôturé.');
        }
        $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'event_type'  => 'required|string|max:100',
            'event_date'  => 'required|date',
            'guest_count' => 'required|integer|min:1',
            'stock_id'    => 'nullable|exists:stocks,id',
        ]);
        $event->update($request->only('client_id', 'event_type', 'event_date', 'guest_count', 'stock_id'));
        return back()->with('success', 'Événement mis à jour.');
    }

    public function destroy(Event $event)
    {
        $this->perm('events.delete');
        if ($event->status !== 'draft') {
            return back()->with('error', 'Seul un événement en brouillon peut être supprimé.');
        }
        foreach ($event->eventMeals as $meal) { $meal->items()->delete(); }
        foreach ($event->options as $opt)     { $opt->items()->delete(); }
        $event->serviceItems()->delete();
        $event->eventMeals()->delete();
        $event->options()->delete();
        $event->delete();
        return redirect()->route('event.index')->with('success', 'Événement supprimé.');
    }

    // ─── Event Items ──────────────────────────────────────────────────────────

    public function storeServiceItem(Request $request, Event $event)
    {
        $this->perm('events.service-items.manage');
        if ($event->status !== 'draft') {
            return back()->with('error', 'Modifications uniquement sur un événement brouillon.');
        }
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity'   => 'required|integer|min:1',
            'price'      => 'required|numeric|min:0',
        ]);
        EventServiceItem::create([
            'event_id'   => $event->id,
            'service_id' => $request->service_id,
            'quantity'   => $request->quantity,
            'price'      => $request->price,
        ]);
        return back()->with('success', 'Service ajouté à l\'événement.');
    }

    public function destroyServiceItem(EventServiceItem $item)
    {
        $this->perm('events.service-items.manage');
        $item->delete();
        return back()->with('success', 'Service retiré.');
    }

    public function storeMeal(Request $request, Event $event)
    {
        $this->perm('events.meals.manage');
        if ($event->status !== 'draft') {
            return back()->with('error', 'Modifications uniquement sur un événement brouillon.');
        }
        $request->validate([
            'type'        => 'required|in:breakfast,lunch,dinner',
            'guest_count' => 'required|integer|min:1',
            'meal_ids'    => 'nullable|array',
            'meal_ids.*'  => 'exists:meals,id',
        ]);
        $eventMeal = EventMeal::create([
            'event_id'    => $event->id,
            'type'        => $request->type,
            'guest_count' => $request->guest_count,
        ]);
        foreach ((array)$request->meal_ids as $mealId) {
            EventMealItem::create(['event_meal_id' => $eventMeal->id, 'meal_id' => $mealId]);
        }
        $label = match($request->type) { 'breakfast' => 'Petit-déjeuner', 'lunch' => 'Déjeuner', 'dinner' => 'Dîner' };
        return back()->with('success', "{$label} ajouté ({$request->guest_count} convives).");
    }

    public function destroyMeal(EventMeal $meal)
    {
        $this->perm('events.meals.manage');
        $meal->items()->delete();
        $meal->delete();
        return back()->with('success', 'Service repas retiré.');
    }

    public function storeOption(Request $request, Event $event)
    {
        $this->perm('events.options.manage');
        if ($event->status !== 'draft') {
            return back()->with('error', 'Modifications uniquement sur un événement brouillon.');
        }
        $request->validate([
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'quantity'     => 'required|integer|min:1',
            'recipe_ids'   => 'nullable|array',
            'recipe_ids.*' => 'exists:recipes,id',
        ]);
        $option = EventOption::create([
            'event_id' => $event->id,
            'name'     => $request->name,
            'price'    => $request->price,
            'quantity' => $request->quantity,
        ]);
        foreach ((array)$request->recipe_ids as $recipeId) {
            EventOptionItem::create(['event_option_id' => $option->id, 'recipe_id' => $recipeId]);
        }
        return back()->with('success', "Option \"{$request->name}\" ajoutée.");
    }

    public function destroyOption(EventOption $option)
    {
        $this->perm('events.options.manage');
        $option->items()->delete();
        $option->delete();
        return back()->with('success', 'Option retirée.');
    }

    // ─── Status Transitions ───────────────────────────────────────────────────

    public function validateEvent(Event $event)
    {
        $this->perm('events.validate');
        if ($event->validated_at) {
            return back()->with('error', 'Cet événement a déjà été validé.');
        }
        if ($event->status === 'cancelled') {
            return back()->with('error', 'Impossible de valider un événement annulé.');
        }

        try {
            DB::transaction(function () use ($event) {
                // Stock sufficiency check (if stock assigned)
                if ($event->stock_id) {
                    $required    = $this->computeStockRequirements($event);
                    $insuffisant = [];
                    foreach ($required as $productId => $qty) {
                        $stockItem = StockItem::where('stock_id', $event->stock_id)
                            ->where('product_id', $productId)->first();
                        $available = (float)($stockItem?->quantity ?? 0);
                        if ($available < $qty) {
                            $product       = Product::find($productId);
                            $insuffisant[] = ($product?->name ?? '#' . $productId)
                                . " (besoin: {$qty}, dispo: {$available})";
                        }
                    }
                    if (!empty($insuffisant)) {
                        throw new \Exception('Stock insuffisant pour : ' . implode(' | ', $insuffisant));
                    }
                }

                $total = $this->computeTotal($event);
                $event->update([
                    'status'       => 'validated',
                    'validated_at' => now(),
                    'total_amount' => $total,
                ]);
            });

            // Génération du PDF après validation
            $event->load(['client', 'serviceItems.service']);
            $pdf = Pdf::loadView('event.validated_pdf', [
                'event' => $event,
                'services' => $event->serviceItems->map(function($item) { return $item->service; })
            ]);
            $pdfContent = $pdf->output();

            // Envoi du PDF par mail au client si email présent
            if ($event->client && $event->client->email) {
                Mail::raw('Votre événement a été validé. Veuillez trouver le PDF en pièce jointe.', function ($message) use ($event, $pdfContent) {
                    $message->to($event->client->email)
                        ->subject('Validation de votre événement')
                        ->attachData($pdfContent, 'evenement_valide.pdf', [
                            'mime' => 'application/pdf',
                        ]);
                });
            }

            $amount = number_format((float)$event->fresh()->total_amount, 0, ',', ' ');
            return back()->with('success', "Événement validé avec succès. Montant total : {$amount} MRU. Un PDF a été envoyé au client si son email est renseigné.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function completeEvent(Event $event)
    {
        $this->perm('events.complete');
        // Guard: prevent re-completion
        if ($event->completed_at) {
            return back()->with('error', 'Cet événement est déjà clôturé. Opération impossible.');
        }
        if (!in_array($event->status, ['validated', 'in_progress'])) {
            return back()->with('error', 'Seul un événement validé ou en cours peut être clôturé.');
        }
        if (!$event->stock_id) {
            return back()->with('error', 'Aucun stock assigné. Assignez un stock avant de clôturer.');
        }

        try {
            DB::transaction(function () use ($event) {
                $required = $this->computeStockRequirements($event);

                // Lock rows and verify stock sufficiency
                $insuffisant = [];
                foreach ($required as $productId => $qty) {
                    $stockItem = StockItem::where('stock_id', $event->stock_id)
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();
                    $available = (float)($stockItem?->quantity ?? 0);
                    if ($available < $qty) {
                        $product       = Product::with('unit')->find($productId);
                        $unit          = $product?->unit?->name ?? '';
                        $insuffisant[] = ($product?->name ?? '#' . $productId)
                            . " (besoin: {$qty} {$unit}, dispo: {$available} {$unit})";
                    }
                }
                if (!empty($insuffisant)) {
                    throw new \Exception('Stock insuffisant : ' . implode(' | ', $insuffisant));
                }

                // Decrement stock + record movements + save usages
                foreach ($required as $productId => $qty) {
                    StockItem::where('stock_id', $event->stock_id)
                        ->where('product_id', $productId)
                        ->decrement('quantity', $qty);

                    StockMovement::create([
                        'product_id'      => $productId,
                        'stock_id'        => $event->stock_id,
                        'type'            => 'out',
                        'quantity'        => $qty,
                        'source_stock_id' => $event->stock_id,
                        'origin_module'   => 'events',
                        'origin_type'     => 'event',
                        'origin_id'       => $event->id,
                        'user_id'         => auth()->id(),
                        'notes'           => 'Clôture événement — ' . $event->event_type . ' #' . $event->id,
                    ]);

                    EventStockUsage::create([
                        'event_id'   => $event->id,
                        'product_id' => $productId,
                        'quantity'   => $qty,
                    ]);
                }

                // Accounting transaction
                $transaction = Transaction::create([
                    'type'      => 'sale',
                    'amount'    => $event->total_amount,
                    'reference' => 'EVNT-' . str_pad($event->id, 5, '0', STR_PAD_LEFT),
                    'date'      => today(),
                    'module'    => 'event',
                ]);

                $event->update([
                    'status'         => 'completed',
                    'completed_at'   => now(),
                    'transaction_id' => $transaction->id,
                ]);
            });

            return back()->with('success', 'Événement clôturé avec succès. Stock décrémenté et comptabilité enregistrée.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancelEvent(Event $event)
    {
        $this->perm('events.cancel');
        if ($event->status === 'completed') {
        }
        $event->update(['status' => 'cancelled']);
        return back()->with('success', 'Événement annulé.');
    }

    // ─── Stock Preview JSON ────────────────────────────────────────────────────

    public function stockPreview(Event $event)
    {
        $this->perm('events.stock-preview');
        if (!$event->stock_id) {
            return response()->json(['error' => 'Aucun stock assigné.', 'items' => [], 'all_ok' => null]);
        }

        $required = $this->computeStockRequirements($event);
        $items    = [];
        $allOk    = true;

        foreach ($required as $productId => $qty) {
            $product   = Product::with('unit')->find($productId);
            $stockItem = StockItem::where('stock_id', $event->stock_id)
                ->where('product_id', $productId)->first();
            $available = (float)($stockItem?->quantity ?? 0);
            $ok        = $available >= $qty;
            if (!$ok) $allOk = false;
            $items[] = [
                'product'   => $product?->name ?? 'Produit#' . $productId,
                'unit'      => $product?->unit?->name ?? '',
                'required'  => round($qty, 2),
                'available' => round($available, 2),
                'ok'        => $ok,
            ];
        }
        usort($items, fn($a, $b) => $a['ok'] <=> $b['ok']);

        return response()->json(compact('items', 'allOk'));
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Aggregate all product quantities needed for this event.
     * Sources: meal services (recipe × guest_count) + options (recipe × option.quantity)
     */
    private function computeStockRequirements(Event $event): array
    {
        $required = [];

        $event->loadMissing([
            'eventMeals.items.meal.recipe.items',
            'options.items.recipe.items',
        ]);

        // From meal services: each dish recipe × guest_count
        foreach ($event->eventMeals as $eventMeal) {
            foreach ($eventMeal->items as $mealItem) {
                $recipe = $mealItem->meal?->recipe;
                if (!$recipe) continue;
                foreach ($recipe->items as $ri) {
                    $pid              = $ri->product_id;
                    $qty              = (float)$ri->quantity * (int)$eventMeal->guest_count;
                    $required[$pid]   = ($required[$pid] ?? 0.0) + $qty;
                }
            }
        }

        // From options: each recipe × option.quantity
        foreach ($event->options as $option) {
            foreach ($option->items as $optItem) {
                $recipe = $optItem->recipe;
                if (!$recipe) continue;
                foreach ($recipe->items as $ri) {
                    $pid            = $ri->product_id;
                    $qty            = (float)$ri->quantity * (int)$option->quantity;
                    $required[$pid] = ($required[$pid] ?? 0.0) + $qty;
                }
            }
        }

        return $required;
    }

    /**
     * Compute total amount: services + meals + options.
     */
    private function computeTotal(Event $event): float
    {
        $event->loadMissing(['serviceItems', 'eventMeals.items.meal', 'options']);

        $servicesTotal = $event->serviceItems->sum(fn($si) => (float)$si->price * (int)$si->quantity);

        $mealsTotal = 0.0;
        foreach ($event->eventMeals as $em) {
            $mealsTotal += (int)$em->guest_count
                * (float)$em->items->sum(fn($mi) => (float)($mi->meal?->price ?? 0));
        }

        $optionsTotal = $event->options->sum(fn($opt) => (float)$opt->price * (int)$opt->quantity);

        return $servicesTotal + $mealsTotal + $optionsTotal;
    }
}
