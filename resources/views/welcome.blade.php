@extends('layouts.auth')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-blue-600 via-blue-700 to-blue-900">
    <!-- Navigation -->
    <nav class="bg-white bg-opacity-10 backdrop-blur-md border-b border-white border-opacity-20">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-crown mr-2"></i>Complex Royal
                </h1>
                @if(auth()->check())
                    <div class="flex gap-4">
                        <a href="{{ route('dashboard') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                            <i class="fas fa-th-large mr-2"></i>Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex gap-4">
                        <a href="{{ route('login') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Connexion
                        </a>
                        <a href="{{ route('register') }}" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>Inscription
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container mx-auto px-4 py-20">
        <div class="text-center text-white mb-12 animate-fade-in">
            <h2 class="text-5xl md:text-6xl font-bold mb-4">
                Bienvenue sur <span class="text-yellow-300">Complex Royal</span>
            </h2>
            <p class="text-xl md:text-2xl text-blue-100 mb-8">
                Système de Gestion Complet des Utilisateurs, Rôles et Permissions
            </p>
            @if(!auth()->check())
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('login') }}" class="bg-white text-blue-600 hover:bg-blue-50 px-8 py-4 rounded-lg font-bold text-lg transition-all transform hover:scale-105 shadow-2xl">
                        <i class="fas fa-sign-in-alt mr-2"></i>Se Connecter
                    </a>
                    <a href="{{ route('register') }}" class="bg-green-500 text-white hover:bg-green-600 px-8 py-4 rounded-lg font-bold text-lg transition-all transform hover:scale-105 shadow-2xl">
                        <i class="fas fa-user-plus mr-2"></i>Créer un Compte
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Features Section -->
    <div class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white bg-opacity-10 backdrop-blur-md border border-white border-opacity-20 rounded-lg p-8 text-white hover:bg-opacity-20 transition-all transform hover:-translate-y-2">
                <div class="text-4xl mb-4">
                    <i class="fas fa-users text-blue-300"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Gestion Utilisateurs</h3>
                <p class="text-blue-100">
                    Créez, modifiez et gérez facilement tous vos utilisateurs avec une interface intuitive et puissante.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white bg-opacity-10 backdrop-blur-md border border-white border-opacity-20 rounded-lg p-8 text-white hover:bg-opacity-20 transition-all transform hover:-translate-y-2">
                <div class="text-4xl mb-4">
                    <i class="fas fa-shield-alt text-green-300"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Rôles & Permissions</h3>
                <p class="text-blue-100">
                    Définissez des rôles granulaires avec des permissions précises pour contrôler l'accès à votre application.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white bg-opacity-10 backdrop-blur-md border border-white border-opacity-20 rounded-lg p-8 text-white hover:bg-opacity-20 transition-all transform hover:-translate-y-2">
                <div class="text-4xl mb-4">
                    <i class="fas fa-lock text-purple-300"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Sécurité</h3>
                <p class="text-blue-100">
                    Authentification sécurisée et contrôle d'accès basé sur les rôles pour protéger vos données.
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    @if(auth()->check())
        <div class="bg-white bg-opacity-10 backdrop-blur-md border-t border-white border-opacity-20 py-16">
            <div class="container mx-auto px-4">
                <h3 class="text-3xl font-bold text-white text-center mb-12">
                    Statistiques du Système
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="bg-white bg-opacity-10 rounded-lg p-8 text-center text-white">
                        <div class="text-5xl font-bold mb-2 text-blue-300">
                            {{ \App\Models\User::count() }}
                        </div>
                        <p class="text-lg">Utilisateurs</p>
                    </div>

                    <div class="bg-white bg-opacity-10 rounded-lg p-8 text-center text-white">
                        <div class="text-5xl font-bold mb-2 text-green-300">
                            {{ \Spatie\Permission\Models\Role::count() }}
                        </div>
                        <p class="text-lg">Rôles</p>
                    </div>

                    <div class="bg-white bg-opacity-10 rounded-lg p-8 text-center text-white">
                        <div class="text-5xl font-bold mb-2 text-yellow-300">
                            {{ \Spatie\Permission\Models\Permission::count() }}
                        </div>
                        <p class="text-lg">Permissions</p>
                    </div>

                    <div class="bg-white bg-opacity-10 rounded-lg p-8 text-center text-white">
                        <div class="text-5xl font-bold mb-2 text-purple-300">
                            {{ auth()->user()->roles->count() }}
                        </div>
                        <p class="text-lg">Vos Rôles</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Features List Section -->
    <div class="container mx-auto px-4 py-16">
        <h3 class="text-3xl font-bold text-white text-center mb-12">
            Fonctionnalités Principales
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-white">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-2">Gestion CRUD Complète</h4>
                    <p class="text-blue-100">Créez, lisez, modifiez et supprimez facilement les utilisateurs, rôles et permissions</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-2">Attribution Flexible</h4>
                    <p class="text-blue-100">Attribuez facilement plusieurs rôles et permissions à chaque utilisateur</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-2">Interface Intuitive</h4>
                    <p class="text-blue-100">Interface moderne et facile à utiliser avec Tailwind CSS</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-2">Pagination Automatique</h4>
                    <p class="text-blue-100">Gestion automatique de la pagination pour les grandes listes</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-2">Authentification Sécurisée</h4>
                    <p class="text-blue-100">Authentification robuste avec mots de passe hashés et sessions sécurisées</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-2">Dashboard Complet</h4>
                    <p class="text-blue-100">Vue d'ensemble avec statistiques et raccourcis vers les fonctionnalités</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    @if(!auth()->check())
        <div class="bg-white bg-opacity-10 backdrop-blur-md border-t border-white border-opacity-20 py-16">
            <div class="container mx-auto px-4 text-center">
                <h3 class="text-3xl font-bold text-white mb-8">
                    Prêt à commencer?
                </h3>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('login') }}" class="bg-white text-blue-600 hover:bg-blue-50 px-8 py-4 rounded-lg font-bold text-lg transition-all transform hover:scale-105 shadow-2xl">
                        <i class="fas fa-sign-in-alt mr-2"></i>Se Connecter
                    </a>
                    <a href="{{ route('register') }}" class="bg-green-500 text-white hover:bg-green-600 px-8 py-4 rounded-lg font-bold text-lg transition-all transform hover:scale-105 shadow-2xl">
                        <i class="fas fa-user-plus mr-2"></i>Créer un Compte
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="bg-black bg-opacity-30 border-t border-white border-opacity-20 py-8">
        <div class="container mx-auto px-4 text-center text-blue-100">
            <p class="mb-2">
                &copy; 2026 Complex Royal - Système de Gestion Utilisateurs
            </p>
            <p class="text-sm">
                Construit avec Laravel, Spatie/Laravel-Permission et Tailwind CSS
            </p>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 0.8s ease-out;
    }
</style>
@endsection
