@extends('layouts.parameters')

@section('title', 'Modifier un Type de Paiement')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('payment-types.index') }}" class="text-green-600 hover:text-green-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Modifier le Type de Paiement</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('payment-types.update', $paymentType) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Nom du Type -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-credit-card mr-2"></i>Nom du Type de Paiement *
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Ex: Espèce, Carte Bancaire, Chèque"
                    value="{{ old('name', $paymentType->name) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-pencil mr-2"></i>Description (Optionnel)
                </label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Description du type de paiement..."
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('description') border-red-500 @enderror"
                >{{ old('description', $paymentType->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Statut -->
            <div class="mb-6 flex items-center">
                <label for="is_active" class="flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $paymentType->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 text-green-600 rounded focus:ring-2 focus:ring-green-500"
                    >
                    <span class="ml-3 text-sm font-bold text-gray-700">
                        <i class="fas fa-check-circle mr-2"></i>Activer ce type de paiement
                    </span>
                </label>
            </div>

            <!-- Boutons -->
            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Mettre à jour
                </button>
                <a href="{{ route('payment-types.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
