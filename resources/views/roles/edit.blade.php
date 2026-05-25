@extends('layouts.parameters')

@section('page_title', 'Éditer le Rôle')
@section('page_subtitle', 'Modifiez les informations du rôle')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Éditer le rôle - {{ $role->name }}</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('roles.update', $role) }}" class="bg-white p-6 rounded shadow-md">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Nom du rôle</label>
            <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                required>
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
            <textarea name="description" id="description" rows="3"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('description', $role->description) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Permissions</label>
            <div class="grid grid-cols-2 gap-4">
                @foreach($permissions as $permission)
                    <div class="flex items-center">
                        <input type="checkbox" name="permissions[]" id="permission_{{ $permission->id }}"
                            value="{{ $permission->id }}"
                            {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}
                            class="form-checkbox">
                        <label for="permission_{{ $permission->id }}" class="ml-2 text-gray-700">
                            {{ $permission->name }}
                            <small class="text-gray-500 block">{{ $permission->description }}</small>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Mettre à jour
            </button>
            <a href="{{ route('roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
