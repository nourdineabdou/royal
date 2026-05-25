@extends('layouts.parameters')

@section('page_title', 'Détails de l\'Utilisateur')
@section('page_subtitle', 'Consultez les informations')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
        <div>
            @can('users.edit')
            <a href="{{ route('users.edit', $user) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">
                Éditer
            </a>
            @endcan
            <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Retour
            </a>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow-md mb-6">
        <h2 class="text-xl font-bold mb-4">Détails de l'utilisateur</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Nom</label>
                <p class="text-gray-600">{{ $user->name }}</p>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Email</label>
                <p class="text-gray-600">{{ $user->email }}</p>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Date de création</label>
                <p class="text-gray-600">{{ $user->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Rôles assignés</h2>

        @if($user->roles->count() > 0)
            <ul class="list-disc list-inside">
                @foreach($user->roles as $role)
                    <li class="text-gray-600 mb-2">
                        <span class="inline-block bg-blue-200 text-blue-800 text-sm px-3 py-1 rounded">
                            {{ $role->name }}
                        </span>
                        <p class="text-sm text-gray-500 ml-4">{{ $role->description }}</p>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500">Aucun rôle assigné.</p>
        @endif
    </div>
</div>
@endsection
