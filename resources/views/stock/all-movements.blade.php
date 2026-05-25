@extends('layouts.stock')

@section('title', 'Tous les mouvements')
@section('header', 'Tous les mouvements de stock')

@section('content')
<div class="py-4 space-y-5">

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Stock</label>
                <select name="stock_id" class="w-full border border-slate-200 rounded-lg px-2 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-300">
                    <option value="">Tous stocks</option>
                    @foreach($stocks as $s)
                        <option value="{{ $s->id }}" {{ ($filters['stock_id'] ?? '') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
                <select name="type" class="w-full border border-slate-200 rounded-lg px-2 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-300">
                    <option value="">Tous types</option>
                    <option value="in"       {{ ($filters['type'] ?? '') === 'in'       ? 'selected' : '' }}>Entrée</option>
                    <option value="out"      {{ ($filters['type'] ?? '') === 'out'      ? 'selected' : '' }}>Sortie</option>
                    <option value="transfer" {{ ($filters['type'] ?? '') === 'transfer' ? 'selected' : '' }}>Transfert</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Origine module</label>
                <select name="origin_module" class="w-full border border-slate-200 rounded-lg px-2 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-300">
                    <option value="">Toutes origines</option>
                    <option value="restaurant" {{ ($filters['origin_module'] ?? '') === 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                    <option value="catering"   {{ ($filters['origin_module'] ?? '') === 'catering'   ? 'selected' : '' }}>Catering</option>
                    <option value="events"     {{ ($filters['origin_module'] ?? '') === 'events'     ? 'selected' : '' }}>Événements</option>
                    <option value="transfer"   {{ ($filters['origin_module'] ?? '') === 'transfer'   ? 'selected' : '' }}>Transfert</option>
                    <option value="purchase"   {{ ($filters['origin_module'] ?? '') === 'purchase'   ? 'selected' : '' }}>Achat</option>
                    <option value="manual"     {{ ($filters['origin_module'] ?? '') === 'manual'     ? 'selected' : '' }}>Manuel</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Produit</label>
                <select name="product_id" class="w-full border border-slate-200 rounded-lg px-2 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-300">
                    <option value="">Tous produits</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ ($filters['product_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Du</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}"
                       class="w-full border border-slate-200 rounded-lg px-2 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-300">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Au</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}"
                       class="w-full border border-slate-200 rounded-lg px-2 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-300">
            </div>
               <div class="flex items-center mt-4">
                   <input type="checkbox" id="cumule" name="cumule" value="1" {{ request('cumule') ? 'checked' : '' }} class="mr-2">
                   <label for="cumule" class="text-xs font-medium text-slate-500 mb-1">Cumulé</label>
               </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="bg-emerald-600 text-white text-xs px-4 py-2 rounded-lg hover:bg-emerald-700 font-semibold">
                <i class="fa-solid fa-filter mr-1"></i> Filtrer
            </button>
            <a href="{{ route('stock.all-movements') }}" class="text-xs border border-slate-200 px-4 py-2 rounded-lg hover:bg-slate-50">
                Reset
            </a>
            <a href="{{ route('stock.export.movements') }}?{{ http_build_query(request()->query()) }}"
               class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-semibold flex items-center gap-1">
                <i class="fa-solid fa-file-csv"></i> Exporter CSV
            </a>
        </div>
    </form>

    @if(!empty($cumule) && $cumule)
        {{-- Tableau cumulé par produit --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3">Produit</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3 text-right">Quantité totale</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($cumuls as $cumul)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $cumul->product?->name ?? '—' }}
                                <span class="text-xs text-slate-400 ml-1">{{ $cumul->product?->unit?->name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($cumul->type === 'in')
                                    <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                                        <i class="fa-solid fa-arrow-down mr-1"></i>Entrée
                                    </span>
                                @elseif($cumul->type === 'out')
                                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">
                                        <i class="fa-solid fa-arrow-up mr-1"></i>Sortie
                                    </span>
                                @else
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">
                                        <i class="fa-solid fa-right-left mr-1"></i>Transfert
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold {{ $cumul->type === 'out' ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $cumul->type === 'out' ? '-' : '+' }}{{ number_format($cumul->total_quantity, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-slate-400 text-sm">
                                <i class="fa-solid fa-arrows-rotate text-4xl mb-2 block text-slate-300"></i>
                                Aucun cumul trouvé.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- Table classique --}}
        <p class="text-xs text-slate-500">{{ $movements->total() }} mouvement(s) trouvé(s)</p>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Produit</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Origine</th>
                            <th class="px-4 py-3 text-right">Quantité</th>
                            <th class="px-4 py-3">Notes / Référence</th>
                            <th class="px-4 py-3">Utilisateur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($movements as $mv)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                {{ $mv->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <a href="{{ route('stock.movements', $mv->stock_id) }}"
                                   class="font-medium text-emerald-700 hover:underline">
                                    {{ $mv->stock?->name ?? '—' }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $mv->product?->name ?? '—' }}
                                <span class="text-xs text-slate-400 ml-1">{{ $mv->product?->unit?->name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($mv->type === 'in')
                                    <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                                        <i class="fa-solid fa-arrow-down mr-1"></i>Entrée
                                    </span>
                                @elseif($mv->type === 'out')
                                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">
                                        <i class="fa-solid fa-arrow-up mr-1"></i>Sortie
                                    </span>
                                @else
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">
                                        <i class="fa-solid fa-right-left mr-1"></i>Transfert
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $origColors = [
                                        'restaurant' => 'blue',
                                        'catering'   => 'purple',
                                        'events'     => 'orange',
                                        'transfer'   => 'teal',
                                        'purchase'   => 'indigo',
                                        'manual'     => 'slate',
                                    ];
                                    $oc = $origColors[$mv->origin_module] ?? 'slate';
                                @endphp
                                @if($mv->origin_module)
                                    <span class="text-xs bg-{{ $oc }}-100 text-{{ $oc }}-700 px-2 py-0.5 rounded-full">
                                        {{ $mv->origin_module_label }}
                                    </span>
                                    @if($mv->origin_id)
                                        <span class="text-xs text-slate-400 ml-1">#{{ $mv->origin_id }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                                @if($mv->type === 'transfer')
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        {{ $mv->sourceStock?->name ?? '?' }} → {{ $mv->destinationStock?->name ?? '?' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold {{ $mv->type === 'out' ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $mv->type === 'out' ? '-' : '+' }}{{ number_format($mv->quantity, 2) }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 max-w-xs truncate">
                                {{ $mv->notes ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                {{ $mv->user?->name ?? '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-slate-400 text-sm">
                                <i class="fa-solid fa-arrows-rotate text-4xl mb-2 block text-slate-300"></i>
                                Aucun mouvement trouvé.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($movements->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
            @endif
        </div>
    @endif
</div>
@endsection
