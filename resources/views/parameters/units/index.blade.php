@extends('layouts.purchases')

@section('title', 'Unités de Mesure')

@section('content')
<div class="px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Gestion des Unités de Mesure</h1>
        @can('units.create')
        <a href="{{ route('purchases.units.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            <i class="fas fa-plus mr-2"></i>Ajouter Unité
        </a>
        @endcan
    </div>

    @if ($message = Session::get('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <i class="fas fa-check mr-2"></i>{{ $message }}
        </div>
    @endif

    <!-- Recherche -->
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('purchases.units.index') }}" class="flex gap-2">
            <input
                type="text"
                name="search"
                placeholder="Rechercher par nom ou abréviation..."
                value="{{ request('search') }}"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                <i class="fas fa-search mr-2"></i>Rechercher
            </button>
        </form>
    </div>

    <!-- Tableau des Unités -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Nom</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Abréviation</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Produits</th>
                    <th class="px-6 py-3 text-center text-sm font-bold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $unit->name }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded font-bold">{{ $unit->symbol }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="px-3 py-1 bg-gray-100 rounded">{{ $unit->products()->count() }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                @can('units.edit')
                                <a href="{{ route('purchases.units.edit', $unit) }}" class="text-blue-600 hover:text-blue-800 text-lg" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @if($unit->products()->count() == 0)
                                    @can('units.delete')
                                    <form action="{{ route('purchases.units.destroy', $unit) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-lg" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @else
                                    <span class="text-gray-400 text-lg cursor-not-allowed" title="Non supprimable (en utilisation)">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg">Aucune unité trouvée.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $units->links() }}
    </div>
</div>
@endsection
