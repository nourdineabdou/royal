@extends('layouts.hr')
@section('title', 'Contrats')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold text-gray-800">Contrats</h2>
    <button onclick="document.getElementById('modalAdd').classList.remove('hidden')"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
        <i class="fas fa-plus mr-1"></i> Nouveau contrat
    </button>
</div>

@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('hr.contracts') }}" class="bg-white rounded-xl shadow p-4 {{ !$status ? 'ring-2 ring-indigo-400' : '' }}">
        <div class="text-xs text-gray-400 uppercase font-bold">Tous les contrats</div>
        <div class="text-2xl font-bold text-gray-700 mt-1">{{ $contracts->total() }}</div>
    </a>
    <a href="{{ route('hr.contracts', ['status' => 'ending_soon']) }}" class="bg-white rounded-xl shadow p-4 {{ $status === 'ending_soon' ? 'ring-2 ring-amber-400' : '' }}">
        <div class="text-xs text-gray-400 uppercase font-bold">Fin de contrat &lt; 30 jours</div>
        <div class="text-2xl font-bold text-amber-600 mt-1">{{ $endingSoonCount }}</div>
    </a>
    <a href="{{ route('hr.contracts', ['status' => 'expired']) }}" class="bg-white rounded-xl shadow p-4 {{ $status === 'expired' ? 'ring-2 ring-red-400' : '' }}">
        <div class="text-xs text-gray-400 uppercase font-bold">Expirés (non clôturés)</div>
        <div class="text-2xl font-bold text-red-600 mt-1">{{ $expiredCount }}</div>
    </a>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b text-xs text-gray-400 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Employé</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">Début</th>
                <th class="px-4 py-3 text-left">Fin</th>
                <th class="px-4 py-3 text-left">Période d'essai</th>
                <th class="px-4 py-3 text-center">Statut</th>
                <th class="px-4 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contracts as $c)
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                <td class="px-4 py-3 font-medium text-gray-700">
                    {{ $c->employee->full_name ?? '—' }}
                    <div class="text-xs text-gray-400">{{ $c->employee->jobTitle->name ?? '' }}</div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-medium">{{ \App\Models\EmployeeContract::TYPES[$c->type] ?? $c->type }}</span>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $c->start_date->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-gray-600">
                    {{ $c->end_date?->format('d/m/Y') ?? '— (CDI)' }}
                    @if($c->is_ending_soon)
                    <span class="block text-xs text-amber-600 font-semibold"><i class="fas fa-triangle-exclamation"></i> Dans {{ (int) round(now()->diffInDays($c->end_date)) }} j</span>
                    @elseif($c->is_expired)
                    <span class="block text-xs text-red-600 font-semibold"><i class="fas fa-circle-exclamation"></i> Expiré</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-600">
                    {{ $c->trial_period_end?->format('d/m/Y') ?? '—' }}
                    @if($c->is_in_trial)<span class="block text-xs text-blue-600">En cours</span>@endif
                </td>
                <td class="px-4 py-3 text-center">
                    @switch($c->status)
                        @case('active')     <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">Actif</span> @break
                        @case('ended')      <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 font-medium">Terminé</span> @break
                        @case('terminated') <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">Rompu</span> @break
                    @endswitch
                </td>
                <td class="px-4 py-3 text-center">
                    @if($c->status === 'active')
                    <form method="POST" action="{{ route('hr.contracts.terminate', $c) }}" onsubmit="return confirm('Marquer ce contrat comme rompu ?')">
                        @csrf
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700"><i class="fas fa-ban mr-1"></i>Rompre</button>
                    </form>
                    @else
                    <span class="text-gray-300 text-xs">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-10 text-gray-400">Aucun contrat pour l'instant.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $contracts->links() }}</div>

{{-- MODAL: Nouveau contrat --}}
<div id="modalAdd" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Nouveau contrat</h3>
            <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('hr.contracts.store') }}" class="p-6 grid grid-cols-2 gap-4">
            @csrf
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Employé *</label>
                <select name="employee_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">— Choisir —</option>
                    @foreach($employees as $e)
                    <option value="{{ $e->id }}">{{ $e->full_name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">S'il a déjà un contrat actif, il sera automatiquement clôturé (renouvellement).</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type *</label>
                <select name="type" id="contract_type" required onchange="toggleEndDate()" class="w-full border rounded-lg px-3 py-2 text-sm">
                    @foreach(\App\Models\EmployeeContract::TYPES as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Salaire (MRU)</label>
                <input type="number" step="0.01" name="salary" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date de début *</label>
                <input type="date" name="start_date" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div id="end_date_wrapper">
                <label class="block text-xs font-medium text-gray-600 mb-1">Date de fin</label>
                <input type="date" name="end_date" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Fin de période d'essai</label>
                <input type="date" name="trial_period_end" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="col-span-2 flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleEndDate() {
    const isCdi = document.getElementById('contract_type').value === 'cdi';
    document.getElementById('end_date_wrapper').style.display = isCdi ? 'none' : '';
}
document.addEventListener('DOMContentLoaded', toggleEndDate);
</script>
@endsection
