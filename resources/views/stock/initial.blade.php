@extends('layouts.stock')

@section('title', 'Stock initial')
@section('header', 'Stock initial')

@section('content')
@php use App\Support\Qty; @endphp
<div class="py-4 max-w-3xl mx-auto space-y-4 pb-40">

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-base"><i class="fa-solid fa-circle-check mr-1"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-base">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-base">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <details class="bg-white rounded-xl border border-slate-100 px-4 py-3 text-sm text-slate-600">
        <summary class="cursor-pointer font-semibold text-slate-700"><i class="fa-solid fa-circle-info text-emerald-600 mr-1"></i>Comment ça marche ?</summary>
        <ol class="list-decimal ml-5 mt-2 space-y-1 leading-relaxed">
            <li><strong>Scannez</strong> le code-barres du produit ou de son emballage (carton, sac…), ou cherchez-le par son nom.</li>
            <li>Indiquez <strong>combien il y en a</strong>, puis <strong>Ajouter</strong>. Pour 3 sacs + 10 kg en vrac, ajoutez les deux : ils s'additionnent.</li>
            <li><strong>Enregistrer</strong> : la quantité saisie <strong>devient</strong> le stock du produit dans le magasin.</li>
        </ol>
    </details>

    {{-- Magasin (Stock Catering / Economat par défaut) --}}
    @if($stocks->count() > 1)
    <form method="GET" action="{{ route('stock.initial') }}" class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
        <label class="block text-sm font-bold text-slate-700 mb-1.5">Magasin</label>
        <select name="stock_id" id="stockSelect" style="width:100%" onchange="this.form.submit()">
            @foreach($stocks as $s)<option value="{{ $s->id }}" @selected($s->id === $stock->id)>{{ $s->name }}</option>@endforeach
        </select>
    </form>
    @endif

    {{-- ── 1. Scanner ─────────────────────────────────────────────────── --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-base font-bold text-slate-800"><i class="fa-solid fa-barcode mr-1 text-slate-500"></i>Scanner un produit</h2>
            <span class="text-xs text-slate-500 truncate">Magasin : <strong class="text-slate-700">{{ $stock->name }}</strong></span>
        </div>
        <div class="flex gap-2">
            <input type="text" id="scanCode" data-bc-cam="1" data-bc-label="Stock initial : produit ou emballage"
                   inputmode="numeric" autocomplete="off" maxlength="64" placeholder="Code-barres"
                   class="flex-1 min-w-0 px-4 py-3.5 border border-slate-300 rounded-xl bg-white font-mono text-base focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <button type="button" class="bc-cam-btn shrink-0 inline-flex items-center justify-center gap-2 px-5 rounded-xl bg-slate-800 hover:bg-slate-900 active:bg-slate-900 text-white text-base font-bold" title="Scanner avec la caméra">
                <i class="fa-solid fa-camera"></i><span>Scanner</span>
            </button>
        </div>
        <p id="scanMsg" class="hidden text-sm rounded-xl px-3 py-2.5"></p>
        <label class="flex items-start gap-3 text-sm text-slate-600 bg-slate-50 rounded-xl px-3 py-2.5 cursor-pointer">
            <input type="checkbox" id="countMode" class="mt-0.5 w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span><strong class="text-slate-800">Compter en scannant</strong> : chaque scan ajoute directement 1 à la liste (pratique pour compter carton par carton).</span>
        </label>
    </section>

    {{-- ── 2. Produit choisi : emballage + quantité ───────────────────── --}}
    <section class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-4 space-y-4">
        <div>
            <label for="prod" class="block text-sm font-bold text-slate-700 mb-1.5"><i class="fa-solid fa-magnifying-glass text-emerald-600 mr-1"></i>Ou chercher par nom</label>
            <select id="prod" style="width:100%">
                <option value="">— Rechercher un produit —</option>
                @foreach($catalog as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }}{{ $p['ref'] ? ' · ' . $p['ref'] : '' }}</option>@endforeach
            </select>
        </div>

        <div id="picked" class="hidden space-y-4">
            <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3">
                <p id="pickedName" class="text-lg font-bold text-slate-800 leading-tight"></p>
                <p id="cur" class="text-sm text-slate-600 mt-0.5"></p>
            </div>

            <div>
                <p class="text-sm font-bold text-slate-700 mb-1.5">Compté en</p>
                <div id="packs" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
            </div>

            <div>
                <label for="qty" class="block text-sm font-bold text-slate-700 mb-1.5">Combien ?</label>
                <div class="flex items-stretch gap-2">
                    <button type="button" data-step="-1" class="qty-step w-14 shrink-0 rounded-xl bg-slate-100 active:bg-slate-200 text-2xl font-bold text-slate-700" aria-label="Moins">−</button>
                    <input type="number" id="qty" inputmode="decimal" min="0" step="any" placeholder="0"
                           class="flex-1 min-w-0 text-center font-bold border border-slate-300 rounded-xl px-3 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-400" style="font-size:24px">
                    <button type="button" data-step="1" class="qty-step w-14 shrink-0 rounded-xl bg-slate-100 active:bg-slate-200 text-2xl font-bold text-slate-700" aria-label="Plus">+</button>
                </div>
                <p id="eq" class="text-sm text-emerald-700 font-semibold mt-1.5 text-center min-h-[1.25rem]"></p>
            </div>

            <div class="flex gap-2">
                <button type="button" id="clearBtn" class="px-4 py-3.5 rounded-xl bg-slate-100 text-slate-600 font-semibold">Annuler</button>
                <button type="button" id="addBtn" disabled class="flex-1 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white text-base font-bold">
                    <i class="fa-solid fa-plus mr-1"></i> Ajouter à la liste
                </button>
            </div>
        </div>
    </section>

    {{-- ── 3. Liste à enregistrer ─────────────────────────────────────── --}}
    <form method="POST" action="{{ route('stock.adjustments.store') }}" id="saveForm" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
        @csrf
        <input type="hidden" name="stock_id" value="{{ $stock->id }}">
        <input type="hidden" name="back" value="initial">
        <div id="hidden"></div>
        <h3 class="font-bold text-slate-800 text-base mb-2">À enregistrer dans « {{ $stock->name }} » <span id="countTitle" class="text-emerald-700"></span></h3>
        <div id="lines" class="space-y-3"></div>
        <p id="empty" class="text-center text-sm text-slate-400 py-6 border border-dashed border-slate-200 rounded-xl">Aucun produit pour l'instant. Scannez ou cherchez un produit.</p>

        <div class="fixed bottom-0 left-0 right-0 md:left-64 z-30 bg-white/95 backdrop-blur border-t border-slate-200 p-3 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]"
             style="padding-bottom: max(.75rem, env(safe-area-inset-bottom))">
            <div class="max-w-3xl mx-auto flex flex-col sm:flex-row gap-2 sm:items-center">
                <input type="text" name="notes" maxlength="500" value="Stock initial" aria-label="Note"
                       class="flex-1 px-3 py-2.5 rounded-xl border border-slate-300 text-base focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <button id="saveBtn" disabled class="px-5 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white text-base font-bold">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Enregistrer <span id="count"></span>
                </button>
            </div>
        </div>
    </form>

    {{-- Dernières saisies --}}
    @if($recent->isNotEmpty())
    <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <h3 class="font-bold text-slate-700 text-base mb-2"><i class="fa-solid fa-clock-rotate-left text-slate-400 mr-1"></i>Dernières saisies</h3>
        <div class="divide-y divide-slate-100">
            @foreach($recent as $m)
                @php $u = $m->product->unit->symbol ?? ''; @endphp
                <div class="py-2.5 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800">{{ $m->product->name ?? '—' }}</p>
                        <p class="text-xs text-slate-500">{{ $m->notes }} · {{ $m->user->name ?? '—' }} · {{ $m->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <strong class="shrink-0 text-base {{ $m->type === 'in' ? 'text-emerald-700' : 'text-red-600' }}">{{ $m->type === 'in' ? '+' : '−' }}{{ Qty::format($m->quantity, $u) }}</strong>
                </div>
            @endforeach
        </div>
    </section>
    @endif
</div>

@include('partials.barcode-camera', ['selector' => '#scanCode'])
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const CATALOG = new Map(@json($catalog).map(p => [String(p.id), p]));
    const CURRENT = @json($current, JSON_FORCE_OBJECT);
    const nf = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 3 });
    const disp = u => ({ g: ['kg', 1000], ml: ['L', 1000] })[(u || '').toLowerCase()] || [u || 'unité', 1];
    const friendly = (q, u) => { const d = disp(u); return (d[1] > 1 && Math.abs(q) >= d[1]) ? nf.format(q / d[1]) + ' ' + d[0] : nf.format(q) + ' ' + (u || ''); };
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const norm = c => String(c || '').replace(/\s+/g, '');
    const num = v => parseFloat(String(v).replace(',', '.')) || 0;
    const $id = id => document.getElementById(id);
    const prod = $id('prod'), qty = $id('qty'), scan = $id('scanCode');

    // Index des codes-barres : code -> { pid, pack } (pack = id de l'emballage, ou 'u' = 1 unité de base)
    const CODES = new Map();
    CATALOG.forEach(p => {
        if (p.barcode) CODES.set(norm(p.barcode), { pid: String(p.id), pack: 'u' });
        p.packagings.forEach(pp => { if (pp.barcode) CODES.set(norm(pp.barcode), { pid: String(p.id), pack: String(pp.id) }); });
    });

    // Choix possibles pour un produit : ses emballages, 1 unité (si code de base), en vrac
    function choices(p) {
        const d = disp(p.unit), list = p.packagings.map(pp => ({ id: String(pp.id), name: pp.name, f: pp.qty, hint: friendly(pp.qty, p.unit) }));
        if (p.barcode) list.push({ id: 'u', name: 'À l\'unité', f: 1, hint: '1 ' + (p.unit || 'unité') });
        list.push({ id: 'v', name: 'En vrac', f: d[1], hint: 'en ' + d[0], bulk: true });
        return list;
    }

    let cur = null;          // produit choisi
    let packId = null;       // choix d'emballage
    let lines = new Map();   // "pid|pack" -> { pid, pack, name, f, n, bulk }

    const current = pid => CURRENT[pid] || 0;
    const choice = () => cur ? choices(cur).find(c => c.id === packId) : null;

    function pick(pid, pack) {
        cur = CATALOG.get(String(pid)) || null;
        $id('picked').classList.toggle('hidden', !cur);
        if (!cur) { packId = null; return refresh(); }
        if (prod.value !== String(cur.id)) jQuery(prod).val(String(cur.id)).trigger('change.select2');
        const list = choices(cur);
        packId = pack && list.some(c => c.id === pack) ? pack : list[0].id;
        $id('pickedName').textContent = cur.name;
        $id('cur').innerHTML = 'Stock actuel : <strong>' + esc(friendly(current(cur.id), cur.unit)) + '</strong>';
        $id('packs').innerHTML = list.map(c =>
            '<button type="button" data-pack="' + c.id + '" class="pack-btn text-left px-4 py-3 rounded-xl border-2 transition">' +
            '<span class="block font-bold text-slate-800">' + esc(c.name) + '</span>' +
            '<span class="block text-sm text-slate-500">' + esc(c.hint) + '</span></button>').join('');
        refresh();
    }

    function refresh() {
        document.querySelectorAll('.pack-btn').forEach(b => {
            const on = b.dataset.pack === packId;
            b.classList.toggle('border-emerald-500', on); b.classList.toggle('bg-emerald-50', on);
            b.classList.toggle('border-slate-200', !on); b.classList.toggle('bg-white', !on);
        });
        const c = choice(), n = num(qty.value);
        const base = c ? Math.round(n * c.f * 100) / 100 : 0;
        $id('eq').textContent = c && base > 0 && !c.bulk && c.f !== 1 ? '= ' + friendly(base, cur.unit) : '';
        $id('addBtn').disabled = !(c && base > 0);
    }

    function message(text, kind) {
        const m = $id('scanMsg');
        m.innerHTML = text;
        m.className = 'text-sm rounded-xl px-3 py-2.5 ' + ({ ok: 'bg-emerald-50 text-emerald-800 border border-emerald-200', err: 'bg-red-50 text-red-700 border border-red-200' }[kind]);
    }

    function addLine(pid, pack, n) {
        const p = CATALOG.get(String(pid)), c = choices(p).find(x => x.id === pack);
        if (!p || !c || n <= 0) return;
        const key = p.id + '|' + c.id;
        const l = lines.get(key) || { pid: String(p.id), pack: c.id, name: c.name, f: c.f, n: 0, bulk: !!c.bulk };
        l.n = Math.round((l.n + n) * 1000) / 1000;
        lines.set(key, l);
        render(key);
    }

    // ── Scan (douchette, clavier ou caméra) ──────────────────────────────
    function onScan() {
        const code = norm(scan.value);
        if (!code) return;
        scan.value = '';
        const hit = CODES.get(code);
        if (!hit) {
            message('<i class="fa-solid fa-triangle-exclamation mr-1"></i>Code <strong>' + esc(code) + '</strong> inconnu : il n\'est mis sur aucun produit. Cherchez le produit par son nom, et ajoutez ce code sur sa fiche produit.', 'err');
            return;
        }
        const p = CATALOG.get(hit.pid), c = choices(p).find(x => x.id === hit.pack);
        if ($id('countMode').checked) {
            addLine(hit.pid, hit.pack, 1);
            const l = lines.get(p.id + '|' + c.id);
            message('<i class="fa-solid fa-circle-check mr-1"></i><strong>' + esc(p.name) + '</strong> · ' + esc(c.name) + ' : <strong>' + nf.format(l.n) + '</strong> compté(s)', 'ok');
            return;
        }
        // Même produit et même emballage que celui en cours : +1
        const same = cur && String(cur.id) === hit.pid && packId === hit.pack;
        pick(hit.pid, hit.pack);
        qty.value = same ? String(Math.round((num(qty.value) + 1) * 1000) / 1000) : 1;
        refresh();
        message('<i class="fa-solid fa-circle-check mr-1"></i>Trouvé : <strong>' + esc(p.name) + '</strong> · ' + esc(c.name) + '. Indiquez combien, puis Ajouter.', 'ok');
        $id('picked').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    scan.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); onScan(); } });
    scan.addEventListener('change', onScan); // caméra : le champ est rempli puis « change »

    // ── Choix manuel ─────────────────────────────────────────────────────
    jQuery(prod).on('change', function () {
        if (cur && String(cur.id) === this.value) return;
        qty.value = '';
        pick(this.value);
        if (cur) setTimeout(() => qty.focus(), 50);
    });
    $id('packs').addEventListener('click', e => {
        const b = e.target.closest('.pack-btn');
        if (b) { packId = b.dataset.pack; refresh(); }
    });
    document.querySelectorAll('.qty-step').forEach(b => b.addEventListener('click', () => {
        qty.value = Math.max(0, num(qty.value) + (+b.dataset.step));
        refresh();
    }));
    qty.addEventListener('input', refresh);
    qty.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); if (!$id('addBtn').disabled) $id('addBtn').click(); } });

    function reset() {
        qty.value = ''; cur = null; packId = null;
        jQuery(prod).val('').trigger('change.select2');
        $id('picked').classList.add('hidden');
    }
    $id('clearBtn').addEventListener('click', reset);
    $id('addBtn').addEventListener('click', () => {
        if (!cur || !choice()) return;
        addLine(cur.id, packId, num(qty.value));
        message('<i class="fa-solid fa-circle-check mr-1"></i><strong>' + esc(cur.name) + '</strong> ajouté à la liste. Scannez le produit suivant.', 'ok');
        reset();
        if (window.matchMedia('(min-width: 768px)').matches) scan.focus();
        else $id('scanMsg').scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    // ── Liste (un bloc par produit, ses emballages s'additionnent) ────────
    function render(flashKey) {
        const byPid = new Map();
        lines.forEach((l, key) => { const g = byPid.get(l.pid) || []; g.push([key, l]); byPid.set(l.pid, g); });
        $id('lines').innerHTML = [...byPid.entries()].map(([pid, parts]) => {
            const p = CATALOG.get(pid), was = current(pid);
            const total = Math.round(parts.reduce((s, [, l]) => s + l.n * l.f, 0) * 100) / 100, delta = total - was;
            return '<div class="rounded-xl border border-slate-200 p-3' + (parts.some(([k]) => k === flashKey) ? ' bc-flash' : '') + '">' +
                '<p class="font-bold text-slate-800 text-base leading-tight">' + esc(p.name) + '</p>' +
                '<div class="mt-2 space-y-2">' + parts.map(([key, l]) =>
                    '<div class="flex flex-wrap items-center gap-2">' +
                    '<span class="w-full sm:w-auto sm:flex-1 min-w-0 text-sm font-semibold text-slate-600">' + esc(l.name) + (l.bulk ? ' (' + disp(p.unit)[0] + ')' : '') + '</span>' +
                    '<div class="flex items-center gap-2 ml-auto">' +
                    '<button type="button" data-k="' + esc(key) + '" data-d="-1" class="w-10 h-10 rounded-lg bg-slate-100 active:bg-slate-200 text-xl font-bold text-slate-700">−</button>' +
                    '<input type="number" inputmode="decimal" min="0" step="any" data-k="' + esc(key) + '" value="' + l.n + '" class="line-n w-20 text-center font-bold text-lg border border-slate-300 rounded-lg py-1.5">' +
                    '<button type="button" data-k="' + esc(key) + '" data-d="1" class="w-10 h-10 rounded-lg bg-slate-100 active:bg-slate-200 text-xl font-bold text-slate-700">+</button>' +
                    '<button type="button" data-k="' + esc(key) + '" data-del="1" class="w-10 h-10 rounded-lg text-red-500 hover:bg-red-50" title="Retirer"><i class="fa-solid fa-trash"></i></button>' +
                    '</div></div>').join('') + '</div>' +
                '<div class="mt-2 pt-2 border-t border-dashed border-slate-200 flex flex-wrap items-baseline justify-between gap-x-3 text-sm">' +
                '<span class="text-slate-500">' + esc(friendly(was, p.unit)) + ' → <strong class="text-emerald-700 text-base">' + esc(friendly(total, p.unit)) + '</strong></span>' +
                (was > 0 && delta !== 0 ? '<span class="text-xs font-semibold ' + (delta < 0 ? 'text-red-600' : 'text-amber-700') + '">remplace le stock (' + (delta > 0 ? '+' : '−') + esc(friendly(Math.abs(delta), p.unit)) + ')</span>' : '') +
                '</div></div>';
        }).join('');
        $id('hidden').innerHTML = [...byPid.entries()].map(([pid, parts]) =>
            '<input type="hidden" name="quantities[' + pid + ']" value="' + (Math.round(parts.reduce((s, [, l]) => s + l.n * l.f, 0) * 100) / 100) + '">').join('');
        const n = byPid.size;
        $id('empty').classList.toggle('hidden', n > 0);
        $id('saveBtn').disabled = n === 0;
        $id('count').textContent = n ? '(' + n + ')' : '';
        $id('countTitle').textContent = n ? '· ' + n + ' produit' + (n > 1 ? 's' : '') : '';
    }
    $id('lines').addEventListener('click', e => {
        const b = e.target.closest('button[data-k]');
        if (!b) return;
        const l = lines.get(b.dataset.k);
        if (!l) return;
        if (b.dataset.del) { if (confirm('Retirer « ' + CATALOG.get(l.pid).name + ' · ' + l.name + ' » ?')) lines.delete(b.dataset.k); }
        else { l.n = Math.max(0, Math.round((l.n + (+b.dataset.d)) * 1000) / 1000); if (l.n === 0) lines.delete(b.dataset.k); }
        render();
    });
    $id('lines').addEventListener('change', e => {
        const i = e.target.closest('.line-n');
        if (!i) return;
        const l = lines.get(i.dataset.k), n = num(i.value);
        if (l) { if (n > 0) l.n = n; else lines.delete(i.dataset.k); }
        render();
    });
    $id('saveForm').addEventListener('submit', e => {
        if (!lines.size) { e.preventDefault(); return; }
        $id('saveBtn').disabled = true;
    });
    window.addEventListener('beforeunload', e => { if (lines.size && !$id('saveBtn').disabled) { e.preventDefault(); e.returnValue = ''; } });

    if (window.matchMedia('(min-width: 768px)').matches) scan.focus();
    render();
})();
</script>
@endpush
