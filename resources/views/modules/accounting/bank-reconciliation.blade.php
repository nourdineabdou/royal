@extends('layouts.accounting')

@section('title', 'Rapprochement Bancaire — Complex Royal')
@section('accounting_content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
            <i class="fas fa-money-check-dollar"></i> Rapprochement Bancaire
        </h1>
        <div class="text-gray-500 mt-1">Pointez chaque mouvement contre votre relevé bancaire réel pour détecter les écarts.</div>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Compte de trésorerie</label>
            <select name="account_id" class="border border-gray-300 rounded-lg px-3 py-2">
                @foreach($treasuryAccounts as $acc)
                <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>{{ $acc->code }} — {{ $acc->label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Arrêté au</label>
            <input type="date" name="to_date" value="{{ $toDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Solde du relevé bancaire</label>
            <input type="number" step="0.01" name="statement_balance" value="{{ $statementBalance }}" placeholder="Ex: 150000"
                   class="border border-gray-300 rounded-lg px-3 py-2 w-48">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-5 py-2 rounded-lg">
            <i class="fas fa-filter"></i> Afficher
        </button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs text-gray-400 uppercase font-bold">Solde comptable (tous mouvements)</div>
            <div class="text-2xl font-bold text-slate-700 mt-1">{{ number_format($bookBalance, 0, ',', ' ') }} MRU</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs text-gray-400 uppercase font-bold">Solde pointé (rapproché)</div>
            <div class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($reconciledBalance, 0, ',', ' ') }} MRU</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs text-gray-400 uppercase font-bold">Écart avec le relevé</div>
            @if($diff === null)
            <div class="text-lg text-gray-400 mt-1">Renseignez le solde du relevé</div>
            @elseif(abs($diff) < 1)
            <div class="text-2xl font-bold text-emerald-600 mt-1"><i class="fas fa-circle-check"></i> 0 MRU</div>
            @else
            <div class="text-2xl font-bold text-red-600 mt-1">{{ number_format($diff, 0, ',', ' ') }} MRU</div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-center w-12">Pointé</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Référence</th>
                    <th class="px-5 py-3 text-left">Libellé</th>
                    <th class="px-5 py-3 text-right">Débit</th>
                    <th class="px-5 py-3 text-right">Crédit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lines as $line)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition" id="line-{{ $line->id }}">
                    <td class="px-5 py-3 text-center">
                        <input type="checkbox" class="reconcile-check w-4 h-4" data-line-id="{{ $line->id }}" {{ $line->is_reconciled ? 'checked' : '' }}>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $line->journalEntry->entry_date->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-indigo-600">{{ $line->journalEntry->reference }}</td>
                    <td class="px-5 py-3 text-gray-700">{{ $line->label ?? $line->journalEntry->label }}</td>
                    <td class="px-5 py-3 text-right font-medium">{{ $line->debit > 0 ? number_format($line->debit, 0, ',', ' ') : '' }}</td>
                    <td class="px-5 py-3 text-right font-medium text-red-500">{{ $line->credit > 0 ? number_format($line->credit, 0, ',', ' ') : '' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Aucun mouvement pour ce compte à cette date.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.reconcile-check').forEach(function (cb) {
    cb.addEventListener('change', function () {
        const lineId = this.dataset.lineId;
        fetch(`/modules/accounting/journal-lines/${lineId}/toggle-reconciliation`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => r.json())
        .then(() => location.reload())
        .catch(() => alert('Erreur lors du pointage.'));
    });
});
</script>
@endsection
