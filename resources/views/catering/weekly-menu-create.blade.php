@extends('layouts.catering')
@section('title', 'Créer un menu — ' . ($contract->client->name ?? ''))

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('catering.contracts.show', $contract) }}" class="text-slate-400 hover:text-slate-600 transition">
        <i class="fa-solid fa-arrow-left text-lg"></i>
    </a>
    <div>
        <h1 class="text-xl font-bold text-slate-800">Créer un menu hebdomadaire</h1>
        <p class="text-sm text-slate-500">
            Contrat : <strong>{{ $contract->client->name ?? '—' }}</strong>
            · {{ $contract->guest_count }} convives
            · {{ implode(', ', array_map(fn($t) => match($t) { 'breakfast'=>'Petit-dej','lunch'=>'Déjeuner','dinner'=>'Dîner',default=>$t }, $contract->getActiveMealTypes())) }}
        </p>
    </div>
</div>

{{-- Client + Week selector --}}
<div class="bg-white rounded-2xl shadow-sm p-5 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                <i class="fa-solid fa-building mr-1 text-teal-500"></i>
                Client catering
            </label>
            <select id="clientSelect" class="w-full">
                @foreach($allContracts as $c)
                <option value="{{ $c['id'] }}" data-programmed="{{ $c['programmed'] ? '1' : '0' }}" {{ $c['id'] === $contract->id ? 'selected' : '' }}>
                    @if($c['out_of_period'])
                        ⚠️ {{ $c['client_name'] }} — hors période (terminé/débute le {{ $c['end_date'] }})
                    @else
                        {{ $c['programmed'] ? '✅' : '⬜' }} {{ $c['client_name'] }} — {{ $c['programmed'] ? 'Programmé' : 'Non programmé' }}
                    @endif
                </option>
                @endforeach
            </select>
            <div class="flex items-center gap-4 mt-2">
                <label class="flex items-center gap-1.5 text-xs text-slate-500">
                    <input type="checkbox" id="filterProgrammed" checked> Programmés
                </label>
                <label class="flex items-center gap-1.5 text-xs text-slate-500">
                    <input type="checkbox" id="filterNotProgrammed" checked> Non programmés
                </label>
                <span class="text-xs text-slate-400">— statut pour la semaine affichée</span>
            </div>
        </div>
        <div class="border-l border-slate-100 pl-6 md:pl-6">
            <p class="text-sm font-semibold text-slate-700 mb-2">Contrat sélectionné</p>
            <p class="text-sm text-slate-600">
                <strong>{{ $contract->client->name ?? '—' }}</strong><br>
                {{ $contract->guest_count }} convives ·
                {{ implode(', ', array_map(fn($t) => match($t) { 'breakfast'=>'Petit-dej','lunch'=>'Déjeuner','dinner'=>'Dîner',default=>$t }, $contract->getActiveMealTypes())) }}
            </p>
        </div>
    </div>
</div>

{{-- Week selector --}}
<div class="bg-white rounded-2xl shadow-sm p-5 mb-6">
    <label class="block text-sm font-semibold text-slate-700 mb-2">
        <i class="fa-solid fa-calendar mr-1 text-teal-500"></i>
        Sélectionner la semaine
    </label>
    <div class="flex items-center gap-4 flex-wrap">
        <input type="date" id="weekInput"
               class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
             value="{{ $prefillWeekStart ?? date('Y-m-d') }}">
        <button type="button" id="reloadWeekBtn"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition">
            <i class="fa-solid fa-rotate"></i> Afficher la semaine
        </button>
        <button type="button" id="copyPrevWeekBtn"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition">
            <i class="fa-solid fa-copy"></i> Copier la semaine precedente
        </button>
        <a href="{{ route('catering.weekly-menu.print', $contract) }}?week_start={{ $prefillWeekStart }}" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition">
            <i class="fa-solid fa-print"></i> Imprimer ce menu
        </a>
        <div id="weekLabel" class="text-sm text-slate-500 font-medium"></div>
    </div>

    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center gap-3 flex-wrap">
        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-copy text-indigo-500"></i> Copier le menu d'un autre client (même semaine)
        </label>
        <select id="copyFromClientSelect" style="min-width: 220px;">
            <option value="">— Choisir un client —</option>
            @foreach($allContracts as $c)
                @if($c['id'] !== $contract->id)
                <option value="{{ $c['id'] }}" {{ !$c['programmed'] ? 'disabled' : '' }}>
                    {{ $c['client_name'] }} {{ $c['programmed'] ? '' : '(pas encore programmé)' }}
                </option>
                @endif
            @endforeach
        </select>
        <button type="button" id="copyFromClientBtn"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-semibold transition">
            <i class="fa-solid fa-clone"></i> Copier ici
        </button>
        <span class="text-xs text-slate-400">Copie les plats et quantités de ce client sur cette semaine — à ajuster puis enregistrer pour {{ $contract->client->name ?? 'ce client' }}.</span>
    </div>
    <p class="text-xs text-slate-400 mt-1">La semaine commence toujours le lundi. Vous choisissez un ou plusieurs plats, et une quantité, par service et par jour.</p>

    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center gap-3 flex-wrap">
        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-bolt text-amber-500"></i> Quantité par défaut pour toute la semaine
        </label>
        <input type="number" min="0" id="globalQtyInput" placeholder="{{ $contract->guest_count }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm w-28 focus:ring-2 focus:ring-teal-500 focus:outline-none">
        <button type="button" id="applyGlobalQtyBtn"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 text-sm font-semibold transition">
            <i class="fa-solid fa-fill-drip"></i> Appliquer partout
        </button>
        <span class="text-xs text-slate-400">Par défaut chaque case reprend l'effectif du contrat ({{ $contract->guest_count }}) — modifiable case par case ou jour par jour ci-dessous.</span>
    </div>
