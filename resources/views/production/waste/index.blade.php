@extends('layouts.production')

@section('title', 'Produits périmés/gâtés')
@section('page_title', 'Sorties de stock pour pertes')
@section('page_subtitle', 'Historique des produits retirés du stock (périmés, gâtés, cassés...)')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold">Produits périmés/gâtés</h2>
    <a href="{{ route('production.waste.create') }}" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-semibold shadow">Nouvelle sortie</a>
</div>
@if(session('success'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif
<table class="min-w-full bg-white rounded-xl shadow overflow-hidden">
    <thead class="bg-orange-100">
        <tr>
            <th class="px-4 py-2">Date</th>
            <th class="px-4 py-2">Produit</th>
            <th class="px-4 py-2">Stock</th>
            <th class="px-4 py-2">Quantité</th>
            <th class="px-4 py-2">Raison</th>
            <th class="px-4 py-2">Validé par</th>
        </tr>
    </thead>
    <tbody>
        @forelse($wastes as $waste)
        <tr class="border-b">
            <td class="px-4 py-2">
                @if($waste->validated_at)
                    {{ \Illuminate\Support\Carbon::parse($waste->validated_at)->format('d/m/Y H:i') }}
                @endif
            </td>
            <td class="px-4 py-2">{{ $waste->product->name ?? '-' }}</td>
            <td class="px-4 py-2">{{ $waste->stock->name ?? '-' }}</td>
            <td class="px-4 py-2">{{ $waste->quantity }}</td>
            <td class="px-4 py-2">{{ $waste->reason }}</td>
            <td class="px-4 py-2">{{ $waste->user->name ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center py-4 text-gray-500">Aucune sortie de stock enregistrée.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="mt-4">{{ $wastes->links() }}</div>
@endsection
