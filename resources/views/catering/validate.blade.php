@extends('layouts.catering')
@section('title', 'Validation de code')

@section('content')

<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-teal-100 rounded-2xl mb-3">
            <i class="fa-solid fa-qrcode text-teal-600 text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Validation de code repas</h1>
        <p class="text-slate-500 mt-1 text-sm">Entrez ou scannez le code du bénéficiaire</p>
    </div>

    {{-- Code input --}}
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
        <div class="flex gap-3">
            <input id="codeInput" type="text"
                   placeholder="CAT-ABC-1234"
                   class="flex-1 border-2 border-slate-200 rounded-xl px-4 py-3 text-lg font-mono uppercase tracking-widest focus:outline-none focus:border-teal-500 transition"
                   autocomplete="off" autofocus>
            <button id="checkBtn"
                    class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold px-6 py-3 rounded-xl transition text-sm">
                <i class="fa-solid fa-magnifying-glass"></i> Vérifier
            </button>
        </div>
        <p class="text-xs text-slate-400 mt-2">Appuyez sur Entrée ou cliquez Vérifier</p>
    </div>

    {{-- Result panel (hidden initially) --}}
    <div id="resultPanel" class="hidden mb-6">
        <div id="resultCard" class="rounded-2xl border-2 p-6">
            <div id="resultContent"></div>
            <div id="confirmArea" class="hidden mt-4 pt-4 border-t border-slate-200">
                <button id="confirmBtn"
                        class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3.5 rounded-xl transition text-base">
                    <i class="fa-solid fa-circle-check"></i> Confirmer la consommation
                </button>
            </div>
        </div>
    </div>

    {{-- Loading indicator --}}
    <div id="loadingIndicator" class="hidden text-center py-8 text-slate-500">
        <i class="fa-solid fa-spinner fa-spin text-2xl text-teal-500 mb-2 block"></i>
        Vérification en cours...
    </div>

    {{-- Recent validations --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-teal-500 text-sm"></i>
                Validations récentes
            </h2>
            <span class="text-xs text-slate-400" id="feedUpdated">Mise à jour automatique</span>
        </div>
        <div id="recentFeed">
            @forelse($recentValidations as $v)
            <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-check text-emerald-600 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-mono font-semibold text-slate-700">
                            {{ $v->mealCode->code ?? '—' }}</p>
                        <p class="text-xs text-slate-400">
                            {{ $v->mealCode?->menuMeal?->menuDay?->weeklyMenu?->contract?->client?->name ?? '—' }}
                            · {{ $v->mealCode?->menuMeal?->type_label ?? '' }}
                        </p>
                    </div>
                </div>
                <span class="text-xs text-slate-400">{{ $v->consumed_at?->format('d/m H:i') }}</span>
            </div>
            @empty
            <p class="text-slate-400 text-sm text-center py-4">Aucune validation récente</p>
            @endforelse
        </div>
    </div>

</div>{{-- end max-w-3xl --}}

@push('scripts')
<script>
let currentCodeId = null;

// ── Check code ────────────────────────────────────────────────────────────────
function checkCode() {
    const code = $('#codeInput').val().trim().toUpperCase();
    if (!code) return;

    $('#resultPanel').addClass('hidden');
    $('#loadingIndicator').removeClass('hidden');
    $('#checkBtn').prop('disabled', true);

    $.ajax({
        url:  '/catering/check-code',
        method: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content'), code: code },
        success: function(res) {
            $('#loadingIndicator').addClass('hidden');
            $('#checkBtn').prop('disabled', false);
            renderResult(res);
        },
        error: function() {
            $('#loadingIndicator').addClass('hidden');
            $('#checkBtn').prop('disabled', false);
            renderResult({ status: 'error', message: 'Erreur de connexion. Réessayez.' });
        }
    });
}

// ── Render result ─────────────────────────────────────────────────────────────
function renderResult(res) {
    currentCodeId = null;
    const panel  = $('#resultPanel');
    const card   = $('#resultCard');
    const content = $('#resultContent');
    const confirmArea = $('#confirmArea');

    panel.removeClass('hidden');
    confirmArea.addClass('hidden');

    if (res.status === 'valid') {
        card.attr('class', 'rounded-2xl border-2 p-6 border-emerald-300 bg-emerald-50');
        content.html(`
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-emerald-700 text-lg">Code valide !</p>
                    <p class="font-mono text-sm text-slate-600">${res.code}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><p class="text-xs text-slate-500 mb-0.5">Client</p>
                     <p class="font-semibold text-slate-800">${res.client}${res.company ? ' <span class="text-xs text-slate-400 font-normal">('+res.company+')</span>' : ''}</p></div>
                <div><p class="text-xs text-slate-500 mb-0.5">Repas</p>
                     <p class="font-semibold text-slate-800">${res.type_icon} ${res.meal_type} · ${res.date}</p></div>
                <div><p class="text-xs text-slate-500 mb-0.5">Plats</p>
                     <p class="font-semibold text-slate-800">${res.dishes}</p></div>
                <div><p class="text-xs text-slate-500 mb-0.5">Montant</p>
                     <p class="font-bold text-teal-700 text-lg">${res.price}</p></div>
            </div>
        `);
        currentCodeId = res.code_id;
        confirmArea.removeClass('hidden');

    } else if (res.status === 'used') {
        card.attr('class', 'rounded-2xl border-2 p-6 border-amber-300 bg-amber-50');
        content.html(`
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-amber-700 text-lg">Code déjà utilisé</p>
                    <p class="text-sm text-slate-600">${res.message}</p>
                    <p class="text-sm text-slate-600 mt-1">Client : <strong>${res.client}</strong></p>
                </div>
            </div>
        `);

    } else if (res.status === 'not_found') {
        card.attr('class', 'rounded-2xl border-2 p-6 border-red-300 bg-red-50');
        content.html(`
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-circle-xmark text-red-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-red-700 text-lg">Code introuvable</p>
                    <p class="text-sm text-slate-600">Vérifiez le code et réessayez.</p>
                </div>
            </div>
        `);

    } else {
        card.attr('class', 'rounded-2xl border-2 p-6 border-slate-300 bg-slate-50');
        content.html(`<p class="text-slate-600">${res.message || 'Erreur inconnue.'}</p>`);
    }
}

// ── Confirm consumption ───────────────────────────────────────────────────────
$('#confirmBtn').on('click', function() {
    if (!currentCodeId) return;
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Validation...');

    $.ajax({
        url:  '/catering/confirm-code',
        method: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content'), code_id: currentCodeId },
        success: function(res) {
            $('#resultCard').attr('class', 'rounded-2xl border-2 p-6 border-emerald-400 bg-emerald-100');
            $('#confirmArea').addClass('hidden');
            $('#resultContent').prepend(`
                <div class="flex items-center gap-3 mb-4 p-3 bg-emerald-600 rounded-xl text-white">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                    <p class="font-bold">${res.message}</p>
                </div>
            `);
            currentCodeId = null;
            $('#codeInput').val('').focus();
            refreshFeed();
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.error || 'Erreur lors de la validation.';
            alert(msg);
            btn.prop('disabled', false).html('<i class="fa-solid fa-circle-check"></i> Confirmer la consommation');
        }
    });
});

// ── Keyboard shortcut ─────────────────────────────────────────────────────────
$('#codeInput').on('keydown', function(e) {
    if (e.key === 'Enter') checkCode();
});
$('#checkBtn').on('click', checkCode);

// ── Auto-refresh recent feed ──────────────────────────────────────────────────
function refreshFeed() {
    $.getJSON('/catering/recent-validations', function(data) {
        if (!data.items || !data.items.length) return;
        const html = data.items.map(item => `
            <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-check text-emerald-600 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-mono font-semibold text-slate-700">${item.code}</p>
                        <p class="text-xs text-slate-400">${item.client} · ${item.meal_type}</p>
                    </div>
                </div>
                <span class="text-xs text-slate-400">${item.time}</span>
            </div>
        `).join('');
        $('#recentFeed').html(html);
        $('#feedUpdated').text('Mis à jour à ' + new Date().toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'}));
    });
}

setInterval(refreshFeed, 8000);
</script>
@endpush

@endsection

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-teal-100 rounded-2xl mb-3">
            <i class="fa-solid fa-ticket-simple text-teal-600 text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-800">Validation de code repas</h2>
        <p class="text-slate-500 mt-1 text-sm">Entrez ou scannez le code du bénéficiaire</p>
    </div>

    {{-- Register alert --}}
    @if(!$openRegister)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-start gap-3">
        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
        <div>
            <p class="text-sm font-semibold text-amber-800">Aucune caisse ouverte</p>
            <p class="text-xs text-amber-700 mt-0.5">Les transactions ne seront pas liées à une caisse. Ouvrez une caisse pour une traçabilité complète.</p>
        </div>
    </div>
    @else
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
        <i class="fa-solid fa-cash-register text-emerald-500"></i>
        <p class="text-sm text-emerald-800 font-medium">Caisse ouverte — {{ $openRegister->shift === 'morning' ? 'Service matin' : 'Service soir' }}</p>
    </div>
    @endif

    {{-- Code input --}}
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
        <div class="flex gap-3">
            <input type="text" id="codeInput"
                   placeholder="CAT-XXXXXXXX" maxlength="15" autocomplete="off" spellcheck="false"
                   class="flex-1 border-2 border-slate-200 rounded-xl px-4 py-3 text-lg font-mono text-slate-800 uppercase focus:ring-2 focus:ring-teal-500 focus:border-teal-400 focus:outline-none tracking-widest placeholder:normal-case placeholder:text-slate-300"
                   oninput="this.value = this.value.toUpperCase()">
            <button onclick="checkCode()"
                    class="px-5 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-semibold text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="hidden sm:inline">Vérifier</span>
            </button>
        </div>
        <p class="text-xs text-slate-400 mt-2">Appuyez sur Entrée pour vérifier</p>
    </div>

    {{-- Result zone --}}
    <div id="resultZone" class="mb-6 hidden">

        {{-- VALID --}}
        <div id="resultValid" class="hidden bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <span class="font-bold text-emerald-800 text-lg">Code valide</span>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                <div class="bg-white rounded-xl p-3">
                    <p class="text-xs text-slate-500 mb-0.5">Client</p>
                    <p id="rClient" class="font-semibold text-slate-800"></p>
                </div>
                <div class="bg-white rounded-xl p-3">
                    <p class="text-xs text-slate-500 mb-0.5">Entreprise</p>
                    <p id="rCompany" class="font-semibold text-slate-800"></p>
                </div>
                <div class="bg-white rounded-xl p-3">
                    <p class="text-xs text-slate-500 mb-0.5">Date du repas</p>
                    <p id="rDate" class="font-semibold text-slate-800"></p>
                </div>
                <div class="bg-white rounded-xl p-3">
                    <p class="text-xs text-slate-500 mb-0.5">Montant</p>
                    <p id="rPrice" class="font-bold text-teal-700"></p>
                </div>
            </div>
            <div id="rMeals" class="flex flex-wrap gap-2 mb-4"></div>
            <form method="POST" action="{{ route('catering.confirm-code') }}" id="confirmForm">
                @csrf
                <input type="hidden" name="consumption_id" id="rConsumptionId">
                <button type="submit"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check-double"></i> Confirmer et valider ce repas
                </button>
            </form>
        </div>

        {{-- ALREADY USED --}}
        <div id="resultUsed" class="hidden bg-amber-50 border-2 border-amber-300 rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-lg"></i>
                <span class="font-bold text-amber-800 text-lg">Code déjà utilisé</span>
            </div>
            <p id="rUsedMessage" class="text-sm text-amber-700"></p>
        </div>

        {{-- NOT FOUND --}}
        <div id="resultNotFound" class="hidden bg-red-50 border-2 border-red-200 rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-2">
                <i class="fa-solid fa-circle-xmark text-red-500 text-lg"></i>
                <span class="font-bold text-red-800 text-lg">Code introuvable</span>
            </div>
            <p class="text-sm text-red-600">Ce code ne correspond à aucun bon de repas enregistré.</p>
        </div>

    </div>

    {{-- Recent validations today --}}
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-teal-400"></i> Validations récentes
            <span id="recentCount" class="ml-auto text-xs text-slate-400"></span>
        </h3>
        <div id="recentList">
            <p class="text-center text-slate-400 text-sm py-4">Les validations d'aujourd'hui apparaîtront ici.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('codeInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') checkCode();
});

function showResult(type) {
    document.getElementById('resultZone').classList.remove('hidden');
    ['resultValid','resultUsed','resultNotFound'].forEach(id => document.getElementById(id).classList.add('hidden'));
    document.getElementById('result' + type).classList.remove('hidden');
}

async function checkCode() {
    const code = document.getElementById('codeInput').value.trim();
    if (!code) { document.getElementById('codeInput').focus(); return; }

    try {
        const res = await fetch('{{ route("catering.check-code") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken,'Accept':'application/json'},
            body: JSON.stringify({ code })
        });
        const data = await res.json();

        if (data.status === 'valid') {
            document.getElementById('rClient').textContent    = data.client;
            document.getElementById('rCompany').textContent   = data.company || '—';
            document.getElementById('rDate').textContent      = data.date;
            document.getElementById('rPrice').textContent     = data.price_per_meal;
            document.getElementById('rConsumptionId').value   = data.consumption_id;
            const mealsEl = document.getElementById('rMeals');
            mealsEl.innerHTML = '';
            (data.meal_types || []).forEach(m => {
                const badge = document.createElement('span');
                badge.className = 'px-2.5 py-1 rounded-full text-xs bg-white border border-emerald-200 text-emerald-700 font-medium';
                badge.textContent = m;
                mealsEl.appendChild(badge);
            });
            showResult('Valid');
        } else if (data.status === 'already_used') {
            document.getElementById('rUsedMessage').textContent = data.message;
            showResult('Used');
        } else {
            showResult('NotFound');
        }
    } catch(err) {
        showResult('NotFound');
    }
}

async function loadRecent() {
    try {
        const res = await fetch('{{ route("catering.recent-validations") }}', {
            headers:{'Accept':'application/json','X-CSRF-TOKEN': csrfToken}
        });
        const data = await res.json();
        const list = document.getElementById('recentList');
        document.getElementById('recentCount').textContent = data.total + ' auj.';
        if (!data.items || !data.items.length) {
            list.innerHTML = '<p class="text-center text-slate-400 text-sm py-4">Aucune validation aujourd\'hui.</p>';
            return;
        }
        list.innerHTML = data.items.map(item => `
            <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                <div>
                    <span class="font-mono text-xs text-teal-700 bg-teal-50 px-2 py-0.5 rounded">${item.code}</span>
                    <span class="text-xs text-slate-600 ml-2">${item.client}</span>
                </div>
                <span class="text-xs text-slate-400">${item.used_at}</span>
            </div>
        `).join('');
    } catch(e) {}
}

loadRecent();
// Refresh every 30 seconds
setInterval(loadRecent, 30000);
</script>
@endpush

@endsection
