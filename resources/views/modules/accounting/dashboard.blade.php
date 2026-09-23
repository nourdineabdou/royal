@extends('layouts.accounting')

@section('title', 'Dashboard Comptabilité — Complex Royal')
@section('accounting_content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
            <i class="fas fa-chart-pie"></i> Dashboard Comptabilité
        </h1>
        <div class="text-gray-500 mt-1">Vue d'ensemble des indicateurs comptables</div>
    </div>

    {{-- Accès rapides — comptabilité en partie double (PCM) --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('accounting.journal') }}" class="bg-white rounded-xl shadow p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg"><i class="fas fa-book"></i></div>
            <div>
                <div class="font-bold text-gray-800">Journal</div>
                <div class="text-xs text-gray-500">Écritures comptables</div>
            </div>
        </a>
        <a href="{{ route('accounting.ledger') }}" class="bg-white rounded-xl shadow p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg"><i class="fas fa-columns"></i></div>
            <div>
                <div class="font-bold text-gray-800">Grand Livre</div>
                <div class="text-xs text-gray-500">Détail par compte</div>
            </div>
        </a>
        <a href="{{ route('accounting.balance') }}" class="bg-white rounded-xl shadow p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg"><i class="fas fa-balance-scale"></i></div>
            <div>
                <div class="font-bold text-gray-800">Balance</div>
                <div class="text-xs text-gray-500">Débit / Crédit / Solde</div>
            </div>
        </a>
        <a href="{{ route('settings.chart-of-accounts') }}" class="bg-white rounded-xl shadow p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-lg"><i class="fas fa-sitemap"></i></div>
            <div>
                <div class="font-bold text-gray-800">Plan comptable</div>
                <div class="text-xs text-gray-500">Comptes PCM</div>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <div class="text-2xl font-bold text-green-600">{{ number_format($totalAmount, 0, ',', ' ') }} MRU</div>
            <div class="text-gray-600 mt-2">Montant total encaissé</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <div class="text-2xl font-bold text-blue-600">{{ $totalTransactions }}</div>
            <div class="text-gray-600 mt-2">Transactions</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <div class="text-2xl font-bold text-purple-600">{{ $totalSessions }}</div>
            <div class="text-gray-600 mt-2">Sessions de caisse</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-lg font-semibold text-indigo-700 mb-2">Sessions de caisse</div>
            <div class="flex gap-6">
                <div class="flex-1 text-center">
                    <div class="text-xl font-bold text-yellow-600">{{ $openSessions }}</div>
                    <div class="text-gray-500">Ouvertes</div>
                </div>
                <div class="flex-1 text-center">
                    <div class="text-xl font-bold text-green-600">{{ $validatedSessions }}</div>
                    <div class="text-gray-500">Validées</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-lg font-semibold text-indigo-700 mb-2">5 dernières transactions</div>
            <ul class="divide-y divide-gray-200">
                @forelse($recentTransactions as $t)
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-gray-700">{{ $t->date }} — {{ $t->module }} ({{ $t->type }})</span>
                        <span class="font-bold text-green-600">{{ number_format($t->amount, 0, ',', ' ') }} MRU</span>
                    </li>
                @empty
                    <li class="py-2 text-gray-400">Aucune transaction récente</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <div class="text-lg font-semibold text-indigo-700 mb-4">Statistiques par module</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Module</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Transactions</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Montant total (MRU)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modulesStats as $stat)
                        <tr>
                            <td class="px-4 py-2 text-gray-700 font-semibold">{{ $stat->module ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $stat->transactions_count }}</td>
                            <td class="px-4 py-2 font-bold text-green-700">{{ number_format($stat->total_amount, 0, ',', ' ') }} MRU</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-gray-400">Aucune donnée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
