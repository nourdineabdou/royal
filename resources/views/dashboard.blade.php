@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Gestion des utilisateurs, rôles et permissions</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card Utilisateurs -->
        <div class="bg-white p-6 rounded shadow-md">
            <h2 class="text-2xl font-bold text-blue-600 mb-2">{{ $usersCount ?? 0 }}</h2>
            <p class="text-gray-600 mb-4">Utilisateurs au total</p>
            <a href="{{ route('users.index') }}" class="text-blue-500 hover:text-blue-700">
                Gérer les utilisateurs →
            </a>
        </div>

        <!-- Card Rôles -->
        <div class="bg-white p-6 rounded shadow-md">
            <h2 class="text-2xl font-bold text-green-600 mb-2">{{ $rolesCount ?? 0 }}</h2>
            <p class="text-gray-600 mb-4">Rôles disponibles</p>
            <a href="{{ route('roles.index') }}" class="text-green-500 hover:text-green-700">
                Gérer les rôles →
            </a>
        </div>

        <!-- Card Permissions -->
        <div class="bg-white p-6 rounded shadow-md">
            <h2 class="text-2xl font-bold text-purple-600 mb-2">{{ $permissionsCount ?? 0 }}</h2>
            <p class="text-gray-600 mb-4">Permissions définies</p>
            <a href="{{ route('permissions.index') }}" class="text-purple-500 hover:text-purple-700">
                Gérer les permissions →
            </a>
        </div>
    </div>

    <!-- Liens rapides -->
    <div class="bg-white p-6 rounded shadow-md">
        <h2 class="text-2xl font-bold mb-4">Actions rapides</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('users.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded text-center">
                ➕ Créer un utilisateur
            </a>
            <a href="{{ route('roles.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-4 rounded text-center">
                ➕ Créer un rôle
            </a>
            <a href="{{ route('permissions.create') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded text-center">
                ➕ Créer une permission
            </a>
        </div>
    </div>
</div>
@endsection
