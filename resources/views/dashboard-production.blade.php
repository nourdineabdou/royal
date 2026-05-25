@extends('layouts.production')

@section('title', 'Dashboard - Production')
@section('page_title', 'Production Dashboard')
@section('page_subtitle', 'Gestion complète de la production culinaire')

@section('content')
{{-- Module Hero --}}
<div class="relative rounded-2xl overflow-hidden mb-8 shadow-xl" style="height:200px;">
    <img src="{{ asset('royal grill.jpeg') }}" alt="Royal Grill" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-900/80 to-transparent flex items-center px-8">
        <div>
            <h2 class="text-4xl font-bold text-white">Royal Grill</h2>
            <p class="text-indigo-200 mt-1 text-lg">Module Production — Gestion de la cuisine</p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Meals Card -->
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Repas</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['total_meals'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-utensils text-blue-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-blue-600 text-xs font-semibold mt-4">
                    <a href="{{ route('meals.index') }}" class="hover:underline">Voir tous les repas →</a>
                </p>
            </div>

            <!-- Total Categories Card -->
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Catégories</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['total_categories'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-layer-group text-green-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-green-600 text-xs font-semibold mt-4">
                    <a href="{{ route('categories.index') }}" class="hover:underline">Gérer catégories →</a>
                </p>
            </div>

            <!-- Total Orders Card -->
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Commandes</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['total_orders'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-purple-600 text-xs font-semibold mt-4">
                    <span>Commandes actives</span>
                </p>
            </div>

            <!-- Total Revenue Card -->
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Revenus Totaux</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($stats['total_revenue'], 2) }} MRU</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-euro-sign text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-yellow-600 text-xs font-semibold mt-4">
                    <span>Tous les paiements</span>
                </p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Sales by Day Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                    Ventes par Jour (7 derniers jours)
                </h3>
                <div class="relative" style="height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Top Meals Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-green-500 mr-2"></i>
                    Top 5 Repas Vendus
                </h3>
                <div class="relative" style="height: 300px;">
                    <canvas id="topMealsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-list text-indigo-500 mr-2"></i>
                Commandes Récentes
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">ID Commande</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Nombre Repas</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Montant Total</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Statut</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-mono text-sm text-gray-800">#{{ $order->id }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $order->order_items_count ?? 0 }} repas</td>
                                <td class="py-3 px-4 font-semibold text-gray-800">
                                    {{ $order->total_amount ? number_format($order->total_amount, 2) . ' MRU' : '0.00 MRU' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-600 text-sm">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 px-4 text-center text-gray-500">
                                    <i class="fas fa-info-circle mr-2"></i>Aucune commande enregistrée
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <a href="{{ route('meals.create') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition duration-300 text-center">
                <i class="fas fa-plus text-2xl mb-3 block"></i>
                <h4 class="font-bold mb-1">Ajouter Repas</h4>
                <p class="text-blue-100 text-sm">Créer un nouveau repas</p>
            </a>
            <a href="{{ route('categories.index') }}" class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition duration-300 text-center">
                <i class="fas fa-layer-group text-2xl mb-3 block"></i>
                <h4 class="font-bold mb-1">Catégories</h4>
                <p class="text-green-100 text-sm">Gérer les catégories</p>
            </a>
            <a href="{{ route('accompaniments.index') }}" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transition duration-300 text-center">
                <i class="fas fa-star text-2xl mb-3 block"></i>
                <h4 class="font-bold mb-1">Accompagnements</h4>
                <p class="text-purple-100 text-sm">Gérer les accompagnements</p>
            </a>
        </div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales by Day Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesData['labels']) !!},
            datasets: [{
                label: 'Ventes (MRU)',
                data: {!! json_encode($salesData['sales']) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 12 }, usePointStyle: true }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(value) { return value.toFixed(0) + ' MRU'; } }
                }
            }
        }
    });

    // Top Meals Chart
    const mealsCtx = document.getElementById('topMealsChart').getContext('2d');
    new Chart(mealsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($topMealsData['labels']) !!},
            datasets: [{
                label: 'Nombre de Ventes',
                data: {!! json_encode($topMealsData['data']) !!},
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b',
                    '#ef4444', '#8b5cf6'
                ],
                borderColor: [
                    '#1e40af', '#047857', '#d97706',
                    '#b91c1c', '#6d28d9'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>
@endsection
