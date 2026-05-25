@extends('layouts.production')

@section('title', 'Créer une Catégorie')
@section('page_title', 'Créer une Catégorie')
@section('page_subtitle', 'Ajouter une nouvelle catégorie')

@section('content')
<div class="max-w-2xl">

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf

                <!-- Name Field -->
                <div class="mb-8">
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-3">
                        <i class="fas fa-heading text-green-500 mr-2"></i>Nom de la Catégorie
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        placeholder="Ex: Entrées, Plats principaux, Desserts..."
                        class="w-full px-4 py-3 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition text-lg"
                        required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-2">Le nom doit être unique et descriptif</p>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg font-bold hover:shadow-lg transition duration-300 flex items-center justify-center text-lg">
                        <i class="fas fa-save mr-2"></i>Créer la Catégorie
                    </button>
                    <a href="{{ route('categories.index') }}" class="flex-1 bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-bold hover:bg-gray-400 transition duration-300 text-center">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="mt-8 bg-green-50 border-2 border-green-200 rounded-xl p-6">
            <p class="text-green-800">
                <i class="fas fa-lightbulb mr-2"></i>
                <strong>Conseil:</strong> Les catégories permettent d'organiser vos repas. Par exemple: Entrées, Plats chauds, Salades, Desserts, Boissons, etc.
            </p>
</div>
@endsection
