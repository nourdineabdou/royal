<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardProductionController extends Controller
{
    public function index()
    {
        $this->perm('production.dashboard');
        // Statistiques principales
        $stats = [
            'total_meals' => Meal::count(),
            'total_categories' => Category::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Payment::sum('amount') ?? 0,
        ];

        // Top 5 repas les plus vendus
        $topMeals = DB::table('order_items')
            ->select('meal_id', DB::raw('count(*) as total'), 'meals.name', 'meals.price')
            ->join('meals', 'order_items.meal_id', '=', 'meals.id')
            ->groupBy('meal_id', 'meals.name', 'meals.price')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Ventes par jour (derniers 7 jours)
        $salesByDay = DB::table('payments')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Dernières commandes
        $recentOrders = Order::with('items.meal', 'payment.paymentType')
            ->latest()
            ->limit(10)
            ->get();

        // Préparation des données pour les graphiques
        $salesData = [
            'labels' => $salesByDay->map(fn($s) => $s->date)->toArray(),
            'sales' => $salesByDay->map(fn($s) => (float)$s->total)->toArray(),
            'counts' => $salesByDay->map(fn($s) => (int)$s->count)->toArray(),
        ];

        $topMealsData = [
            'labels' => $topMeals->map(fn($m) => $m->name)->toArray(),
            'data' => $topMeals->map(fn($m) => (int)$m->total)->toArray(),
        ];

        return view('dashboard-production', compact('stats', 'recentOrders', 'salesData', 'topMealsData', 'topMeals'));
    }
}
