@extends('layouts.purchases')
@section('title', 'Ruptures de stock')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation text-red-500"></i> Alertes & Ruptures de stock
    </h2>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-red-50 border border-red-100 rounded-2xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-circle-xmark text-red-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-red-500 uppercase tracking-wide">En rupture totale</p>
            <p class="text-3xl font-bold text-red-700">{{ $ruptures->count() }}</p>
            <p class="text-xs text-red-400">produits à stock zéro</p>
        </div>
    </div>
    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-triangle-exclamation text-amber-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Stock critique</p>
            <p class="text-3xl font-bold text-amber-700">{{ $lowStock->count() }}</p>
            <p class="text-xs text-amber-400">produits ≤ 5 unités</p>
        </div>
    </div>
</div>

{{-- Ruptures totales --}}
@if($ruptures->count() > 0)
<div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-4 bg-red-50 border-b border-red-100 flex items-center gap-2">
        <i class="fa-solid fa-circle-xmark text-red-500"></i>
        <h3 class="font-semibold text-red-700">Rupture totale — quantité = 0</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs text-slate-400 uppercase border-b bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left">Produit</th>
                    <th class="px-5 py-3 text-left">Unité</th>
                    <th class="px-5 py-3 text-left">Dépôt</th>
                    <th class="px-5 py-3 text-right">Quantité</th>
                    <th class="px-5 py-3 text-center">Action rapide</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ruptures as $item)
                <tr class="border-b border-slate-50 hover:bg-red-50/30 transition">
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $item->product->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $item->product->unit->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $item->stock->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right">
                        <span class="px-2 py-1 bg-red-100 text-red-700 font-bold rounded-lg">0 {{ $item->product->unit->symbol ?? '' }}</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @can('purchases.orders.create')
                        <a href="{{ route('purchases.orders.create') }}"
                           class="text-xs text-orange-600 hover:underline font-medium">
                            <i class="fa-solid fa-plus mr-1"></i>Commander
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5 mb-6 text-center">
    <i class="fa-solid fa-circle-check text-emerald-500 text-2xl mb-2"></i>
    <p class="text-emerald-700 font-medium">Aucune rupture totale</p>
</div>
@endif

{{-- Stock critique --}}
@if($lowStock->count() > 0)
<div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-4 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
        <h3 class="font-semibold text-amber-700">Stock critique — quantité ≤ 5</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs text-slate-400 uppercase border-b bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left">Produit</th>
                    <th class="px-5 py-3 text-left">Unité</th>
                    <th class="px-5 py-3 text-left">Dépôt</th>
                    <th class="px-5 py-3 text-right">Quantité</th>
                    <th class="px-5 py-3 text-center">Action rapide</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStock as $item)
                <tr class="border-b border-slate-50 hover:bg-amber-50/30 transition">
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $item->product->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $item->product->unit->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $item->stock->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right">
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 font-bold rounded-lg">
                            {{ $item->quantity }} {{ $item->product->unit->symbol ?? '' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @can('purchases.orders.create')
                        <a href="{{ route('purchases.orders.create') }}"
                           class="text-xs text-orange-600 hover:underline font-medium">
                            <i class="fa-solid fa-plus mr-1"></i>Commander
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5 mb-6 text-center">
    <i class="fa-solid fa-circle-check text-emerald-500 text-2xl mb-2"></i>
    <p class="text-emerald-700 font-medium">Aucun produit en stock critique</p>
</div>
@endif

{{-- CTA --}}
@can('purchases.orders.create')
<div class="text-center">
    <a href="{{ route('purchases.orders.create') }}"
       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-semibold text-sm transition shadow">
        <i class="fa-solid fa-plus"></i> Créer une commande d'approvisionnement
    </a>
</div>
@endcan

@endsection
