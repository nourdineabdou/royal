@extends('layouts.catering')
@section('title', 'Contrats')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Contrats de catering</h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ $contracts->total() }} contrat(s)</p>
    </div>
    @can('catering.contracts.create')
    <button onclick="openModal('modalCreate')"
            class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-sm">
        <i class="fa-solid fa-plus"></i> Nouveau contrat
    </button>
    @endcan
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Client</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Période</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Convives</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Repas</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Menus</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Statut</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($contracts as $c)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800">{{ $c->client->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $c->client->company ?? '' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-xs">
                        {{ $c->start_date->format('d/m/Y') }} → {{ $c->end_date->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-center text-slate-700 font-medium">{{ $c->guest_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            @if($c->has_breakfast)<span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">🌅 petit-dej</span>@endif
                            @if($c->has_lunch)<span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">☀️ déjeuner</span>@endif
                            @if($c->has_dinner)<span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">🌙 dîner</span>@endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-700 text-xs font-bold">
                            {{ $c->weekly_menus_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $c->status === 'active' ? 'bg-emerald-100 text-emerald-700' :
                               ($c->status === 'paused' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ $c->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('catering.contracts.show', $c) }}"
                               class="w-8 h-8 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-600 inline-flex items-center justify-center transition"
                               title="Voir détail">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            @can('catering.contracts.edit')
                            <button onclick="openEditContract({{ $c->load('prices')->toJson() }})"
                                    class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 inline-flex items-center justify-center transition"
                                    title="Modifier">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            @endcan
                            @can('catering.contracts.delete')
                            <form method="POST" action="{{ route('catering.contracts.destroy', $c) }}" class="inline"
                                  onsubmit="return confirm('Supprimer ce contrat et toutes ses données ?')">
                                @csrf @method('DELETE')
                                <button class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 inline-flex items-center justify-center transition"
                                        title="Supprimer">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-file-contract text-3xl mb-2 block opacity-30"></i>
                        Aucun contrat — créez-en un !
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($contracts->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $contracts->links() }}</div>
    @endif
</div>

{{-- ── CREATE MODAL ──────────────────────────────────────────────────────────── --}}
<div id="modalCreate" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-6">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <h2 class="font-bold text-slate-800 text-lg">Nouveau contrat</h2>
            <button onclick="closeModal('modalCreate')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('catering.contracts.store') }}" class="p-6">
            @csrf
            @include('catering._contract_form', ['edit' => false, 'contract' => null])
            <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalCreate')"
                        class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Annuler</button>
                <button type="submit"
                        class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-xl transition">
                    Créer le contrat
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── EDIT MODAL ────────────────────────────────────────────────────────────── --}}
<div id="modalEdit" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-6">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <h2 class="font-bold text-slate-800 text-lg">Modifier le contrat</h2>
            <button onclick="closeModal('modalEdit')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form id="editContractForm" method="POST" class="p-6">
            @csrf @method('PUT')
            @include('catering._contract_form', ['edit' => true, 'contract' => null])
            <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalEdit')"
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
    document.getElementById(id).classList.remove('flex');
    document.getElementById(id).classList.add('hidden');
}

function openEditContract(c) {
    const f = document.getElementById('editContractForm');
    f.action = '/catering/contracts/' + c.id;

    f.querySelector('[name="client_id"]').value  = c.client_id ?? '';
    f.querySelector('[name="start_date"]').value = (c.start_date ?? '').substring(0, 10);
    f.querySelector('[name="end_date"]').value   = (c.end_date   ?? '').substring(0, 10);
    f.querySelector('[name="guest_count"]').value = c.guest_count ?? '';
    f.querySelector('[name="status"]').value      = c.status ?? 'active';

    // Active days
    f.querySelectorAll('[name="active_days[]"]').forEach(cb => {
        cb.checked = (c.active_days || []).includes(parseInt(cb.value));
    });

    // Meal types
    ['breakfast', 'lunch', 'dinner'].forEach(type => {
        const cb = f.querySelector('[name="has_' + type + '"]');
        if (cb) cb.checked = !!c['has_' + type];
    });

    // Prices from contract.prices array
    const prices = c.prices || [];
    ['breakfast', 'lunch', 'dinner'].forEach(type => {
        const pr = prices.find(p => p.type === type);
        const inp = f.querySelector('[name="price_' + type + '"]');
        if (inp) inp.value = pr ? pr.price : '';
    });

    openModal('modalEdit');
    updatePriceFields(f);
}

function updatePriceFields(form) {
    ['breakfast', 'lunch', 'dinner'].forEach(type => {
        const cb  = form.querySelector('[name="has_' + type + '"]');
        const row = form.querySelector('#price_row_' + type + (form.id === 'editContractForm' ? '_edit' : ''));
        if (cb && row) {
            row.style.display = cb.checked ? '' : 'none';
        }
    });
}

// Bind checkbox change events for both forms
document.querySelectorAll('[name="has_breakfast"],[name="has_lunch"],[name="has_dinner"]').forEach(cb => {
    cb.addEventListener('change', function() {
        updatePriceFields(this.closest('form'));
    });
});

@if($errors->any())
openModal('modalCreate');
@endif
</script>
@endpush

@endsection
