@extends('layouts.purchases')

@section('title', 'Modifier un Type d\'Emballage')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('purchases.packagings.index') }}" class="text-purple-600 hover:text-purple-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Modifier l'Emballage</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('purchases.packagings.update', $packaging) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Nom de l'Emballage -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-cube mr-2"></i>Nom de l'Emballage *
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Ex: Boîte carton"
                    value="{{ old('name', $packaging->name) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Photo de l'emballage -->
            <div class="mb-6">
                <label for="image" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-image mr-2"></i>Photo de l'Emballage
                </label>

                @if($packaging->image)
                    <div class="mb-3 flex items-center gap-3">
                        <img src="{{ $packaging->image_url }}" alt="{{ $packaging->name }}"
                             onclick="openImageLightbox('{{ $packaging->image_url }}', '{{ $packaging->name }}')"
                             class="h-32 rounded-lg border border-gray-200 cursor-zoom-in hover:opacity-90 transition">
                        <label class="inline-flex items-center gap-2 text-sm text-red-600">
                            <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300">
                            Supprimer la photo actuelle
                        </label>
                    </div>
                @endif

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/*"
                    onchange="previewImage(this)"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('image') border-red-500 @enderror"
                >
                <p class="text-xs text-gray-500 mt-1">Choisissez un fichier pour remplacer la photo actuelle. JPG/PNG, 2 Mo max.</p>
                <img id="image-preview" src="" alt="Aperçu" class="mt-3 h-32 rounded-lg border border-gray-200 hidden">
                @error('image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-pencil mr-2"></i>Description (Optionnel)
                </label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Description du type d'emballage..."
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('description') border-red-500 @enderror"
                >{{ old('description', $packaging->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Boutons -->
            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Mettre à jour
                </button>
                <a href="{{ route('purchases.packagings.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        if (input.files && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);
            preview.classList.remove('hidden');
        }
    }
</script>
@endsection
