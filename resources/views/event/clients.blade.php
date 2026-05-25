@extends('layouts.event')
@section('title', 'Clients')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Clients</h2>
        <p class="text-sm text-slate-500 mt-0.5">{{ $clients->total() }} client(s) enregistré(s)</p>
    </div>
    @can('events.clients.create')
    <button onclick="document.getElementById('modalClient').classList.remove('hidden')"
            class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
        <i class="fa-solid fa-plus"></i> Nouveau client
    </button>
    @endcan
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nom</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Entreprise</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Téléphone</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Événements</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($clients as $client)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $client->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $client->company ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $client->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $client->email ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-violet-100 text-violet-700 font-medium">
                            {{ $client->events_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('events.clients.edit')
                            <button onclick='openEditClient(@json($client))'
                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-violet-100 text-slate-500 hover:text-violet-600 inline-flex items-center justify-center transition">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            @endcan
                            @if($client->events_count === 0)
                            @can('events.clients.delete')
                            <form method="POST" action="{{ route('event.clients.destroy', $client) }}"
                                  onsubmit="return confirm('Supprimer ce client ?')">
                                @csrf @method('DELETE')
                                <button class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 inline-flex items-center justify-center transition">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-400 py-10">
                        <i class="fa-solid fa-address-book text-2xl block mb-2"></i>
                        Aucun client enregistré.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clients->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $clients->links() }}</div>
    @endif
</div>

{{-- CREATE MODAL --}}
<div id="modalClient" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Nouveau client</h3>
            <button onclick="document.getElementById('modalClient').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('event.clients.store') }}" class="p-6 space-y-4">
            @csrf
            @include('event._client_form')
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalClient').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div id="modalEditClient" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Modifier le client</h3>
            <button onclick="document.getElementById('modalEditClient').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" id="editClientForm" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="editName" required
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Téléphone</label>
                    <input type="text" name="phone" id="editPhone"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" id="editEmail"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Entreprise</label>
                <input type="text" name="company" id="editCompany"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                <textarea name="notes" id="editNotes" rows="2"
                          class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalEditClient').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditClient(c) {
    document.getElementById('editClientForm').action = '/events/clients/' + c.id;
    document.getElementById('editName').value    = c.name    ?? '';
    document.getElementById('editPhone').value   = c.phone   ?? '';
    document.getElementById('editEmail').value   = c.email   ?? '';
    document.getElementById('editCompany').value = c.company ?? '';
    document.getElementById('editNotes').value   = c.notes   ?? '';
    document.getElementById('modalEditClient').classList.remove('hidden');
}
</script>
@endpush

@endsection
