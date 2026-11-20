@extends('layouts.stock')

@section('title', 'Scanner une sortie')
@section('header', 'Scanner une sortie')

@section('content')
<div id="scanApp" class="py-4 pb-48 max-w-3xl mx-auto space-y-3">

    {{-- ── Stock ────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="h-1.5 bg-red-600"></div>
        <div class="p-3 space-y-2">
            <p class="text-sm font-bold text-red-700 flex items-center gap-2"><i class="fa-solid fa-arrow-up"></i> SORTIE DE STOCK</p>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Stock</label>
                <select id="stockSelect" style="width:100%">
                    @foreach($stocks as $s)
                        <option value="{{ $s->id }}" @selected($s->id === $stockId)>{{ $s->name }}{{ $s->module ? ' — ' . $s->module_label : '' }}</option>
                    @endforeach
                </select>
            </div>
            <p class="text-xs text-slate-500">
                <i class="fa-solid fa-circle-info mr-1"></i>Les entrées se font par une
                <a href="{{ route('purchases.deliveries') }}" class="text-emerald-700 font-semibold underline">réception fournisseur (BL)</a>.
            </p>
        </div>
    </div>

    @include('partials.scan-input')

    <div id="errorBox" class="hidden bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm"></div>

    {{-- ── Panier ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between px-1">
        <h2 class="font-semibold text-slate-700 text-sm">Articles à sortir</h2>
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
            <input type="text" id="notes" maxlength="300" placeholder="Motif (optionnel) : cuisine, événement, casse…"
                   class="sk-input flex-1 px-3 py-2.5 rounded-lg border border-slate-300 sm:text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            <button type="button" id="submitBtn" disabled
                    class="px-5 py-3 rounded-lg bg-red-600 hover:bg-red-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-sm font-bold flex items-center justify-center gap-2">
                <i class="fa-solid fa-check"></i> <span id="submitLabel">Valider la sortie</span>
            </button>
        </div>
    </div>
</div>

@include('partials.scan-kit')
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const ROUTES   = { lookup: @json(route('stock.scan.lookup')), link: @json(route('stock.scan.link')), store: @json(route('stock.scan.store')) };
    const CSRF     = @json(csrf_token());
    const AVAIL    = @json($availability, JSON_FORCE_OBJECT);
    const STORE_KEY = 'royal.stockScan.v2';

    const state = { stockId: @json($stockId), lines: {}, busy: false };

    const kit = ScanKit({
        catalog: @json($catalog),
        canLink: @json($canLink),
        csrf: CSRF,
        routes: ROUTES,
        onHit(pid, ppid, n) { addLine(pid, ppid, n); },
    });
    const { products, nf, esc, friendly, levelOf } = kit;

    try {
        const saved = JSON.parse(localStorage.getItem(STORE_KEY) || 'null');
        if (saved) {
            Object.entries(saved.lines || {}).forEach(([k, l]) => { if (products.has(l.pid)) state.lines[k] = l; });
            if (saved.notes) document.getElementById('notes').value = saved.notes;
        }
    } catch (e) {}
    const save = () => {
        try { localStorage.setItem(STORE_KEY, JSON.stringify({ lines: state.lines, notes: document.getElementById('notes').value })); } catch (e) {}
    };
    const available = pid => (AVAIL[state.stockId] || {})[pid] || 0;

    function addLine(pid, ppid, n) {
        const p = products.get(pid);
        const level = p && levelOf(p, ppid);
        if (!level) return;
        const key = pid + ':' + (ppid || 'base');
        const line = state.lines[key] || { pid, ppid, count: 0 };
        line.count = Math.round((line.count + n) * 100) / 100;
        line.t = Date.now();
        state.lines[key] = line;
        kit.beep(true);
        kit.showLast(kit.okLine(p, level, n, '−'), 'ok');
        render(pid);
        save();
    }

    function render(flashPid) {
        const groups = new Map();
        Object.entries(state.lines).forEach(([key, l]) => {
            if (!groups.has(l.pid)) groups.set(l.pid, []);
            groups.get(l.pid).push({ key, ...l });
        });
        const ordered = [...groups.entries()].sort((a, b) =>
            Math.max(...b[1].map(l => l.t || 0)) - Math.max(...a[1].map(l => l.t || 0)));

        let shortage = false;
        document.getElementById('cart').innerHTML = ordered.map(([pid, lines]) => {
            const p = products.get(pid);
            lines.sort((a, b) => (levelOf(p, b.ppid)?.factor || 0) - (levelOf(p, a.ppid)?.factor || 0));
            const total = lines.reduce((s, l) => s + l.count * (levelOf(p, l.ppid)?.factor || 0), 0);
            const avail = available(pid);
            const short = total > avail + 0.0001;
            shortage = shortage || short;
            const info = short
                ? '<span class="text-red-600 font-semibold"><i class="fa-solid fa-triangle-exclamation"></i> Disponible : ' + friendly(avail, p.unit) + '</span>'
                : 'Stock : ' + friendly(avail, p.unit) + ' → <strong>' + friendly(avail - total, p.unit) + '</strong>';

            return '<div class="bg-white rounded-xl border ' + (short ? 'border-red-300' : 'border-slate-200') + ' overflow-hidden' + (flashPid === pid ? ' sk-flash' : '') + '">' +
                '<div class="px-3 pt-3 pb-2 flex items-start justify-between gap-2">' +
                    '<div class="min-w-0"><p class="font-semibold text-slate-800 text-sm leading-tight">' + esc(p.name) + '</p>' +
                    '<p class="text-xs text-slate-500 mt-0.5">' + info + '</p></div>' +
                    '<div class="text-right shrink-0"><p class="font-bold text-base text-red-700">−' + friendly(total, p.unit) + '</p>' +
                    '<button type="button" class="text-[11px] text-slate-400 hover:text-red-600" data-action="delprod" data-pid="' + pid + '">Retirer</button></div>' +
                '</div>' +
                lines.map(l => {
                    const lv = levelOf(p, l.ppid);
                    return '<div class="px-3 py-2 border-t border-slate-100 flex items-center gap-2">' +
                        '<div class="flex-1 min-w-0"><p class="text-sm text-slate-700 truncate">' + esc(lv.name) + '</p>' +
                        '<p class="text-[11px] text-slate-400">' + (l.ppid ? '1 = ' + friendly(lv.factor, p.unit) + (lv.breakdown ? ' · ' + esc(lv.breakdown) : '') : '1 ' + esc(p.unit)) + '</p></div>' +
                        '<div class="flex items-stretch border border-slate-300 rounded-lg overflow-hidden shrink-0">' +
                            '<button type="button" class="w-10 bg-slate-50 active:bg-slate-200 text-lg" data-action="dec" data-key="' + l.key + '">−</button>' +
                            '<input type="number" inputmode="decimal" min="0" step="any" value="' + l.count + '" data-key="' + l.key + '" class="sk-input w-16 text-center font-bold py-2 focus:outline-none">' +
                            '<button type="button" class="w-10 bg-slate-50 active:bg-slate-200 text-lg" data-action="inc" data-key="' + l.key + '">+</button>' +
                        '</div></div>';
                }).join('') +
            '</div>';
        }).join('');

        const n = groups.size;
        document.getElementById('emptyCart').classList.toggle('hidden', n > 0);
        document.getElementById('clearBtn').classList.toggle('hidden', n === 0);
        const btn = document.getElementById('submitBtn');
        btn.disabled = n === 0 || shortage || state.busy;
        document.getElementById('submitLabel').textContent = n === 0 ? 'Valider la sortie'
            : (shortage ? 'Stock insuffisant' : 'Valider la sortie · ' + n + ' produit' + (n > 1 ? 's' : ''));
    }

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
    $('#stockSelect').on('change', function () { state.stockId = +this.value; render(); });
    document.getElementById('notes').addEventListener('input', save);

    document.getElementById('submitBtn').addEventListener('click', async () => {
        const lines = Object.values(state.lines).map(l => ({ product_id: l.pid, product_packaging_id: l.ppid, count: l.count }));
        if (!lines.length) return;
        const stockName = $('#stockSelect option:selected').text().trim();
        const nProducts = new Set(lines.map(l => l.product_id)).size;
        if (!confirm('SORTIE de ' + nProducts + ' produit(s) du stock « ' + stockName + ' ».\nConfirmer ?')) return;

        const errorBox = document.getElementById('errorBox');
        errorBox.classList.add('hidden');
        state.busy = true; render();
        try {
            const r = await fetch(ROUTES.store, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ stock_id: state.stockId, notes: document.getElementById('notes').value, lines }),
            });
            const data = await r.json().catch(() => ({}));
            if (!r.ok) {
                const msgs = data.errors ? Object.values(data.errors).flat() : [data.message || ('Erreur ' + r.status)];
                errorBox.innerHTML = msgs.map(m => '<p>' + esc(m) + '</p>').join('');
                errorBox.classList.remove('hidden');
                kit.beep(false);
                return;
            }
            AVAIL[state.stockId] = Object.assign(AVAIL[state.stockId] || {}, data.availability);
            state.lines = {};
            document.getElementById('notes').value = '';
            kit.hideLast();
            kit.toast(data.message);
            kit.beep(true);
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
