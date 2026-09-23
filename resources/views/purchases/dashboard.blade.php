@extends('layouts.purchases')
@section('title', 'Tableau de bord')

@section('content')

{{-- ── KPI CARDS ─────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

    {{-- Fournisseurs --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-truck text-orange-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">Fournisseurs</p>
            <p class="text-2xl font-bold text-slate-800">{{ $totalSuppliers }}</p>
        </div>
    </div>

    {{-- Commandes en attente --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-clock text-yellow-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">En attente</p>
            <p class="text-2xl font-bold text-slate-800">{{ $pendingOrders }}</p>
        </div>
    </div>

    {{-- Commandes confirmées --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-paper-plane text-blue-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">Commandées</p>
            <p class="text-2xl font-bold text-slate-800">{{ $orderedOrders }}</p>
        </div>
    </div>

    {{-- Reçues ce mois --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-circle-check text-emerald-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">Reçues</p>
            <p class="text-2xl font-bold text-slate-800">{{ $receivedOrders }}</p>
        </div>
    </div>

    {{-- Achats du mois --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-coins text-indigo-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">Achats du mois</p>
            <p class="text-xl font-bold text-slate-800">{{ number_format($monthlyPurchases, 0, ',', ' ') }} MRU</p>
        </div>
    </div>

    {{-- Montant impayé --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-file-invoice-dollar text-red-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">Impayé total</p>
            <p class="text-xl font-bold text-red-600">{{ number_format($unpaidAmount, 0, ',', ' ') }} MRU</p>
        </div>
    </div>

    {{-- Paiements partiels --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-hourglass-half text-amber-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">Partiellement payé</p>
            <p class="text-2xl font-bold text-slate-800">{{ $partialOrders }}</p>
        </div>
    </div>

    {{-- Ruptures de stock --}}
    <a href="{{ route('purchases.stock-ruptures') }}"
       class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-4 hover:bg-red-50 transition group">
        <div class="w-12 h-12 {{ $totalRuptures > 0 ? 'bg-red-100' : 'bg-slate-100' }} rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-triangle-exclamation {{ $totalRuptures > 0 ? 'text-red-500' : 'text-slate-400' }} text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">Ruptures / Alertes</p>
            <p class="text-2xl font-bold {{ $totalRuptures > 0 ? 'text-red-600' : 'text-slate-800' }}">{{ $totalRuptures }}</p>
        </div>
    </a>

</div>

{{-- ── CHARTS ROW ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

    {{-- Monthly purchases chart --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-chart-bar text-orange-500"></i> Achats — 6 derniers mois
            </h3>
        </div>
        <canvas id="purchasesChart" height="160"></canvas>
    </div>

    {{-- Top suppliers --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-trophy text-amber-500"></i> Top fournisseurs
        </h3>
        @forelse($topSuppliers as $sup)
        @php $pct = $sup->purchase_orders_sum_total_amount > 0 ? min(100, ($sup->purchase_orders_sum_total_amount / max($topSuppliers->max('purchase_orders_sum_total_amount'), 1)) * 100) : 0; @endphp
        <div class="mb-3">
            <div class="flex justify-between text-sm mb-1">
                <span class="font-medium text-slate-700">{{ $sup->name }}</span>
                <span class="text-slate-500">{{ number_format($sup->purchase_orders_sum_total_amount ?? 0, 0, ',', ' ') }} MRU</span>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-orange-400 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
        </div>
        @empty
        <p class="text-slate-400 text-sm text-center py-6">Aucune donnée</p>
        @endforelse
    </div>

</div>

{{-- ── RECENT ORDERS + RECENT RECEIPTS ──────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

    {{-- Recent orders --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice text-blue-500"></i> Commandes récentes
            </h3>
            <a href="{{ route('purchases.orders') }}" class="text-xs text-orange-500 hover:underline">Voir tout</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                        <th class="pb-2 text-left">Référence</th>
                        <th class="pb-2 text-left">Fournisseur</th>
                        <th class="pb-2 text-right">Montant</th>
                        <th class="pb-2 text-center">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                        <td class="py-2 font-mono text-xs text-slate-600">
                            <a href="{{ route('purchases.orders.show', $order) }}" class="hover:text-orange-600">{{ $order->reference }}</a>
                        </td>
                        <td class="py-2 text-slate-700">{{ $order->supplier->name ?? '—' }}</td>
                        <td class="py-2 text-right font-medium">{{ number_format($order->total_amount, 0, ',', ' ') }}</td>
                        <td class="py-2 text-center">
                            @switch($order->status)
                                @case('pending')  <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700">En attente</span> @break
                                @case('ordered')  <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">Envoyée</span> @break
                                @case('received') <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700">Reçue</span> @break
                                @case('cancelled')<span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">Annulée</span> @break
                                @default          <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $order->status }}</span>
                            @endswitch
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-6 text-center text-slate-400">Aucune commande</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent receipts --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-emerald-500"></i> Réceptions récentes
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                        <th class="pb-2 text-left">Date</th>
                        <th class="pb-2 text-left">Commande</th>
                        <th class="pb-2 text-left">Fournisseur</th>
                        <th class="pb-2 text-left">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReceipts as $receipt)
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                        <td class="py-2 text-slate-500 text-xs">{{ \Carbon\Carbon::parse($receipt->received_at ?? $receipt->created_at)->format('d/m/Y') }}</td>
                        <td class="py-2 font-mono text-xs text-slate-600">
                            <a href="{{ route('purchases.orders.show', $receipt->purchaseOrder) }}" class="hover:text-orange-600">
                                {{ $receipt->purchaseOrder->reference ?? '—' }}
                            </a>
                        </td>
                        <td class="py-2 text-slate-700">{{ $receipt->purchaseOrder->supplier->name ?? '—' }}</td>
                        <td class="py-2 text-slate-500">{{ $receipt->stock->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-6 text-center text-slate-400">Aucune réception</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── STOCK ALERTS ──────────────────────────────────────────────────────── --}}
@if($stockRuptures->count() > 0 || $lowStockItems->count() > 0)
<div class="bg-white rounded-2xl shadow-sm p-5 mb-6">
    <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation text-red-500"></i> Alertes de stock
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        @if($stockRuptures->count() > 0)
        <div>
            <p class="text-xs font-semibold text-red-600 uppercase mb-2">Rupture totale (quantité = 0)</p>
            <div class="space-y-2">
                @foreach($stockRuptures as $item)
                <div class="flex items-center justify-between bg-red-50 rounded-xl px-4 py-2 text-sm">
                    <span class="font-medium text-slate-700">{{ $item->product->name ?? '—' }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-slate-400 text-xs">{{ $item->stock->name ?? '' }}</span>
                        <span class="font-bold text-red-600">0 {{ $item->product->unit->symbol ?? '' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($lowStockItems->count() > 0)
        <div>
            <p class="text-xs font-semibold text-amber-600 uppercase mb-2">Stock critique (≤ 5)</p>
            <div class="space-y-2">
                @foreach($lowStockItems as $item)
                <div class="flex items-center justify-between bg-amber-50 rounded-xl px-4 py-2 text-sm">
                    <span class="font-medium text-slate-700">{{ $item->product->name ?? '—' }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-slate-400 text-xs">{{ $item->stock->name ?? '' }}</span>
                        <span class="font-bold text-amber-600">{{ $item->quantity }} {{ $item->product->unit->symbol ?? '' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endif

{{-- ── BESOINS PRODUCTION CATERING (jour) ────────────────────────────────── --}}
@if($cateringShortfall->isNotEmpty())
<div class="bg-white rounded-2xl shadow-sm p-5 mb-6">
    <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-bell text-violet-500"></i> Besoins de production (Catering) non couverts aujourd'hui
    </h3>
    <div class="space-y-2">
        @foreach($cateringShortfall as $productId => $row)
            @php $product = $cateringShortfallProducts[$productId] ?? null; @endphp
            <div class="flex items-center justify-between bg-violet-50 rounded-xl px-4 py-2 text-sm">
                <span class="font-medium text-slate-700">{{ $product->name ?? '#' . $productId }}</span>
                <div class="flex items-center gap-3">
                    <span class="text-slate-400 text-xs">besoin {{ round($row['needed'], 2) }} · dispo {{ round($row['available'], 2) }}</span>
                    <span class="font-bold text-violet-600">manque {{ round($row['missing'], 2) }} {{ $product?->unit?->symbol ?? '' }}</span>
                </div>
            </div>
        @endforeach
    </div>
    <div class="flex gap-3 mt-4">
        <a href="{{ route('production.catering-today') }}" class="text-sm text-violet-600 hover:underline font-medium">
            <i class="fa-solid fa-calendar-day mr-1"></i>Voir le plan de production du jour
        </a>
        <a href="{{ route('purchases.orders.create') }}" class="text-sm text-violet-600 hover:underline font-medium">
            <i class="fa-solid fa-plus mr-1"></i>Créer une commande
        </a>
    </div>
</div>
@endif

{{-- ── QUICK ACTIONS ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm p-5">
    <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-bolt text-amber-500"></i> Actions rapides
    </h3>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('purchases.orders.create') }}"
           class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition shadow-sm">
            <i class="fa-solid fa-plus"></i> Nouvelle commande
        </a>
        <a href="{{ route('purchases.suppliers') }}"
           class="flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition shadow-sm">
            <i class="fa-solid fa-truck"></i> Gérer fournisseurs
        </a>
        <a href="{{ route('purchases.stock-ruptures') }}"
           class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition shadow-sm">
            <i class="fa-solid fa-triangle-exclamation"></i> Voir ruptures ({{ $totalRuptures }})
        </a>
        <a href="{{ route('purchases.orders') }}?payment_status=unpaid"
           class="flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition shadow-sm">
            <i class="fa-solid fa-file-invoice-dollar"></i> Commandes impayées
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
const ctx = document.getElementById('purchasesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [
            {
                label: 'Total commandé (MRU)',
                data: {!! json_encode($chartTotals) !!},
                backgroundColor: 'rgba(249,115,22,0.7)',
                borderRadius: 6,
            },
            {
                label: 'Total payé (MRU)',
                data: {!! json_encode($chartPaid) !!},
                backgroundColor: 'rgba(16,185,129,0.7)',
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' MRU' } }
        }
    }
});
</script>
@endpush
