@extends('layouts.accounting')

@section('title', 'Soldes Clients — Complex Royal')
@section('accounting_content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
            <i class="fas fa-people-arrows"></i> Soldes Clients
        </h1>
        <div class="text-gray-500 mt-1">Combien chaque client doit encore, symétrique aux soldes fournisseurs.</div>
        <div class="mt-2 inline-flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 text-xs px-3 py-1.5 rounded-lg">
            <i class="fas fa-circle-info"></i> Pour l'instant limité aux clients Catering — Événements et Résidence n'ont pas encore de facturation client reliée pour apparaître ici.
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Client</th>
                    <th class="px-5 py-3 text-right">Nb factures</th>
                    <th class="px-5 py-3 text-right">Total facturé</th>
                    <th class="px-5 py-3 text-right">Total payé</th>
                    <th class="px-5 py-3 text-right">Solde dû</th>
                    <th class="px-5 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="px-5 py-3 font-medium text-gray-700">{{ $client->name }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ $client->catering_invoices_count }}</td>
                    <td class="px-5 py-3 text-right font-semibold">{{ number_format($client->invoiced_total ?? 0, 0, ',', ' ') }} MRU</td>
                    <td class="px-5 py-3 text-right text-emerald-600">{{ number_format($client->paid_total ?? 0, 0, ',', ' ') }} MRU</td>
                    <td class="px-5 py-3 text-right font-bold {{ $client->balance_due > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                        {{ number_format($client->balance_due, 0, ',', ' ') }} MRU
                        @if($client->balance_due <= 0)
                        <span class="block text-xs font-normal text-emerald-500">Soldé</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center">
                        <a href="{{ route('catering.billing.index', ['client_id' => $client->id]) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">
                            <i class="fas fa-list"></i> Détail des factures
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Aucun client avec des factures pour l'instant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $clients->links() }}</div>
</div>
@endsection
