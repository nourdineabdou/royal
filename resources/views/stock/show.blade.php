@extends('layouts.stock')

@section('title', $stock->name . ' — Produits')
@section('header', $stock->name)

@section('header-actions')
    <a href="{{ route('stock.movements', $stock) }}"
       class="bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-arrows-rotate"></i> Mouvements
    </a>
    <a href="{{ route('stock.export.stock-inventory', $stock) }}"
       class="bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-file-csv"></i> Exporter CSV
    </a>
    @can('stock.products.adjust')
    <button onclick="document.getElementById('addModal').style.display='flex'"
            class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Ajustement
    </button>
    @endcan
@endsection

@section('content')
<div class="py-4 space-y-5">

    {{-- breadcrumb --}}
    <nav class="text-xs text-slate-500 flex items-center gap-1">
        <a href="{{ route('stock.index') }}" class="hover:text-emerald-600">Stocks</a>
        <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
        <span class="text-slate-700 font-medium">{{ $stock->name }}</span>
        @if($stock->module)
            <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                {{ \App\Models\Stock::MODULES[$stock->module] ?? $stock->module }}
            </span>
        @endif
    </nav>

    {{-- search --}}
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Rechercher un produit…"
               class="flex-1 max-w-xs border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
        <button class="bg-emerald-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-emerald-700">
            <i class="fa-solid fa-search"></i>
        </button>
        @if(request('search'))
            <a href="{{ route('stock.show', $stock) }}" class="text-sm px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50">Reset</a>
        @endif
    </form>

    {{-- KPI --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Produits</p>
                <p class="text-xl font-bold text-slate-800">{{ $items->total() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Emplacement</p>
                <p class="text-sm font-semibold text-slate-700">{{ $stock->location ?: 'Non défini' }}</p>
            </div>
        </div>
    </div>

    {{-- Products table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100">
        <div class="px-5 py-3 border-b border-slate-100">
            <h2 class="font-semibold text-slate-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-list text-emerald-500"></i>
                Inventaire — {{ $stock->name }}
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3">Produit</th>
                        <th class="px-4 py-3">Unité</th>
                        <th class="px-4 py-3 text-right">Quantité en stock</th>
                        <th class="px-4 py-3 text-right">État</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">
                            <i class="fa-solid fa-cube text-slate-300 mr-1"></i>
                            {{ $item->product?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">
                            {{ $item->product?->unit?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold {{ $item->quantity <= 0 ? 'text-red-600' : ($item->quantity < 5 ? 'text-amber-600' : 'text-slate-800') }}">
                            {{ number_format($item->quantity, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($item->quantity <= 0)
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Rupture</span>
                            @elseif($item->quantity < 5)
                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Faible</span>
                            @else
                                <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">OK</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-400 text-sm">
                            <i class="fa-solid fa-box-open text-4xl mb-2 block text-slate-300"></i>
                            Aucun produit dans ce stock.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $items->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ── Ajustement Modal ──────────────────────────────────────────────────── --}}
<div id="addModal" style="display:none"
     class="fixed inset-0 bg-black/50 z-50 items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-slate-800">Ajustement manuel de stock</h3>
            <button onclick="document.getElementById('addModal').style.display='none'"
                    class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>
        <form method="POST" action="{{ route('stock.products.add', $stock) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Produit</label>
                    <select name="product_id" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
                        <option value="">Sélectionner…</option>
                        @foreach($items as $item)
                            <option value="{{ $item->product_id }}">{{ $item->product?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
                    <select name="type" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
                        <option value="in">Entrée (+)</option>
                        <option value="out">Sortie (-)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Quantité</label>
                    <input type="number" name="quantity" step="0.01" min="0.01" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Notes (optionnel)</label>
                    <textarea name="notes" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300"
                              placeholder="Raison de l'ajustement…"></textarea>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" onclick="document.getElementById('addModal').style.display='none'"
                            class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50">Annuler</button>
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-semibold">
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
