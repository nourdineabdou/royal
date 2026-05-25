@extends('layouts.hr')
@section('title', 'Employés')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Employés</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $employees->total() }} employé(s) enregistré(s)</p>
    </div>
    @can('hr.employees.create')
    <button onclick="document.getElementById('modalAdd').classList.remove('hidden')"
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
        <i class="fas fa-plus"></i> Ajouter employé
    </button>
    @endcan
</div>

{{-- TABLE --}}
<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Employé</th>
                    <th class="px-4 py-3 text-left">Poste</th>
                    <th class="px-4 py-3 text-left">Téléphone</th>
                    <th class="px-4 py-3 text-left">Date d'embauche</th>
                    <th class="px-4 py-3 text-right">Salaire base</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($employees as $emp)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-xs">
                                {{ strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $emp->full_name }}</p>
                                <p class="text-xs text-gray-400">ID #{{ $emp->id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $emp->jobTitle->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $emp->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $emp->hire_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-700">
                        {{ $emp->salary_base ? number_format($emp->salary_base, 0, ',', ' ').' MRU' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $emp->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-500' }}">
                            {{ $emp->status === 'active' ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @can('hr.employees.edit')
                        <button onclick='openEditModal(@json($emp))'
                                class="text-blue-500 hover:text-blue-700 p-1" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        @endcan
                        @can('hr.employees.delete')
                        <form method="POST" action="{{ route('hr.employees.destroy', $emp) }}" class="inline"
                              onsubmit="return confirm('Supprimer cet employé ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 p-1" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-10 text-gray-400">Aucun employé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t">{{ $employees->links() }}</div>
</div>

{{-- MODAL ADD --}}
<div id="modalAdd" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Ajouter un employé</h3>
            <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('hr.employees.store') }}" class="p-6 grid grid-cols-2 gap-4">
            @csrf
            <div class="col-span-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Prénom *</label>
                <input name="first_name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div class="col-span-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Nom *</label>
                <input name="last_name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Poste *</label>
                <select name="job_title_id" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <option value="">-- Choisir --</option>
                    @foreach($jobTitles as $jt)
                    <option value="{{ $jt->id }}">{{ $jt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
                <input name="phone" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Date d'embauche *</label>
                <input name="hire_date" type="date" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Salaire de base (MRU)</label>
                <input name="salary_base" type="number" step="0.01" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Statut *</label>
                <select name="status" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                </select>
            </div>
            <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Adresse</label>
                <input name="address" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            <div class="col-span-2 flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="modalEdit" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Modifier l'employé</h3>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="editForm" method="POST" class="p-6 grid grid-cols-2 gap-4">
            @csrf @method('PUT')
            <div class="col-span-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Prénom *</label>
                <input id="ef_first_name" name="first_name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="col-span-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Nom *</label>
                <input id="ef_last_name" name="last_name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Poste *</label>
                <select id="ef_job_title_id" name="job_title_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    @foreach($jobTitles as $jt)
                    <option value="{{ $jt->id }}">{{ $jt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
                <input id="ef_phone" name="phone" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Date d'embauche *</label>
                <input id="ef_hire_date" name="hire_date" type="date" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Salaire de base (MRU)</label>
                <input id="ef_salary_base" name="salary_base" type="number" step="0.01" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Statut *</label>
                <select id="ef_status" name="status" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                </select>
            </div>
            <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Adresse</label>
                <input id="ef_address" name="address" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            <div class="col-span-2 flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditModal(emp) {
    document.getElementById('editForm').action = '/hr/employees/' + emp.id;
    document.getElementById('ef_first_name').value  = emp.first_name;
    document.getElementById('ef_last_name').value   = emp.last_name;
    document.getElementById('ef_job_title_id').value = emp.job_title_id;
    document.getElementById('ef_phone').value       = emp.phone ?? '';
    document.getElementById('ef_hire_date').value   = emp.hire_date;
    document.getElementById('ef_salary_base').value = emp.salary_base ?? '';
    document.getElementById('ef_status').value      = emp.status;
    document.getElementById('ef_address').value     = emp.address ?? '';
    document.getElementById('modalEdit').classList.remove('hidden');
}
</script>
@endpush
