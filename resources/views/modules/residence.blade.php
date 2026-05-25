@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-crown text-white"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-home text-green-600 mr-2"></i>Résidence - Complex Royal
                </h1>
            </div>
        </div>
        <a href="{{ route('dashboard-modern') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i> Retour au Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <p class="text-gray-600 mb-4">Bienvenue dans le module Résidence de Complex Royal</p>
        <p class="text-gray-500">Cette page sera développée prochainement avec toutes les fonctionnalités de gestion des résidences.</p>
    </div>
</div>
@endsection
