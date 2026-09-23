@extends('layouts.purchases')

@section('title', 'Types d\'Emballage')

@section('content')
<div class="px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Gestion des Types d'Emballage</h1>
        @can('packagings.create')
        <a href="{{ route('purchases.packagings.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            <i class="fas fa-plus mr-2"></i>Ajouter Emballage
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
        <form method="GET" action="{{ route('purchases.packagings.index') }}" class="flex gap-2">
            <input
                type="text"
                name="search"
                placeholder="Rechercher par nom..."
                value="{{ request('search') }}"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
            >
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                <i class="fas fa-search mr-2"></i>Rechercher
            </button>
        </form>
    </div>

    <!-- Tableau des Emballages -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Photo</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Nom de l'Emballage</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Description</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Produits</th>
                    <th class="px-6 py-3 text-center text-sm font-bold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packagings as $packaging)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            @if($packaging->image)
                                <img src="{{ $packaging->image_url }}" alt="{{ $packaging->name }}"
                                     onclick="openImageLightbox('{{ $packaging->image_url }}', '{{ $packaging->name }}')"
                                     class="h-12 w-12 object-cover rounded-lg border border-gray-200 cursor-zoom-in hover:scale-105 transition">
                            @else
                                <div class="h-12 w-12 rounded-lg border border-dashed border-gray-300 flex items-center justify-center text-gray-300">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                            <i class="fas fa-cube text-purple-600 mr-2"></i>{{ $packaging->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $packaging->description ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="px-3 py-1 bg-gray-100 rounded">{{ $packaging->product_packagings_count }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                @can('packagings.edit')
                                <a href="{{ route('purchases.packagings.edit', $packaging) }}" class="text-blue-600 hover:text-blue-800 text-lg" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @if($packaging->product_packagings_count == 0)
                                    @can('packagings.delete')
                                    <form action="{{ route('purchases.packagings.destroy', $packaging) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr?');">
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
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg">Aucun type d'emballage trouvé.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $packagings->links() }}
    </div>
</div>
@endsection
