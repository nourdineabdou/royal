@extends('layouts.stock')

@section('title', 'Stocks')
@section('header', 'Tableau de bord des Stocks')

@section('header-actions')
    <a href="{{ route('stock.export.inventory') }}"
       class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-file-csv"></i> Exporter inventaire
    </a>
    @can('stock.transfer')
    <a href="{{ route('stock.transfer') }}"
       class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-right-left"></i> Transfert
    </a>
    @endcan
@endsection

@section('content')
<div class="py-4 space-y-6">

    {{-- ── KPI Bar ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-warehouse text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Stocks</p>
                <p class="text-xl font-bold text-slate-800">{{ $stocks->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-boxes-stacked text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Produits distincts</p>
                <p class="text-xl font-bold text-slate-800">{{ $totalProducts }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                <i class="fa-solid fa-arrows-rotate text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Mouvements totaux</p>
                <p class="text-xl font-bold text-slate-800">{{ $totalMovements }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 {{ count($missingModules) > 0 ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600' }} rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-{{ count($missingModules) > 0 ? 'triangle-exclamation' : 'circle-check' }} text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Modules liés</p>
                <p class="text-xl font-bold text-slate-800">
                    {{ count(\App\Models\Stock::MODULES) - count($missingModules) }}/{{ count(\App\Models\Stock::MODULES) }}
                </p>
            </div>
        </div>
    </div>

    {{-- ── Missing module warnings ──────────────────────────────────────── --}}
    @if(count($missingModules) > 0)
    <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 flex items-start gap-3">
        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 text-lg"></i>
        <div>
            <p class="font-semibold text-amber-800 text-sm">Modules sans stock lié</p>
            <p class="text-amber-700 text-xs mt-1">
                Les modules suivants n'ont pas de stock par défaut assigné :
                <strong>{{ implode(', ', array_map(fn($m) => \App\Models\Stock::MODULES[$m], $missingModules)) }}</strong>.
                Assignez un stock à chaque module ci-dessous pour activer le suivi automatique de stock.
            </p>
        </div>
    </div>
    @endif

    {{-- ── Module Assignment Card ──────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-link text-emerald-500"></i>
                Liaison Module ↔ Stock par défaut
            </h2>
        </div>
        <div class="p-5 grid md:grid-cols-3 gap-4">
            @foreach(\App\Models\Stock::MODULES as $moduleKey => $moduleLabel)
                @php
                    $linkedStock = $stocks->firstWhere('module', $moduleKey);
                    $icons = ['restaurant' => 'fa-utensils', 'catering' => 'fa-bowl-food', 'events' => 'fa-calendar-star'];
                    $colors = ['restaurant' => 'blue', 'catering' => 'purple', 'events' => 'orange'];
                    $c = $colors[$moduleKey] ?? 'slate';
                @endphp
                <div class="border border-{{ $c }}-100 rounded-lg p-4 bg-{{ $c }}-50">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-{{ $c }}-100 rounded-lg flex items-center justify-center text-{{ $c }}-600">
                            <i class="fa-solid {{ $icons[$moduleKey] ?? 'fa-store' }} text-sm"></i>
                        </div>
                        <p class="font-semibold text-{{ $c }}-700 text-sm">{{ $moduleLabel }}</p>
                        @if($linkedStock)
                            <span class="ml-auto text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                                <i class="fa-solid fa-circle-check"></i> Lié
                            </span>
                        @else
                            <span class="ml-auto text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">
                                <i class="fa-solid fa-circle-xmark"></i> Non lié
                            </span>
                        @endif
                    </div>
                    @if($linkedStock)
                        <p class="text-xs text-{{ $c }}-700 mb-2">
                            <i class="fa-solid fa-warehouse mr-1"></i> Stock actuel : <strong>{{ $linkedStock->name }}</strong>
                        </p>
                    @else
                        <p class="text-xs text-red-600 mb-2">Aucun stock lié — les décrements automatiques sont désactivés.</p>
                    @endif
                    @can('stock.assign-module')
                    <form method="POST" action="{{ route('stock.assign-module') }}" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="module" value="{{ $moduleKey }}">
                        <select name="stock_id" class="flex-1 text-xs border border-{{ $c }}-200 rounded px-2 py-1 bg-white focus:outline-none focus:ring-1 focus:ring-{{ $c }}-400">
                            <option value="">— Aucun —</option>
                            @foreach($stocks as $s)
                                <option value="{{ $s->id }}" {{ $linkedStock?->id == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}{{ $s->module && $s->module !== $moduleKey ? ' ('.\App\Models\Stock::MODULES[$s->module].')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-{{ $c }}-600 hover:bg-{{ $c }}-700 text-white text-xs px-3 py-1 rounded font-semibold">
                            Lier
                        </button>
                    </form>
                    @endcan
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Stocks Table ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-emerald-500"></i>
                Tous les stocks
            </h2>
            <a href="{{ route('stock.all-movements') }}"
               class="text-xs text-emerald-600 hover:underline flex items-center gap-1">
                <i class="fa-solid fa-arrows-rotate"></i> Voir tous les mouvements
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3">Nom du Stock</th>
                        <th class="px-4 py-3">Emplacement</th>
                        <th class="px-4 py-3">Module lié</th>
                        <th class="px-4 py-3 text-right">Produits</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($stocks as $stock)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3 font-semibold text-slate-800">
                            <i class="fa-solid fa-warehouse text-emerald-400 mr-2"></i>
                            {{ $stock->name }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $stock->location ?: '—' }}</td>
                        <td class="px-4 py-3">
                            @if($stock->module)
                                @php
                                    $badge = ['restaurant' => 'blue', 'catering' => 'purple', 'events' => 'orange'][$stock->module] ?? 'slate';
                                @endphp
                                <span class="text-xs bg-{{ $badge }}-100 text-{{ $badge }}-700 px-2 py-0.5 rounded-full font-medium">
                                    {{ \App\Models\Stock::MODULES[$stock->module] ?? $stock->module }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">Non lié</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-700">{{ $stock->items_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('stock.show', $stock) }}"
                                   class="text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-3 py-1 rounded-lg font-medium flex items-center gap-1">
                                    <i class="fa-solid fa-eye"></i> Produits
                                </a>
                                <a href="{{ route('stock.movements', $stock) }}"
                                   class="text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-1 rounded-lg font-medium flex items-center gap-1">
                                    <i class="fa-solid fa-arrows-rotate"></i> Mouvements
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-400 text-sm">
                            <i class="fa-solid fa-box-open text-4xl mb-2 block text-slate-300"></i>
                            Aucun stock trouvé.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
