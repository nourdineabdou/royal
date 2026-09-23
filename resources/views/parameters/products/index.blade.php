@extends('layouts.purchases')

@section('title', 'Produits')

@section('content')
<div class="px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Gestion des Produits</h1>
        @can('products.create')
        <a href="{{ route('purchases.products.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            <i class="fas fa-plus mr-2"></i>Ajouter Produit
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
        <form method="GET" action="{{ route('purchases.products.index') }}" class="flex gap-2">
            <input
                type="text"
                name="search"
                placeholder="Rechercher par nom..."
                value="{{ request('search') }}"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                <i class="fas fa-search mr-2"></i>Rechercher
            </button>
        </form>
    </div>

    <!-- Tableau des Produits -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Nom du Produit</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Unité</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Emballage</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Consommable</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Prix (MRU)</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Stock</th>
                    <th class="px-6 py-3 text-center text-sm font-bold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-800">
                            <a href="{{ route('purchases.products.show', $product) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                {{ $product->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">{{ $product->unit->symbol }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $product->productPackagings->pluck('packaging.name')->implode(', ') ?: '—' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($product->is_consumable)
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-800 rounded">Oui</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded">Non</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                            {{ $product->sale_price !== null ? number_format($product->sale_price, 2, ',', ' ') : '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">Stock</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                @can('products.edit')
                                <a href="{{ route('purchases.products.edit', $product) }}" class="text-blue-600 hover:text-blue-800 text-lg" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('products.delete')
                                <form action="{{ route('purchases.products.destroy', $product) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-lg" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg">Aucun produit trouvé.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>
@endsection
