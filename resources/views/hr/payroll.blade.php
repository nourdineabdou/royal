@extends('layouts.hr')
@section('title', 'Paie')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Fiches de paie</h1>
        <p class="text-gray-500 text-sm mt-1">
            {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
        </p>
    </div>

    <div class="flex items-center gap-3">
        {{-- Generate payroll --}}
        @can('hr.payroll.generate')
        <form method="POST" action="{{ route('hr.payroll.generate') }}"
              onsubmit="return confirm('Générer les fiches de paie pour ce mois ?')">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                <i class="fas fa-cogs"></i> Générer paie
            </button>
        </form>
        @endcan
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" class="bg-white rounded-xl shadow p-4 mb-6 grid grid-cols-2 md:grid-cols-6 gap-4 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Mois</label>
        <select name="month" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none">
            @for($m=1;$m<=12;$m++)
            <option value="{{ $m }}" {{ $m==$month?'selected':'' }}>{{ \Carbon\Carbon::createFromDate(null,$m,1)->translatedFormat('F') }}</option>
            @endfor
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Année</label>
        <input name="year" type="number" value="{{ $year }}" class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Nom</label>
        <input type="text" name="name" value="{{ $name }}" placeholder="Prénom ou nom..."
               class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Poste</label>
        <select name="job_title_id" class="w-full border rounded-lg px-3 py-2 text-sm">
            <option value="">Tous les postes</option>
            @foreach($jobTitles as $jt)
                <option value="{{ $jt->id }}" {{ (string) $jobTitleId === (string) $jt->id ? 'selected' : '' }}>{{ $jt->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
        <input type="text" name="phone" value="{{ $phone }}" placeholder="Rechercher un numéro..."
               class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>
    <div class="flex gap-2">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            <i class="fas fa-filter mr-1"></i> Filtrer
        </button>
        @if($jobTitleId || $phone || $name)
        <a href="{{ route('hr.payroll') }}?month={{ $month }}&year={{ $year }}" class="border rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
            Réinitialiser
        </a>
        @endif
    </div>
</form>

{{-- Summary bar (calculé sur l'ensemble filtré, pas seulement la page affichée) --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-100">
        <p class="text-xs text-indigo-500 font-medium">Masse salariale nette</p>
        <p class="text-2xl font-bold text-indigo-700 mt-1">{{ number_format($totalNet,0,',',' ') }} MRU</p>
    </div>
    <div class="bg-green-50 rounded-xl p-4 border border-green-100">
        <p class="text-xs text-green-600 font-medium">Fiches payées</p>
        <p class="text-2xl font-bold text-green-700 mt-1">{{ $paidCount }}</p>
    </div>
    <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
        <p class="text-xs text-amber-600 font-medium">En attente</p>
        <p class="text-2xl font-bold text-amber-700 mt-1">{{ $pendCount }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Employé</th>
                    <th class="px-4 py-3 text-right">Salaire base</th>
                    <th class="px-4 py-3 text-right">Bonus</th>
                    <th class="px-4 py-3 text-right">Déductions</th>
                    <th class="px-4 py-3 text-right">Avances</th>
                    <th class="px-4 py-3 text-right">Net à payer</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($payrolls as $p)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $p->employee->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $p->employee->jobTitle->name ?? '' }}</p>
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-gray-700">{{ number_format($p->base_salary,0,',',' ') }}</td>
                    <td class="px-4 py-3 text-right text-green-600 font-mono">+{{ number_format($p->bonus,0,',',' ') }}</td>
                    <td class="px-4 py-3 text-right text-red-500 font-mono">-{{ number_format($p->deduction,0,',',' ') }}</td>
                    <td class="px-4 py-3 text-right text-orange-500 font-mono">-{{ number_format($p->advance_deduction,0,',',' ') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-indigo-700 font-mono">{{ number_format($p->net_salary,0,',',' ') }} MRU</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $p->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $p->status === 'paid' ? 'Payé' : 'En attente' }}
                        </span>
                        @if($p->paid_at)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $p->paid_at->format('d/m/Y') }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2 flex-wrap">
                            @if($p->status === 'pending')
                            @can('hr.payroll.mark-paid')
                            <button type="button" onclick="openPayModal({{ $p->id }}, '{{ $p->employee->full_name ?? '' }}', '{{ number_format($p->net_salary,0,',',' ') }}')"
                                    class="text-xs bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg">
                                Marquer payé
                            </button>
                            @endcan
                            @elseif($p->paymentType)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $p->paymentType->name }}</p>
                            @else
                            @can('hr.payroll.mark-paid')
                            <button type="button" onclick="openPayModal({{ $p->id }}, '{{ $p->employee->full_name ?? '' }}', '{{ number_format($p->net_salary,0,',',' ') }}')"
                                    class="text-xs bg-amber-100 hover:bg-amber-200 text-amber-700 px-3 py-1 rounded-lg" title="Mode de paiement non renseigné — absent de la comptabilité">
                                <i class="fas fa-triangle-exclamation mr-1"></i>Renseigner le paiement
                            </button>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endcan
                            @endif
                            <a href="{{ route('hr.payroll.payslip', $p) }}" target="_blank" title="Fiche de paie"
                               class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-600 px-2 py-1 rounded-lg">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-10 text-gray-400">Aucune fiche de paie. Cliquez sur "Générer paie".</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t">{{ $payrolls->links() }}</div>
</div>

{{-- MODAL PAIEMENT --}}
<div id="modalPay" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Marquer la paie comme payée</h3>
            <button type="button" onclick="document.getElementById('modalPay').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="payForm" method="POST" class="p-6 space-y-4">
            @csrf
            <p class="text-sm text-gray-600">
                Employé : <span id="payEmployeeName" class="font-medium text-gray-800"></span><br>
                Net à payer : <span id="payAmount" class="font-bold text-indigo-700"></span> MRU
            </p>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Mode de paiement *</label>
                <select name="payment_type_id" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
                    <option value="">-- Choisir --</option>
                    @foreach($paymentTypes as $pt)
                    <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalPay').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">Confirmer le paiement</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openPayModal(payrollId, employeeName, amount) {
    document.getElementById('payForm').action = '/hr/payroll/' + payrollId + '/paid';
    document.getElementById('payEmployeeName').textContent = employeeName;
    document.getElementById('payAmount').textContent = amount;
    document.getElementById('modalPay').classList.remove('hidden');
}
</script>
@endpush
@endsection
