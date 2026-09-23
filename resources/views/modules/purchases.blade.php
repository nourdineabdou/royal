@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-shopping-cart text-white"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-shopping-cart text-cyan-600 mr-2"></i>Module Achats
                </h1>
                <p class="text-gray-600">Gestion complète des achats, fournisseurs et produits</p>
            </div>
        </div>
        <a href="{{ route('dashboard-modern') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i> Retour au Dashboard
        </a>
    </div>

    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-cyan-500 to-blue-600 rounded-xl shadow-lg p-8 mb-8 text-white border-l-4 border-cyan-300">
        <h2 class="text-2xl font-bold mb-2">Bienvenue dans le Module Achats</h2>
        <p>Gérez vos fournisseurs, commandes, bons de livraison et produits de manière centralisée et efficace.</p>
    </div>

    <!-- Quick Links Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Dashboard Link -->
        <a href="{{ route('purchases.dashboard') }}" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-blue-500">
                <i class="fas fa-chart-line text-4xl text-blue-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Dashboard</h3>
                <p class="text-gray-600 text-sm mb-4">Statistiques et suivi des achats</p>
                <span class="inline-block px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold group-hover:bg-blue-600 group-hover:text-white transition">
                    Accéder
                </span>
            </div>
        </a>

        <!-- Suppliers Link -->
        <a href="{{ route('purchases.suppliers') }}" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-green-500">
                <i class="fas fa-user-tie text-4xl text-green-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Fournisseurs</h3>
                <p class="text-gray-600 text-sm mb-4">Gérez vos contacts fournisseurs</p>
                <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-semibold group-hover:bg-green-600 group-hover:text-white transition">
                    Gérer
                </span>
            </div>
        </a>

        <!-- Purchase Orders Link -->
        <a href="{{ route('purchases.orders') }}" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-orange-500">
                <i class="fas fa-file-invoice text-4xl text-orange-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Bon de Commande</h3>
                <p class="text-gray-600 text-sm mb-4">Créer et gérer les commandes</p>
                <span class="inline-block px-4 py-2 bg-orange-100 text-orange-700 rounded-lg text-sm font-semibold group-hover:bg-orange-600 group-hover:text-white transition">
                    Accéder
                </span>
            </div>
        </a>

        <!-- Delivery Notes Link -->
        <a href="{{ route('purchases.deliveries') }}" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-purple-500">
                <i class="fas fa-truck text-4xl text-purple-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Bon de Livraison</h3>
                <p class="text-gray-600 text-sm mb-4">Réception et validation des livraisons</p>
                <span class="inline-block px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-semibold group-hover:bg-purple-600 group-hover:text-white transition">
                    Accéder
                </span>
            </div>
        </a>

        <!-- Products Link -->
        <a href="{{ route('purchases.products.index') }}" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-indigo-500">
                <i class="fas fa-boxes text-4xl text-indigo-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Produits</h3>
                <p class="text-gray-600 text-sm mb-4">Gestion des produits d'achat</p>
                <span class="inline-block px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg text-sm font-semibold group-hover:bg-indigo-600 group-hover:text-white transition">
                    Gérer
                </span>
            </div>
        </a>

        <!-- Packaging Link -->
        <a href="{{ route('purchases.packagings.index') }}" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-red-500">
                <i class="fas fa-cube text-4xl text-red-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Emballages</h3>
                <p class="text-gray-600 text-sm mb-4">Gestion des types d'emballage</p>
                <span class="inline-block px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-semibold group-hover:bg-red-600 group-hover:text-white transition">
                    Gérer
                </span>
            </div>
        </a>

        <!-- Units Link -->
        <a href="{{ route('purchases.units.index') }}" class="group">
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 p-8 text-center h-full border-t-4 border-teal-500">
                <i class="fas fa-ruler text-4xl text-teal-600 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Unités</h3>
                <p class="text-gray-600 text-sm mb-4">Gestion des unités de mesure</p>
                <span class="inline-block px-4 py-2 bg-teal-100 text-teal-700 rounded-lg text-sm font-semibold group-hover:bg-teal-600 group-hover:text-white transition">
                    Gérer
                </span>
            </div>
        </a>
    </div>

    <!-- Info Card -->
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-xl p-6 mt-8">
        <h3 class="text-lg font-bold text-blue-900 mb-3">
            <i class="fas fa-info-circle mr-2"></i>Workflow d'Achat
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg p-4">
                <div class="text-2xl font-bold text-orange-600 mb-2">1</div>
                <h4 class="font-semibold text-gray-800 mb-2">Bon de Commande</h4>
                <p class="text-sm text-gray-600">Créer une commande sans fournisseur (optionnel)</p>
            </div>
            <div class="bg-white rounded-lg p-4">
                <div class="text-2xl font-bold text-purple-600 mb-2">2</div>
                <h4 class="font-semibold text-gray-800 mb-2">Bon de Livraison</h4>
                <p class="text-sm text-gray-600">Ajouter fournisseur et saisir livraison (peut différer)</p>
            </div>
            <div class="bg-white rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600 mb-2">3</div>
                <h4 class="font-semibold text-gray-800 mb-2">Facture</h4>
                <p class="text-sm text-gray-600">Traiter dans le module Comptable</p>
            </div>
        </div>
    </div>
</div>
@endsection
