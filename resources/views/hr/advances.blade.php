@extends('layouts.hr')
@section('title', 'Avances sur salaire')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Avances sur salaire</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $advances->total() }} avance(s) enregistrée(s)</p>
    </div>
    @can('hr.advances.request')
    <button onclick="document.getElementById('modalAdd').classList.remove('hidden')"
            class="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
        <i class="fas fa-plus"></i> Nouvelle avance
    </button>
    @endcan
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Employé</th>
                    <th class="px-4 py-3 text-right">Montant total</th>
                    <th class="px-4 py-3 text-right">Reste à rembourser</th>
                    <th class="px-4 py-3 text-center">% mensuel</th>
                    <th class="px-4 py-3 text-center">Date</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($advances as $adv)
                @php
                    $statusClass = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-blue-100 text-blue-700','deducted'=>'bg-gray-100 text-gray-500','completed'=>'bg-emerald-100 text-emerald-700'][$adv->status] ?? '';
                    $statusLabel = ['pending'=>'En attente','approved'=>'En remboursement','deducted'=>'Déduite','completed'=>'Soldée'][$adv->status] ?? $adv->status;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $adv->employee->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $adv->employee->jobTitle->name ?? '' }}</p>
                    </td>
                    <td class="px-4 py-3 text-right font-bold font-mono text-violet-700">
                        {{ number_format($adv->amount,0,',',' ') }} MRU
                    </td>
                    <td class="px-4 py-3 text-right font-mono {{ $adv->remaining_balance > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                        {{ number_format($adv->remaining_balance,0,',',' ') }} MRU
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ rtrim(rtrim(number_format($adv->repayment_percentage,2),'0'),'.') }}%</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $adv->date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($adv->status === 'pending')
                        @can('hr.advances.approve')
                        <form method="POST" action="{{ route('hr.advances.approve', $adv) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg">
                                Approuver
                            </button>
                        </form>
                        @endcan
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-10 text-gray-400">Aucune avance enregistrée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t">{{ $advances->links() }}</div>
</div>

{{-- MODAL ADD --}}
<div id="modalAdd" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Nouvelle avance</h3>
            <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('hr.advances.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Employé *</label>
                <select name="employee_id" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-400">
                    <option value="">-- Choisir --</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Montant total (MRU) *</label>
                <input name="amount" type="number" min="1" step="0.01" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Remboursement mensuel (% du salaire) *</label>
                <input name="repayment_percentage" type="number" min="1" max="100" step="0.01" value="20" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-400">
                <p class="text-xs text-gray-400 mt-1">Ce pourcentage du salaire sera déduit chaque mois jusqu'au remboursement complet.</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date *</label>
                <input name="date" type="date" value="{{ today()->toDateString() }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-violet-600 hover:bg-violet-700 text-white rounded-lg font-medium">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
