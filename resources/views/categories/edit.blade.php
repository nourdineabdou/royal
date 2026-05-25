@extends('layouts.production')

@section('title', 'Éditer une Catégorie')
@section('page_title', 'Éditer une Catégorie')
@section('page_subtitle', 'Modifiez les détails de la catégorie')

@section('content')
<div class="max-w-2xl">

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Name Field -->
                <div class="mb-8">
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-3">
                        <i class="fas fa-heading text-green-500 mr-2"></i>Nom de la Catégorie
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}"
                        placeholder="Ex: Entrées, Plats principaux, Desserts..."
                        class="w-full px-4 py-3 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition text-lg"
                        required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Section -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                    <p class="text-blue-800 text-sm">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Information:</strong> Cette catégorie contient {{ $category->meals_count ?? 0 }} repas
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg font-bold hover:shadow-lg transition duration-300 flex items-center justify-center text-lg">
                        <i class="fas fa-save mr-2"></i>Mettre à jour
                    </button>
                    <a href="{{ route('categories.index') }}" class="flex-1 bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-bold hover:bg-gray-400 transition duration-300 text-center">
                        Annuler
                    </a>
                </div>
            </form>
</div>
@endsection
