@extends('layouts.hr')
@section('title', 'Emplacements')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Emplacements</h1>
        <p class="text-gray-500 text-sm mt-1">Créez et modifiez les lieux de travail liés aux employés.</p>
    </div>
    <button onclick="document.getElementById('modalAddSite').classList.remove('hidden')"
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
        <i class="fas fa-map-marker-alt"></i> Ajouter emplacement
    </button>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Adresse</th>
                    <th class="px-4 py-3 text-left">Téléphone</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($sites as $site)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-800">{{ $site->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $site->address ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $site->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <button onclick="openSiteEditModal(@json($site))"
                                class="text-blue-500 hover:text-blue-700 p-1" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('hr.sites.destroy', $site) }}" class="inline" onsubmit="return confirm('Supprimer cet emplacement ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 p-1" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-10 text-gray-400">Aucun emplacement défini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t">{{ $sites->links() }}</div>
</div>

<div id="modalAddSite" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Ajouter un emplacement</h3>
            <button onclick="document.getElementById('modalAddSite').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('hr.sites.store') }}" class="p-6 grid grid-cols-1 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nom *</label>
                <input name="name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Adresse</label>
                <input name="address" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
                <input name="phone" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalAddSite').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditSite" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Modifier l'emplacement</h3>
            <button onclick="document.getElementById('modalEditSite').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="editSiteForm" method="POST" class="p-6 grid grid-cols-1 gap-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nom *</label>
                <input id="es_name" name="name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Adresse</label>
                <input id="es_address" name="address" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
                <input id="es_phone" name="phone" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalEditSite').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openSiteEditModal(site) {
    document.getElementById('editSiteForm').action = '/hr/sites/' + site.id;
    document.getElementById('es_name').value = site.name;
    document.getElementById('es_address').value = site.address ?? '';
    document.getElementById('es_phone').value = site.phone ?? '';
    document.getElementById('modalEditSite').classList.remove('hidden');
}
</script>
@endpush
