@extends('layouts.stock')

@section('title', 'Stock initial')
@section('header', 'Stock initial')

@section('content')
@php use App\Support\Qty; @endphp
<div class="py-4 max-w-4xl mx-auto space-y-4 pb-32">

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm"><i class="fa-solid fa-circle-check mr-1"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <p class="text-sm text-slate-600 bg-white rounded-xl border border-slate-100 px-4 py-3">
        <i class="fa-solid fa-circle-info text-emerald-600 mr-1"></i>
        Saisissez ce qu'il y a <strong>réellement dans le magasin</strong>, sans commande ni réception :
        cherchez le produit, tapez la quantité (en sacs, cartons… ou en vrac), ajoutez-le à la liste, puis <strong>Enregistrer</strong>.
        La quantité saisie <strong>devient</strong> le stock du produit.
    </p>

    {{-- Magasin (Stock Catering par défaut) --}}
    <form method="GET" action="{{ route('stock.initial') }}" class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
        <label class="block text-xs font-semibold text-slate-500 mb-1">Magasin</label>
        <select name="stock_id" id="stockSelect" style="width:100%" onchange="this.form.submit()">
            @foreach($stocks as $s)<option value="{{ $s->id }}" @selected($s->id === $stock->id)>{{ $s->name }}</option>@endforeach
        </select>
    </form>

    {{-- Ajouter un produit --}}
    <div class="bg-white rounded-xl border border-emerald-200 shadow-sm p-4">
        <p class="text-sm font-semibold text-slate-700 mb-2"><i class="fa-solid fa-magnifying-glass text-emerald-600 mr-1"></i>Produit à saisir</p>
        <div class="grid grid-cols-12 gap-2 items-start">
            <div class="col-span-12 sm:col-span-5">
                <select id="prod" style="width:100%">
                    <option value="">— Rechercher un produit —</option>
                    @foreach($catalog as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }}{{ $p['ref'] ? ' · ' . $p['ref'] : '' }}</option>@endforeach
                </select>
                <p id="cur" class="text-[11px] text-slate-500 mt-1"></p>
            </div>
            <div class="col-span-7 sm:col-span-4">
                <select id="pack" style="width:100%" disabled><option value="">— emballage —</option></select>
            </div>
            <div class="col-span-5 sm:col-span-3">
                <input type="number" id="qty" inputmode="decimal" min="0" step="any" placeholder="Quantité"
                       class="w-full text-right font-bold border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400" style="font-size:18px">
                <p id="eq" class="text-[11px] text-emerald-700 mt-1 text-right"></p>
            </div>
        </div>
        <button type="button" id="addBtn" disabled class="mt-3 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white text-sm font-semibold">
            <i class="fa-solid fa-plus mr-1"></i> Ajouter à la liste
        </button>
        <p class="text-[11px] text-slate-400 mt-2">Astuce : pour 3 sacs + 10 kg en vrac, ajoutez deux lignes pour le même produit, elles s'additionnent.</p>
    </div>

    {{-- Liste à enregistrer --}}
    <form method="POST" action="{{ route('stock.adjustments.store') }}" id="saveForm" class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
        @csrf
        <input type="hidden" name="stock_id" value="{{ $stock->id }}">
        <input type="hidden" name="back" value="initial">
        <div id="hidden"></div>
        <h3 class="font-semibold text-slate-700 text-sm mb-2">À enregistrer dans « {{ $stock->name }} »</h3>
        <div id="lines" class="divide-y divide-slate-100"></div>
        <p id="empty" class="text-center text-sm text-slate-400 py-6">Aucun produit pour l'instant.</p>

        <div class="fixed bottom-0 left-0 right-0 md:left-64 z-30 bg-white border-t border-slate-200 p-3 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]">
            <div class="max-w-4xl mx-auto flex flex-col sm:flex-row gap-2 sm:items-center">
                <input type="text" name="notes" maxlength="500" value="Stock initial"
                       class="flex-1 px-3 py-2.5 rounded-lg border border-slate-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <button id="saveBtn" disabled class="px-5 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white text-sm font-bold">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Enregistrer <span id="count"></span>
                </button>
            </div>
        </div>
    </form>

    {{-- Dernières saisies --}}
    @if($recent->isNotEmpty())
    <section class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
        <h3 class="font-semibold text-slate-700 text-sm mb-2"><i class="fa-solid fa-clock-rotate-left text-slate-400 mr-1"></i>Dernières saisies dans ce magasin</h3>
        <div class="divide-y divide-slate-100 text-sm">
            @foreach($recent as $m)
                @php $u = $m->product->unit->symbol ?? ''; @endphp
                <div class="py-2 flex flex-wrap justify-between gap-2">
                    <span class="text-slate-700">{{ $m->product->name ?? '—' }} <span class="text-xs text-slate-400">· {{ $m->notes }}</span></span>
                    <span class="text-xs text-slate-500">
                        <strong class="{{ $m->type === 'in' ? 'text-emerald-700' : 'text-red-600' }}">{{ $m->type === 'in' ? '+' : '−' }}{{ Qty::format($m->quantity, $u) }}</strong>
                        · {{ $m->user->name ?? '—' }} · {{ $m->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
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
    const $id = id => document.getElementById(id);
    const prod = $id('prod'), pack = $id('pack'), qty = $id('qty');
    let lines = []; // { pid, label, base }

    function current(pid) { return CURRENT[pid] || 0; }

    function refreshAdd() {
        const p = CATALOG.get(prod.value), opt = pack.selectedOptions[0];
        const n = parseFloat(String(qty.value).replace(',', '.')) || 0;
        const base = p && opt ? Math.round(n * (parseFloat(opt.dataset.f) || 1) * 100) / 100 : 0;
        $id('eq').textContent = p && base > 0 && opt.value !== 'v' ? '= ' + friendly(base, p.unit) : '';
        $id('addBtn').disabled = !(p && base > 0);
        return { p, opt, n, base };
    }

    jQuery(prod).on('change', function () {
        const p = CATALOG.get(this.value);
        if (!p) { pack.innerHTML = '<option value="">— emballage —</option>'; pack.disabled = true; $id('cur').textContent = ''; }
        else {
            const d = disp(p.unit);
            pack.innerHTML = p.packagings.map(pp => '<option value="' + pp.id + '" data-f="' + pp.qty + '">' + esc(pp.name) + ' (' + friendly(pp.qty, p.unit) + ')</option>').join('')
                + '<option value="v" data-f="' + d[1] + '"' + (p.packagings.length ? '' : ' selected') + '>En vrac (' + esc(d[0]) + ')</option>';
            pack.disabled = false;
            $id('cur').innerHTML = 'Stock actuel dans ce magasin : <strong>' + friendly(current(p.id), p.unit) + '</strong>';
            setTimeout(() => qty.focus(), 0);
        }
        if (jQuery(pack).hasClass('select2-hidden-accessible')) jQuery(pack).trigger('change.select2');
        refreshAdd();
    });
    jQuery(pack).on('change', refreshAdd);
    qty.addEventListener('input', refreshAdd);
    qty.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); if (!$id('addBtn').disabled) $id('addBtn').click(); } });

    $id('addBtn').addEventListener('click', () => {
        const { p, opt, n, base } = refreshAdd();
        if (!p || base <= 0) return;
        const label = opt.value === 'v' ? friendly(base, p.unit) + ' en vrac' : nf.format(n) + ' × ' + opt.text.replace(/\s*\(.*\)$/, '');
        lines.push({ pid: p.id, label, base });
        qty.value = ''; jQuery(prod).val('').trigger('change');
        render();
        setTimeout(() => jQuery(prod).select2('open'), 50);
    });

    function render() {
        // Total par produit (plusieurs lignes pour le même produit s'additionnent)
        const byPid = new Map();
        lines.forEach((l, i) => { const g = byPid.get(l.pid) || { pid: l.pid, parts: [], total: 0 }; g.parts.push({ i, label: l.label }); g.total += l.base; byPid.set(l.pid, g); });
        $id('lines').innerHTML = [...byPid.values()].map(g => {
            const p = CATALOG.get(String(g.pid)), cur = current(g.pid), total = Math.round(g.total * 100) / 100;
            const delta = total - cur;
            return '<div class="py-3 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">' +
                '<div class="flex-1 min-w-0"><p class="font-semibold text-slate-800">' + esc(p.name) + '</p>' +
                '<p class="text-xs text-slate-500">' + g.parts.map(x => esc(x.label) + ' <button type="button" class="text-red-500 hover:text-red-700" data-del="' + x.i + '" title="Retirer">✕</button>').join(' + ') + '</p></div>' +
                '<div class="text-sm sm:text-right"><span class="text-slate-400 text-xs">' + friendly(cur, p.unit) + ' → </span><strong class="text-emerald-700">' + friendly(total, p.unit) + '</strong>' +
                (cur > 0 ? '<p class="text-[11px] ' + (delta < 0 ? 'text-red-600' : 'text-amber-700') + '">remplace le stock actuel (' + (delta >= 0 ? '+' : '−') + friendly(Math.abs(delta), p.unit) + ')</p>' : '') + '</div></div>';
        }).join('');
        $id('hidden').innerHTML = [...byPid.values()].map(g => '<input type="hidden" name="quantities[' + g.pid + ']" value="' + (Math.round(g.total * 100) / 100) + '">').join('');
        $id('empty').classList.toggle('hidden', byPid.size > 0);
        $id('saveBtn').disabled = byPid.size === 0;
        $id('count').textContent = byPid.size ? '(' + byPid.size + ' produit' + (byPid.size > 1 ? 's' : '') + ')' : '';
    }
    $id('lines').addEventListener('click', e => {
        const b = e.target.closest('[data-del]');
        if (b) { lines.splice(+b.dataset.del, 1); render(); }
    });
    $id('saveForm').addEventListener('submit', e => {
        if (!lines.length) { e.preventDefault(); return; }
        $id('saveBtn').disabled = true;
    });
    render();
})();
</script>
@endpush
