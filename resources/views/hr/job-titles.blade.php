@extends('layouts.hr')
@section('title', 'Postes')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Postes</h1>
        <p class="text-gray-500 text-sm mt-1">Créez et modifiez les fonctions/postes proposés aux employés.</p>
    </div>
    <button onclick="document.getElementById('modalAddJobTitle').classList.remove('hidden')"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
        <i class="fas fa-briefcase"></i> Ajouter un poste
    </button>
</div>

@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-right">Salaire de base</th>
                    <th class="px-4 py-3 text-center">Employés</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($jobTitles as $jt)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $jt->name }}</td>
                    <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $jt->description ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-700">
                        {{ $jt->base_salary ? number_format($jt->base_salary, 0, ',', ' ').' MRU' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $jt->employees_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $jt->employees_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button onclick="openEditJobTitleModal(@json($jt))"
                                class="text-blue-500 hover:text-blue-700 p-1" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('hr.job-titles.destroy', $jt) }}" class="inline"
                              onsubmit="return confirm('Supprimer ce poste ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 p-1" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-10 text-gray-400">Aucun poste défini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t">{{ $jobTitles->links() }}</div>
</div>

{{-- MODAL ADD --}}
<div id="modalAddJobTitle" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Ajouter un poste</h3>
            <button onclick="document.getElementById('modalAddJobTitle').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('hr.job-titles.store') }}" class="p-6 grid grid-cols-1 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nom *</label>
                <input name="name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="ex: Chef Cuisinier">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Salaire de base (MRU)</label>
                <input name="base_salary" type="number" step="0.01" min="0" class="w-full border rounded-lg px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">Utilisé par défaut lors de la génération de paie si l'employé n'a pas de salaire propre.</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalAddJobTitle').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="modalEditJobTitle" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Modifier le poste</h3>
            <button onclick="document.getElementById('modalEditJobTitle').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="editJobTitleForm" method="POST" class="p-6 grid grid-cols-1 gap-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nom *</label>
                <input id="ejt_name" name="name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Salaire de base (MRU)</label>
                <input id="ejt_base_salary" name="base_salary" type="number" step="0.01" min="0" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                <textarea id="ejt_description" name="description" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalEditJobTitle').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditJobTitleModal(jt) {
    document.getElementById('editJobTitleForm').action = '/hr/job-titles/' + jt.id;
    document.getElementById('ejt_name').value = jt.name;
    document.getElementById('ejt_base_salary').value = jt.base_salary ?? '';
    document.getElementById('ejt_description').value = jt.description ?? '';
    document.getElementById('modalEditJobTitle').classList.remove('hidden');
}
</script>
@endpush
