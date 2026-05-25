@extends('layouts.parameters')

@section('page_title', 'Gestion des Permissions')
@section('page_subtitle', 'Gérez toutes les permissions du système')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Permissions</h1>
        @can('permissions.create')
        <a href="{{ route('permissions.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Créer une permission
        </a>
        @endcan
    </div>

    @if ($message = Session::get('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ $message }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left">Nom</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Description</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($permissions as $permission)
                    <tr class="hover:bg-gray-100">
                        <td class="border border-gray-300 px-4 py-2 font-mono text-sm">{{ $permission->name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $permission->description ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            <a href="{{ route('permissions.show', $permission) }}" class="text-blue-500 hover:text-blue-700 mr-2">Voir</a>
                            @can('permissions.edit')
                            <a href="{{ route('permissions.edit', $permission) }}" class="text-yellow-500 hover:text-yellow-700 mr-2">Éditer</a>
                            @endcan
                            @can('permissions.delete')
                            <form method="POST" action="{{ route('permissions.destroy', $permission) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="border border-gray-300 px-4 py-2 text-center text-gray-500">
                            Aucune permission trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $permissions->links() }}
    </div>
</div>
@endsection
