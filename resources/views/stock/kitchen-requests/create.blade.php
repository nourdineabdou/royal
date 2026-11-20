@extends('layouts.stock')

@section('title', 'Nouvelle sortie vers une cuisine')
@section('header', 'Nouvelle sortie vers une cuisine')

@section('content')
{{-- La zone de scan contient son propre petit formulaire : elle reste hors du formulaire principal --}}
<div class="py-4 max-w-4xl mx-auto space-y-4 pb-32">
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <p class="text-sm text-slate-600 bg-white rounded-xl border border-slate-100 px-4 py-3">
        <i class="fa-solid fa-circle-info text-emerald-600 mr-1"></i>
        Choisissez le cuisinier qui reçoit, puis <strong>scannez</strong> les produits ou ajoutez-les ligne par ligne.
        La sortie part <strong>« à confirmer »</strong> chez ce cuisinier : <strong>le stock ne diminue que lorsqu'il valide.</strong>
    </p>

    <form method="POST" action="{{ route('stock.kitchen-requests.store') }}" id="srForm" class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 grid sm:grid-cols-2 gap-3">
        @csrf
        <div id="hidden" class="hidden"></div>
        <div>
            <label for="receiver_id" class="block text-xs font-semibold text-slate-500 mb-1">Cuisinier qui reçoit <span class="text-red-500">*</span></label>
            <select id="receiver_id" name="receiver_id" required style="width:100%">
                <option value="">— Choisir le cuisinier —</option>
                @foreach($cooks as $cook)<option value="{{ $cook->id }}" @selected(old('receiver_id') == $cook->id)>{{ $cook->name }}</option>@endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">C'est lui qui se connecte pour confirmer, et qui signe le bon de sortie.</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Magasin d'où sortent les produits <span class="text-red-500">*</span></label>
            <select name="stock_id" id="stockSelect" style="width:100%">
                @foreach($stocks as $s)<option value="{{ $s->id }}" @selected(old('stock_id', $defaultStock) == $s->id)>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="needed_on" class="block text-xs font-semibold text-slate-500 mb-1">Pour le</label>
            <input id="needed_on" type="date" name="needed_on" value="{{ old('needed_on', now()->toDateString()) }}"
                   class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
        </div>
        <div>
            <label for="notes" class="block text-xs font-semibold text-slate-500 mb-1">Remarque</label>
            <input id="notes" name="notes" maxlength="500" value="{{ old('notes') }}" placeholder="ex : déjeuner du personnel"
                   class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
        </div>
    </form>

    {{-- Scan : caméra, douchette ou code tapé --}}
    @include('partials.scan-input')

    {{-- Lignes, comme dans la demande d'achat : produit, emballage, quantité en emballages --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-slate-700 text-sm">Produits remis</h3>
            <button type="button" id="addLineBtn" class="flex items-center gap-2 text-sm text-emerald-700 hover:text-emerald-800 font-semibold">
                <i class="fa-solid fa-plus-circle"></i> Ajouter une ligne
            </button>
        </div>
        <div class="hidden sm:grid grid-cols-12 gap-2 text-[11px] font-semibold uppercase text-slate-400 border-b pb-2">
            <span class="col-span-5">Produit</span><span class="col-span-4">Emballage</span><span class="col-span-2 text-right">Quantité</span><span></span>
        </div>
        <div id="lines" class="divide-y divide-slate-100"></div>
        <p id="empty" class="text-center text-sm text-slate-400 py-6">Scannez un produit ou cliquez sur « Ajouter une ligne ».</p>
    </div>

    <div class="fixed bottom-0 left-0 right-0 md:left-64 z-30 bg-white border-t border-slate-200 p-3 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]">
        <div class="max-w-4xl mx-auto flex items-center gap-3">
            <p id="summary" class="flex-1 text-xs text-slate-500">0 produit</p>
            <button id="submitBtn" type="submit" form="srForm" disabled class="px-5 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white text-sm font-bold">
                <i class="fa-solid fa-hand-holding mr-1"></i> Remettre à la cuisine
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
    const AVAIL = @json($available, JSON_FORCE_OBJECT);
    const kit = ScanKit({
        catalog: @json($catalog),
        canLink: @json($canLink),
        csrf: @json(csrf_token()),
        routes: { lookup: @json(route('stock.scan.lookup')), link: @json(route('stock.scan.link')) },
        onHit(pid, ppid, n) { addFromScan(pid, ppid, n); },
    });
    const { products, nf, esc, friendly, displayUnit } = kit;
    const linesEl = document.getElementById('lines');
    const stockId = () => document.getElementById('stockSelect').value;
    const avail = pid => ((AVAIL[stockId()] || {})[pid]) || 0;
    const sorted = [...products.values()].sort((a, b) => a.name.localeCompare(b.name, 'fr'));
    const productOptions = sorted.map(p => '<option value="' + p.id + '">' + esc(p.name) + '</option>').join('');

    // Emballages d'un produit (le plus grand d'abord) + « en vrac » en kg / L / pièces
    function packOptions(p, selected) {
        const d = displayUnit(p.unit);
        const opts = p.packagings.map(pp =>
            '<option value="' + pp.id + '" data-f="' + pp.qty + '"' + (String(selected) === String(pp.id) ? ' selected' : '') + '>' +
            esc(pp.name) + ' (' + friendly(pp.qty, p.unit) + ')</option>');
        opts.push('<option value="v" data-f="' + d.factor + '"' + (selected === 'v' || (!selected && !p.packagings.length) ? ' selected' : '') + '>En vrac (' + esc(d.label || 'unité') + ')</option>');
        return opts.join('');
    }

    function addLine(pid, pack, qty) {
        const row = document.createElement('div');
        row.className = 'line grid grid-cols-12 gap-2 items-start py-3';
        row.innerHTML =
            '<div class="col-span-12 sm:col-span-5"><select class="prod" style="width:100%"><option value="">— Produit —</option>' + productOptions + '</select>' +
                '<p class="hint text-[11px] text-slate-400 mt-1"></p></div>' +
            '<div class="col-span-7 sm:col-span-4"><select class="pack" style="width:100%" disabled><option value="">— emballage —</option></select></div>' +
            '<div class="col-span-4 sm:col-span-2"><input type="number" inputmode="decimal" min="0" step="any" placeholder="0" class="qty sk-input w-full text-right font-bold border border-slate-300 rounded-lg px-2 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400">' +
                '<p class="eq text-[11px] text-emerald-700 mt-1 text-right"></p></div>' +
            '<div class="col-span-1 flex justify-end"><button type="button" class="del w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500" title="Retirer"><i class="fa-solid fa-xmark text-xs"></i></button></div>';
        linesEl.prepend(row);
        if (pid) setProduct(row, pid, pack);
        if (qty) row.querySelector('.qty').value = qty;
        refresh();
        return row;
    }

    function setProduct(row, pid, pack) {
        const p = products.get(+pid);
        const prod = row.querySelector('.prod'), packSel = row.querySelector('.pack');
        prod.value = p ? p.id : '';
        if (window.jQuery && jQuery(prod).hasClass('select2-hidden-accessible')) jQuery(prod).trigger('change.select2');
        if (!p) { packSel.innerHTML = '<option value="">— emballage —</option>'; packSel.disabled = true; }
        else { packSel.innerHTML = packOptions(p, pack); packSel.disabled = false; }
        if (window.jQuery && jQuery(packSel).hasClass('select2-hidden-accessible')) jQuery(packSel).trigger('change.select2');
    }

    const rows = () => [...linesEl.querySelectorAll('.line')];
    const lineOf = row => {
        const p = products.get(+row.querySelector('.prod').value);
        const opt = row.querySelector('.pack').selectedOptions[0];
        const n = parseFloat(String(row.querySelector('.qty').value).replace(',', '.')) || 0;
        const f = opt ? parseFloat(opt.dataset.f) || 1 : 1;
        return { p, pack: opt ? opt.value : '', n, base: Math.round(n * f * 100) / 100 };
    };

    // Scan : même produit et même emballage → on ajoute à la ligne existante
    function addFromScan(pid, ppid, n) {
        const pack = ppid || 'v';
        const p = products.get(pid);
        let row = rows().find(r => +r.querySelector('.prod').value === pid && r.querySelector('.pack').value === String(pack));
        if (row) {
            const q = row.querySelector('.qty');
            q.value = Math.round(((parseFloat(q.value) || 0) + n) * 1000) / 1000;
            refresh();
        } else row = addLine(pid, pack, n);
        row.classList.add('sk-flash'); setTimeout(() => row.classList.remove('sk-flash'), 900);
        kit.beep(true);
        const label = ppid ? (p.packagings.find(x => x.id === ppid) || {}).name : displayUnit(p.unit).label;
        kit.showLast('<i class="fa-solid fa-circle-check text-emerald-600"></i><span><strong>+' + nf.format(n) + ' ' + esc(label || '') + '</strong> · ' + esc(p.name) + '</span>', 'ok');
    }

    function refresh() {
        const totals = {};
        rows().forEach(r => { const l = lineOf(r); if (l.p) totals[l.p.id] = (totals[l.p.id] || 0) + l.base; });
        let ok = 0, bad = false, missing = false;
        rows().forEach(r => {
            const l = lineOf(r);
            const hint = r.querySelector('.hint'), eq = r.querySelector('.eq');
            if (!l.p) { hint.textContent = ''; eq.textContent = ''; missing = true; r.classList.remove('bg-red-50'); return; }
            const short = totals[l.p.id] > avail(l.p.id) + 0.0001;
            hint.innerHTML = 'Disponible : <strong>' + friendly(avail(l.p.id), l.p.unit) + '</strong>' + (short ? ' · <span class="text-red-600 font-semibold">pas assez dans ce magasin</span>' : '');
            eq.textContent = l.base > 0 && l.pack !== 'v' ? '= ' + friendly(l.base, l.p.unit) : '';
            r.classList.toggle('bg-red-50', short);
            bad = bad || short;
            if (l.base > 0) ok++; else missing = true;
        });
        const n = rows().length;
        document.getElementById('empty').classList.toggle('hidden', n > 0);
        document.getElementById('summary').textContent = n + ' ligne' + (n > 1 ? 's' : '') + (bad ? ' · stock insuffisant' : (missing ? ' · produit ou quantité manquant' : ''));
        document.getElementById('submitBtn').disabled = !n || ok < n || bad || missing;
    }

    // Changements (select2 déclenche des événements jQuery : on écoute via jQuery)
    jQuery(linesEl).on('change', '.prod', function () { setProduct(this.closest('.line'), this.value, null); refresh(); });
    jQuery(linesEl).on('change input', '.pack, .qty', refresh);
    linesEl.addEventListener('click', e => { const b = e.target.closest('.del'); if (b) { b.closest('.line').remove(); refresh(); } });
    document.getElementById('addLineBtn').addEventListener('click', () => {
        const row = addLine();
        setTimeout(() => { if (window.jQuery) jQuery(row.querySelector('.prod')).select2('open'); }, 50);
    });

    // Dernier magasin utilisé sur cet appareil
    try {
        const last = localStorage.getItem('royal.storeExit.stock');
        if (last && !@json(old('stock_id')) && $('#stockSelect option[value="' + last + '"]').length) $('#stockSelect').val(last).trigger('change.select2');
    } catch (e) {}
    $('#stockSelect').on('change', function () { try { localStorage.setItem('royal.storeExit.stock', this.value); } catch (e) {} refresh(); });

    document.getElementById('srForm').addEventListener('submit', e => {
        if (!document.getElementById('receiver_id').value) { e.preventDefault(); kit.error('Choisissez le cuisinier qui reçoit'); return; }
        document.getElementById('hidden').innerHTML = rows().map((r, i) => {
            const l = lineOf(r);
            return '<input type="hidden" name="items[' + i + '][product_id]" value="' + l.p.id + '">' +
                   '<input type="hidden" name="items[' + i + '][quantity]" value="' + l.base + '">';
        }).join('');
        document.getElementById('submitBtn').disabled = true;
    });

    refresh();
})();
</script>
@endpush
