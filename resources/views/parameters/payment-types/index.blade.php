@extends('layouts.parameters')

@section('title', 'Types de Paiement')

@section('content')
<div class="px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Gestion des Types de Paiement</h1>
        @can('payment-types.create')
        <a href="{{ route('payment-types.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            <i class="fas fa-plus mr-2"></i>Ajouter Type
        </a>
        @endcan
    </div>

    @if ($message = Session::get('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <i class="fas fa-check mr-2"></i>{{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ $message }}
        </div>
    @endif

    <!-- Recherche -->
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('payment-types.index') }}" class="flex gap-2">
            <input
                type="text"
                name="search"
                placeholder="Rechercher par nom..."
                value="{{ request('search') }}"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            >
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                <i class="fas fa-search mr-2"></i>Rechercher
            </button>
        </form>
    </div>

    <!-- Tableau des Types de Paiement -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Nom du Type</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Description</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Statut</th>
                    <th class="px-6 py-3 text-center text-sm font-bold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentTypes as $paymentType)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                            <i class="fas fa-credit-card text-green-600 mr-2"></i>{{ $paymentType->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $paymentType->description ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($paymentType->is_active)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded font-semibold">
                                    <i class="fas fa-check mr-1"></i>Actif
                                </span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded font-semibold">
                                    <i class="fas fa-times mr-1"></i>Inactif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                @can('payment-types.edit')
                                <a href="{{ route('payment-types.edit', $paymentType) }}" class="text-blue-600 hover:text-blue-800 text-lg" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('payment-types.delete')
                                <form action="{{ route('payment-types.destroy', $paymentType) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr?');">
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
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg">Aucun type de paiement trouvé.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $paymentTypes->links() }}
    </div>
</div>
@endsection
