@extends('layouts.production')

@section('title', 'Éditer un Repas')
@section('page_title', 'Éditer un Repas')
@section('page_subtitle', 'Modifiez les détails du repas')

@section('content')
<div class="max-w-4xl">

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form action="{{ route('meals.update', $meal) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Name Field -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-heading text-blue-500 mr-2"></i>Nom du Repas
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $meal->name) }}"
                        placeholder="Ex: Couscous Royale"
                        class="w-full px-4 py-3 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price Field -->
                <div class="mb-6">
                    <label for="price" class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-dinar text-green-500 mr-2"></i>Prix (MRU)
                    </label>
                    <input type="number" id="price" name="price" value="{{ old('price', $meal->price) }}"
                        placeholder="Ex: 12.50" step="0.01" min="0"
                        class="w-full px-4 py-3 border @error('price') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        required>
                    @error('price')
                        <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category Field -->
                <div class="mb-6">
                    <label for="category_id" class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-layer-group text-indigo-500 mr-2"></i>Catégorie
                    </label>
                    <select id="category_id" name="category_id"
                        class="w-full px-4 py-3 border @error('category_id') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $meal->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Field -->
                <div class="mb-6">
                    <label for="image" class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-image text-purple-500 mr-2"></i>Image du Repas
                    </label>

                    <!-- Current Image -->
                    @if($meal->image)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">Image actuelle:</p>
                            <img src="{{ asset('storage/' . $meal->image) }}" alt="{{ $meal->name }}" class="rounded-lg max-w-xs h-auto shadow-lg">
                        </div>
                    @endif

                    <div class="relative">
                        <input type="file" id="image" name="image" accept="image/*"
                            class="w-full px-4 py-3 border @error('image') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition cursor-pointer"
                            onchange="previewImage(event)">
                        <p class="text-gray-500 text-sm mt-2">Formats acceptés: JPEG, PNG, GIF (max 2 MB) - Laissez vide pour garder l'image actuelle</p>
                    </div>

                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-4 hidden">
                        <p class="text-sm text-gray-600 mb-2">Aperçu de la nouvelle image:</p>
                        <img id="previewImg" src="" alt="Aperçu" class="rounded-lg max-w-xs h-auto shadow-lg">
                    </div>

                    @error('image')
                        <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Accompaniments Field -->
                <div class="mb-8">
                    <label for="accompaniments" class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>Accompagnements
                    </label>
                    <div class="border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto bg-gray-50">
                        <div class="space-y-2">
                            @forelse($accompaniments as $accompaniment)
                                <label class="flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded transition">
                                    <input type="checkbox" name="accompaniments[]" value="{{ $accompaniment->id }}"
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                        {{ $meal->accompaniments->contains($accompaniment->id) ? 'checked' : '' }}>
                                    <span class="ml-3 text-gray-700">
                                        {{ $accompaniment->name }}
                                        <span class="text-gray-500 text-sm">({{ $accompaniment->price }} MRU)</span>
                                    </span>
                                </label>
                            @empty
                                <p class="text-gray-500 text-sm">Aucun accompagnement disponible. <a href="{{ route('accompaniments.create') }}" class="text-blue-600 hover:underline">Créer un</a></p>
                            @endforelse
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm mt-2">Sélectionnez les accompagnements disponibles avec ce repas</p>
                    @error('accompaniments')
                        <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg font-bold hover:shadow-lg transition duration-300 flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>Mettre à jour
                    </button>
                    <a href="{{ route('meals.index') }}" class="flex-1 bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-bold hover:bg-gray-400 transition duration-300 text-center">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
