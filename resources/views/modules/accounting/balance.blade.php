@extends('layouts.accounting')

@section('title', 'Balance — Complex Royal')
@section('accounting_content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
            <i class="fas fa-balance-scale"></i> Balance des comptes
        </h1>
        <div class="text-gray-500 mt-1">Total débit / crédit / solde par compte, sur la période</div>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Du</label>
            <input type="date" name="from_date" value="{{ $fromDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Au</label>
            <input type="date" name="to_date" value="{{ $toDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-5 py-2 rounded-lg">
            <i class="fas fa-filter"></i> Filtrer
        </button>
    </form>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Compte</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Libellé</th>
                        <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Total Débit</th>
                        <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Total Crédit</th>
                        <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Solde débiteur</th>
                        <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Solde créditeur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($accounts as $acc)
                        <tr>
                            <td class="px-4 py-2 font-semibold text-gray-700">{{ $acc->code }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $acc->label }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($acc->total_debit, 2, ',', ' ') }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($acc->total_credit, 2, ',', ' ') }}</td>
                            <td class="px-4 py-2 text-right font-bold text-emerald-700">{{ $acc->balance > 0 ? number_format($acc->balance, 2, ',', ' ') : '' }}</td>
                            <td class="px-4 py-2 text-right font-bold text-red-600">{{ $acc->balance < 0 ? number_format(abs($acc->balance), 2, ',', ' ') : '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">Aucun mouvement sur cette période.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td class="px-4 py-2" colspan="2">Total</td>
                        <td class="px-4 py-2 text-right">{{ number_format($totalDebit, 2, ',', ' ') }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($totalCredit, 2, ',', ' ') }}</td>
                        <td class="px-4 py-2 text-right" colspan="2">
                            @if(abs($totalDebit - $totalCredit) < 0.01)
                                <span class="text-emerald-700"><i class="fas fa-check-circle"></i> Équilibrée</span>
                            @else
                                <span class="text-red-600"><i class="fas fa-exclamation-triangle"></i> Écart {{ number_format($totalDebit - $totalCredit, 2, ',', ' ') }}</span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
