@extends('layouts.parameters')

@section('page_title', 'Gestion des Rôles')
@section('page_subtitle', 'Gérez tous les rôles de votre système')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Rôles</h1>
        @can('roles.create')
        <a href="{{ route('roles.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Créer un rôle
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
                    <th class="border border-gray-300 px-4 py-2 text-left">Permissions</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr class="hover:bg-gray-100">
                        <td class="border border-gray-300 px-4 py-2">{{ $role->name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $role->description ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            <div class="flex flex-wrap gap-1">
                                @forelse($role->permissions as $permission)
                                    <span class="inline-block bg-green-200 text-green-800 text-xs px-2 py-1 rounded">
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-gray-500 text-sm">Aucune permission</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <a href="{{ route('roles.show', $role) }}" class="text-blue-500 hover:text-blue-700 mr-2">Voir</a>
                            @can('roles.edit')
                            <a href="{{ route('roles.edit', $role) }}" class="text-yellow-500 hover:text-yellow-700 mr-2">Éditer</a>
                            @endcan
                            @can('roles.delete')
                            <form method="POST" action="{{ route('roles.destroy', $role) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="border border-gray-300 px-4 py-2 text-center text-gray-500">
                            Aucun rôle trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $roles->links() }}
    </div>
</div>
@endsection
