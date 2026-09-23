@extends('layouts.purchases')

@section('title', 'Modifier un Produit')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('purchases.products.index') }}" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Modifier le Produit</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('purchases.products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Nom du Produit -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-box mr-2"></i>Nom du Produit *
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Ex: Eau 0,5L"
                    value="{{ old('name', $product->name) }}"
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
                    <i class="fas fa-ruler mr-2"></i>Unité de mesure (stock / recettes) *
                </label>
                <select
                    id="unit_id"
                    name="unit_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('unit_id') border-red-500 @enderror"
                    required
                >
                    <option value="">-- Sélectionnez une unité --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }} ({{ $unit->symbol }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Doit toujours être une unité physique divisible (kg, g, L, mL, pièce...). C'est cette unité qui est utilisée dans le stock et les recettes — jamais un emballage comme "bouteille" ou "caisse".</p>
                @error('unit_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Emballages (achat/vente) -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-cube mr-2"></i>Emballages (achat / vente)
                </label>
                <p class="text-xs text-gray-500 mb-2">Un produit peut avoir plusieurs emballages (ex: Bouteille = 0,5 L, Caisse = 6 L). Chaque emballage indique combien d'unités de base (ci-dessus) il contient — sert à convertir les achats/ventes en stock.</p>

                @php
                    $oldPackagingIds = old('packaging_id');
                    $oldPackagingQty = old('packaging_quantity');
                    $existingRows = $oldPackagingIds !== null
                        ? collect($oldPackagingIds)->map(fn($id, $i) => ['packaging_id' => $id, 'quantity' => $oldPackagingQty[$i] ?? null])
                        : $product->productPackagings->map(fn($pp) => ['packaging_id' => $pp->packaging_id, 'quantity' => $pp->quantity]);
                @endphp

                <div id="packaging-rows" class="space-y-2 mb-2">
                    @foreach($existingRows as $row)
                        <div class="packaging-row flex items-center gap-2">
                            <select name="packaging_id[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Emballage --</option>
                                @foreach($packagings as $packaging)
                                    <option value="{{ $packaging->id }}" {{ $row['packaging_id'] == $packaging->id ? 'selected' : '' }}>{{ $packaging->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="0.01" min="0.01" name="packaging_quantity[]"
                                   value="{{ $row['quantity'] }}"
                                   placeholder="Qté en unité de base (ex: 6)"
                                   class="w-56 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="button" onclick="this.closest('.packaging-row').remove()" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-packaging-row" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-plus mr-1"></i> Ajouter un emballage
                </button>
                @error('packaging_id.*')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('packaging_quantity.*')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Options de vente libre -->
            <div class="mb-4 p-4 rounded-lg border border-amber-200 bg-amber-50">
                <label class="inline-flex items-center gap-2 text-sm font-bold text-gray-700 mb-3">
                    <input type="checkbox" id="is_consumable" name="is_consumable" value="1"
                           {{ old('is_consumable', $product->is_consumable) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                    Produit consommable (vendable en POS catering)
                </label>

                <div>
                    <label for="sale_price" class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-tags mr-2"></i>Prix de vente unitaire (MRU)
                    </label>
                    <input
                        type="number"
                        id="sale_price"
                        name="sale_price"
                        min="0"
                        step="0.01"
                        value="{{ old('sale_price', $product->sale_price) }}"
                        placeholder="Ex: 150"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 @error('sale_price') border-red-500 @enderror"
                    >
                    <p class="text-xs text-gray-500 mt-1">Optionnel — laissez vide si le prix n'est pas encore fixé (une alerte s'affichera à la vente tant qu'il n'est pas renseigné).</p>
                    @error('sale_price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Mettre à jour
                </button>
                <a href="{{ route('purchases.products.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<template id="packaging-row-template">
    <div class="packaging-row flex items-center gap-2">
        <select name="packaging_id[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">-- Emballage --</option>
            @foreach($packagings as $packaging)
                <option value="{{ $packaging->id }}">{{ $packaging->name }}</option>
            @endforeach
        </select>
        <input type="number" step="0.01" min="0.01" name="packaging_quantity[]"
               placeholder="Qté en unité de base (ex: 6)"
               class="w-56 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="button" onclick="this.closest('.packaging-row').remove()" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</template>

<script>
    document.getElementById('add-packaging-row').addEventListener('click', function () {
        const tpl = document.getElementById('packaging-row-template').content.cloneNode(true);
        document.getElementById('packaging-rows').appendChild(tpl);
    });

    const consumableCheckbox = document.getElementById('is_consumable');
    const salePriceInput = document.getElementById('sale_price');

    function syncConsumableUi() {
        if (!consumableCheckbox.checked) {
            salePriceInput.value = '';
        }
    }

    consumableCheckbox.addEventListener('change', syncConsumableUi);
    syncConsumableUi();
</script>
@endsection
