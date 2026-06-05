{{-- validate.blade.php: Transferts POS validés (remplace l'ancien système de codes) --}}
@extends('layouts.catering')
@section('title', 'Transferts POS Catering')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Transferts POS Catering</h2>
        <p class="text-slate-500 text-sm mt-1">Suivi des transferts production → point de vente catering</p>
    </div>
    <div class="flex items-center gap-3">
        @if($openRegister)
        <a href="{{ route('cashier.session', $openRegister->id) }}"
           class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            <i class="fa-solid fa-cash-register"></i> Caisse active
        </a>
        @endif
        <a href="{{ route('pos-transfer.create') }}"
           class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            <i class="fa-solid fa-plus"></i> Nouveau transfert
        </a>
    </div>
</div>

@if($openRegister)
<div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
    <i class="fa-solid fa-circle-check text-emerald-600 text-xl"></i>
    <div>
        <p class="font-semibold text-emerald-800">Caisse POS active : {{ $openRegister->label ?? $openRegister->module }}</p>
        <p class="text-sm text-emerald-700">Caissier : {{ $openRegister->user->name ?? '—' }} · Ouverte le {{ $openRegister->opened_at?->format('d/m/Y à H:i') }}</p>
    </div>
</div>
@else
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-xl"></i>
    <div>
        <p class="font-semibold text-amber-800">Aucune caisse POS catering active</p>
        <p class="text-sm text-amber-700">Le caissier doit d'abord ouvrir sa session depuis le module Caisse.</p>
    </div>
    <a href="{{ route('cashier.open') }}" class="ml-auto text-sm bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700 transition">
        Ouvrir une session
    </a>
</div>
@endif

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
        <i class="fa-solid fa-truck-fast text-blue-500"></i>
        <h3 class="font-semibold text-slate-700">Transferts récents validés</h3>
    </div>

    @forelse($recentTransfers as $t)
    <div class="px-5 py-4 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-truck text-blue-600"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-slate-800">{{ $t->reference }}</p>
                        <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Validé</span>
                    </div>
                    <p class="text-sm text-slate-500">
                        Client : {{ $t->client->name ?? '—' }} ·
                        Caisse : {{ $t->cashRegister->label ?? ($t->cashRegister->user->name ?? '—') }} ·
                        {{ $t->transfer_date->format('d/m/Y') }}
                    </p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <p class="text-sm font-semibold text-slate-700">{{ $t->items->count() }} article(s)</p>
                @if($t->total_extra_amount > 0)
                <p class="text-xs text-orange-600">Extras : {{ number_format($t->total_extra_amount, 0, ',', ' ') }} MRU</p>
                @endif
            </div>
        </div>

        @if($t->items->count())
        <div class="mt-3 ml-13 pl-13">
            <table class="w-full text-xs text-slate-600">
                <thead>
                    <tr class="text-slate-400">
                        <th class="text-left pb-1">Article</th>
                        <th class="text-center pb-1">Type</th>
                        <th class="text-right pb-1">Qté</th>
                        <th class="text-right pb-1">Servi</th>
                        <th class="text-right pb-1">Vendu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($t->items as $item)
                    <tr class="border-t border-slate-50">
                        <td class="py-1">{{ $item->label }}</td>
                        <td class="text-center">
                            <span class="px-1.5 py-0.5 rounded text-xs {{ $item->item_type === 'contract' ? 'bg-teal-50 text-teal-700' : 'bg-orange-50 text-orange-700' }}">
                                {{ $item->item_type === 'contract' ? 'Contrat' : 'Vente' }}
                            </span>
                        </td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ $item->served_qty }}</td>
                        <td class="text-right">{{ $item->sold_qty }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @empty
    <div class="px-5 py-12 text-center">
        <i class="fa-solid fa-truck-fast text-4xl text-slate-200 mb-3 block"></i>
        <p class="text-slate-400">Aucun transfert validé récemment</p>
        <a href="{{ route('pos-transfer.create') }}" class="mt-3 inline-flex items-center gap-2 text-sm text-teal-600 hover:underline">
            <i class="fa-solid fa-plus"></i> Créer le premier transfert
        </a>
    </div>
    @endforelse
</div>

@endsection
