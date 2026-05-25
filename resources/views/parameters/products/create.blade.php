@extends('layouts.production')

@section('title', 'Ajouter un Produit')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Ajouter un Nouveau Produit</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf

            <!-- Nom du Produit -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-box mr-2"></i>Nom du Produit *
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Ex: Tomate fraîche"
                    value="{{ old('name') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Unité -->
            <div class="mb-4">
                <label for="unit_id" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-ruler mr-2"></i>Unité de Mesure *
                </label>
                <select
                    id="unit_id"
                    name="unit_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('unit_id') border-red-500 @enderror"
                    required
                >
                    <option value="">-- Sélectionnez une unité --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }} ({{ $unit->abbreviation }})
                        </option>
                    @endforeach
                </select>
                @error('unit_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Emballage -->
            <div class="mb-4">
                <label for="packaging_id" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-cube mr-2"></i>Type d'Emballage *
                </label>
                <select
                    id="packaging_id"
                    name="packaging_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('packaging_id') border-red-500 @enderror"
                    required
                >
                    <option value="">-- Sélectionnez un emballage --</option>
                    @foreach($packagings as $packaging)
                        <option value="{{ $packaging->id }}" {{ old('packaging_id') == $packaging->id ? 'selected' : '' }}>
                            {{ $packaging->name }}
                        </option>
                    @endforeach
                </select>
                @error('packaging_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Enregistrer
                </button>
                <a href="{{ route('products.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
