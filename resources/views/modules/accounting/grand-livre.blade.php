@extends('layouts.accounting')

@section('title', 'Grand Livre — Complex Royal')
@section('accounting_content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
            <i class="fas fa-columns"></i> Grand Livre
        </h1>
        <div class="text-gray-500 mt-1">Détail des mouvements d'un compte, avec solde cumulé</div>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div class="min-w-[280px]">
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Compte</label>
            <select name="chart_of_account_id" class="border border-gray-300 rounded-lg px-3 py-2 w-full" required>
                <option value="">— Choisir un compte —</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ (string) $accountId === (string) $acc->id ? 'selected' : '' }}>{{ $acc->code }} — {{ $acc->label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Du</label>
            <input type="date" name="from_date" value="{{ $fromDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Au</label>
            <input type="date" name="to_date" value="{{ $toDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-5 py-2 rounded-lg">
            <i class="fas fa-search"></i> Afficher
        </button>
    </form>

    @if($account)
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 font-bold text-indigo-700">
                {{ $account->code }} — {{ $account->label }}
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Référence</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Libellé</th>
                            <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Débit</th>
                            <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Crédit</th>
                            <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Solde</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($lines as $line)
                            <tr>
                                <td class="px-4 py-2 text-gray-700">{{ $line->journalEntry->entry_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $line->journalEntry->reference }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $line->label }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-700">{{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '' }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-700">{{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '' }}</td>
                                <td class="px-4 py-2 text-right font-bold {{ $line->running_balance >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ number_format($line->running_balance, 2, ',', ' ') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-400">Aucun mouvement pour ce compte sur cette période.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">
            <i class="fas fa-hand-pointer text-2xl mb-2"></i>
            <p>Sélectionnez un compte pour afficher son grand livre.</p>
        </div>
    @endif
</div>
@endsection
