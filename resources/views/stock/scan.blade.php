@extends('layouts.stock')

@section('title', 'Scanner')
@section('header', 'Scanner entrée / sortie')

@push('head')
<style>
    .qty-in { font-size: 16px; }
    .qty-in::-webkit-outer-spin-button, .qty-in::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .qty-in[type=number] { -moz-appearance: textfield; }
    #reader video { object-fit: cover; border-radius: 0.75rem; }
    #reader { overflow: hidden; border-radius: 0.75rem; }
    .flash-new { animation: flashNew 0.9s ease-out; }
    @keyframes flashNew { from { background: #bbf7d0; } to { background: transparent; } }
    .mode-in  .mode-accent { background: #059669; }
    .mode-out .mode-accent { background: #dc2626; }
    .mode-in  .mode-text { color: #047857; }
    .mode-out .mode-text { color: #b91c1c; }
    .mode-btn[aria-pressed=true][data-mode=in]  { background: #059669; color: #fff; border-color: #059669; }
    .mode-btn[aria-pressed=true][data-mode=out] { background: #dc2626; color: #fff; border-color: #dc2626; }
</style>
@endpush

@section('content')
<div id="scanApp" class="mode-in py-4 pb-48 max-w-3xl mx-auto space-y-3">

    {{-- ── Mode + stock ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="mode-accent h-1.5"></div>
        <div class="p-3 space-y-3">
            <div class="grid grid-cols-2 gap-2" role="group" aria-label="Type de mouvement">
                <button type="button" class="mode-btn py-3 rounded-lg border-2 border-slate-200 font-bold text-sm text-slate-600 flex items-center justify-center gap-2" data-mode="in" aria-pressed="true">
                    <i class="fa-solid fa-arrow-down"></i> ENTRÉE
                </button>
                <button type="button" class="mode-btn py-3 rounded-lg border-2 border-slate-200 font-bold text-sm text-slate-600 flex items-center justify-center gap-2" data-mode="out" aria-pressed="false">
                    <i class="fa-solid fa-arrow-up"></i> SORTIE
                </button>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Stock</label>
                <select id="stockSelect" style="width:100%">
                    @foreach($stocks as $s)
                        <option value="{{ $s->id }}" @selected($s->id === $stockId)>{{ $s->name }}{{ $s->module ? ' — ' . $s->module_label : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ── Zone de scan ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-3 space-y-3">
        <div id="cameraBox" class="hidden">
            <div id="reader" class="bg-black w-full"></div>
            <p id="cameraMsg" class="hidden text-xs text-red-600 mt-2"></p>
        </div>

        <div class="flex gap-2">
            <button type="button" id="cameraBtn"
                    class="shrink-0 px-4 py-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold flex items-center gap-2">
                <i class="fa-solid fa-camera"></i> <span>Caméra</span>
            </button>
            <form id="manualForm" class="flex-1 flex min-w-0" autocomplete="off">
                <input id="manualCode" type="text" inputmode="numeric" placeholder="Code-barres…"
                       class="qty-in flex-1 min-w-0 px-3 py-3 border border-slate-300 rounded-l-lg font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <button class="px-4 rounded-r-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm">OK</button>
            </form>
        </div>

        <div class="flex items-center justify-between gap-3">
            <p class="text-xs text-slate-500 leading-snug">
                <i class="fa-solid fa-barcode mr-1"></i>Douchette USB / Bluetooth : scannez directement.
            </p>
            <div class="flex items-center gap-1 shrink-0" title="Quantité ajoutée au prochain scan">
                <span class="text-xs text-slate-500 mr-1">Prochain scan</span>
                <button type="button" id="multDec" class="w-9 h-9 rounded-lg border border-slate-300 text-slate-600 text-lg leading-none">−</button>
                <input id="multiplier" type="number" inputmode="numeric" min="1" value="1"
                       class="qty-in w-14 h-9 text-center font-bold border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <button type="button" id="multInc" class="w-9 h-9 rounded-lg border border-slate-300 text-slate-600 text-lg leading-none">+</button>
            </div>
        </div>

        {{-- Retour du dernier scan --}}
        <div id="lastScan" class="hidden rounded-lg px-3 py-2.5 text-sm flex items-center gap-3"></div>
    </div>

    {{-- ── Erreurs de validation ───────────────────────────────────────── --}}
    <div id="errorBox" class="hidden bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm"></div>

    {{-- ── Panier ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between px-1">
        <h2 class="font-semibold text-slate-700 text-sm">Articles scannés</h2>
        <button type="button" id="clearBtn" class="hidden text-xs text-red-600 font-semibold hover:underline">Tout effacer</button>
    </div>
    <div id="cart" class="space-y-2"></div>
    <div id="emptyCart" class="text-center text-slate-400 text-sm py-10 bg-white rounded-xl border border-dashed border-slate-300">
        <i class="fa-solid fa-barcode text-3xl mb-2 block"></i>
        Scannez un article pour commencer.
    </div>

    {{-- ── Barre d'action fixe ─────────────────────────────────────────── --}}
    <div class="fixed bottom-0 left-0 right-0 md:left-64 z-30 bg-white border-t border-slate-200 shadow-[0_-4px_12px_rgba(0,0,0,0.06)] p-3">
        <div class="max-w-3xl mx-auto flex flex-col sm:flex-row gap-2 sm:items-center">
            <input type="text" id="notes" maxlength="300" placeholder="Note (optionnel) : n° BL, fournisseur, motif…"
                   class="flex-1 px-3 py-2.5 rounded-lg border border-slate-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <button type="button" id="submitBtn" disabled
                    class="mode-accent px-5 py-3 rounded-lg disabled:!bg-slate-300 disabled:cursor-not-allowed text-white text-sm font-bold flex items-center justify-center gap-2">
                <i class="fa-solid fa-check"></i> <span id="submitLabel">Valider</span>
            </button>
        </div>
    </div>
</div>

{{-- ── Panneau d'association d'un code inconnu ───────────────────────── --}}
<div id="linkSheet" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="absolute bottom-0 left-0 right-0 sm:top-1/2 sm:bottom-auto sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2 sm:w-[32rem]
                bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl max-h-[90vh] flex flex-col">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div class="min-w-0">
                <p class="font-bold text-slate-800 text-sm">Associer un code-barres</p>
                <p class="font-mono text-xs text-slate-500 truncate" id="linkCode"></p>
            </div>
            <button type="button" class="w-9 h-9 rounded-lg hover:bg-slate-100 text-slate-500" data-close aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Étape 1 : produit --}}
        <div id="linkStep1" class="p-4 flex flex-col gap-3 min-h-0">
            <input type="search" id="linkSearch" placeholder="Rechercher le produit…"
                   class="qty-in w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <div id="linkResults" class="overflow-y-auto -mx-1 divide-y divide-slate-100" style="max-height:50vh"></div>
        </div>

        {{-- Étape 2 : niveau de conditionnement --}}
        <div id="linkStep2" class="hidden p-4 overflow-y-auto space-y-3">
            <div class="flex items-center justify-between gap-2 bg-slate-50 rounded-lg px-3 py-2">
                <p class="font-semibold text-sm text-slate-800 truncate" id="linkProductName"></p>
                <button type="button" id="linkChangeProduct" class="text-xs text-emerald-700 font-semibold shrink-0">Changer</button>
            </div>
            <p class="text-xs text-slate-500">Ce code correspond à :</p>
            <div id="linkTargets" class="space-y-2"></div>
            <div id="linkNewBox" class="hidden grid grid-cols-5 gap-2 pl-7">
                <input type="text" id="linkNewName" list="packagingNames" placeholder="Nom (ex : Caisse de 12)"
                       class="qty-in col-span-3 px-3 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <div class="col-span-2 flex items-center border border-slate-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-emerald-400">
                    <input type="number" id="linkNewQty" inputmode="decimal" min="0" step="any" placeholder="Contenance"
                           class="qty-in w-full min-w-0 px-2 py-2.5 focus:outline-none">
                    <span class="px-2 text-xs text-slate-500 bg-slate-50 self-stretch flex items-center" id="linkNewUnit"></span>
                </div>
                <p class="col-span-5 text-xs text-slate-400">Contenance en unité de base. Ex : caisse de 12 bouteilles de 1 L = 12000 mL.</p>
            </div>
            <datalist id="packagingNames">
                @foreach($packagingNames as $n)<option value="{{ $n }}">@endforeach
            </datalist>
            <p id="linkError" class="hidden text-sm text-red-600"></p>
            <button type="button" id="linkSave"
                    class="w-full py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm">
                Associer et ajouter
            </button>
        </div>
    </div>
</div>

{{-- Notification --}}
<div id="toast" class="hidden fixed top-4 left-1/2 -translate-x-1/2 z-[60] px-4 py-2.5 rounded-lg shadow-lg text-sm font-semibold text-white max-w-[90vw]"></div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Données serveur ────────────────────────────────────────────────────
    const ROUTES = {
        lookup: @json(route('stock.scan.lookup')),
        store:  @json(route('stock.scan.store')),
        link:   @json(route('stock.scan.link')),
    };
    const CSRF     = @json(csrf_token());
    const CAN_LINK = @json($canLink);
    const AVAIL    = @json($availability, JSON_FORCE_OBJECT);
    const STORE_KEY = 'royal.stockScan.v1';

    const products = new Map();   // id → produit
    const codeIndex = new Map();  // variante de code → { pid, ppid }

    // ── Codes-barres : variantes GTIN (identique à App\Support\Barcode) ───
    function normalize(code) { return String(code || '').replace(/\s+/g, ''); }
    function variants(code) {
        code = normalize(code);
        if (!code) return [];
        const out = new Set([code]);
        if (/^\d+$/.test(code)) {
            const core = code.replace(/^0+/, '');
            [8, 12, 13, 14].forEach(len => { if (core.length <= len) out.add(core.padStart(len, '0')); });
            if (core) out.add(core);
        }
        return [...out];
    }
    function indexProduct(p) {
        // Retire les anciens codes de ce produit avant de réindexer
        for (const [k, v] of codeIndex) if (v.pid === p.id) codeIndex.delete(k);
        products.set(p.id, p);
        if (p.barcode) variants(p.barcode).forEach(v => codeIndex.set(v, { pid: p.id, ppid: null }));
        p.packagings.forEach(pp => {
            if (pp.barcode) variants(pp.barcode).forEach(v => codeIndex.set(v, { pid: p.id, ppid: pp.id }));
        });
    }
    @json($catalog).forEach(indexProduct);

    function findLocal(code) {
        for (const v of variants(code)) if (codeIndex.has(v)) return codeIndex.get(v);
        return null;
    }

    // ── État ────────────────────────────────────────────────────────────────
    const state = { mode: 'in', stockId: @json($stockId), lines: {}, multiplier: 1, busy: false };
    try {
        const saved = JSON.parse(localStorage.getItem(STORE_KEY) || 'null');
        if (saved) {
            state.mode = saved.mode === 'out' ? 'out' : 'in';
            Object.entries(saved.lines || {}).forEach(([k, l]) => { if (products.has(l.pid)) state.lines[k] = l; });
            if (saved.notes) document.getElementById('notes').value = saved.notes;
        }
    } catch (e) {}
    function save() {
        try {
            localStorage.setItem(STORE_KEY, JSON.stringify({ mode: state.mode, lines: state.lines, notes: document.getElementById('notes').value }));
        } catch (e) {}
    }

    // ── Formatage ───────────────────────────────────────────────────────────
    const nf = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 });
    function friendly(q, unit) {
        const u = (unit || '').toLowerCase();
        if (u === 'g' && Math.abs(q) >= 1000) return nf.format(q / 1000) + ' kg';
        if (u === 'ml' && Math.abs(q) >= 1000) return nf.format(q / 1000) + ' L';
        return nf.format(q) + (unit ? ' ' + unit : '');
    }
    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }
    function levelOf(p, ppid) {
        if (!ppid) return { name: 'Unité', factor: 1, breakdown: null };
        const pp = p.packagings.find(x => x.id === ppid);
        return pp ? { name: pp.name, factor: pp.qty, breakdown: pp.breakdown } : null;
    }
    function available(pid) { return (AVAIL[state.stockId] || {})[pid] || 0; }

    // ── Retour sonore / vibration ───────────────────────────────────────────
    let audioCtx = null;
    function beep(ok) {
        try {
            audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
            const play = (freq, start, dur) => {
                const o = audioCtx.createOscillator(), g = audioCtx.createGain();
                o.frequency.value = freq; o.type = 'square'; g.gain.value = 0.06;
                o.connect(g); g.connect(audioCtx.destination);
                o.start(audioCtx.currentTime + start); o.stop(audioCtx.currentTime + start + dur);
            };
            if (ok) play(1400, 0, 0.07); else { play(320, 0, 0.15); play(250, 0.2, 0.2); }
        } catch (e) {}
        try { navigator.vibrate && navigator.vibrate(ok ? 40 : [90, 60, 90]); } catch (e) {}
    }

    let toastTimer;
    function toast(msg, ok = true) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[60] px-4 py-2.5 rounded-lg shadow-lg text-sm font-semibold text-white max-w-[90vw] ' + (ok ? 'bg-emerald-600' : 'bg-red-600');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => t.classList.add('hidden'), 3000);
    }

    function showLast(html, kind) {
        const el = document.getElementById('lastScan');
        el.className = 'rounded-lg px-3 py-2.5 text-sm flex items-center gap-3 ' +
            (kind === 'ok' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                           : 'bg-red-50 text-red-800 border border-red-200');
        el.innerHTML = html;
    }

    // ── Traitement d'un scan ────────────────────────────────────────────────
    async function handleCode(raw) {
        const code = normalize(raw);
        if (!code) return;

        let hit = findLocal(code);
        if (!hit) {
            // Code peut-être associé depuis un autre poste : on demande au serveur
            try {
                const r = await fetch(ROUTES.lookup + '?code=' + encodeURIComponent(code), { headers: { Accept: 'application/json' } });
                const data = await r.json();
                if (data.found) { indexProduct(data.product); hit = findLocal(code); }
            } catch (e) {}
        }

        if (!hit) {
            beep(false);
            showLast(
                '<i class="fa-solid fa-circle-question text-lg"></i><div class="flex-1 min-w-0">' +
                '<p class="font-semibold">Code inconnu</p><p class="font-mono text-xs truncate">' + esc(code) + '</p></div>' +
                (CAN_LINK ? '<button type="button" class="shrink-0 px-3 py-2 rounded-lg bg-red-600 text-white text-xs font-bold" data-link="' + esc(code) + '">Associer</button>'
                          : '<span class="text-xs">Demandez à un responsable de l\'associer.</span>'),
                'err'
            );
            if (CAN_LINK) openLink(code);
            return;
        }

        addLine(hit.pid, hit.ppid, state.multiplier);
        setMultiplier(1);
    }

    function addLine(pid, ppid, n) {
        const p = products.get(pid);
        const level = levelOf(p, ppid);
        if (!p || !level) return;
        const key = pid + ':' + (ppid || 'base');
        const line = state.lines[key] || { pid, ppid, count: 0 };
        line.count = Math.round((line.count + n) * 100) / 100;
        line.t = Date.now();
        state.lines[key] = line;

        beep(true);
        showLast(
            '<i class="fa-solid fa-circle-check text-lg"></i><div class="flex-1 min-w-0">' +
            '<p class="font-semibold truncate">' + esc(p.name) + '</p>' +
            '<p class="text-xs">' + (state.mode === 'in' ? '+' : '−') + nf.format(n) + ' × ' + esc(level.name) +
            ' <span class="opacity-70">(' + friendly(n * level.factor, p.unit) + ')</span></p></div>',
            'ok'
        );
        render(pid);
        save();
    }

    // ── Rendu du panier ─────────────────────────────────────────────────────
    function render(flashPid) {
        const app = document.getElementById('scanApp');
        app.classList.toggle('mode-in', state.mode === 'in');
        app.classList.toggle('mode-out', state.mode === 'out');
        document.querySelectorAll('.mode-btn').forEach(b => b.setAttribute('aria-pressed', b.dataset.mode === state.mode));

        const groups = new Map();
        Object.entries(state.lines).forEach(([key, l]) => {
            if (!groups.has(l.pid)) groups.set(l.pid, []);
            groups.get(l.pid).push({ key, ...l });
        });
        const ordered = [...groups.entries()].sort((a, b) =>
            Math.max(...b[1].map(l => l.t || 0)) - Math.max(...a[1].map(l => l.t || 0)));

        let shortage = false;
        const html = ordered.map(([pid, lines]) => {
            const p = products.get(pid);
            lines.sort((a, b) => (levelOf(p, b.ppid)?.factor || 0) - (levelOf(p, a.ppid)?.factor || 0));
            const total = lines.reduce((s, l) => s + l.count * (levelOf(p, l.ppid)?.factor || 0), 0);
            const avail = available(pid);
            const short = state.mode === 'out' && total > avail + 0.0001;
            shortage = shortage || short;

            const stockInfo = state.mode === 'in'
                ? 'Stock : ' + friendly(avail, p.unit) + ' → <strong>' + friendly(avail + total, p.unit) + '</strong>'
                : (short ? '<span class="text-red-600 font-semibold"><i class="fa-solid fa-triangle-exclamation"></i> Disponible : ' + friendly(avail, p.unit) + '</span>'
                         : 'Stock : ' + friendly(avail, p.unit) + ' → <strong>' + friendly(avail - total, p.unit) + '</strong>');

            return '<div class="bg-white rounded-xl border ' + (short ? 'border-red-300' : 'border-slate-200') + ' overflow-hidden' + (flashPid === pid ? ' flash-new' : '') + '">' +
                '<div class="px-3 pt-3 pb-2 flex items-start justify-between gap-2">' +
                    '<div class="min-w-0"><p class="font-semibold text-slate-800 text-sm leading-tight">' + esc(p.name) + '</p>' +
                    '<p class="text-xs text-slate-500 mt-0.5">' + stockInfo + '</p></div>' +
                    '<div class="text-right shrink-0"><p class="mode-text font-bold text-base">' + (state.mode === 'in' ? '+' : '−') + friendly(total, p.unit) + '</p>' +
                    '<button type="button" class="text-[11px] text-slate-400 hover:text-red-600" data-action="delprod" data-pid="' + pid + '">Retirer</button></div>' +
                '</div>' +
                lines.map(l => {
                    const lv = levelOf(p, l.ppid);
                    return '<div class="px-3 py-2 border-t border-slate-100 flex items-center gap-2">' +
                        '<div class="flex-1 min-w-0"><p class="text-sm text-slate-700 truncate">' + esc(lv.name) + '</p>' +
                        '<p class="text-[11px] text-slate-400">' + (l.ppid ? '1 = ' + friendly(lv.factor, p.unit) + (lv.breakdown ? ' · ' + esc(lv.breakdown) : '') : '1 ' + esc(p.unit)) + '</p></div>' +
                        '<div class="flex items-stretch border border-slate-300 rounded-lg overflow-hidden shrink-0">' +
                            '<button type="button" class="w-10 bg-slate-50 active:bg-slate-200 text-lg" data-action="dec" data-key="' + l.key + '">−</button>' +
                            '<input type="number" inputmode="decimal" min="0" step="any" value="' + l.count + '" data-key="' + l.key + '" class="qty-in w-16 text-center font-bold py-2 focus:outline-none">' +
                            '<button type="button" class="w-10 bg-slate-50 active:bg-slate-200 text-lg" data-action="inc" data-key="' + l.key + '">+</button>' +
                        '</div></div>';
                }).join('') +
            '</div>';
        }).join('');

        document.getElementById('cart').innerHTML = html;
        const n = groups.size;
        const scans = Object.values(state.lines).reduce((s, l) => s + l.count, 0);
        document.getElementById('emptyCart').classList.toggle('hidden', n > 0);
        document.getElementById('clearBtn').classList.toggle('hidden', n === 0);

        const btn = document.getElementById('submitBtn');
        btn.disabled = n === 0 || shortage || state.busy;
        document.getElementById('submitLabel').textContent = n === 0
            ? 'Valider'
            : (shortage ? 'Stock insuffisant'
                        : 'Valider ' + (state.mode === 'in' ? "l'entrée" : 'la sortie') + ' · ' + n + ' produit' + (n > 1 ? 's' : '') + ' (' + nf.format(scans) + ')');
    }

    // ── Interactions panier ─────────────────────────────────────────────────
    const cart = document.getElementById('cart');
    cart.addEventListener('click', e => {
        const b = e.target.closest('[data-action]');
        if (!b) return;
        if (b.dataset.action === 'delprod') {
            Object.keys(state.lines).forEach(k => { if (state.lines[k].pid === +b.dataset.pid) delete state.lines[k]; });
        } else {
            const l = state.lines[b.dataset.key];
            if (!l) return;
            l.count = Math.max(0, Math.round((l.count + (b.dataset.action === 'inc' ? 1 : -1)) * 100) / 100);
            if (l.count === 0) delete state.lines[b.dataset.key];
        }
        render(); save();
    });
    cart.addEventListener('change', e => {
        const key = e.target.dataset.key;
        if (!key || !state.lines[key]) return;
        const v = parseFloat(e.target.value);
        if (!v || v <= 0) delete state.lines[key]; else state.lines[key].count = Math.round(v * 100) / 100;
        render(); save();
    });
    cart.addEventListener('focusin', e => { if (e.target.matches('input')) e.target.select(); });

    document.getElementById('clearBtn').addEventListener('click', () => {
        if (!confirm('Effacer tous les articles scannés ?')) return;
        state.lines = {}; render(); save();
    });
    document.querySelectorAll('.mode-btn').forEach(b => b.addEventListener('click', () => {
        state.mode = b.dataset.mode; render(); save();
    }));
    $('#stockSelect').on('change', function () { state.stockId = +this.value; render(); });
    document.getElementById('notes').addEventListener('input', save);
    document.getElementById('lastScan').addEventListener('click', e => {
        const b = e.target.closest('[data-link]');
        if (b) openLink(b.dataset.link);
    });

    // ── Multiplicateur « prochain scan » ────────────────────────────────────
    const multInput = document.getElementById('multiplier');
    function setMultiplier(n) { state.multiplier = Math.max(1, Math.round(n * 100) / 100 || 1); multInput.value = state.multiplier; }
    document.getElementById('multInc').addEventListener('click', () => setMultiplier(state.multiplier + 1));
    document.getElementById('multDec').addEventListener('click', () => setMultiplier(state.multiplier - 1));
    multInput.addEventListener('change', () => setMultiplier(parseFloat(multInput.value)));

    // ── Saisie manuelle ─────────────────────────────────────────────────────
    document.getElementById('manualForm').addEventListener('submit', e => {
        e.preventDefault();
        const input = document.getElementById('manualCode');
        handleCode(input.value);
        input.value = '';
    });

    // ── Douchette (émulation clavier) : capte les frappes rapides hors des champs ──
    let buf = '', lastKey = 0;
    document.addEventListener('keydown', e => {
        if (!document.getElementById('linkSheet').classList.contains('hidden')) return;
        if (e.target.closest && e.target.closest('input, textarea, select, [contenteditable], .select2-container')) return;
        const now = performance.now();
        if (now - lastKey > 80) buf = '';
        lastKey = now;
        if (e.key === 'Enter' || e.key === 'Tab') {
            if (buf.length >= 3) { e.preventDefault(); handleCode(buf); }
            buf = '';
        } else if (e.key.length === 1) {
            buf += e.key;
        }
    });

    // ── Caméra ──────────────────────────────────────────────────────────────
    let scanner = null, cameraOn = false, lastCam = { code: '', t: 0 };
    function loadScript(urls) {
        return urls.reduce((p, url) => p.catch(() => new Promise((ok, ko) => {
            const s = document.createElement('script');
            s.src = url; s.onload = ok; s.onerror = () => { s.remove(); ko(); };
            document.head.appendChild(s);
        })), Promise.reject());
    }
    function cameraError(msg) {
        const m = document.getElementById('cameraMsg');
        m.textContent = msg; m.classList.remove('hidden');
    }
    async function startCamera() {
        const box = document.getElementById('cameraBox');
        box.classList.remove('hidden');
        document.getElementById('cameraMsg').classList.add('hidden');
        if (!window.isSecureContext) {
            cameraError("La caméra n'est disponible qu'en HTTPS (ou sur localhost). Utilisez la saisie manuelle ou une douchette, ou ouvrez le site en https://.");
            return;
        }
        try {
            if (!window.Html5Qrcode) {
                await loadScript([
                    'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js',
                    'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
                ]);
            }
            const F = Html5QrcodeSupportedFormats;
            scanner = scanner || new Html5Qrcode('reader', {
                formatsToSupport: [F.EAN_13, F.EAN_8, F.UPC_A, F.UPC_E, F.CODE_128, F.CODE_39, F.ITF, F.QR_CODE],
                experimentalFeatures: { useBarCodeDetectorIfSupported: true },
                verbose: false,
            });
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 12, qrbox: (w, h) => ({ width: Math.floor(Math.min(w * 0.85, 360)), height: Math.floor(Math.min(h * 0.5, 170)) }) },
                code => {
                    const now = Date.now();
                    if (code === lastCam.code && now - lastCam.t < 1800) return; // même code encore devant l'objectif
                    lastCam = { code, t: now };
                    handleCode(code);
                },
                () => {}
            );
            cameraOn = true;
            setCameraBtn();
        } catch (e) {
            cameraError("Impossible d'ouvrir la caméra : " + (e && e.message ? e.message : e) + '. Vérifiez l\'autorisation dans le navigateur.');
        }
    }
    async function stopCamera() {
        try { if (scanner && cameraOn) await scanner.stop(); } catch (e) {}
        cameraOn = false;
        document.getElementById('cameraBox').classList.add('hidden');
        setCameraBtn();
    }
    function pauseCamera(p) {
        try { if (scanner && cameraOn) p ? scanner.pause(true) : scanner.resume(); } catch (e) {}
    }
    function setCameraBtn() {
        const b = document.getElementById('cameraBtn');
        b.querySelector('span').textContent = cameraOn ? 'Arrêter' : 'Caméra';
        b.classList.toggle('bg-red-600', cameraOn);
    }
    document.getElementById('cameraBtn').addEventListener('click', () => cameraOn ? stopCamera() : startCamera());

    // ── Association d'un code inconnu ───────────────────────────────────────
    const sheet = document.getElementById('linkSheet');
    let link = { code: '', product: null };

    function openLink(code) {
        link = { code, product: null };
        document.getElementById('linkCode').textContent = code;
        document.getElementById('linkSearch').value = '';
        document.getElementById('linkError').classList.add('hidden');
        showStep(1);
        renderResults();
        sheet.classList.remove('hidden');
        pauseCamera(true);
        setTimeout(() => document.getElementById('linkSearch').focus(), 50);
    }
    function closeLink() { sheet.classList.add('hidden'); pauseCamera(false); }
    sheet.addEventListener('click', e => { if (e.target.closest('[data-close]')) closeLink(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !sheet.classList.contains('hidden')) closeLink(); });

    function showStep(n) {
        document.getElementById('linkStep1').classList.toggle('hidden', n !== 1);
        document.getElementById('linkStep2').classList.toggle('hidden', n !== 2);
    }
    function renderResults() {
        const q = document.getElementById('linkSearch').value.trim().toLowerCase();
        const list = [...products.values()]
            .filter(p => !q || (p.name + ' ' + (p.ref || '')).toLowerCase().includes(q))
            .slice(0, 60);
        document.getElementById('linkResults').innerHTML = list.length
            ? list.map(p => '<button type="button" class="w-full text-left px-3 py-3 hover:bg-emerald-50 flex justify-between gap-2" data-pid="' + p.id + '">' +
                '<span class="text-sm text-slate-800">' + esc(p.name) + '</span>' +
                '<span class="text-xs text-slate-400 shrink-0">' + esc(p.ref || '') + '</span></button>').join('')
            : '<p class="text-sm text-slate-400 text-center py-6">Aucun produit.</p>';
    }
    document.getElementById('linkSearch').addEventListener('input', renderResults);
    document.getElementById('linkResults').addEventListener('click', e => {
        const b = e.target.closest('[data-pid]');
        if (!b) return;
        link.product = products.get(+b.dataset.pid);
        renderTargets();
        showStep(2);
    });
    document.getElementById('linkChangeProduct').addEventListener('click', () => showStep(1));

    function renderTargets() {
        const p = link.product;
        document.getElementById('linkProductName').textContent = p.name;
        document.getElementById('linkNewUnit').textContent = p.unit || '';
        const opt = (value, title, sub, checked) =>
            '<label class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">' +
            '<input type="radio" name="linkTarget" value="' + value + '" class="mt-1 accent-emerald-600"' + (checked ? ' checked' : '') + '>' +
            '<span class="min-w-0"><span class="block text-sm font-semibold text-slate-800">' + title + '</span>' +
            (sub ? '<span class="block text-xs text-slate-500">' + sub + '</span>' : '') + '</span></label>';

        const replaced = code => code ? ' · <span class="text-amber-600">remplace le code ' + esc(code) + '</span>' : '';
        let html = p.packagings.map((pp, i) =>
            opt('pp:' + pp.id, esc(pp.name), '1 = ' + friendly(pp.qty, p.unit) + (pp.breakdown ? ' · ' + esc(pp.breakdown) : '') + replaced(pp.barcode), i === 0)
        ).join('');
        html += opt('base', 'Unité de base', '1 = 1 ' + esc(p.unit) + replaced(p.barcode), p.packagings.length === 0);
        html += opt('new', '<i class="fa-solid fa-plus mr-1"></i>Nouvel emballage', 'Bouteille, pack, caisse… avec sa contenance', false);
        document.getElementById('linkTargets').innerHTML = html;
        toggleNewBox();
    }
    function toggleNewBox() {
        const v = (document.querySelector('input[name=linkTarget]:checked') || {}).value;
        document.getElementById('linkNewBox').classList.toggle('hidden', v !== 'new');
    }
    document.getElementById('linkTargets').addEventListener('change', toggleNewBox);

    document.getElementById('linkSave').addEventListener('click', async () => {
        const target = (document.querySelector('input[name=linkTarget]:checked') || {}).value;
        const err = document.getElementById('linkError');
        err.classList.add('hidden');
        if (!target) return;

        const body = { code: link.code, product_id: link.product.id, target };
        if (target === 'new') {
            body.packaging_name = document.getElementById('linkNewName').value.trim();
            body.packaging_quantity = document.getElementById('linkNewQty').value;
        }

        const btn = document.getElementById('linkSave');
        btn.disabled = true;
        try {
            const r = await fetch(ROUTES.link, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(body),
            });
            const data = await r.json();
            if (!r.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Erreur'));

            indexProduct(data.product);
            closeLink();
            toast(data.message);
            const hit = findLocal(link.code);
            if (hit) { addLine(hit.pid, hit.ppid, state.multiplier); setMultiplier(1); }
        } catch (e) {
            err.textContent = e.message;
            err.classList.remove('hidden');
        } finally {
            btn.disabled = false;
        }
    });

    // ── Validation ──────────────────────────────────────────────────────────
    document.getElementById('submitBtn').addEventListener('click', async () => {
        const lines = Object.values(state.lines).map(l => ({ product_id: l.pid, product_packaging_id: l.ppid, count: l.count }));
        if (!lines.length) return;
        const stockName = $('#stockSelect option:selected').text().trim();
        const nProducts = new Set(lines.map(l => l.product_id)).size;
        if (!confirm((state.mode === 'in' ? 'ENTRÉE' : 'SORTIE') + ' de ' + nProducts + ' produit(s) dans « ' + stockName + ' ».\nConfirmer ?')) return;

        const errorBox = document.getElementById('errorBox');
        errorBox.classList.add('hidden');
        state.busy = true; render();
        try {
            const r = await fetch(ROUTES.store, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ stock_id: state.stockId, type: state.mode, notes: document.getElementById('notes').value, lines }),
            });
            const data = await r.json().catch(() => ({}));
            if (!r.ok) {
                const msgs = data.errors ? Object.values(data.errors).flat() : [data.message || ('Erreur ' + r.status)];
                errorBox.innerHTML = msgs.map(m => '<p>' + esc(m) + '</p>').join('');
                errorBox.classList.remove('hidden');
                beep(false);
                return;
            }
            AVAIL[state.stockId] = Object.assign(AVAIL[state.stockId] || {}, data.availability);
            state.lines = {};
            document.getElementById('notes').value = '';
            document.getElementById('lastScan').className = 'hidden';
            toast(data.message);
            beep(true);
        } catch (e) {
            errorBox.textContent = 'Connexion impossible. Vos scans sont conservés, réessayez.';
            errorBox.classList.remove('hidden');
        } finally {
            state.busy = false; render(); save();
        }
    });

    render();
})();
</script>
@endpush
