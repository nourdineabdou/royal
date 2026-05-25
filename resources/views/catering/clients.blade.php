@extends('layouts.catering')
@section('title', 'Clients')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Clients Catering</h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ $clients->total() }} client(s) enregistrés</p>
    </div>
    @can('catering.clients.create')
    <button onclick="openModal('createModal')"
            class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition">
        <i class="fa-solid fa-plus"></i> Nouveau client
    </button>
    @endcan
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Nom</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Entreprise</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Téléphone</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Email</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600">Contrats</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($clients as $client)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-3 font-medium text-slate-800">{{ $client->name }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $client->company ?: '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $client->phone ?: '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $client->email ?: '—' }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-700 text-xs font-bold">
                        {{ $client->catering_contracts_count }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    @can('catering.clients.edit')
                    <button onclick="openEditClient({{ json_encode($client) }})"
                            class="text-slate-400 hover:text-teal-600 transition p-1 rounded" title="Modifier">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    @endcan
                    @if($client->catering_contracts_count == 0)
                    @can('catering.clients.delete')
                    <form method="POST" action="{{ route('catering.clients.destroy', $client) }}" class="inline"
                          onsubmit="return confirm('Supprimer \u00ab {{ $client->name }} \u00bb ?')">
                        @csrf @method('DELETE')
                        <button class="text-slate-400 hover:text-red-600 transition p-1 rounded" title="Supprimer">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    @endcan
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                    <i class="fa-solid fa-users text-3xl mb-2 block opacity-30"></i>
                    Aucun client enregistré
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($clients->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $clients->links() }}
    </div>
    @endif
</div>

{{-- ── CREATE MODAL ──────────────────────────────────────────────────────────── --}}
<div id="createModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <h2 class="font-bold text-slate-800">Nouveau client</h2>
            <button onclick="closeModal('createModal')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('catering.clients.store') }}" class="p-5 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nom *</label>
                    <input name="name" required value="{{ old('name') }}"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Téléphone</label>
                    <input name="phone" value="{{ old('phone') }}"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Entreprise</label>
                    <input name="company" value="{{ old('company') }}"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('createModal')"
                        class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Annuler</button>
                <button type="submit"
                        class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-xl transition">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── EDIT MODAL ────────────────────────────────────────────────────────────── --}}
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <h2 class="font-bold text-slate-800">Modifier le client</h2>
            <button onclick="closeModal('editModal')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-5 space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nom *</label>
                    <input id="edit_name" name="name" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Téléphone</label>
                    <input id="edit_phone" name="phone"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input id="edit_email" name="email" type="email"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Entreprise</label>
                    <input id="edit_company" name="company"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                    <textarea id="edit_notes" name="notes" rows="2"
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('editModal')"
                        class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Annuler</button>
                <button type="submit"
                        class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-xl transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}
function openEditClient(client) {
    document.getElementById('editForm').action = '/catering/clients/' + client.id;
    document.getElementById('edit_name').value    = client.name    || '';
    document.getElementById('edit_phone').value   = client.phone   || '';
    document.getElementById('edit_email').value   = client.email   || '';
    document.getElementById('edit_company').value = client.company || '';
    document.getElementById('edit_notes').value   = client.notes   || '';
    openModal('editModal');
}
// Auto-open create modal if validation failed
@if($errors->any())
openModal('createModal');
@endif
</script>
@endpush

@endsection
