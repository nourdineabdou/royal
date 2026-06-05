@extends('layouts.parameters')

@section('page_title', 'Module Paramètres')
@section('page_subtitle', 'Accéder à tous les outils de configuration')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-8 border-l-4 border-amber-500 mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Bienvenue dans les Paramètres</h2>
    <p class="text-gray-600">Gérez complètement la configuration et les paramètres de votre application Complex Royal.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- POS Terminals Link -->
    <a href="{{ route('settings.pos-terminals.index') }}" class="group">
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-violet-500">
            <i class="fas fa-store text-4xl text-violet-600 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Points de Vente</h3>
            <p class="text-gray-600 text-sm mb-4">Créer et configurer les terminaux: ordinaire ou distant (lié au stock)</p>
            <span class="inline-block px-4 py-2 bg-violet-100 text-violet-700 rounded-lg text-sm font-semibold group-hover:bg-violet-600 group-hover:text-white transition">
                Gérer
            </span>
        </div>
    </a>

    <!-- Payment Types Link -->
    <a href="{{ route('payment-types.index') }}" class="group">
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-amber-500">
            <i class="fas fa-credit-card text-4xl text-amber-600 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Types de Paiement</h3>
            <p class="text-gray-600 text-sm mb-4">Gérez les modes de paiement disponibles</p>
            <span class="inline-block px-4 py-2 bg-amber-100 text-amber-700 rounded-lg text-sm font-semibold group-hover:bg-amber-600 group-hover:text-white transition">
                Gérer
            </span>
        </div>
    </a>

    <!-- Users Link -->
    <a href="{{ route('users.index') }}" class="group">
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-orange-500">
            <i class="fas fa-users text-4xl text-orange-600 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Utilisateurs</h3>
            <p class="text-gray-600 text-sm mb-4">Gérez tous les utilisateurs du système</p>
            <span class="inline-block px-4 py-2 bg-orange-100 text-orange-700 rounded-lg text-sm font-semibold group-hover:bg-orange-600 group-hover:text-white transition">
                Gérer
            </span>
        </div>
    </a>

    <!-- Roles Link -->
    <a href="{{ route('roles.index') }}" class="group">
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-cyan-500">
            <i class="fas fa-shield-alt text-4xl text-cyan-600 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Rôles</h3>
            <p class="text-gray-600 text-sm mb-4">Gérez tous les rôles du système</p>
            <span class="inline-block px-4 py-2 bg-cyan-100 text-cyan-700 rounded-lg text-sm font-semibold group-hover:bg-cyan-600 group-hover:text-white transition">
                Gérer
            </span>
        </div>
    </a>

    <!-- Permissions Link -->
    <a href="{{ route('permissions.index') }}" class="group">
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-emerald-500">
            <i class="fas fa-key text-4xl text-emerald-600 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Permissions</h3>
            <p class="text-gray-600 text-sm mb-4">Gérez toutes les permissions du système</p>
            <span class="inline-block px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold group-hover:bg-emerald-600 group-hover:text-white transition">
                Gérer
            </span>
        </div>
    </a>
</div>
@endsection
