@extends('layouts.accounting')

@section('accounting_content')
<div class="p-4 md:p-8">
    <div class="mb-8">
        <div class="flex items-center gap-5 mb-6 p-5 bg-white rounded-2xl shadow-sm">
            <div class="w-16 h-16 bg-gradient-to-br from-red-100 to-red-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-calculator text-4xl text-red-600"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Comptabilité</h2>
                <p class="text-gray-500 text-sm">Tableau de bord centralisé pour la gestion comptable et financière</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Statistiques principales -->
            <div class="bg-white rounded-xl p-6 border border-gray-200 flex flex-col items-center justify-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center mb-3">
                    <i class="fas fa-coins text-2xl text-emerald-600"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(\App\Models\Transaction::sum('amount'), 2) }} </div>
                <div class="text-xs text-gray-500 mt-1">Total transactions</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-200 flex flex-col items-center justify-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center mb-3">
                    <i class="fas fa-calendar-day text-2xl text-blue-600"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format(\App\Models\Transaction::whereDate('date', today())->sum('amount'), 2) }}</div>
                <div class="text-xs text-gray-500 mt-1">Total aujourd'hui</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-200 flex flex-col items-center justify-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-100 to-red-50 flex items-center justify-center mb-3">
                    <i class="fas fa-layer-group text-2xl text-red-600"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ \App\Models\Transaction::distinct('module')->count('module') }}</div>
                <div class="text-xs text-gray-500 mt-1">Modules actifs</div>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-200 flex flex-col items-center justify-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-100 to-yellow-50 flex items-center justify-center mb-3">
                    <i class="fas fa-tags text-2xl text-yellow-600"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ \App\Models\Transaction::distinct('type')->count('type') }}</div>
                <div class="text-xs text-gray-500 mt-1">Types d'opérations</div>
            </div>
            <a href="{{ route('accounting.transactions') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 cursor-pointer group block hover:no-underline">
                <div class="bg-gradient-to-br from-green-100 to-green-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                    <i class="fas fa-list module-icon text-green-600"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Toutes les transactions</h4>
                <p class="text-gray-600 text-sm mb-4">Voir, filtrer et exporter toutes les transactions</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">Reporting</span>
                    <button class="text-green-600 hover:text-green-700">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </a>
            <a href="{{ route('accounting.sessions') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 cursor-pointer group block hover:no-underline">
                <div class="bg-gradient-to-br from-blue-100 to-blue-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                    <i class="fas fa-cash-register module-icon text-blue-600"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Comptabilité caisse</h4>
                <p class="text-gray-600 text-sm mb-4">Sessions de caisse, encaissements, clôtures</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Caisse</span>
                    <button class="text-blue-600 hover:text-blue-700">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </a>
        </div>
        <!-- Ajoutez ici d'autres tuiles/statistiques si besoin -->
    </div>
</div>
@endsection
