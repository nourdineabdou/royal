@extends('layouts.production')

@section('title', 'Gestion des Repas')
@section('page_title', 'Gestion des Repas')
@section('page_subtitle', 'Gérez tous vos repas et plats disponibles')

@section('content')
<div class="flex justify-end mb-6">
    @can('meals.create')
    <a href="{{ route('meals.create') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition duration-300 flex items-center">
        <i class="fas fa-plus mr-2"></i>Ajouter Repas
    </a>
    @endcan
</div>

<!-- Search and Filter -->
<div class="bg-white rounded-xl shadow-lg p-6 mb-8">
    <form action="{{ route('meals.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom du repas..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
            <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center justify-center">
                <i class="fas fa-search mr-2"></i>Rechercher
            </button>
        </div>
    </form>
</div>

<!-- Meals Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    @forelse($meals as $meal)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
            <!-- Image -->
            <div class="relative h-48 bg-gray-200 overflow-hidden group">
                @if($meal->image)
                    <img src="{{ asset('storage/' . $meal->image) }}" alt="{{ $meal->name }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-300 to-gray-400">
                        <i class="fas fa-image text-gray-500 text-4xl"></i>
                    </div>
                @endif

                <!-- Action Buttons Overlay -->
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition duration-300 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                    @can('meals.edit')
                    <a href="{{ route('meals.edit', $meal) }}" class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="{{ route('meals.recipe.edit', $meal) }}" class="px-3 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition" title="Recette">
                        <i class="fas fa-list-ul"></i>
                    </a>
                    @endcan
                    <a href="{{ route('meals.show', $meal) }}" class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                        <i class="fas fa-eye"></i>
                    </a>
                    @can('meals.delete')
                    <form action="{{ route('meals.destroy', $meal) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr ?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endcan
                </div>
            </div>

            <!-- Content -->
            <div class="p-4">
                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $meal->name }}</h3>
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full font-medium">
                        {{ $meal->category->name ?? 'Sans catégorie' }}
                    </span>
                    <span class="text-2xl font-bold text-green-600">{{ number_format($meal->price, 2) }} MRU</span>
                </div>

                <!-- Accompaniments -->
                @if($meal->accompaniments->count() > 0)
                    <div class="text-xs text-gray-600 mb-3">
                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                        {{ $meal->accompaniments->count() }} accompagnement(s)
                    </div>
                @endif

                <!-- Edit Button -->
                @can('meals.edit')
                <a href="{{ route('meals.edit', $meal) }}" class="w-full block text-center bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition duration-300 font-medium">
                    Éditer
                </a>
                @endcan
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-xl shadow-lg p-12 text-center">
            <i class="fas fa-utensils text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">Aucun repas trouvé</p>
            @can('meals.create')
            <a href="{{ route('meals.create') }}" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                Créer le premier repas
            </a>
            @endcan
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="bg-white rounded-xl shadow-lg p-6">
    {{ $meals->links() }}
</div>
@endsection
