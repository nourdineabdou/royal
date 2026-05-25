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
               value="{{ date('Y-m-d') }}">
        <div id="weekLabel" class="text-sm text-slate-500 font-medium"></div>
    </div>
    <p class="text-xs text-slate-400 mt-1">La semaine commence toujours le lundi.</p>
</div>

{{-- Dynamic grid container --}}
<div id="menuGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6"></div>

{{-- Empty state --}}
<div id="emptyState" class="hidden bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-12 text-center text-slate-400 mb-6">
    <i class="fa-solid fa-calendar-days text-4xl mb-3 block opacity-30"></i>
    <p>Sélectionnez une semaine pour voir les jours actifs du contrat.</p>
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

@push('scripts')
<script>
// ── Contract data from PHP ────────────────────────────────────────────────────
const CONTRACT = {
    id:            {{ $contract->id }},
    guest_count:   {{ $contract->guest_count }},
    active_days:   @json($contract->active_days ?? [1,2,3,4,5]),
    has_breakfast: {{ $contract->has_breakfast ? 'true' : 'false' }},
    has_lunch:     {{ $contract->has_lunch     ? 'true' : 'false' }},
    has_dinner:    {{ $contract->has_dinner    ? 'true' : 'false' }},
};

const ALL_MEALS = @json($allMeals->map(fn($m) => ['id' => $m->id, 'name' => $m->name]));

const MEAL_TYPES = [];
if (CONTRACT.has_breakfast) MEAL_TYPES.push({ key: 'breakfast', label: 'Petit-déjeuner', icon: '🌅', color: 'amber' });
if (CONTRACT.has_lunch)     MEAL_TYPES.push({ key: 'lunch',     label: 'Déjeuner',       icon: '☀️',  color: 'blue'  });
if (CONTRACT.has_dinner)    MEAL_TYPES.push({ key: 'dinner',    label: 'Dîner',           icon: '🌙', color: 'indigo'});

const DAY_NAMES = { 1:'Lundi', 2:'Mardi', 3:'Mercredi', 4:'Jeudi', 5:'Vendredi', 6:'Samedi', 7:'Dimanche' };

// ── Utilities ─────────────────────────────────────────────────────────────────
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

function formatHuman(d) {
    return d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
}

function getDayOfWeekISO(d) {
    return d.getDay() === 0 ? 7 : d.getDay();
}

function colorClasses(color) {
    return {
        amber:  { bg: 'bg-amber-50',  border: 'border-amber-200',  badge: 'bg-amber-100 text-amber-700'  },
        blue:   { bg: 'bg-blue-50',   border: 'border-blue-200',   badge: 'bg-blue-100 text-blue-700'    },
        indigo: { bg: 'bg-indigo-50', border: 'border-indigo-200', badge: 'bg-indigo-100 text-indigo-700' },
    }[color] || { bg: 'bg-slate-50', border: 'border-slate-200', badge: 'bg-slate-100 text-slate-600' };
}

