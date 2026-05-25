@extends('layouts.parameters')

@section('page_title', 'Gestion des Utilisateurs')
@section('page_subtitle', 'Gérez tous les utilisateurs du système')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Utilisateurs</h1>
        @can('users.create')
        <a href="{{ route('users.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Créer un utilisateur
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
                    <th class="border border-gray-300 px-4 py-2 text-left">Email</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Rôles</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-100">
                        <td class="border border-gray-300 px-4 py-2">{{ $user->name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $user->email }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            @foreach($user->roles as $role)
                                <span class="inline-block bg-blue-200 text-blue-800 text-xs px-2 py-1 rounded mr-1">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <a href="{{ route('users.show', $user) }}" class="text-blue-500 hover:text-blue-700 mr-2">Voir</a>
                            @can('users.edit')
                            <a href="{{ route('users.edit', $user) }}" class="text-yellow-500 hover:text-yellow-700 mr-2">Éditer</a>
                            @endcan
                            @can('users.delete')
                            <form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline;">
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
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
