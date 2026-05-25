@extends('layouts.event')
@section('title', 'Dashboard Events')

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-violet-700">{{ $totalEvents }}</p>
        <p class="text-xs text-slate-500 mt-1">Total</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-slate-600">{{ $draftCount }}</p>
        <p class="text-xs text-slate-500 mt-1">Brouillons</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-blue-700">{{ $validatedCount }}</p>
        <p class="text-xs text-slate-500 mt-1">Validés</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-emerald-700">{{ $completedCount }}</p>
        <p class="text-xs text-slate-500 mt-1">Clôturés</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-orange-600">{{ number_format($monthRevenue, 0, ',', ' ') }}</p>
        <p class="text-xs text-slate-500 mt-1">MRU ce mois</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    {{-- Chart --}}
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-bar text-violet-400"></i> Événements créés par mois
        </h3>
        <canvas id="eventsChart" height="100"></canvas>
    </div>

    {{-- Quick actions --}}
    <div class="bg-white rounded-2xl shadow-sm p-5 flex flex-col justify-between">
        <h3 class="font-semibold text-slate-700 mb-4">Actions rapides</h3>
        <div class="space-y-3">
            <a href="{{ route('event.create') }}"
               class="flex items-center gap-3 p-3 rounded-xl bg-violet-50 hover:bg-violet-100 text-violet-700 transition">
                <i class="fa-solid fa-plus-circle text-lg"></i>
                <span class="font-medium text-sm">Nouvel événement</span>
            </a>
            <a href="{{ route('event.clients') }}"
               class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition">
                <i class="fa-solid fa-user-plus text-lg"></i>
                <span class="font-medium text-sm">Nouveau client</span>
            </a>
            <a href="{{ route('event.services') }}"
               class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition">
                <i class="fa-solid fa-briefcase text-lg"></i>
                <span class="font-medium text-sm">Catalogue services</span>
            </a>
        </div>
        <div class="mt-4 pt-4 border-t">
            <a href="{{ route('event.index') }}"
               class="flex items-center gap-2 text-slate-500 hover:text-violet-600 text-sm transition">
                <i class="fa-solid fa-list"></i> Voir tous les événements
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    {{-- Upcoming events --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-calendar-check text-violet-400"></i> Prochains événements
        </h3>
        @forelse($upcomingEvents as $ev)
        <a href="{{ route('event.show', $ev) }}"
           class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition">
            <div>
                <p class="font-medium text-slate-800 text-sm">{{ $ev->client?->name ?? '—' }}
                    <span class="text-slate-400 font-normal">· {{ $ev->event_type }}</span>
                </p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $ev->event_date->format('d/m/Y') }} · {{ $ev->guest_count }} convives</p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $ev->status_color }}">
                {{ $ev->status_label }}
            </span>
        </a>
        @empty
        <p class="text-center text-slate-400 text-sm py-6">Aucun événement à venir.</p>
        @endforelse
    </div>

    {{-- Recent completed --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-flag-checkered text-emerald-400"></i> Récemment clôturés
        </h3>
        @forelse($recentCompleted as $ev)
        <a href="{{ route('event.show', $ev) }}"
           class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition">
            <div>
                <p class="font-medium text-slate-800 text-sm">{{ $ev->client?->name ?? '—' }}
                    <span class="text-slate-400 font-normal">· {{ $ev->event_type }}</span>
                </p>
                <p class="text-xs text-slate-400 mt-0.5">Clôturé le {{ $ev->completed_at?->format('d/m/Y') }}</p>
            </div>
            <span class="font-bold text-emerald-700 text-sm">
                {{ number_format((float)$ev->total_amount, 0, ',', ' ') }} MRU
            </span>
        </a>
        @empty
        <p class="text-center text-slate-400 text-sm py-6">Aucun événement clôturé.</p>
        @endforelse
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('eventsChart'), {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'Événements',
            data: @json($chartData),
            backgroundColor: 'rgba(139,92,246,0.7)',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
@endpush

@endsection
