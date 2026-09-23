@extends('layouts.production')

@section('title', 'Production Catering du jour')
@section('page_title', 'Plats à produire — ' . $date->translatedFormat('l d/m/Y'))
@section('page_subtitle', 'Tous contrats catering actifs confondus')

@section('content')

<div class="flex justify-end mb-6">
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-calendar-day mr-1"></i>Afficher
        </button>
    </form>
</div>

@if(!empty($missingRecipeMeals) && $missingRecipeMeals->isNotEmpty())
<div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-4 mb-6 text-amber-800">
    <i class="fas fa-triangle-exclamation mr-2"></i>
    Besoins non calculés pour : <strong>{{ $missingRecipeMeals->join(', ') }}</strong> (aucune recette définie).
</div>
@endif

@if(!$cateringStock)
<div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 mb-6 text-red-700">
    <i class="fas fa-triangle-exclamation mr-2"></i>
    Aucun stock n'est assigné au module « Catering » — impossible de comparer les besoins à un stock. Configurez-le dans Stocks &rarr; Assigner un module.
</div>
@endif

<!-- Plats à produire par contrat -->
<div class="bg-white rounded-xl shadow-lg p-6 mb-8">
    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-utensils text-teal-500 mr-3"></i>Plats à produire aujourd'hui
    </h2>

    @forelse($byContract as $row)
        @php $contract = $row['contract']; @endphp
        <div class="border border-gray-200 rounded-xl p-4 mb-4">
            <p class="font-semibold text-gray-800 mb-3">
                <i class="fas fa-building text-slate-400 mr-1"></i>
                {{ $contract->client->name ?? '—' }}
                <span class="text-sm text-gray-400 font-normal">({{ $contract->guest_count }} convives)</span>
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach($row['slots'] as $slot)
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-1">
                            {{ match($slot['type']) { 'breakfast' => 'Petit-déjeuner', 'lunch' => 'Déjeuner', 'dinner' => 'Dîner', default => $slot['type'] } }}
                            — {{ $slot['quantity'] }} portions
                        </p>
                        @forelse($slot['dishes'] as $dish)
                            <p class="text-sm text-gray-700">{{ $dish->name }}</p>
                        @empty
                            <p class="text-sm text-gray-400 italic">Aucun plat</p>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-center text-gray-400 py-8">Aucun menu programmé pour cette date.</p>
    @endforelse
</div>

<!-- Besoins en produits -->
@if($cateringStock && !empty($comparison))
<div class="bg-white rounded-xl shadow-lg p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-boxes-stacked text-indigo-500 mr-3"></i>Besoins en produits — Stock « {{ $cateringStock->name }} »
    </h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Produit</th>
                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Besoin</th>
                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Disponible</th>
                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Manquant</th>
                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($comparison as $productId => $row)
                    @php $product = $products[$productId] ?? null; @endphp
                    <tr class="{{ $row['missing'] > 0 ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-2 font-semibold text-gray-800">{{ $product->name ?? '#' . $productId }}</td>
                        <td class="px-4 py-2 text-right text-gray-700">{{ round($row['needed'], 2) }} {{ $product?->unit?->name }}</td>
                        <td class="px-4 py-2 text-right {{ $row['missing'] > 0 ? 'text-red-600 font-bold' : 'text-emerald-700' }}">
                            {{ round($row['available'], 2) }} {{ $product?->unit?->name }}
                        </td>
                        <td class="px-4 py-2 text-right font-bold {{ $row['missing'] > 0 ? 'text-red-600' : 'text-gray-300' }}">
                            {{ $row['missing'] > 0 ? round($row['missing'], 2) . ' ' . $product?->unit?->name : '—' }}
                        </td>
                        <td class="px-4 py-2 text-right">
                            @if($row['missing'] > 0)
                                @php $sources = $transferSuggestions[$productId] ?? collect(); @endphp
                                @if($sources->isNotEmpty())
                                    @php $best = $sources->first(); @endphp
                                    <a href="{{ route('stock.transfer', [
                                            'source_stock_id' => $best->stock_id,
                                            'destination_stock_id' => $cateringStock->id,
                                            'product_id' => $productId,
                                            'quantity' => min($row['missing'], $best->quantity),
                                        ]) }}"
                                       class="inline-flex items-center gap-1 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                        <i class="fas fa-right-left"></i> Transférer depuis {{ $best->stock->name }}
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-semibold px-3 py-1.5 rounded-lg">
                                        <i class="fas fa-cart-plus"></i> À acheter
                                    </span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
