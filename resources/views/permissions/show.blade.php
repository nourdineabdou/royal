@extends('layouts.parameters')

@section('page_title', 'Détails de la Permission')
@section('page_subtitle', 'Consultez les informations complétes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{{ $permission->name }}</h1>
        <div>
            @can('permissions.edit')
            <a href="{{ route('permissions.edit', $permission) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">
                Éditer
            </a>
            @endcan
            <a href="{{ route('permissions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Retour
            </a>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Détails de la permission</h2>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Nom</label>
            <p class="text-gray-600 font-mono">{{ $permission->name }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Description</label>
            <p class="text-gray-600">{{ $permission->description ?? 'Aucune description' }}</p>
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Date de création</label>
            <p class="text-gray-600">{{ $permission->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>
@endsection
