@extends('layouts.production')

@section('title', $product->name)

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">{{ $product->name }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Détails Principaux -->
        <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Informations du Produit</h2>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <!-- Nom -->
                <div class="border-l-4 border-indigo-600 pl-4">
                    <p class="text-gray-600 text-sm">Nom du Produit</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $product->name }}</p>
                </div>

                <!-- Unité -->
                <div class="border-l-4 border-blue-600 pl-4">
                    <p class="text-gray-600 text-sm">Unité de Mesure</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $product->unit->name }}
                        <span class="text-sm text-gray-500">({{ $product->unit->abbreviation }})</span>
                    </p>
                </div>

                <!-- Emballage -->
                <div class="border-l-4 border-purple-600 pl-4">
                    <p class="text-gray-600 text-sm">Type d'Emballage</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $product->packaging?->name ?? '—' }}</p>
                </div>

                <!-- Prix -->
                <div class="border-l-4 border-green-600 pl-4">
                    <p class="text-gray-600 text-sm">Prix Unitaire</p>
                    <p class="text-lg font-semibold text-green-600">—</p>
                </div>
            </div>

            <!-- Stock -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <p class="text-gray-600 text-sm mb-2">Stocks associés</p>
                <p class="text-sm text-gray-500">Consultez le module Stock pour voir les quantités par dépôt.</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                @can('products.edit')
                <a href="{{ route('products.edit', $product) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 text-center">
                    <i class="fas fa-edit mr-2"></i>Modifier
                </a>
                @endcan
                @can('products.delete')
                <form action="{{ route('products.destroy', $product) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-trash mr-2"></i>Supprimer
                    </button>
                </form>
                @endcan
            </div>
        </div>

        <!-- Informations Complémentaires -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Résumé</h3>

            <div class="space-y-4">
                <div class="bg-indigo-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 mb-1">Valeur Totale Stock</p>
                    <p class="text-2xl font-bold text-indigo-600">—</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">Dates</p>
                    <p class="text-xs text-gray-500 mb-1">
                        <i class="fas fa-calendar mr-2"></i>Créé le {{ $product->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-pencil mr-2"></i>Modifié le {{ $product->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
