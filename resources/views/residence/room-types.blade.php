@extends('layouts.residence')
@section('title', 'Types de chambre')

@section('content')

<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <h2 class="text-xl font-bold text-slate-800">Types de chambre</h2>
    @can('residence.rooms.create')
    <button onclick="document.getElementById('addTypeModal').classList.remove('hidden')"
            class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow">
        <i class="fa-solid fa-plus"></i> Nouveau type
    </button>
    @endcan
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="text-xs text-slate-400 uppercase bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-5 py-3 text-left">Nom</th>
                <th class="px-5 py-3 text-left">Description</th>
                <th class="px-5 py-3 text-right">Prix/nuit</th>
                <th class="px-5 py-3 text-right">Nb chambres</th>
                <th class="px-5 py-3 w-24"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($roomTypes as $rt)
            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                <td class="px-5 py-3 font-semibold text-slate-800">{{ $rt->name }}</td>
                <td class="px-5 py-3 text-slate-500">{{ $rt->description ?: '—' }}</td>
                <td class="px-5 py-3 text-right font-medium text-violet-700">{{ number_format($rt->base_price, 0, ',', ' ') }} MRU</td>
                <td class="px-5 py-3 text-right">
                    <span class="px-2 py-0.5 bg-violet-100 text-violet-700 rounded-full font-medium">{{ $rt->rooms_count }}</span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-2">
                        @can('residence.rooms.edit')
                        <button onclick='openEditType(@json($rt))'
                                class="text-xs text-slate-500 hover:text-violet-600 transition px-2 py-1 rounded-lg hover:bg-violet-50">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        @endcan
                        @can('residence.rooms.delete')
                        <form method="POST" action="{{ route('residence.room-types.destroy', $rt) }}"
                              onsubmit="return confirm('Supprimer ce type ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 px-2 py-1 rounded-lg hover:bg-red-50 transition">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-12 text-slate-400">Aucun type de chambre</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Add Type Modal --}}
@can('residence.rooms.create')
<div id="addTypeModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Nouveau type de chambre</h3>
            <button onclick="document.getElementById('addTypeModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('residence.room-types.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="ex: Suite, Studio, Appartement…"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Prix par nuit (MRU) <span class="text-red-500">*</span></label>
                <input type="number" name="base_price" required step="0.01" min="0" placeholder="0"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Description optionnelle…"
                          class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addTypeModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">Créer</button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- Edit Type Modal --}}
@can('residence.rooms.edit')
<div id="editTypeModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Modifier le type</h3>
            <button onclick="document.getElementById('editTypeModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" id="editTypeForm" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nom</label>
                <input type="text" name="name" id="editTypeName" required
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Prix par nuit (MRU)</label>
                <input type="number" name="base_price" id="editTypePrice" required step="0.01" min="0"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
                <textarea name="description" id="editTypeDesc" rows="2"
                          class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('editTypeModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<script>
function openEditType(rt) {
    document.getElementById('editTypeForm').action = '/residence/room-types/' + rt.id;
    document.getElementById('editTypeName').value  = rt.name;
    document.getElementById('editTypePrice').value = rt.base_price;
    document.getElementById('editTypeDesc').value  = rt.description ?? '';
    document.getElementById('editTypeModal').classList.remove('hidden');
}
</script>
@endcan

@endsection
