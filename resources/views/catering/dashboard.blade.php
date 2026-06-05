{{-- Dashboard: uses $totalContracts,$activeContracts,$monthRevenue,$activeContractsList,$recentTransfers --}}
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
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

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
        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-coins text-orange-600"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500">Revenu (mois)</p>
            <p class="text-base font-bold text-orange-600">{{ number_format($monthRevenue, 0, ',', ' ') }} MRU</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-truck-fast text-blue-600"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500">Transferts récents</p>
            <p class="text-xl font-bold text-blue-700">{{ $recentTransfers->count() }}</p>
        </div>
    </div>

</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Active contracts --}}
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-file-contract text-teal-500"></i> Contrats actifs récents
            </h3>
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

    {{-- Quick actions --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-3">Actions rapides</h3>
        <div class="space-y-2">
            <a href="{{ route('catering.contracts') }}"
               class="flex items-center gap-3 text-sm px-4 py-2.5 rounded-xl bg-teal-50 text-teal-700 hover:bg-teal-100 transition font-medium">
                <i class="fa-solid fa-plus w-4"></i> Nouveau contrat
            </a>
            <a href="{{ route('catering.planning.index') }}"
               class="flex items-center gap-3 text-sm px-4 py-2.5 rounded-xl bg-cyan-50 text-cyan-700 hover:bg-cyan-100 transition font-medium">
                <i class="fa-solid fa-calendar-week w-4"></i> Programmer la semaine
            </a>
            <a href="{{ route('catering.clients') }}"
               class="flex items-center gap-3 text-sm px-4 py-2.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition font-medium">
                <i class="fa-solid fa-user-plus w-4"></i> Gérer les clients
            </a>
            <a href="{{ route('catering.billing.index') }}"
               class="flex items-center gap-3 text-sm px-4 py-2.5 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 transition font-medium">
                <i class="fa-solid fa-file-invoice-dollar w-4"></i> Facturation mensuelle
            </a>
            <a href="{{ route('catering.validate') }}"
               class="flex items-center gap-3 text-sm px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition font-medium">
                <i class="fa-solid fa-truck-fast w-4"></i> Transferts POS
            </a>
            <a href="{{ route('pos-transfer.create') }}"
               class="flex items-center gap-3 text-sm px-4 py-2.5 rounded-xl bg-violet-50 text-violet-700 hover:bg-violet-100 transition font-medium">
                <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Créer un transfert
            </a>
        </div>
    </div>
</div>

{{-- Recent POS transfers --}}
<div class="bg-white rounded-2xl shadow-sm p-5 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-truck-fast text-blue-500"></i> Derniers transferts POS
        </h3>
    </div>
    @forelse($recentTransfers as $t)
    <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-truck text-blue-600 text-xs"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-700">{{ $t->reference }}</p>
                <p class="text-xs text-slate-400">{{ $t->client->name ?? '—' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @php
                $badge = match($t->status) {
                    'pending'   => 'bg-amber-100 text-amber-700',
                    'in_transit'=> 'bg-blue-100 text-blue-700',
                    'validated' => 'bg-emerald-100 text-emerald-700',
                    'closed'    => 'bg-slate-100 text-slate-600',
                    default     => 'bg-slate-100 text-slate-600',
                };
                $label = match($t->status) {
                    'pending'   => 'En attente',
                    'in_transit'=> 'En transit',
                    'validated' => 'Validé',
                    'closed'    => 'Clôturé',
                    default     => $t->status,
                };
            @endphp
            <span class="text-xs px-2 py-1 rounded-full font-medium {{ $badge }}">{{ $label }}</span>
            <span class="text-xs text-slate-400">{{ $t->transfer_date->format('d/m') }}</span>
        </div>
    </div>
    @empty
    <p class="text-slate-400 text-sm text-center py-4">Aucun transfert récent</p>
    @endforelse
</div>

@endsection
