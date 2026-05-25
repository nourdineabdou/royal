@extends('layouts.stock')

@section('title', 'Mouvements — ' . $stock->name)
@section('header', 'Mouvements — ' . $stock->name)

@section('header-actions')
    <a href="{{ route('stock.show', $stock) }}"
       class="bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-boxes-stacked"></i> Inventaire
    </a>
@endsection

@section('content')
<div class="py-4 space-y-5">

    {{-- breadcrumb --}}
    <nav class="text-xs text-slate-500 flex items-center gap-1">
        <a href="{{ route('stock.index') }}" class="hover:text-emerald-600">Stocks</a>
        <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
        <a href="{{ route('stock.show', $stock) }}" class="hover:text-emerald-600">{{ $stock->name }}</a>
        <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
        <span class="text-slate-700 font-medium">Mouvements</span>
    </nav>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
                <select name="type" class="w-full border border-slate-200 rounded-lg px-2 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-300">
                    <option value="">Tous types</option>
                    <option value="in"       {{ $filters['type'] ?? '' === 'in'       ? 'selected' : '' }}>Entrée</option>
                    <option value="out"      {{ $filters['type'] ?? '' === 'out'      ? 'selected' : '' }}>Sortie</option>
                    <option value="transfer" {{ $filters['type'] ?? '' === 'transfer' ? 'selected' : '' }}>Transfert</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Origine</label>
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
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="bg-emerald-600 text-white text-xs px-4 py-2 rounded-lg hover:bg-emerald-700 font-semibold">
                <i class="fa-solid fa-filter mr-1"></i> Filtrer
            </button>
            <a href="{{ route('stock.movements', $stock) }}" class="text-xs border border-slate-200 px-4 py-2 rounded-lg hover:bg-slate-50">
                Reset
            </a>
        </div>
    </form>

    {{-- Results count --}}
    <p class="text-xs text-slate-500">{{ $movements->total() }} mouvement(s) trouvé(s)</p>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Date</th>
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
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400 text-sm">
                            <i class="fa-solid fa-arrows-rotate text-4xl mb-2 block text-slate-300"></i>
                            Aucun mouvement pour ce stock.
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
</div>
@endsection
