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
        <div id="weekLabel" class="text-sm text-slate-500 font-medium"></div>
    </div>
    <p class="text-xs text-slate-400 mt-1">La semaine commence toujours le lundi. Vous choisissez un plat par type de repas et par jour.</p>
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
                    <tr class="border-b border-slate-100">
                        <td class="px-4 py-3 align-top">
                            <p class="font-semibold text-slate-800 text-sm">{{ $day['label'] }}</p>
                            <p class="text-xs text-slate-500">{{ $day['human_label'] }}</p>
                        </td>
                        @foreach($mealTypeDefs as $type)
                            @php
                                $selectedIds = $prefillDays[$day['date']][$type['key']]['meal_ids'] ?? [];
                            @endphp
                            <td class="px-4 py-3">
                                <select class="meal-select" multiple data-date="{{ $day['date'] }}" data-type="{{ $type['key'] }}">
                                    @foreach($allMeals as $meal)
                                        <option value="{{ $meal->id }}" {{ in_array($meal->id, $selectedIds) ? 'selected' : '' }}>{{ $meal->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1">Ajouter un ou plusieurs plats</p>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Empty state --}}
<div id="emptyState" class="hidden bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-12 text-center text-slate-400 mb-6">
    <i class="fa-solid fa-calendar-days text-4xl mb-3 block opacity-30"></i>
    <p>Aucun jour actif sur cette semaine pour ce contrat.</p>
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
    }
}

// ── Collect form data ─────────────────────────────────────────────────────────
function collectData() {
    const days = {};
    document.querySelectorAll('#planningBody .meal-select').forEach(select => {
        const date = select.dataset.date;
        const type = select.dataset.type;
        const values = Array.from(select.selectedOptions).map(opt => parseInt(opt.value)).filter(v => !Number.isNaN(v));
        if (!values.length) return;
        if (!days[date]) days[date] = {};
        days[date][type] = { meal_ids: values };
    });
    return days;
}

function applyTemplateDays(days) {
    document.querySelectorAll('#planningBody .meal-select').forEach(select => {
        select.value = '';
    });

    Object.entries(days || {}).forEach(([date, mealTypes]) => {
        Object.entries(mealTypes || {}).forEach(([type, mealData]) => {
            const mealIds = (mealData?.meal_ids || []).map(v => String(v));
            const select = document.querySelector(`#planningBody .meal-select[data-date="${date}"][data-type="${type}"]`);
            if (select) {
                Array.from(select.options).forEach(opt => {
                    opt.selected = mealIds.includes(opt.value);
                });
            }
        });
    });

    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
        window.jQuery('#planningBody .meal-select').trigger('change.select2');
    }
}

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