</div>

{{-- Dynamic planning table --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-800">Tableau de programmation</h2>
        <span class="text-xs text-slate-500">Colonnes basées sur le contrat (1/2/3 repas)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[980px] text-sm" id="planningTable">
            <thead class="bg-slate-50 border-b border-slate-100" id="planningHead">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Jour</th>
                    @foreach($mealTypeDefs as $type)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">{{ $type['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody id="planningBody">
                @foreach($weekDays as $day)
                    <tr class="border-b border-slate-100 day-row" data-date="{{ $day['date'] }}">
                        <td class="px-4 py-3 align-top">
                            <p class="font-semibold text-slate-800 text-sm">{{ $day['label'] }}</p>
                            <p class="text-xs text-slate-500 mb-2">{{ $day['human_label'] }}</p>
                            <label class="text-[11px] text-slate-400 block mb-0.5">Qté ce jour (tous services)</label>
                            <input type="number" min="0" class="day-qty-input w-24 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                   placeholder="{{ $contract->guest_count }}" data-date="{{ $day['date'] }}">
                        </td>
                        @foreach($mealTypeDefs as $type)
                            @php
                                $cellData = $prefillDays[$day['date']][$type['key']] ?? [];
                                $selectedIds = $cellData['meal_ids'] ?? [];
                                $cellQty = $cellData['quantity'] ?? null;
                            @endphp
                            <td class="px-4 py-3">
                                <select class="meal-select" multiple data-date="{{ $day['date'] }}" data-type="{{ $type['key'] }}">
                                    @foreach($allMeals as $meal)
                                        <option value="{{ $meal->id }}" {{ in_array($meal->id, $selectedIds) ? 'selected' : '' }}>{{ $meal->name }}</option>
                                    @endforeach
                                </select>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <i class="fa-solid fa-users text-slate-300 text-xs"></i>
                                    <input type="number" min="0" class="qty-input w-20 border border-slate-200 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                           data-date="{{ $day['date'] }}" data-type="{{ $type['key'] }}"
                                           placeholder="{{ $contract->guest_count }}" value="{{ $cellQty }}">
                                    <span class="text-[11px] text-slate-400">repas</span>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Empty state --}}
<div id="emptyState" class="hidden bg-amber-50 border-2 border-dashed border-amber-200 rounded-2xl p-12 text-center text-amber-700 mb-6">
    <i class="fa-solid fa-triangle-exclamation text-4xl mb-3 block opacity-50"></i>
    <p class="font-semibold">{{ $emptyReason ?? "Aucun jour actif sur cette semaine pour ce contrat." }}</p>
    <p class="text-sm text-amber-500 mt-2">Choisissez une autre semaine, ou vérifiez les dates du contrat.</p>
</div>

{{-- Save button --}}
<div id="saveBar" class="hidden fixed bottom-0 left-64 right-0 bg-white border-t border-slate-200 px-6 py-4 flex items-center justify-between gap-4 z-40">
    <p class="text-sm text-slate-600" id="saveBarText">Remplissez les repas puis enregistrez.</p>
    <button id="saveBtn"
            class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold px-6 py-2.5 rounded-xl transition text-sm shadow-md">
        <i class="fa-solid fa-floppy-disk"></i> Enregistrer le menu
    </button>
</div>

{{-- Toast --}}
<div id="toast" class="fixed top-5 right-5 z-50 hidden px-5 py-3 rounded-xl text-sm font-semibold shadow-xl max-w-sm"></div>

@push('head')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        padding-top: 4px;
    }
    .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        padding: 2px 6px;
    }
</style>
@endpush

