@extends('layouts.production')

@section('title', 'Gestion des Accompagnements')
@section('page_title', 'Gestion des Accompagnements')
@section('page_subtitle', 'Gérez les accompagnements disponibles')

@section('content')
<div class="flex justify-end mb-6">
    @can('accompaniments.create')
    <a href="{{ route('accompaniments.create') }}" class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition duration-300 flex items-center">
        <i class="fas fa-plus mr-2"></i>Ajouter Accompagnement
    </a>
    @endcan
</div>

<!-- Search -->
<div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <form action="{{ route('accompaniments.index') }}" method="GET" class="flex gap-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un accompagnement..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                <button type="submit" class="bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700 transition duration-300">
                    <i class="fas fa-search mr-2"></i>Chercher
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white">
                    <tr>
                        <th class="text-left py-4 px-6 font-bold">#</th>
                        <th class="text-left py-4 px-6 font-bold">Nom</th>
                        <th class="text-left py-4 px-6 font-bold">Type</th>
                        <th class="text-left py-4 px-6 font-bold">Prix</th>
                        <th class="text-left py-4 px-6 font-bold">Créé le</th>
                        <th class="text-right py-4 px-6 font-bold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accompaniments as $accompaniment)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="py-4 px-6 font-mono text-sm text-gray-600">{{ $accompaniment->id }}</td>
                            <td class="py-4 px-6">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                    <span class="font-semibold text-gray-800">{{ $accompaniment->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                @if($accompaniment->type === 'single')
                                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                                        <i class="fas fa-circle mr-1"></i>Choix unique
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-medium">
                                        <i class="fas fa-layer-group mr-1"></i>Choix multiple
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-semibold text-green-600 text-lg">{{ number_format($accompaniment->price, 2) }} MRU</span>
                            </td>
                            <td class="py-4 px-6 text-gray-600 text-sm">
                                {{ $accompaniment->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex gap-2 justify-end">
                                    @can('accompaniments.edit')
                                    <a href="{{ route('accompaniments.edit', $accompaniment) }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300 text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>Éditer
                                    </a>
                                    @endcan
                                    @can('accompaniments.delete')
                                    <form action="{{ route('accompaniments.destroy', $accompaniment) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr ?');">
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
                            <td colspan="6" class="py-12 px-6 text-center text-gray-500">
                                <i class="fas fa-star text-6xl text-gray-300 mb-4 block"></i>
                                <p class="text-lg">Aucun accompagnement trouvé</p>
                                @can('accompaniments.create')
                                <a href="{{ route('accompaniments.create') }}" class="mt-4 inline-block bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700 transition duration-300">
                                    Créer le premier accompagnement
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
            {{ $accompaniments->links() }}
</div>
@endsection
