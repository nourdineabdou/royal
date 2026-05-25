@extends('layouts.parameters')

@section('page_title', 'Détails du Rôle')
@section('page_subtitle', 'Consultez les informations complétes du rôle')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{{ $role->name }}</h1>
        <div>
            @can('roles.edit')
            <a href="{{ route('roles.edit', $role) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">
                Éditer
            </a>
            @endcan
            <a href="{{ route('roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Retour
            </a>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow-md mb-6">
        <h2 class="text-xl font-bold mb-4">Détails du rôle</h2>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Description</label>
            <p class="text-gray-600">{{ $role->description ?? 'Aucune description' }}</p>
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Date de création</label>
            <p class="text-gray-600">{{ $role->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Permissions assignées</h2>

        @if($role->permissions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($role->permissions as $permission)
                    <div class="border border-gray-300 p-4 rounded">
                        <h3 class="font-bold text-green-700">{{ $permission->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $permission->description }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Aucune permission assignée.</p>
        @endif
    </div>
</div>
@endsection
