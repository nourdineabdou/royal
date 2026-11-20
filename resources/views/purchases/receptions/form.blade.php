@extends('layouts.purchases')

@section('title', $order ? 'Réception ' . $order->reference : 'Réception directe')

@section('content')
<div id="recApp" class="max-w-3xl mx-auto space-y-3 pb-48">

    {{-- ── En-tête ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="h-1.5 bg-emerald-600"></div>
        <div class="p-4 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-emerald-700 flex items-center gap-2">
                        <i class="fa-solid fa-truck-ramp-box"></i> ENTRÉE EN STOCK — RÉCEPTION
                    </p>
                    @if($order)
                        <p class="text-lg font-bold text-slate-800 mt-0.5">{{ $order->reference }}</p>
                        <p class="text-xs text-slate-500">Commande du {{ $order->created_at->format('d/m/Y') }}{{ $order->status === 'partial' ? ' · déjà partiellement reçue' : '' }}</p>
                    @else
                        <p class="text-lg font-bold text-slate-800 mt-0.5">Réception directe</p>
                        <p class="text-xs text-slate-500">Achat sans bon de commande (marché, livraison du jour). Le bon de commande est créé automatiquement.</p>
                    @endif
                </div>
                <a href="{{ $order ? route('purchases.orders.show', $order) : route('purchases.deliveries') }}"
                   class="shrink-0 text-xs text-slate-500 hover:text-slate-700"><i class="fa-solid fa-xmark mr-1"></i>Annuler</a>
            </div>

            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Fournisseur <span class="text-red-500">*</span></label>
                    <select id="supplierSelect" style="width:100%">
                        <option value="">— Qui a livré ? —</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" @selected($order && $order->supplier_id === $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Stock de destination <span class="text-red-500">*</span></label>
                    <select id="stockSelect" style="width:100%">
                        @foreach($stocks as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}{{ $s->module ? ' — ' . $s->module_label : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">N° du BL fournisseur</label>
                    <input type="text" id="blNumber" maxlength="100" placeholder="Numéro inscrit sur le bon papier"
                           class="sk-input w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Remarque</label>
                    <input type="text" id="notes" maxlength="500" placeholder="Écart, produit abîmé…"
                           class="sk-input w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400">
                </div>
            </div>
        </div>
    </div>

    @include('partials.scan-input')

    <div id="errorBox" class="hidden bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm"></div>

    {{-- ── Barre d'outils ───────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-2 px-1">
        <h2 class="font-semibold text-slate-700 text-sm">{{ $order ? 'Articles attendus' : 'Articles reçus' }}</h2>
        <div class="flex gap-2">
            @if($order)
                <button type="button" id="fillAllBtn" class="text-xs font-semibold px-3 py-2 rounded-lg border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100">
                    <i class="fa-solid fa-check-double mr-1"></i>Tout reçu
                </button>
            @else
                <button type="button" id="addProductBtn" class="text-xs font-semibold px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                    <i class="fa-solid fa-plus mr-1"></i>Ajouter un produit
                </button>
            @endif
            <button type="button" id="resetBtn" class="text-xs font-semibold px-3 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">
                Remettre à zéro
            </button>
        </div>
    </div>

    <div id="cards" class="space-y-2"></div>
    <div id="emptyCards" class="hidden text-center text-slate-400 text-sm py-10 bg-white rounded-xl border border-dashed border-slate-300">
        <i class="fa-solid fa-basket-shopping text-3xl mb-2 block"></i>
        Scannez un article ou ajoutez un produit (vrac : légumes, poisson, viande…).
    </div>

    {{-- ── Barre de validation ──────────────────────────────────────────── --}}
    <div class="fixed bottom-0 left-0 right-0 md:left-64 z-30 bg-white border-t border-slate-200 shadow-[0_-4px_12px_rgba(0,0,0,0.06)] p-3">
        <div class="max-w-3xl mx-auto flex items-center gap-3">
            <p id="summary" class="flex-1 text-xs text-slate-500 leading-snug"></p>
            <button type="button" id="submitBtn" disabled
                    class="px-5 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-check"></i> Valider la réception
            </button>
        </div>
    </div>
</div>

{{-- ── Ajout d'un produit sans code-barres (réception directe) ─────────── --}}
<div id="pickSheet" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="absolute bottom-0 left-0 right-0 sm:top-1/2 sm:bottom-auto sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2 sm:w-[30rem]
                bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl max-h-[85vh] flex flex-col">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <p class="font-bold text-slate-800 text-sm">Ajouter un produit</p>
            <button type="button" class="w-9 h-9 rounded-lg hover:bg-slate-100 text-slate-500" data-close aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-4 flex flex-col gap-3 min-h-0">
            <input type="search" id="pickSearch" placeholder="Rechercher : tomate, poisson, riz…"
                   class="sk-input w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <div id="pickResults" class="overflow-y-auto -mx-1 divide-y divide-slate-100" style="max-height:55vh"></div>
        </div>
    </div>
</div>

@include('partials.scan-kit')
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const ORDER_ID = @json($order?->id);
    const EXPECTED = @json($expected);           // [{product_id, ordered, received, label}] en unité de base
    const ROUTES   = { lookup: @json(route('stock.scan.lookup')), link: @json(route('stock.scan.link')), store: @json(route('purchases.receptions.store')) };
    const CSRF     = @json(csrf_token());
    const DRAFT_KEY = 'royal.reception.' + (ORDER_ID || 'direct');
    const STOCK_KEY = 'royal.reception.stock';

    const kit = ScanKit({
        catalog: @json($catalog),
        canLink: @json($canLink),
        csrf: CSRF,
        routes: ROUTES,
        onHit(pid, ppid, n) { onScan(pid, ppid, n); },
        isBlocked: () => !document.getElementById('pickSheet').classList.contains('hidden'),
    });
    const { products, nf, esc, friendly, displayUnit } = kit;
    const expected = new Map(EXPECTED.map(e => [e.product_id, e]));

    // État : par produit → { levels: {ppid: nombre}, loose: quantité en unité d'affichage (kg, L, pcs), amount, t }
    let state = {};
    const blank = () => ({ levels: {}, loose: 0, amount: '', t: 0 });
    if (ORDER_ID) EXPECTED.forEach(e => { if (products.has(e.product_id)) state[e.product_id] = blank(); });

    // ── Brouillon (rechargement de page, coupure réseau) ────────────────────
    try {
        const d = JSON.parse(localStorage.getItem(DRAFT_KEY) || 'null');
        if (d) {
            Object.entries(d.state || {}).forEach(([pid, s]) => { if (products.has(+pid) && (!ORDER_ID || expected.has(+pid))) state[pid] = s; });
            ['blNumber', 'notes'].forEach(id => { if (d[id]) document.getElementById(id).value = d[id]; });
        }
        const st = localStorage.getItem(STOCK_KEY);
        if (st && $('#stockSelect option[value="' + st + '"]').length) $('#stockSelect').val(st).trigger('change.select2');
    } catch (e) {}
    const save = () => {
        try {
            localStorage.setItem(DRAFT_KEY, JSON.stringify({ state, blNumber: blNumber.value, notes: notes.value }));
        } catch (e) {}
    };
    const blNumber = document.getElementById('blNumber');
    const notes = document.getElementById('notes');

    // ── Calculs ─────────────────────────────────────────────────────────────
    function baseOf(pid) {
        const p = products.get(+pid), s = state[pid];
        if (!p || !s) return 0;
        let base = 0;
        p.packagings.forEach(pp => { base += (parseFloat(s.levels[pp.id]) || 0) * pp.qty; });
        base += (parseFloat(s.loose) || 0) * displayUnit(p.unit).factor;
        return Math.round(base * 100) / 100;
    }
    function status(pid) {
        const e = expected.get(+pid);
        const got = baseOf(pid);
        if (!e) return got > 0 ? { k: 'extra', got } : { k: 'none', got };
        const remaining = Math.max(0, e.ordered - e.received);
        if (got <= 0) return { k: remaining > 0 ? 'none' : 'done-before', got, remaining };
        if (Math.abs(got - remaining) < 0.005) return { k: 'full', got, remaining };
        return got < remaining ? { k: 'partial', got, remaining } : { k: 'over', got, remaining, diff: got - remaining };
    }

    // ── Scan ────────────────────────────────────────────────────────────────
    function onScan(pid, ppid, n) {
        const p = products.get(pid);
        if (ORDER_ID && !expected.has(pid)) {
            kit.error(p.name + ' n\'est pas sur cette commande', 'Faites une réception directe pour les articles non commandés.');
            return;
        }
        const s = state[pid] = state[pid] || blank();
        if (ppid) s.levels[ppid] = Math.round(((parseFloat(s.levels[ppid]) || 0) + n) * 100) / 100;
        else s.loose = Math.round(((parseFloat(s.loose) || 0) + n / displayUnit(p.unit).factor) * 1000) / 1000;
        s.t = Date.now();
        const level = kit.levelOf(p, ppid);
        kit.beep(true);
        kit.showLast(kit.okLine(p, level, n, '+'), 'ok');
        render(pid);
        save();
        const st = status(pid);
        if (st.k === 'over') kit.toast('Attention : ' + p.name + ' dépasse la commande de ' + friendly(st.diff, p.unit), false);
    }

    // ── Rendu ───────────────────────────────────────────────────────────────
    const chip = (cls, html) => '<span class="text-[11px] font-semibold px-2 py-0.5 rounded-full ' + cls + '">' + html + '</span>';
    const openDetails = new Set(); // détails « Livré autrement ? » ouverts par l'utilisateur
    function render(flashPid) {
        let pids = Object.keys(state).map(Number);
        if (!ORDER_ID) pids.sort((a, b) => (state[b].t || 0) - (state[a].t || 0));
        else pids.sort((a, b) => {
            const done = pid => status(pid).k === 'done-before' ? 1 : 0;
            return done(a) - done(b) || products.get(a).name.localeCompare(products.get(b).name);
        });

        document.getElementById('cards').innerHTML = pids.map(pid => {
            const p = products.get(pid), s = state[pid], st = status(pid), e = expected.get(pid);
            const du = displayUnit(p.unit);
            let head = '', badge = '', border = 'border-slate-200';
            if (e) {
                head = '<p class="text-xs text-slate-500 mt-0.5">Total commandé : ' + friendly(e.ordered, p.unit) + '</p>';
                badge = {
                    'none':        chip('bg-slate-100 text-slate-500', 'À recevoir : ' + friendly(st.remaining, p.unit)),
                    'done-before': chip('bg-slate-100 text-slate-400', 'Déjà reçu entièrement'),
                    'full':        chip('bg-emerald-100 text-emerald-700', '<i class="fa-solid fa-check"></i> Complet'),
                    'partial':     chip('bg-amber-100 text-amber-700', 'Reliquat : ' + friendly(st.remaining - st.got, p.unit)),
                    'over':        chip('bg-orange-100 text-orange-700', '<i class="fa-solid fa-triangle-exclamation"></i> Surplus +' + friendly(st.diff, p.unit)),
                }[st.k];
                if (st.k === 'over') border = 'border-orange-300';
                if (st.k === 'full') border = 'border-emerald-300';
            }
            const pct = e && st.remaining > 0 ? Math.min(100, st.got / st.remaining * 100) : 0;

            // Une ligne « emballage » : nom, contenance, −/nombre/+
            const levelRow = (pp, title, sub, big) =>
                '<div class="flex items-center gap-2 ' + (big ? 'py-3' : 'py-1.5') + '">' +
                    '<div class="flex-1 min-w-0"><p class="' + (big ? 'text-base font-bold text-slate-800' : 'text-sm text-slate-700') + ' truncate">' + title + '</p>' +
                    '<p class="text-[11px] text-slate-500">' + sub + '</p></div>' +
                    (big ? '<span class="text-[11px] font-semibold text-emerald-700 shrink-0">Reçu</span>' : '') +
                    '<div class="flex items-stretch border ' + (big ? 'border-emerald-400 ring-1 ring-emerald-200' : 'border-slate-300') + ' rounded-lg overflow-hidden shrink-0">' +
                        '<button type="button" class="w-10 bg-slate-50 active:bg-slate-200 text-lg" data-act="dec" data-pid="' + pid + '" data-pp="' + pp.id + '">−</button>' +
                        '<input type="number" inputmode="decimal" min="0" step="any" value="' + (s.levels[pp.id] || '') + '" placeholder="0" data-field="level" data-pid="' + pid + '" data-pp="' + pp.id + '" class="sk-input ' + (big ? 'w-20 text-lg' : 'w-16') + ' text-center font-bold py-2 focus:outline-none">' +
                        '<button type="button" class="w-10 bg-slate-50 active:bg-slate-200 text-lg" data-act="inc" data-pid="' + pid + '" data-pp="' + pp.id + '">+</button>' +
                    '</div></div>';
            const ppSub = pp => '1 = ' + friendly(pp.qty, p.unit) + (pp.breakdown ? ' · ' + esc(pp.breakdown) : '') + (pp.barcode ? ' · <i class="fa-solid fa-barcode"></i>' : '');

            // La ligne « vrac / au détail » : quantité en kg, L ou pièces
            const looseLabel = p.bulk ? 'Pesée / vrac' : (du.factor > 1 ? 'Au détail' : (p.packagings.length ? 'À l\'unité' : 'Quantité'));
            const looseRowOf = (title, sub, big) =>
                '<div class="flex items-center gap-2 ' + (big ? 'py-3' : 'py-1.5') + '">' +
                    '<div class="flex-1 min-w-0"><p class="' + (big ? 'text-base font-bold text-slate-800' : 'text-sm text-slate-700') + '">' + title + '</p>' +
                    '<p class="text-[11px] text-slate-500">' + sub + '</p></div>' +
                    (big ? '<span class="text-[11px] font-semibold text-emerald-700 shrink-0">Reçu</span>' : '') +
                    '<div class="flex items-center border ' + (big ? 'border-emerald-400 ring-1 ring-emerald-200' : 'border-slate-300') + ' rounded-lg overflow-hidden shrink-0 focus-within:ring-2 focus-within:ring-emerald-400">' +
                        '<input type="number" inputmode="decimal" min="0" step="any" value="' + (s.loose || '') + '" placeholder="0" data-field="loose" data-pid="' + pid + '" class="sk-input ' + (big ? 'w-24 text-lg' : 'w-24') + ' text-right font-bold py-2 px-2 focus:outline-none">' +
                        '<span class="px-2 text-xs text-slate-500 bg-slate-50 self-stretch flex items-center">' + esc(du.label) + '</span>' +
                    '</div></div>';
            const looseSub = 'en ' + esc(du.label) + (p.bulk ? ' — poids réel de la balance' : '');

            let rowsHtml;
            if (e) {
                // Mode BL : la ligne de la commande d'abord (« Carton de 12 · Commandé 10 · Reçu [ ] »),
                // les autres façons de compter repliées en dessous.
                const primary = e.primary_pp ? p.packagings.find(x => x.id === e.primary_pp) : null;
                let main, others;
                if (primary) {
                    const ord = e.ordered / primary.qty, rec = e.received / primary.qty;
                    main = levelRow(primary, esc(primary.name),
                        '<strong>Commandé ' + nf.format(ord) + '</strong>' + (rec > 0 ? ' · déjà reçu ' + nf.format(rec) + ' · reste ' + nf.format(Math.max(0, ord - rec)) : '') + ' · ' + ppSub(primary), true);
                    others = p.packagings.filter(x => x.id !== primary.id).map(pp => levelRow(pp, esc(pp.name), ppSub(pp), false)).join('') +
                             looseRowOf(looseLabel, looseSub, false);
                } else {
                    main = looseRowOf(p.bulk ? 'Pesée / vrac' : 'Quantité',
                        '<strong>Commandé ' + friendly(e.ordered, p.unit) + '</strong>' + (e.received > 0 ? ' · déjà reçu ' + friendly(e.received, p.unit) : '') + ' · ' + looseSub, true);
                    others = p.packagings.map(pp => levelRow(pp, esc(pp.name), ppSub(pp), false)).join('');
                }
                const othersUsed = Object.entries(s.levels).some(([id, v]) => +id !== (primary && primary.id) && parseFloat(v) > 0) || (primary && parseFloat(s.loose) > 0);
                rowsHtml = main + (others
                    ? '<details data-det="' + pid + '"' + (othersUsed || openDetails.has(pid) ? ' open' : '') + '>' +
                      '<summary class="text-xs text-slate-500 py-2 cursor-pointer select-none">Livré autrement ? (autre emballage, à l\'unité, vrac)</summary>' + others + '</details>'
                    : '');
            } else {
                rowsHtml = p.packagings.map(pp => levelRow(pp, esc(pp.name), ppSub(pp), false)).join('') + looseRowOf(looseLabel, looseSub, false);
            }

            const amountRow = ORDER_ID ? '' :
                '<div class="flex items-center gap-2 py-1.5 border-t border-dashed border-slate-200 mt-1">' +
                    '<div class="flex-1"><p class="text-sm text-slate-700">Montant payé</p><p class="text-[11px] text-slate-400">optionnel — sert au paiement fournisseur</p></div>' +
                    '<div class="flex items-center border border-slate-300 rounded-lg overflow-hidden shrink-0 focus-within:ring-2 focus-within:ring-emerald-400">' +
                        '<input type="number" inputmode="decimal" min="0" step="any" value="' + (s.amount || '') + '" placeholder="0" data-field="amount" data-pid="' + pid + '" class="sk-input w-24 text-right py-2 px-2 focus:outline-none">' +
                        '<span class="px-2 text-xs text-slate-500 bg-slate-50 self-stretch flex items-center">MRU</span>' +
                    '</div></div>';

            const dim = st.k === 'done-before' ? ' opacity-60' : '';
            return '<div class="bg-white rounded-xl border ' + border + ' overflow-hidden' + dim + (flashPid === pid ? ' sk-flash' : '') + '" data-card="' + pid + '">' +
                '<div class="px-3 pt-3 flex items-start justify-between gap-2">' +
                    '<div class="min-w-0"><p class="font-semibold text-slate-800 text-sm leading-tight">' + esc(p.name) +
                        (p.bulk ? ' <span class="text-[10px] font-semibold bg-sky-100 text-sky-700 px-1.5 py-0.5 rounded">VRAC</span>' : '') + '</p>' + head + '</div>' +
                    '<div class="text-right shrink-0"><p class="font-bold text-base ' + (st.got > 0 ? 'text-emerald-700' : 'text-slate-300') + '">+' + friendly(st.got, p.unit) + '</p>' +
                    (ORDER_ID ? '' : '<button type="button" class="text-[11px] text-slate-400 hover:text-red-600" data-act="remove" data-pid="' + pid + '">Retirer</button>') + '</div>' +
                '</div>' +
                (badge ? '<div class="px-3 pt-1.5">' + badge + '</div>' : '') +
                (e ? '<div class="mx-3 mt-2 h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full ' + (st.k === 'over' ? 'bg-orange-500' : 'bg-emerald-500') + '" style="width:' + (st.k === 'over' ? 100 : pct) + '%"></div></div>' : '') +
                '<div class="px-3 pb-2 pt-1 divide-y divide-slate-100">' + rowsHtml + amountRow + '</div>' +
            '</div>';
        }).join('');

        document.getElementById('emptyCards').classList.toggle('hidden', pids.length > 0);
        refreshSummary();
    }

    function refreshSummary() {
        const pids = Object.keys(state);
        const received = pids.filter(pid => baseOf(pid) > 0);
        const partial = pids.filter(pid => ['partial', 'none'].includes(status(pid).k) && expected.has(+pid));
        const over = pids.filter(pid => status(pid).k === 'over');
        let txt = '<strong class="text-slate-700">' + received.length + ' produit' + (received.length > 1 ? 's' : '') + ' reçu' + (received.length > 1 ? 's' : '') + '</strong>';
        if (ORDER_ID && partial.length) txt += ' · <span class="text-amber-600">' + partial.length + ' en reliquat</span>';
        if (over.length) txt += ' · <span class="text-orange-600">' + over.length + ' en surplus</span>';
        document.getElementById('summary').innerHTML = txt;
        document.getElementById('submitBtn').disabled = received.length === 0;
    }

    // ── Interactions ────────────────────────────────────────────────────────
    const cards = document.getElementById('cards');
    cards.addEventListener('click', e => {
        const b = e.target.closest('[data-act]');
        if (!b) return;
        const pid = +b.dataset.pid, s = state[pid];
        if (b.dataset.act === 'remove') { delete state[pid]; render(); save(); return; }
        const pp = b.dataset.pp;
        const cur = parseFloat(s.levels[pp]) || 0;
        s.levels[pp] = Math.max(0, cur + (b.dataset.act === 'inc' ? 1 : -1));
        s.t = Date.now();
        render(); save();
    });
    cards.addEventListener('input', e => {
        const f = e.target.dataset.field;
        if (!f) return;
        const s = state[e.target.dataset.pid];
        const v = e.target.value === '' ? '' : Math.max(0, parseFloat(e.target.value) || 0);
        if (f === 'level') s.levels[e.target.dataset.pp] = v;
        else s[f] = v;
        save();
        // Mise à jour légère : total et statut de la carte sans perdre le focus
        clearTimeout(cards._t);
        cards._t = setTimeout(() => {
            const active = document.activeElement, sel = active && active.dataset ? [active.dataset.field, active.dataset.pid, active.dataset.pp] : null;
            render();
            if (sel && sel[0]) {
                const q = '[data-field="' + sel[0] + '"][data-pid="' + sel[1] + '"]' + (sel[2] ? '[data-pp="' + sel[2] + '"]' : '');
                const el = cards.querySelector(q);
                if (el) { el.focus(); const len = el.value.length; try { el.setSelectionRange(len, len); } catch (x) {} }
            }
        }, 400);
    });
    cards.addEventListener('focusin', e => { if (e.target.matches('input')) try { e.target.select(); } catch (x) {} });
    cards.addEventListener('toggle', e => {
        const d = e.target.closest && e.target.closest('details[data-det]');
        if (d) d.open ? openDetails.add(+d.dataset.det) : openDetails.delete(+d.dataset.det);
    }, true);

    document.getElementById('resetBtn').addEventListener('click', () => {
        if (!confirm('Remettre toutes les quantités à zéro ?')) return;
        if (ORDER_ID) Object.keys(state).forEach(pid => state[pid] = blank()); else state = {};
        render(); save();
    });

    const fillAll = document.getElementById('fillAllBtn');
    if (fillAll) fillAll.addEventListener('click', () => {
        // Comme « Tout reçu » d'Odoo : chaque produit = son restant, dans l'emballage de la commande
        // (ex : 8 Cartons), le reste éventuel en emballage plus petit ou au détail.
        EXPECTED.forEach(e => {
            const p = products.get(e.product_id);
            let left = Math.max(0, e.ordered - e.received);
            const s = state[e.product_id] = blank();
            if (left <= 0) return;
            const primary = e.primary_pp ? p.packagings.find(x => x.id === e.primary_pp) : null;
            const levels = (primary ? [primary] : []).concat(p.packagings.filter(x => x !== primary && (!primary || x.qty < primary.qty)));
            levels.forEach(pp => {
                const n = Math.floor(left / pp.qty + 1e-9);
                if (n > 0) { s.levels[pp.id] = n; left = Math.round((left - n * pp.qty) * 100) / 100; }
            });
            if (left > 0) s.loose = Math.round(left / displayUnit(p.unit).factor * 1000) / 1000;
        });
        render(); save();
        kit.toast('Quantités restantes reportées — corrigez ce qui manque ou dépasse.');
    });

    // ── Ajout manuel d'un produit (réception directe) ───────────────────────
    const pick = document.getElementById('pickSheet');
    const renderPick = () => {
        const q = document.getElementById('pickSearch').value.trim().toLowerCase();
        const list = [...products.values()].filter(p => !q || (p.name + ' ' + (p.ref || '')).toLowerCase().includes(q)).slice(0, 80);
        document.getElementById('pickResults').innerHTML = list.map(p =>
            '<button type="button" class="w-full text-left px-3 py-3 hover:bg-emerald-50 flex justify-between gap-2" data-pid="' + p.id + '">' +
            '<span class="text-sm text-slate-800">' + esc(p.name) + (p.bulk ? ' <span class="text-[10px] font-semibold bg-sky-100 text-sky-700 px-1.5 rounded">VRAC</span>' : '') + '</span>' +
            '<span class="text-xs text-slate-400 shrink-0">' + esc(p.unit) + '</span></button>').join('') ||
            '<p class="text-sm text-slate-400 text-center py-6">Aucun produit.</p>';
    };
    const addBtn = document.getElementById('addProductBtn');
    if (addBtn) addBtn.addEventListener('click', () => {
        document.getElementById('pickSearch').value = '';
        renderPick();
        pick.classList.remove('hidden');
        setTimeout(() => document.getElementById('pickSearch').focus(), 50);
    });
    document.getElementById('pickSearch').addEventListener('input', renderPick);
    pick.addEventListener('click', e => {
        if (e.target.closest('[data-close]')) { pick.classList.add('hidden'); return; }
        const b = e.target.closest('[data-pid]');
        if (!b) return;
        const pid = +b.dataset.pid;
        state[pid] = state[pid] || blank();
        state[pid].t = Date.now();
        pick.classList.add('hidden');
        render(pid); save();
        const el = cards.querySelector('[data-field="loose"][data-pid="' + pid + '"]');
        if (el) el.focus();
    });

    [blNumber, notes].forEach(el => el.addEventListener('input', save));
    $('#stockSelect').on('change', function () { try { localStorage.setItem(STOCK_KEY, this.value); } catch (e) {} });

    // ── Validation ──────────────────────────────────────────────────────────
    document.getElementById('submitBtn').addEventListener('click', async () => {
        const errorBox = document.getElementById('errorBox');
        errorBox.classList.add('hidden');
        const supplierId = $('#supplierSelect').val();
        if (!supplierId) { kit.error('Choisissez le fournisseur'); $('#supplierSelect').select2('open'); return; }

        const payload = Object.keys(state).filter(pid => baseOf(pid) > 0).map(pid => {
            const p = products.get(+pid), s = state[pid];
            return {
                product_id: +pid,
                levels: p.packagings.filter(pp => parseFloat(s.levels[pp.id]) > 0).map(pp => ({ product_packaging_id: pp.id, count: parseFloat(s.levels[pp.id]) })),
                loose: Math.round((parseFloat(s.loose) || 0) * displayUnit(p.unit).factor * 100) / 100,
                amount: ORDER_ID ? null : (parseFloat(s.amount) || null),
            };
        });
        if (!payload.length) return;

        const lines = [];
        const partial = Object.keys(state).filter(pid => expected.has(+pid) && ['partial', 'none'].includes(status(pid).k));
        const over = Object.keys(state).filter(pid => status(pid).k === 'over');
        lines.push('Réception de ' + payload.length + ' produit(s) dans « ' + $('#stockSelect option:selected').text().trim() + ' ».');
        if (ORDER_ID && partial.length) lines.push('\n⚠ ' + partial.length + ' produit(s) incomplet(s) : un reliquat restera à recevoir.');
        if (over.length) lines.push('\n⚠ Surplus : ' + over.map(pid => products.get(+pid).name + ' +' + friendly(status(pid).diff, products.get(+pid).unit)).join(', '));
        if (!confirm(lines.join('') + '\n\nConfirmer ?')) return;

        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        try {
            const r = await fetch(ROUTES.store, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    purchase_order_id: ORDER_ID,
                    supplier_id: +supplierId,
                    stock_id: +$('#stockSelect').val(),
                    supplier_delivery_note: blNumber.value,
                    notes: notes.value,
                    products: payload,
                }),
            });
            const data = await r.json().catch(() => ({}));
            if (!r.ok) {
                const msgs = data.errors ? Object.values(data.errors).flat() : [data.message || ('Erreur ' + r.status)];
                errorBox.innerHTML = msgs.map(m => '<p>' + esc(m) + '</p>').join('');
                errorBox.classList.remove('hidden');
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                kit.beep(false);
                btn.disabled = false;
                return;
            }
            try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
            kit.beep(true);
            kit.toast(data.message);
            setTimeout(() => { window.location = data.redirect; }, 900);
        } catch (e) {
            errorBox.textContent = 'Connexion impossible. Votre saisie est conservée, réessayez.';
            errorBox.classList.remove('hidden');
            btn.disabled = false;
        }
    });

    render();
})();
</script>
@endpush
