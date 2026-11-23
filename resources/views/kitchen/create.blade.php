@extends('layouts.kitchen')
@section('title', 'Nouvelle demande')

@section('content')
<form method="POST" action="{{ route('kitchen.requests.store') }}" id="krForm" class="space-y-4">
    @csrf

    <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
        <h1 class="font-bold text-lg text-slate-800">Nouvelle demande au magasin</h1>
        <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2 sm:col-span-1">
                <label for="kitchen" class="block text-sm font-semibold text-slate-600 mb-1">Cuisine <span class="text-red-500">*</span></label>
                <input id="kitchen" name="kitchen" list="kitchenList" required maxlength="100" value="{{ old('kitchen', $lastKitchen) }}"
                       placeholder="ex : Cuisine TCN" class="k-input w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400">
                <datalist id="kitchenList">@foreach($kitchens as $k)<option value="{{ $k }}">@endforeach</datalist>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label for="needed_on" class="block text-sm font-semibold text-slate-600 mb-1">Pour le</label>
                <input id="needed_on" type="date" name="needed_on" value="{{ old('needed_on', now()->toDateString()) }}"
                       class="k-input w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div class="col-span-2">
                <label for="notes" class="block text-sm font-semibold text-slate-600 mb-1">Remarque</label>
                <input id="notes" name="notes" maxlength="500" value="{{ old('notes') }}" placeholder="ex : pour le déjeuner de lundi"
                       class="k-input w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
        </div>
    </div>

    {{-- Recherche produit --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="search" id="search" autocomplete="off" placeholder="Ajouter un produit : riz, huile, sucre…"
                   class="k-input w-full pl-10 pr-3 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>
        <div id="results" class="hidden max-h-72 overflow-y-auto divide-y divide-slate-100 border border-slate-100 rounded-xl"></div>
    </div>

    {{-- Lignes de la demande --}}
    <div id="lines" class="space-y-2"></div>
    <p id="empty" class="text-center text-sm text-slate-400 bg-white rounded-2xl border border-dashed border-slate-300 p-6">
        Cherchez un produit ci-dessus pour l'ajouter.
    </p>
    <div id="hidden"></div>

    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-3 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
        <div class="max-w-3xl mx-auto flex items-center gap-3">
            <p id="summary" class="flex-1 text-sm text-slate-500">0 produit</p>
            <button id="submitBtn" disabled class="px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 disabled:bg-slate-300 text-white font-bold">
                <i class="fa-solid fa-paper-plane mr-1"></i> Envoyer au magasin
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const CATALOG = @json($catalog);
    const products = new Map(CATALOG.map(p => [p.id, p]));
    const unitOf = sym => ({ g: { l: 'kg', f: 1000 }, ml: { l: 'L', f: 1000 } })[(sym || '').toLowerCase()] || { l: sym || '', f: 1 };
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let lines = []; // { pid, qty (unité d'affichage) }

    const search = document.getElementById('search'), results = document.getElementById('results');
    search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        if (!q) { results.classList.add('hidden'); return; }
        const list = CATALOG.filter(p => (p.name + ' ' + (p.ref || '')).toLowerCase().includes(q)).slice(0, 30);
        results.innerHTML = list.map(p => '<button type="button" class="w-full text-left px-4 py-3 hover:bg-amber-50 flex justify-between gap-2" data-pid="' + p.id + '">' +
            '<span class="text-sm text-slate-800">' + esc(p.name) + '</span><span class="text-xs text-slate-400">' + esc(unitOf(p.unit).l) + '</span></button>').join('')
            || '<p class="px-4 py-3 text-sm text-slate-400">Aucun produit.</p>';
        results.classList.remove('hidden');
    });
    results.addEventListener('click', e => {
        const b = e.target.closest('[data-pid]');
        if (!b) return;
        const pid = +b.dataset.pid;
        if (!lines.some(l => l.pid === pid)) lines.unshift({ pid, qty: '' });
        search.value = ''; results.classList.add('hidden');
        render();
        const input = document.querySelector('[data-qty="' + pid + '"]');
        if (input) input.focus();
    });

    function render() {
        document.getElementById('lines').innerHTML = lines.map(l => {
            const p = products.get(l.pid), u = unitOf(p.unit);
            return '<div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-3">' +
                '<div class="flex-1 min-w-0"><p class="font-semibold text-slate-800 truncate">' + esc(p.name) + '</p>' +
                '<button type="button" class="text-xs text-slate-400 hover:text-red-600" data-del="' + l.pid + '">Retirer</button></div>' +
                '<div class="flex items-center border-2 border-amber-300 rounded-xl overflow-hidden shrink-0 focus-within:ring-2 focus-within:ring-amber-400">' +
                    '<input type="number" inputmode="decimal" min="0" step="any" placeholder="0" value="' + l.qty + '" data-qty="' + l.pid + '" class="k-input w-24 text-right font-bold text-lg py-2 px-2 focus:outline-none">' +
                    '<span class="px-3 text-sm font-semibold text-slate-500 bg-amber-50 self-stretch flex items-center">' + esc(u.l) + '</span>' +
                '</div></div>';
        }).join('');
        document.getElementById('empty').classList.toggle('hidden', lines.length > 0);
        refresh();
    }
    function refresh() {
        const ok = lines.filter(l => parseFloat(l.qty) > 0).length;
        document.getElementById('summary').textContent = lines.length + ' produit' + (lines.length > 1 ? 's' : '') + (ok < lines.length ? ' · quantité manquante' : '');
        document.getElementById('submitBtn').disabled = ok === 0 || ok < lines.length;
    }
    const linesEl = document.getElementById('lines');
    linesEl.addEventListener('input', e => {
        const pid = +e.target.dataset.qty;
        const l = lines.find(x => x.pid === pid);
        if (l) { l.qty = e.target.value; refresh(); }
    });
    linesEl.addEventListener('click', e => {
        const b = e.target.closest('[data-del]');
        if (b) { lines = lines.filter(l => l.pid !== +b.dataset.del); render(); }
    });

    document.getElementById('krForm').addEventListener('submit', () => {
        // Quantités envoyées en unité de base (g, mL, pcs)
        document.getElementById('hidden').innerHTML = lines.map((l, i) => {
            const p = products.get(l.pid), base = Math.round(parseFloat(l.qty) * unitOf(p.unit).f * 100) / 100;
            return '<input type="hidden" name="items[' + i + '][product_id]" value="' + l.pid + '">' +
                   '<input type="hidden" name="items[' + i + '][quantity]" value="' + base + '">';
        }).join('');
        document.getElementById('submitBtn').disabled = true;
    });
    render();
})();
</script>
@endpush
