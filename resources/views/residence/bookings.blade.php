@extends('layouts.residence')
@section('title', 'Réservations')

@section('content')

<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <h2 class="text-xl font-bold text-slate-800">Réservations</h2>
    @can('residence.bookings.create')
    <a href="{{ route('residence.bookings.create') }}"
       class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow">
        <i class="fa-solid fa-plus"></i> Nouvelle réservation
    </a>
    @endcan
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-2xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[180px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Recherche client</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom du client…"
               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
    </div>
    <div class="min-w-[140px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Chambre</label>
        <select name="room_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            <option value="">Toutes</option>
            @foreach($rooms as $r)
            <option value="{{ $r->id }}" @selected(request('room_id') == $r->id)>{{ $r->number }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-[140px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Statut</label>
        <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            <option value="">Tous</option>
            <option value="pending"     @selected(request('status')=='pending')>En attente</option>
            <option value="checked_in"  @selected(request('status')=='checked_in')>Check-in</option>
            <option value="checked_out" @selected(request('status')=='checked_out')>Check-out</option>
            <option value="cancelled"   @selected(request('status')=='cancelled')>Annulé</option>
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-violet-600 text-white rounded-xl text-sm hover:bg-violet-700 transition">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <a href="{{ route('residence.bookings') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm hover:bg-slate-200 transition">
            <i class="fa-solid fa-xmark"></i>
        </a>
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="text-xs text-slate-400 uppercase bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-5 py-3 text-left">Client</th>
                <th class="px-5 py-3 text-left">Chambre</th>
                <th class="px-5 py-3 text-left">Arrivée</th>
                <th class="px-5 py-3 text-left">Départ</th>
                <th class="px-5 py-3 text-right">Nuits</th>
                <th class="px-5 py-3 text-right">Total</th>
                <th class="px-5 py-3 text-right">Payé</th>
                <th class="px-5 py-3 text-center">Statut</th>
                <th class="px-5 py-3 w-12"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
            @php
                $nights = \Carbon\Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
                $statusMap = [
                    'pending'     => ['label'=>'En attente', 'class'=>'bg-yellow-100 text-yellow-700'],
                    'checked_in'  => ['label'=>'Check-in',   'class'=>'bg-violet-100 text-violet-700'],
                    'checked_out' => ['label'=>'Check-out',  'class'=>'bg-emerald-100 text-emerald-700'],
                    'cancelled'   => ['label'=>'Annulé',     'class'=>'bg-red-100 text-red-500'],
                ];
                $s = $statusMap[$booking->status] ?? ['label'=>$booking->status,'class'=>'bg-slate-100 text-slate-500'];
            @endphp
            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                <td class="px-5 py-3">
                    <div class="font-medium text-slate-800">{{ $booking->customer_name }}</div>
                    @if($booking->customer_phone)
                    <div class="text-xs text-slate-400">{{ $booking->customer_phone }}</div>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <div class="font-medium text-slate-800">Ch. {{ $booking->room->number }}</div>
                    <div class="text-xs text-slate-400">{{ $booking->room->roomType->name ?? '—' }}</div>
                </td>
                <td class="px-5 py-3 text-slate-600">{{ \Carbon\Carbon::parse($booking->check_in)->format('d/m/Y') }}</td>
                <td class="px-5 py-3 text-slate-600">{{ \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') }}</td>
                <td class="px-5 py-3 text-right text-slate-700">{{ $nights }}</td>
                <td class="px-5 py-3 text-right font-medium">{{ number_format($booking->total_amount, 0, ',', ' ') }}</td>
                <td class="px-5 py-3 text-right">
                    @php $paid = $booking->paid_amount ?? 0; @endphp
                    <span class="{{ $paid >= $booking->total_amount ? 'text-emerald-600' : 'text-amber-600' }} font-medium">
                        {{ number_format($paid, 0, ',', ' ') }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $s['class'] }}">{{ $s['label'] }}</span>
                </td>
                <td class="px-5 py-3">
                    <a href="{{ route('residence.bookings.show', $booking) }}"
                       class="text-violet-600 hover:text-violet-800">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center py-12 text-slate-400">Aucune réservation</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($bookings->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $bookings->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
