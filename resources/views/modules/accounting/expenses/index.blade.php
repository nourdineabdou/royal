@extends('layouts.accounting')

@section('title', 'Dépenses — Complex Royal')
@section('accounting_content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
            <i class="fas fa-receipt"></i> Dépenses
        </h1>
        <div class="text-gray-500 mt-1">Wifi, électricité, eau, carburant... déclarées et payées par la comptabilité</div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center md:col-span-1">
            <div class="text-2xl font-bold text-red-600">{{ number_format($totalThisMonth, 0, ',', ' ') }} MRU</div>
            <div class="text-gray-600 mt-2">Dépenses ce mois-ci</div>
        </div>

        {{-- Nouvelle dépense --}}
        <div class="bg-white rounded-xl shadow p-6 md:col-span-2">
            <div class="text-sm font-bold text-gray-500 uppercase mb-3">Enregistrer un paiement</div>
            <form method="POST" action="{{ route('accounting.expenses.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Service</label>
                    <select name="expense_type_id" id="expenseTypeSelect" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">— Choisir —</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}"
                                data-fixed="{{ $type->is_fixed_amount ? '1' : '0' }}"
                                data-amount="{{ $type->fixed_amount }}">
                                {{ $type->name }} @if($type->is_fixed_amount)(fixe){{ '' }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Montant (MRU)</label>
                    <input type="number" name="amount" id="amountInput" step="0.01" min="0.01" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Mode de paiement</label>
                    <select name="payment_type_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">—</option>
                        @foreach($paymentTypes as $pt)
                            <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Date</label>
                    <input type="date" name="payment_date" value="{{ today()->toDateString() }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-5">
                    <input type="text" name="notes" placeholder="Note (optionnel)"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-5">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-5 py-2 rounded-lg">
                        <i class="fas fa-check mr-1"></i> Enregistrer et payer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Services de dépense --}}
    <div class="bg-white rounded-xl shadow p-4 mb-6">
        <div class="flex items-center justify-between mb-3">
            <div class="text-sm font-bold text-gray-500 uppercase">Services de dépense</div>
            <button type="button" onclick="document.getElementById('newTypeForm').classList.toggle('hidden')"
                    class="text-xs text-indigo-600 hover:underline font-semibold">
                <i class="fas fa-plus mr-1"></i>Nouveau service
            </button>
        </div>

        <div id="newTypeForm" class="hidden bg-gray-50 rounded-lg p-4 mb-4">
            <form method="POST" action="{{ route('accounting.expenses.types.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nom du service</label>
                    <input type="text" name="name" placeholder="Ex: Wifi, Électricité..." required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Compte comptable (charge)</label>
                    <select name="chart_of_account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">— Choisir —</option>
                        @foreach($chargeAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_fixed_amount" id="isFixedCheckbox" value="1" class="rounded border-gray-300">
                    <label for="isFixedCheckbox" class="text-xs font-bold text-gray-700">Montant fixe</label>
                </div>
                <div class="md:col-span-4" id="fixedAmountWrap" style="display:none;">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Montant fixe (MRU)</label>
                    <input type="number" name="fixed_amount" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-5 py-2 rounded-lg text-sm">
                        Créer
                    </button>
                </div>
            </form>
        </div>

        <div class="flex flex-wrap gap-2">
            @forelse($types as $type)
                <span class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                    {{ $type->name }}
                    <span class="text-gray-400">({{ $type->chartOfAccount->code }})</span>
                    @if($type->is_fixed_amount)
                        <span class="text-emerald-600">{{ number_format($type->fixed_amount, 0, ',', ' ') }} MRU/mois</span>
                    @else
                        <span class="text-amber-600">variable</span>
                    @endif
                </span>
            @empty
                <span class="text-sm text-gray-400">Aucun service défini pour l'instant.</span>
            @endforelse
        </div>
    </div>

    {{-- Historique --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Service</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Mode de paiement</th>
                        <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Montant</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Enregistré par</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="px-4 py-2 text-gray-700">{{ $expense->payment_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 font-semibold text-gray-800">{{ $expense->expenseType->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $expense->paymentType->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-right font-bold text-red-600">{{ number_format($expense->amount, 2, ',', ' ') }} MRU</td>
                            <td class="px-4 py-2 text-gray-500 text-xs">{{ $expense->creator->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Aucune dépense enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $expenses->links() }}</div>
    </div>
</div>

<script>
document.getElementById('isFixedCheckbox').addEventListener('change', function () {
    document.getElementById('fixedAmountWrap').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('expenseTypeSelect').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const amountInput = document.getElementById('amountInput');
    if (opt.dataset.fixed === '1' && opt.dataset.amount) {
        amountInput.value = opt.dataset.amount;
    }
});
</script>
@endsection