@push('scripts')
<script>
// ── Contract data from PHP ────────────────────────────────────────────────────
const CONTRACT = {
    id:            {{ $contract->id }},
    guest_count:   {{ $contract->guest_count }}
};

const ALL_MEALS = @json($allMeals->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values());

function getMondayOf(dateStr) {
    const d = new Date(dateStr);
    const day = d.getDay(); // 0=Sun
    const diff = (day === 0) ? -6 : 1 - day;
    d.setDate(d.getDate() + diff);
    return d;
}

function formatDate(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${dd}`;
}

function initSelect2() {
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
        window.jQuery('.meal-select').select2({
            width: '100%',
            placeholder: 'Choisir plat(s)',
            allowClear: true,
            closeOnSelect: false,
        });
        window.jQuery('#copyFromClientSelect').select2({
            width: '100%',
            placeholder: 'Choisir un client…',
        });
        window.jQuery('#clientSelect').select2({
            width: '100%',
            placeholder: 'Choisir un client…',
            matcher: function (params, data) {
                if (!data.element) return data;
                const isProgrammed = data.element.dataset.programmed === '1';
                const showProgrammed = document.getElementById('filterProgrammed').checked;
                const showNotProgrammed = document.getElementById('filterNotProgrammed').checked;
                if (isProgrammed && !showProgrammed) return null;
                if (!isProgrammed && !showNotProgrammed) return null;
                if (!params.term || params.term.trim() === '') return data;
                if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) return data;
                return null;
            },
        });
    }
}

// ── Sélecteur de client : changer de client sans quitter l'écran ────────────────
// (jQuery .on('change') — Select2 déclenche le changement via jQuery, pas toujours via
// l'événement natif du navigateur, donc addEventListener seul peut ne rien capter)
function goToClient(contractId) {
    if (!contractId) return;
    const week = document.getElementById('weekInput').value || '{{ $prefillWeekStart }}';
    window.location.href = `/catering/contracts/${contractId}/create-menu?week_start=${week}`;
}
document.getElementById('clientSelect').addEventListener('change', function () {
    goToClient(this.value);
});
if (window.jQuery) {
    window.jQuery('#clientSelect').on('select2:select', function (e) {
        goToClient(e.params.data.id);
    });
}

// ── Filtre programmé/non-programmé : pris en compte par le "matcher" Select2 ci-dessus
// à chaque ouverture/recherche — rien d'autre à faire ici.

// ── Collect form data ─────────────────────────────────────────────────────────
function collectData() {
    const days = {};
    document.querySelectorAll('#planningBody .meal-select').forEach(select => {
        const date = select.dataset.date;
        const type = select.dataset.type;
        const values = Array.from(select.selectedOptions).map(opt => parseInt(opt.value)).filter(v => !Number.isNaN(v));
        if (!values.length) return;
        const qtyInput = document.querySelector(`.qty-input[data-date="${date}"][data-type="${type}"]`);
        const qty = qtyInput && qtyInput.value !== '' ? parseInt(qtyInput.value) : null;
        if (!days[date]) days[date] = {};
        days[date][type] = { meal_ids: values, quantity: qty };
    });
    return days;
}

function applyTemplateDays(days) {
    document.querySelectorAll('#planningBody .meal-select').forEach(select => {
        select.value = '';
    });
    document.querySelectorAll('#planningBody .qty-input').forEach(input => { input.value = ''; });

    Object.entries(days || {}).forEach(([date, mealTypes]) => {
        Object.entries(mealTypes || {}).forEach(([type, mealData]) => {
            const mealIds = (mealData?.meal_ids || []).map(v => String(v));
            const select = document.querySelector(`#planningBody .meal-select[data-date="${date}"][data-type="${type}"]`);
            if (select) {
                Array.from(select.options).forEach(opt => {
                    opt.selected = mealIds.includes(opt.value);
                });
            }
            const qtyInput = document.querySelector(`.qty-input[data-date="${date}"][data-type="${type}"]`);
            if (qtyInput && mealData?.quantity !== undefined && mealData?.quantity !== null) {
                qtyInput.value = mealData.quantity;
            }
        });
    });

    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
        window.jQuery('#planningBody .meal-select').trigger('change.select2');
    }
}

// ── Quantité : réglage rapide par jour et pour toute la semaine (moins de clics) ─
document.querySelectorAll('.day-qty-input').forEach(input => {
    input.addEventListener('input', function () {
        const date = this.dataset.date;
        if (this.value === '') return;
        document.querySelectorAll(`.qty-input[data-date="${date}"]`).forEach(qtyInput => {
            qtyInput.value = this.value;
        });
    });
});

