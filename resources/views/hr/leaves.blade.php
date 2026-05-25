@extends('layouts.hr')
@section('title', 'Congés')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestion des congés</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $leaves->total() }} demande(s) au total</p>
    </div>
    @can('hr.leaves.request')
    <button onclick="document.getElementById('modalAdd').classList.remove('hidden')"
            class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
        <i class="fas fa-plus"></i> Nouvelle demande
    </button>
    @endcan
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Employé</th>
                    <th class="px-4 py-3 text-center">Type</th>
                    <th class="px-4 py-3 text-center">Début</th>
                    <th class="px-4 py-3 text-center">Fin</th>
                    <th class="px-4 py-3 text-center">Jours</th>
                    <th class="px-4 py-3 text-left">Raison</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($leaves as $leave)
                @php
                    $typeColors = ['annual'=>'bg-blue-100 text-blue-700','sick'=>'bg-red-100 text-red-600','unpaid'=>'bg-gray-100 text-gray-600'];
                    $typeLabels = ['annual'=>'Annuel','sick'=>'Maladie','unpaid'=>'Non payé'];
                    $statusColors = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-500'];
                    $statusLabels = ['pending'=>'En attente','approved'=>'Approuvé','rejected'=>'Rejeté'];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $leave->employee->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $leave->employee->jobTitle->name ?? '' }}</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $typeColors[$leave->type] ?? '' }}">
                            {{ $typeLabels[$leave->type] ?? $leave->type }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $leave->start_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $leave->end_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center font-bold text-gray-700">{{ $leave->days }}</td>
                    <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $leave->reason ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $statusColors[$leave->status] ?? '' }}">
                            {{ $statusLabels[$leave->status] ?? $leave->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($leave->status === 'pending')
                        @can('hr.leaves.approve')
                        <form method="POST" action="{{ route('hr.leaves.approve', $leave) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-green-500 hover:text-green-700 p-1" title="Approuver">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endcan
                        @can('hr.leaves.reject')
                        <form method="POST" action="{{ route('hr.leaves.reject', $leave) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-400 hover:text-red-600 p-1" title="Rejeter">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        @endcan
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-10 text-gray-400">Aucune demande de congé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t">{{ $leaves->links() }}</div>
</div>

{{-- MODAL ADD --}}
<div id="modalAdd" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Nouvelle demande de congé</h3>
            <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('hr.leaves.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Employé *</label>
                <select name="employee_id" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">-- Choisir --</option>
                    @foreach(\App\Models\Employee::where('status','active')->orderBy('first_name')->get() as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type *</label>
                <select name="type" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="annual">Annuel</option>
                    <option value="sick">Maladie</option>
                    <option value="unpaid">Non payé</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Début *</label>
                    <input name="start_date" type="date" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Fin *</label>
                    <input name="end_date" type="date" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            </div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Raison</label>
                <input name="reason" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium">Soumettre</button>
            </div>
        </form>
    </div>
</div>
@endsection
