@extends('layouts.production')

@section('title', 'Créer un Accompagnement')
@section('page_title', 'Créer un Accompagnement')
@section('page_subtitle', 'Ajouter une nouvelle option')

@section('content')
<div class="max-w-2xl">

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form action="{{ route('accompaniments.store') }}" method="POST">
                @csrf

                <!-- Name Field -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-heading text-yellow-500 mr-2"></i>Nom de l'Accompagnement
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        placeholder="Ex: Frites, Riz, Légumes grillés..."
                        class="w-full px-4 py-3 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition text-lg"
                        required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-2">Le nom doit être unique</p>
                </div>

                <!-- Type Field -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-layer-group text-indigo-500 mr-2"></i>Type de Sélection
                    </label>
                    <select id="type" name="type"
                        class="w-full px-4 py-3 border @error('type') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition text-lg"
                        required>
                        <option value="">-- Sélectionner un type --</option>
                        <option value="single" {{ old('type') === 'single' ? 'selected' : '' }}>
                            <i class="fas fa-circle"></i> Choix unique (le client n'en choisit qu'un)
                        </option>
                        <option value="multiple" {{ old('type') === 'multiple' ? 'selected' : '' }}>
                            <i class="fas fa-layer-group"></i> Choix multiple (le client peut en choisir plusieurs)
                        </option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-2">
                        <strong>Exemple unique:</strong> Sauce (mayo, mustard, ketchup) - le client en choisit une<br>
                        <strong>Exemple multiple:</strong> Options supplémentaires (fromage, bacon, sauce) - le client peut en ajouter plusieurs
                    </p>
                </div>

                <!-- Price Field -->
                <div class="mb-8">
                    <label for="price" class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-dinar text-green-500 mr-2"></i>Prix Supplémentaire (MRU)
                    </label>
                    <input type="number" id="price" name="price" value="{{ old('price') }}"
                        placeholder="Ex: 1.50" step="0.01" min="0"
                        class="w-full px-4 py-3 border @error('price') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition text-lg"
                        required>
                    @error('price')
                        <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-2">Laissez 0 si c'est gratuit</p>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-6 py-3 rounded-lg font-bold hover:shadow-lg transition duration-300 flex items-center justify-center text-lg">
                        <i class="fas fa-save mr-2"></i>Créer l'Accompagnement
                    </button>
                    <a href="{{ route('accompaniments.index') }}" class="flex-1 bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-bold hover:bg-gray-400 transition duration-300 text-center">
                        Annuler
                    </a>
                </div>
            </form>
</div>
@endsection