document.getElementById('applyGlobalQtyBtn').addEventListener('click', function () {
    const val = document.getElementById('globalQtyInput').value;
    if (val === '') {
        showToast('Indiquez une quantité à appliquer.', true);
        return;
    }
    document.querySelectorAll('.day-qty-input').forEach(input => { input.value = val; });
    document.querySelectorAll('.qty-input').forEach(input => { input.value = val; });
    showToast('Quantité appliquée à toute la semaine.');
});

function updateWeekLabel() {
    const weekInput = document.getElementById('weekInput');
    const weekLabel = document.getElementById('weekLabel');
    if (!weekInput.value) return;
    const monday = getMondayOf(weekInput.value);
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    weekLabel.textContent = `Semaine du ${monday.toLocaleDateString('fr-FR', {day:'numeric',month:'long'})} au ${sunday.toLocaleDateString('fr-FR', {day:'numeric',month:'long',year:'numeric'})}`;
}

// ── Show toast ────────────────────────────────────────────────────────────────
function showToast(msg, isError = false) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `fixed top-5 right-5 z-50 px-5 py-3 rounded-xl text-sm font-semibold shadow-xl max-w-sm
        ${isError ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200'}`;
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 4000);
}

const weekInput = document.getElementById('weekInput');
updateWeekLabel();
initSelect2();

if (document.querySelectorAll('#planningBody tr').length === 0) {
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('saveBar').classList.add('hidden');
} else {
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('saveBar').classList.remove('hidden');
}

document.getElementById('reloadWeekBtn').addEventListener('click', function () {
    if (!weekInput.value) {
        showToast('Selectionnez une semaine.', true);
        return;
    }
    const monday = getMondayOf(weekInput.value);
    window.location.href = `{{ route('catering.weekly-menu.create', $contract) }}?week_start=${formatDate(monday)}`;
});

weekInput.addEventListener('change', function () {
    if (!weekInput.value) return;
    const monday = getMondayOf(weekInput.value);
    window.location.href = `{{ route('catering.weekly-menu.create', $contract) }}?week_start=${formatDate(monday)}`;
});

document.getElementById('copyPrevWeekBtn').addEventListener('click', function () {
    if (!weekInput.value) {
        showToast('Sélectionnez d\'abord une semaine.', true);
        return;
    }

    const btn = this;
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Copie...';

    const monday = getMondayOf(weekInput.value);
    const params = new URLSearchParams({ week_start: formatDate(monday) });

    fetch(`{{ route('catering.weekly-menu.template', $contract) }}?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            if (!data.exists) {
                showToast(data.message || 'Aucune semaine precedente disponible.', true);
                return;
            }

            applyTemplateDays(data.days || {});
            showToast('Semaine precedente copiee avec succes.');
        })
        .catch(() => {
            showToast('Erreur pendant la copie de la semaine precedente.', true);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        });
});

// ── Copier le menu d'un autre client (même semaine) ─────────────────────────────
document.getElementById('copyFromClientBtn').addEventListener('click', function () {
    const fromContractId = document.getElementById('copyFromClientSelect').value;
    if (!fromContractId) {
        showToast('Choisissez un client à copier.', true);
        return;
    }
    if (!weekInput.value) {
        showToast('Sélectionnez d\'abord une semaine.', true);
        return;
    }

    const btn = this;
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Copie...';

    const monday = getMondayOf(weekInput.value);
    const params = new URLSearchParams({ week_start: formatDate(monday), from_contract_id: fromContractId });

    fetch(`{{ route('catering.weekly-menu.copy-from', $contract) }}?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            if (!data.exists) {
                showToast(data.message || 'Ce client n\'a pas de menu sur cette semaine.', true);
                return;
            }
            applyTemplateDays(data.days || {});
            showToast('Menu copié — ajustez puis enregistrez pour ce client.');
        })
        .catch(() => {
            showToast('Erreur pendant la copie.', true);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        });
});

// ── Save button ───────────────────────────────────────────────────────────────
document.getElementById('saveBtn').addEventListener('click', function() {
    const days = collectData();
    if (Object.keys(days).length === 0) {
        showToast('Sélectionnez au moins un plat pour un repas.', true);
        return;
    }

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement...';

    const monday = getMondayOf(weekInput.value);

    fetch('/catering/contracts/{{ $contract->id }}/weekly-menu', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            week_start: formatDate(monday),
            days: days,
        }),
    })
    .then(async (res) => {
        const payload = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(payload.error || 'Erreur lors de la création du menu.');
        }
        return payload;
    })
    .then((res) => {
        showToast('Menu enregistre avec succes.');
        setTimeout(() => { window.location.href = res.redirect; }, 800);
    })
    .catch((err) => {
        showToast(err.message || 'Erreur lors de la création du menu.', true);
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Enregistrer le menu';
    });
});
</script>
@endpush

@endsection
