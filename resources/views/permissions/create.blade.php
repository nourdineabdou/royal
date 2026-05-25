@extends('layouts.parameters')

@section('page_title', 'Créer une Permission')
@section('page_subtitle', 'Ajoutez une nouvelle permission')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Créer une permission</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('permissions.store') }}" class="bg-white p-6 rounded shadow-md">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Nom de la permission</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                placeholder="ex: create user"
                required>
            <small class="text-gray-500 block mt-1">Utilisez le format: "verbe objet" (ex: create user, edit post, delete comment)</small>
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
            <textarea name="description" id="description" rows="3"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                placeholder="Description de la permission">{{ old('description') }}</textarea>
        </div>

        <div class="flex justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Créer
            </button>
            <a href="{{ route('permissions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
