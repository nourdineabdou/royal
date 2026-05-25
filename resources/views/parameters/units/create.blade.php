@extends('layouts.production')

@section('title', 'Ajouter une Unité')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('units.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Ajouter une Nouvelle Unité</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('units.store') }}" method="POST">
            @csrf

            <!-- Nom de l'Unité -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-ruler mr-2"></i>Nom de l'Unité *
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Ex: Kilogramme"
                    value="{{ old('name') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Abréviation -->
            <div class="mb-6">
                <label for="abbreviation" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-font mr-2"></i>Abréviation *
                </label>
                <input
                    type="text"
                    id="abbreviation"
                    name="abbreviation"
                    placeholder="Ex: kg"
                    maxlength="10"
                    value="{{ old('abbreviation') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('abbreviation') border-red-500 @enderror"
                    required
                >
                @error('abbreviation')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Enregistrer
                </button>
                <a href="{{ route('units.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
