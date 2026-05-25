{{-- Dashboard: uses $totalContracts,$activeContracts,$totalCodes,$usedCodes,$todayValidations,$monthRevenue,$chartLabels,$chartData,$recentConsumptions,$activeContractsList --}}
@extends('layouts.catering')
@section('title', 'Tableau de bord')

@section('content')

{{-- Module Hero --}}
<div class="relative rounded-2xl overflow-hidden mb-6 shadow-xl" style="height:200px;">
    <img src="{{ asset('catering.jpeg') }}" alt="Catering" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-teal-900/80 to-transparent flex items-center px-8">
        <div>
            <h2 class="text-4xl font-bold text-white">Catering</h2>
            <p class="text-teal-200 mt-1 text-lg">Module Catering — Gestion des contrats</p>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">

    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-file-contract text-teal-600"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500">Contrats totaux</p>
            <p class="text-xl font-bold text-slate-800">{{ $totalContracts }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-circle-play text-emerald-600"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500">Contrats actifs</p>
            <p class="text-xl font-bold text-emerald-700">{{ $activeContracts }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-ticket text-blue-600"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500">Codes générés</p>
            <p class="text-xl font-bold text-slate-800">{{ $totalCodes }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-circle-check text-indigo-600"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500">Codes utilisés</p>
            <p class="text-xl font-bold text-indigo-700">{{ $usedCodes }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-clock text-amber-600"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500">Validés auj.</p>
            <p class="text-xl font-bold text-amber-700">{{ $todayValidations }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-coins text-orange-600"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500">Revenu (mois)</p>
            <p class="text-base font-bold text-orange-600">{{ number_format($monthRevenue, 0, ',', ' ') }} MRU</p>
        </div>
    </div>

</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Chart --}}
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-bar text-teal-500"></i> Consommations — 7 derniers jours
        </h3>
        <canvas id="chartConsumptions" height="100"></canvas>
    </div>

    {{-- Codes progress + quick actions --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-ticket text-teal-500"></i> Taux consommation
            </h3>
            @php $pct = $totalCodes > 0 ? round(($usedCodes / $totalCodes) * 100) : 0; @endphp
            <div class="flex justify-between text-sm mb-2">
                <span class="text-slate-500">{{ $usedCodes }} utilisés</span>
                <span class="font-bold text-teal-600">{{ $pct }}%</span>
            </div>
            <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-teal-400 to-teal-600 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-1">{{ $totalCodes - $usedCodes }} codes en attente</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-3">Actions rapides</h3>
            <div class="space-y-2">
                <a href="{{ route('catering.contracts') }}"
                   class="flex items-center gap-3 text-sm px-4 py-2.5 rounded-xl bg-teal-50 text-teal-700 hover:bg-teal-100 transition font-medium">
                    <i class="fa-solid fa-plus w-4"></i> Nouveau contrat
                </a>
                <a href="{{ route('catering.clients') }}"
                   class="flex items-center gap-3 text-sm px-4 py-2.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition font-medium">
                    <i class="fa-solid fa-user-plus w-4"></i> Gérer les clients
                </a>
                <a href="{{ route('catering.validate') }}"
                   class="flex items-center gap-3 text-sm px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition font-medium">
                    <i class="fa-solid fa-qrcode w-4"></i> Valider un code
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Active contracts + Recent consumptions --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">

    <div class="bg-white rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-700">Contrats actifs récents</h3>
            <a href="{{ route('catering.contracts') }}" class="text-xs text-teal-600 hover:underline">Voir tout</a>
        </div>
        @forelse($activeContractsList as $c)
        <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
            <div>
                <p class="font-medium text-slate-700 text-sm">{{ $c->client->name ?? '—' }}</p>
                <p class="text-xs text-slate-400">
                    {{ $c->start_date->format('d/m/Y') }} → {{ $c->end_date->format('d/m/Y') }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold text-teal-700">{{ $c->guest_count }} convives</p>
                <p class="text-xs text-slate-400">{{ $c->weekly_menus_count }} menu(s)</p>
            </div>
        </div>
        @empty
        <p class="text-slate-400 text-sm text-center py-4">Aucun contrat actif</p>
        @endforelse
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-700">Dernières validations</h3>
            <a href="{{ route('catering.consumptions') }}" class="text-xs text-teal-600 hover:underline">Voir tout</a>
        </div>
        @forelse($recentConsumptions as $c)
        <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-check text-emerald-600 text-xs"></i>
                </div>
                <div>
                    <p class="text-sm font-mono font-semibold text-slate-700">{{ $c->mealCode->code ?? '—' }}</p>
                    <p class="text-xs text-slate-400">
                        {{ $c->mealCode?->menuMeal?->menuDay?->weeklyMenu?->contract?->client?->name ?? '—' }}
                        · {{ $c->mealCode?->menuMeal?->type_label ?? '' }}
                    </p>
                </div>
            </div>
            <span class="text-xs text-slate-400">{{ $c->consumed_at?->format('H:i') }}</span>
        </div>
        @empty
        <p class="text-slate-400 text-sm text-center py-4">Aucune validation récente</p>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
const ctx = document.getElementById('chartConsumptions');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'Repas validés',
            data: @json($chartData),
            backgroundColor: 'rgba(20,184,166,0.7)',
            borderColor: 'rgba(15,118,110,1)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush

@endsection
