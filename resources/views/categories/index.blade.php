@extends('layouts.production')

@section('title', 'Gestion des Catégories')
@section('page_title', 'Gestion des Catégories')
@section('page_subtitle', 'Organisez vos repas par catégorie')

@section('content')
<div class="flex justify-end mb-6">
    @can('categories.create')
    <a href="{{ route('categories.create') }}" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition duration-300 flex items-center">
        <i class="fas fa-plus mr-2"></i>Ajouter Cat\u00e9gorie
    </a>
    @endcan
</div>

<!-- Search -->
<div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <form action="{{ route('categories.index') }}" method="GET" class="flex gap-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une catégorie..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-300">
                    <i class="fas fa-search mr-2"></i>Chercher
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                    <tr>
                        <th class="text-left py-4 px-6 font-bold">#</th>
                        <th class="text-left py-4 px-6 font-bold">Nom</th>
                        <th class="text-left py-4 px-6 font-bold">Nombre de Repas</th>
                        <th class="text-left py-4 px-6 font-bold">Créée le</th>
                        <th class="text-right py-4 px-6 font-bold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="py-4 px-6 font-mono text-sm text-gray-600">{{ $category->id }}</td>
                            <td class="py-4 px-6">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                    <span class="font-semibold text-gray-800">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-gray-600">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                                    {{ $category->meals_count ?? 0 }} repas
                                </span>
                            </td>
                            <td class="py-4 px-6 text-gray-600 text-sm">
                                {{ $category->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex gap-2 justify-end">
                                    @can('categories.edit')
                                    <a href="{{ route('categories.edit', $category) }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300 text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>Éditer
                                    </a>
                                    @endcan
                                    @can('categories.delete')
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr ? Les repas ne seront pas supprimés.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300 text-sm font-medium">
                                            <i class="fas fa-trash mr-1"></i>Supprimer
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-6 text-center text-gray-500">
                                <i class="fas fa-layer-group text-6xl text-gray-300 mb-4 block"></i>
                                <p class="text-lg">Aucune catégorie trouvée</p>
                                @can('categories.create')
                                <a href="{{ route('categories.create') }}" class="mt-4 inline-block bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-300">
                                    Créer la première catégorie
                                </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
            {{ $categories->links() }}
</div>
@endsection