// ── Build the weekly grid ─────────────────────────────────────────────────────
function buildGrid(monday) {
    const grid = document.getElementById('menuGrid');
    const empty = document.getElementById('emptyState');
    const saveBar = document.getElementById('saveBar');
    grid.innerHTML = '';

    // Filter active days for this week
    const activeDays = CONTRACT.active_days.map(Number);
    const daysToShow = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(monday);
        d.setDate(d.getDate() + i);
        const iso = getDayOfWeekISO(d);
        if (activeDays.includes(iso)) {
            daysToShow.push({ date: formatDate(d), isoDay: iso, label: DAY_NAMES[iso], humanLabel: formatHuman(d) });
        }
    }

    if (daysToShow.length === 0) {
        empty.classList.remove('hidden');
        saveBar.classList.add('hidden');
        return;
    }

    empty.classList.add('hidden');
    saveBar.classList.remove('hidden');

    daysToShow.forEach(day => {
        const card = document.createElement('div');
        card.className = 'bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden';
        card.dataset.date = day.date;

        let mealsHtml = '';
        MEAL_TYPES.forEach(type => {
            const c = colorClasses(type.color);
            const mealOptions = ALL_MEALS.map(m =>
                `<label class="flex items-center gap-2 py-1 cursor-pointer hover:bg-slate-50 rounded px-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-500 focus:ring-teal-400"
                           data-date="${day.date}" data-type="${type.key}" data-mealid="${m.id}">
                    <span class="text-xs text-slate-700">${m.name}</span>
                </label>`
            ).join('');

            mealsHtml += `
            <div class="border-t border-slate-100 p-3 ${c.bg}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold ${c.badge.split(' ')[1]} inline-flex items-center gap-1">
                        ${type.icon} ${type.label}
                    </span>
                    <div class="flex gap-1">
                        <button type="button" onclick="selectAll(this, '${day.date}', '${type.key}')"
                                class="text-xs text-teal-500 hover:underline">Tout</button>
                        <span class="text-slate-300 text-xs">|</span>
                        <button type="button" onclick="selectNone(this, '${day.date}', '${type.key}')"
                                class="text-xs text-slate-400 hover:underline">Aucun</button>
                    </div>
                </div>
                <div class="max-h-36 overflow-y-auto space-y-0.5 meal-list">
                    ${mealOptions}
                </div>
            </div>`;
        });

        card.innerHTML = `
        <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-teal-50 to-white">
            <p class="font-semibold text-slate-800 text-sm capitalize">${day.label}</p>
            <p class="text-xs text-slate-400">${day.humanLabel}</p>
        </div>
        ${mealsHtml}`;

        grid.appendChild(card);
    });
}

// ── Select helpers ────────────────────────────────────────────────────────────
function selectAll(btn, date, type) {
    document.querySelectorAll(`input[data-date="${date}"][data-type="${type}"]`)
        .forEach(cb => cb.checked = true);
}
function selectNone(btn, date, type) {
    document.querySelectorAll(`input[data-date="${date}"][data-type="${type}"]`)
        .forEach(cb => cb.checked = false);
}

// ── Collect form data ─────────────────────────────────────────────────────────
function collectData() {
    const days = {};
    document.querySelectorAll('#menuGrid [data-date]').forEach(card => {
        const date = card.dataset.date;
        days[date] = {};
        MEAL_TYPES.forEach(type => {
            const mealIds = [];
            card.querySelectorAll(`input[data-type="${type.key}"]:checked`)
                .forEach(cb => mealIds.push(parseInt(cb.dataset.mealid)));
            if (mealIds.length > 0) {
                days[date][type.key] = { meal_ids: mealIds };
            }
        });
        if (Object.keys(days[date]).length === 0) delete days[date];
    });
    return days;
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

// ── Week input event ──────────────────────────────────────────────────────────
const weekInput = document.getElementById('weekInput');
const weekLabel = document.getElementById('weekLabel');

function onDateChange() {
    if (!weekInput.value) return;
    const monday = getMondayOf(weekInput.value);
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    weekLabel.textContent = `Semaine du ${monday.toLocaleDateString('fr-FR', {day:'numeric',month:'long'})}
        au ${sunday.toLocaleDateString('fr-FR', {day:'numeric',month:'long',year:'numeric'})}`;
    buildGrid(monday);
}

weekInput.addEventListener('change', onDateChange);
onDateChange(); // initialize on load

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

    $.ajax({
        url:         '/catering/contracts/{{ $contract->id }}/weekly-menu',
        method:      'POST',
        contentType: 'application/json',
        data:        JSON.stringify({
            _token:     $('meta[name="csrf-token"]').attr('content'),
            week_start: formatDate(monday),
            days:       days,
        }),
        success: function(res) {
            showToast('Menu créé avec succès !');
            setTimeout(() => { window.location.href = res.redirect; }, 1000);
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.error || 'Erreur lors de la création du menu.';
            showToast(msg, true);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Enregistrer le menu';
        }
    });
});
</script>
@endpush

@endsection
