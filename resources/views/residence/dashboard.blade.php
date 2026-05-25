@extends('layouts.residence')
@section('title', 'Tableau de bord')

@section('content')

{{-- Module Hero --}}
<div class="relative rounded-2xl overflow-hidden mb-6 shadow-xl" style="height:200px;">
    <img src="{{ asset('royal_palm.jpeg') }}" alt="Royal Palm" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-violet-900/80 to-transparent flex items-center px-8">
        <div>
            <h2 class="text-4xl font-bold text-white">Royal Palm</h2>
            <p class="text-violet-200 mt-1 text-lg">Module Résidence — Gestion des hébergements</p>
        </div>
    </div>
</div>

<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <h2 class="text-xl font-bold text-slate-800">Tableau de bord — Résidence</h2>
    @can('residence.bookings.create')
    <a href="{{ route('residence.bookings.create') }}"
       class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow">
        <i class="fa-solid fa-plus"></i> Nouvelle réservation
    </a>
    @endcan
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-building text-violet-600 text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium">Total chambres</p>
            <p class="text-2xl font-bold text-slate-800">{{ $totalRooms }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium">Disponibles</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $available }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-user text-orange-500 text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium">Occupées</p>
            <p class="text-2xl font-bold text-orange-500">{{ $occupied }}</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-wrench text-slate-500 text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium">Maintenance</p>
            <p class="text-2xl font-bold text-slate-500">{{ $maintenance }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-violet-600 rounded-2xl p-5 text-white flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-arrow-right-to-bracket text-white text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-violet-200 font-medium">Check-ins aujourd'hui</p>
            <p class="text-3xl font-bold">{{ $todayCheckIns }}</p>
        </div>
    </div>
    <div class="bg-indigo-600 rounded-2xl p-5 text-white flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-arrow-right-from-bracket text-white text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-indigo-200 font-medium">Check-outs aujourd'hui</p>
            <p class="text-3xl font-bold">{{ $todayCheckOuts }}</p>
        </div>
    </div>
    <div class="bg-emerald-600 rounded-2xl p-5 text-white flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-money-bill-wave text-white text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-emerald-200 font-medium">Recettes ce mois</p>
            <p class="text-2xl font-bold">{{ number_format($monthRevenue, 0, ',', ' ') }} MRU</p>
        </div>
    </div>
</div>

{{-- Caisse alert --}}
@if(!$openRegister)
<div class="mb-6 flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-sm">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <span>Aucune caisse résidence ouverte. <a href="{{ route('residence.caisse') }}" class="font-semibold underline">Ouvrir la caisse</a></span>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    {{-- Active bookings --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-door-open text-violet-500"></i> Clients en séjour
        </h3>
        @forelse($activeBookings as $booking)
        <a href="{{ route('residence.bookings.show', $booking) }}"
           class="flex items-center justify-between px-4 py-3 border border-slate-100 rounded-xl mb-2 hover:bg-slate-50 transition">
            <div>
                <p class="font-medium text-slate-800 text-sm">{{ $booking->customer_name }}</p>
                <p class="text-xs text-slate-500">
                    <span class="font-medium text-violet-600">Ch. {{ $booking->room->number }}</span>
                    — {{ $booking->room->roomType->name }}
                    &nbsp;·&nbsp; Départ {{ $booking->check_out->format('d/m/Y') }}
                </p>
            </div>
            <span class="text-xs px-2 py-1 bg-violet-100 text-violet-700 rounded-full font-medium">
                {{ $booking->nights }} nuit{{ $booking->nights > 1 ? 's' : '' }}
            </span>
        </a>
        @empty
        <p class="text-slate-400 text-sm text-center py-6">Aucun client en séjour</p>
        @endforelse
    </div>

    {{-- Recent bookings --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Réservations récentes
            </h3>
            <a href="{{ route('residence.bookings') }}" class="text-xs text-violet-600 hover:underline">Voir tout</a>
        </div>
        @forelse($recentBookings as $booking)
        <a href="{{ route('residence.bookings.show', $booking) }}"
           class="flex items-center justify-between px-3 py-2 rounded-xl mb-1 hover:bg-slate-50 transition">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                    <i class="fa-solid fa-door-closed text-violet-400 text-xs"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $booking->customer_name }}</p>
                    <p class="text-xs text-slate-400">Ch. {{ $booking->room->number }} · {{ $booking->check_in->format('d/m') }} → {{ $booking->check_out->format('d/m/Y') }}</p>
                </div>
            </div>
            @php
                $statusMap = [
                    'pending'    => ['bg-yellow-100 text-yellow-700', 'En attente'],
                    'checked_in' => ['bg-violet-100 text-violet-700', 'En séjour'],
                    'checked_out'=> ['bg-emerald-100 text-emerald-700', 'Terminé'],
                    'cancelled'  => ['bg-red-100 text-red-700', 'Annulé'],
                ];
                [$cls, $label] = $statusMap[$booking->status] ?? ['bg-slate-100 text-slate-600', $booking->status];
            @endphp
            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $cls }}">{{ $label }}</span>
        </a>
        @empty
        <p class="text-slate-400 text-sm text-center py-6">Aucune réservation</p>
        @endforelse
    </div>
</div>

@endsection
