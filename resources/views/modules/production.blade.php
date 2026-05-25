@extends('layouts.production')

@section('page_title', 'Module Production')
@section('page_subtitle', 'Accéder à tous les outils de gestion de la production')

@section('content')
<div>
    <!-- Welcome Card -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-8 border-l-4 border-orange-500">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Bienvenue dans le Module Production</h2>
            <p class="text-gray-600">Gérez complètement votre restaurant avec cette suite complète de gestion des repas, catégories et accompagnements. Accédez au dashboard pour voir les statistiques en temps réel.</p>
        </div>

        <!-- Quick Links Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Dashboard Link -->
            <a href="{{ route('production.dashboard') }}" class="group">
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-blue-500">
                    <i class="fas fa-chart-line text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dashboard</h3>
                    <p class="text-gray-600 text-sm mb-4">Statistiques et graphiques en temps réel</p>
                    <span class="inline-block px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold group-hover:bg-blue-600 group-hover:text-white transition">
                        Accéder
                    </span>
                </div>
            </a>

            <!-- Meals Link -->
            <a href="{{ route('meals.index') }}" class="group">
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-green-500">
                    <i class="fas fa-utensils text-4xl text-green-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Repas</h3>
                    <p class="text-gray-600 text-sm mb-4">Gérez tous vos plats disponibles</p>
                    <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-semibold group-hover:bg-green-600 group-hover:text-white transition">
                        Gérer
                    </span>
                </div>
            </a>

            <!-- Categories Link -->
            <a href="{{ route('categories.index') }}" class="group">
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-purple-500">
                    <i class="fas fa-layer-group text-4xl text-purple-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Catégories</h3>
                    <p class="text-gray-600 text-sm mb-4">Organisez vos repas par catégorie</p>
                    <span class="inline-block px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-semibold group-hover:bg-purple-600 group-hover:text-white transition">
                        Gérer
                    </span>
                </div>
            </a>

            <!-- Accompaniments Link -->
            <a href="{{ route('accompaniments.index') }}" class="group">
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-yellow-500">
                    <i class="fas fa-star text-4xl text-yellow-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Accompagnements</h3>
                    <p class="text-gray-600 text-sm mb-4">Gestion des options complémentaires</p>
                    <span class="inline-block px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg text-sm font-semibold group-hover:bg-yellow-600 group-hover:text-white transition">
                        Gérer
                    </span>
                </div>
            </a>
        </div>

        <!-- Stats Preview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl shadow-lg text-white p-8">
                <i class="fas fa-utensils text-4xl mb-4 opacity-80"></i>
                <p class="text-blue-100 text-sm mb-1">Accès Rapide</p>
                <h3 class="text-2xl font-bold">Tous les Repas</h3>
                <p class="text-blue-100 text-sm mt-2"><a href="{{ route('meals.index') }}" class="hover:underline font-semibold">Cliquez ici pour accéder →</a></p>
            </div>
            <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-xl shadow-lg text-white p-8">
                <i class="fas fa-layer-group text-4xl mb-4 opacity-80"></i>
                <p class="text-green-100 text-sm mb-1">Accès Rapide</p>
                <h3 class="text-2xl font-bold">Catégories</h3>
                <p class="text-green-100 text-sm mt-2"><a href="{{ route('categories.index') }}" class="hover:underline font-semibold">Cliquez ici pour accéder →</a></p>
            </div>
            <div class="bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl shadow-lg text-white p-8">
                <i class="fas fa-chart-line text-4xl mb-4 opacity-80"></i>
                <p class="text-orange-100 text-sm mb-1">Accès Rapide</p>
                <h3 class="text-2xl font-bold">Dashboard</h3>
                <p class="text-orange-100 text-sm mt-2"><a href="{{ route('production.dashboard') }}" class="hover:underline font-semibold">Cliquez ici pour accéder →</a></p>
            </div>
        </div>
    </div>
</div>
@endsection

