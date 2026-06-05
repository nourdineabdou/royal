@extends('layouts.production')

@section('title', 'Modifier un Produit')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Modifier le Produit</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('products.update', $product) }}" method="POST">
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
                    placeholder="Ex: Tomate fraîche"
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
                        <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
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
                        <option value="{{ $packaging->id }}" {{ old('packaging_id', $product->packaging_id) == $packaging->id ? 'selected' : '' }}>
                            {{ $packaging->name }}
                        </option>
                    @endforeach
                </select>
                @error('packaging_id')
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
                    <p class="text-xs text-gray-500 mt-1">Obligatoire uniquement si le produit est consommable.</p>
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
                <a href="{{ route('products.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const consumableCheckbox = document.getElementById('is_consumable');
    const salePriceInput = document.getElementById('sale_price');

    function syncConsumableUi() {
        salePriceInput.required = consumableCheckbox.checked;
        if (!consumableCheckbox.checked) {
            salePriceInput.value = '';
        }
    }

    consumableCheckbox.addEventListener('change', syncConsumableUi);
    syncConsumableUi();
</script>
@endsection
