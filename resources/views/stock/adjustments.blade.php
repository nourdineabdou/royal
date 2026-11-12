@extends('layouts.stock')

@section('title', 'Ajustement du stock')
@section('header', 'Ajustement / Stock initial')

@push('head')
<style>
    /* Évite le zoom automatique iOS sur les champs (police >= 16px) */
    .qty-input { font-size: 16px; }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .qty-input[type=number] { -moz-appearance: textfield; }
    .row-changed { border-color: #f59e0b !important; background: #fffbeb; }
    .filter-btn.active { background: #059669; color: #fff; border-color: #059669; }
</style>
@endpush

@section('content')
<div class="py-4 space-y-4 pb-40">

    {{-- ── Choix du stock + explication ──────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 space-y-3">
        <form method="GET" action="{{ route('stock.adjustments') }}" id="stockPicker"
              class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Stock à ajuster</label>
                <select name="stock_id" id="stockSelect" class="w-full" style="width:100%">
                    @foreach($stocks as $s)
                        <option value="{{ $s->id }}" @selected($stock && $stock->id === $s->id)>
                            {{ $s->name }}{{ $s->module ? ' — ' . $s->module_label : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="text-xs text-slate-600 bg-slate-50 rounded-lg p-3 leading-relaxed">
            <i class="fa-solid fa-circle-info text-emerald-600 mr-1"></i>
            Saisissez la <strong>quantité réelle</strong> (comptée) de chaque produit. L'écart avec le stock actuel est
            enregistré automatiquement comme mouvement.
            Les produits marqués <span class="inline-block bg-sky-100 text-sky-700 px-1.5 rounded font-semibold">Nouveau</span>
            n'ont encore aucun mouvement dans ce stock : leur saisie est enregistrée comme <strong>stock initial</strong>.
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            @foreach($errors->all() as $err)
                <p><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $err }}</p>
            @endforeach
        </div>
    @endif

    @if(!$stock)
        <div class="bg-white rounded-xl border border-slate-100 p-8 text-center text-slate-500 text-sm">
            Aucun stock n'existe encore.
        </div>
    @else

    {{-- ── Barre de recherche + filtres (collante) ──────────────────────── --}}
    <div class="sticky top-0 z-20 bg-slate-100 pt-1 pb-2 space-y-2">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="search" id="search" placeholder="Rechercher un produit ou une référence…"
                   class="w-full pl-10 pr-3 py-3 rounded-xl border border-slate-200 bg-white text-base focus:outline-none focus:ring-2 focus:ring-emerald-400">
        </div>
        <div class="flex gap-2 overflow-x-auto text-xs font-semibold whitespace-nowrap">
            <button type="button" data-filter="all" class="filter-btn active px-3 py-2 rounded-full border border-slate-300 bg-white text-slate-600">
                Tous (<span>{{ $products->count() }}</span>)
            </button>
            <button type="button" data-filter="stocked" class="filter-btn px-3 py-2 rounded-full border border-slate-300 bg-white text-slate-600">
                En stock ({{ $products->where('current_qty', '>', 0)->count() }})
            </button>
            <button type="button" data-filter="new" class="filter-btn px-3 py-2 rounded-full border border-slate-300 bg-white text-slate-600">
                Nouveaux ({{ $products->where('is_new', true)->count() }})
            </button>
            <button type="button" data-filter="changed" class="filter-btn px-3 py-2 rounded-full border border-slate-300 bg-white text-slate-600">
                Modifiés (<span class="changed-count">0</span>)
            </button>
        </div>
    </div>

    {{-- ── Formulaire de saisie ─────────────────────────────────────────── --}}
    <form method="POST" action="{{ route('stock.adjustments.store') }}" id="adjustForm">
        @csrf
        <input type="hidden" name="stock_id" value="{{ $stock->id }}">

        {{-- En-tête colonnes (tablette / desktop) --}}
        <div class="hidden md:grid grid-cols-12 gap-3 px-4 py-2 text-xs font-semibold text-slate-500 uppercase">
            <div class="col-span-5">Produit</div>
            <div class="col-span-2 text-right">Stock actuel</div>
            <div class="col-span-3 text-center">Nouvelle quantité</div>
            <div class="col-span-2 text-right">Écart</div>
        </div>

        <div id="productList" class="space-y-2">
            @foreach($products as $p)
                @php $unit = $p->unit->symbol ?? ''; @endphp
                <div class="product-row bg-white rounded-xl border border-slate-200 p-3 md:px-4 md:grid md:grid-cols-12 md:gap-3 md:items-center"
                     data-name="{{ \Illuminate\Support\Str::lower($p->name . ' ' . $p->reference) }}"
                     data-new="{{ $p->is_new ? 1 : 0 }}"
                     data-stocked="{{ $p->current_qty > 0 ? 1 : 0 }}">

                    {{-- Produit --}}
                    <div class="md:col-span-5 flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 text-sm leading-tight break-words">{{ $p->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $p->reference ?: '—' }}{{ $unit ? ' · ' . $unit : '' }}
                            </p>
                        </div>
                        @if($p->is_new)
                            <span class="shrink-0 text-[10px] bg-sky-100 text-sky-700 px-2 py-0.5 rounded-full font-semibold">Nouveau</span>
                        @endif
                    </div>

                    {{-- Stock actuel (mobile : en ligne avec l'écart) --}}
                    <div class="md:col-span-2 md:text-right flex md:block justify-between text-sm mt-2 md:mt-0">
                        <span class="md:hidden text-xs text-slate-500">Stock actuel</span>
                        <span class="font-semibold text-slate-700">{{ rtrim(rtrim(number_format($p->current_qty, 2, ',', ' '), '0'), ',') }} <span class="text-xs text-slate-400">{{ $unit }}</span></span>
                    </div>

                    {{-- Saisie --}}
                    <div class="md:col-span-3 mt-2 md:mt-0">
                        <div class="flex items-stretch rounded-lg border border-slate-300 overflow-hidden bg-white focus-within:ring-2 focus-within:ring-emerald-400">
                            <button type="button" class="step-btn w-12 md:w-10 shrink-0 bg-slate-50 hover:bg-slate-100 active:bg-slate-200 text-slate-600 text-lg" data-step="-1" aria-label="Diminuer">−</button>
                            <input type="number" inputmode="decimal" step="any" min="0"
                                   name="quantities[{{ $p->id }}]"
                                   value="{{ $p->current_qty + 0 }}"
                                   data-original="{{ $p->current_qty + 0 }}"
                                   class="qty-input w-full min-w-0 text-center font-bold text-slate-800 py-2.5 focus:outline-none">
                            <button type="button" class="step-btn w-12 md:w-10 shrink-0 bg-slate-50 hover:bg-slate-100 active:bg-slate-200 text-slate-600 text-lg" data-step="1" aria-label="Augmenter">+</button>
                        </div>
                    </div>

                    {{-- Écart --}}
                    <div class="md:col-span-2 md:text-right flex md:block justify-between items-center text-sm mt-2 md:mt-0">
                        <span class="md:hidden text-xs text-slate-500">Écart</span>
                        <span class="diff font-bold text-slate-300">0</span>
                    </div>
                </div>
            @endforeach
        </div>

        <p id="emptyMsg" class="hidden text-center text-sm text-slate-500 py-8">Aucun produit ne correspond.</p>

        {{-- ── Barre d'action fixe en bas ───────────────────────────────── --}}
        <div class="fixed bottom-0 left-0 right-0 md:left-64 z-30 bg-white border-t border-slate-200 shadow-[0_-4px_12px_rgba(0,0,0,0.06)] p-3">
            <div class="max-w-5xl mx-auto flex flex-col sm:flex-row gap-2 sm:items-center">
                <input type="text" name="notes" maxlength="500" value="{{ old('notes') }}"
                       placeholder="Motif (optionnel) : inventaire mensuel, casse…"
                       class="flex-1 px-3 py-2.5 rounded-lg border border-slate-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <div class="flex gap-2">
                    <button type="button" id="resetBtn"
                            class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                        Annuler
                    </button>
                    <button type="submit" id="saveBtn" disabled
                            class="flex-1 sm:flex-none px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-sm font-semibold flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Enregistrer (<span class="changed-count">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- ── Derniers ajustements ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> Derniers ajustements
            </h2>
            <a href="{{ route('stock.movements', ['stock' => $stock->id, 'origin_module' => 'adjustment']) }}"
               class="text-xs text-emerald-700 font-semibold hover:underline">Tout voir</a>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($recent as $m)
                <li class="px-4 py-2.5 flex items-center justify-between gap-3 text-sm">
                    <div class="min-w-0">
                        <p class="font-medium text-slate-700 truncate">{{ $m->product->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400 truncate">
                            {{ $m->created_at->format('d/m/Y H:i') }} · {{ $m->user->name ?? '—' }} · {{ $m->origin_module_label }}
                            @if($m->notes) · {{ $m->notes }} @endif
                        </p>
                    </div>
                    <span class="shrink-0 font-bold {{ $m->type === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $m->type === 'in' ? '+' : '−' }}{{ rtrim(rtrim(number_format($m->quantity, 2, ',', ' '), '0'), ',') }}
                        <span class="text-xs font-normal text-slate-400">{{ $m->product->unit->symbol ?? '' }}</span>
                    </span>
                </li>
            @empty
                <li class="px-4 py-6 text-center text-xs text-slate-400">Aucun ajustement pour ce stock.</li>
            @endforelse
        </ul>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Changement de stock → recharge la page (select2 déclenche un événement jQuery)
    let leaving = false;
    $('#stockSelect').on('change', function () {
        if (window.stockAdjustHasChanges && window.stockAdjustHasChanges()
            && !confirm('Des modifications non enregistrées seront perdues. Continuer ?')) return;
        leaving = true;
        document.getElementById('stockPicker').submit();
    });

    const form = document.getElementById('adjustForm');
    if (!form) return;

    const rows     = Array.from(document.querySelectorAll('.product-row'));
    const search   = document.getElementById('search');
    const saveBtn  = document.getElementById('saveBtn');
    const emptyMsg = document.getElementById('emptyMsg');
    let filter = 'all';

    const fmt = n => (Math.round(n * 100) / 100).toLocaleString('fr-FR', { maximumFractionDigits: 2 });

    function rowState(row) {
        const input = row.querySelector('.qty-input');
        const orig  = parseFloat(input.dataset.original) || 0;
        const raw   = input.value.trim();
        const val   = raw === '' ? orig : parseFloat(raw);
        const diff  = Math.round(((isNaN(val) ? orig : val) - orig) * 100) / 100;
        return { input, diff, invalid: raw !== '' && (isNaN(val) || val < 0) };
    }

    function hasChanges() {
        return rows.some(r => rowState(r).diff !== 0);
    }
    window.stockAdjustHasChanges = hasChanges;

    function refreshRow(row) {
        const { diff, invalid } = rowState(row);
        const el = row.querySelector('.diff');
        row.classList.toggle('row-changed', diff !== 0);
        row.dataset.changed = diff !== 0 ? 1 : 0;
        el.textContent = invalid ? 'Invalide' : (diff > 0 ? '+' : diff < 0 ? '−' : '') + fmt(Math.abs(diff));
        el.className = 'diff font-bold ' + (invalid ? 'text-red-600' : diff > 0 ? 'text-emerald-600' : diff < 0 ? 'text-red-600' : 'text-slate-300');
    }

    function refreshCount() {
        const n = rows.filter(r => r.dataset.changed === '1').length;
        document.querySelectorAll('.changed-count').forEach(e => e.textContent = n);
        saveBtn.disabled = n === 0;
    }

    function applyFilter() {
        const q = search.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach(r => {
            const okSearch = !q || r.dataset.name.includes(q);
            const okFilter = filter === 'all'
                || (filter === 'new' && r.dataset.new === '1')
                || (filter === 'stocked' && r.dataset.stocked === '1')
                || (filter === 'changed' && r.dataset.changed === '1');
            const show = okSearch && okFilter;
            r.classList.toggle('hidden', !show);
            if (show) visible++;
        });
        emptyMsg.classList.toggle('hidden', visible > 0);
    }

    rows.forEach(row => {
        const input = row.querySelector('.qty-input');
        input.addEventListener('input', () => { refreshRow(row); refreshCount(); });
        input.addEventListener('focus', () => input.select());
        row.querySelectorAll('.step-btn').forEach(btn => btn.addEventListener('click', () => {
            const cur = parseFloat(input.value) || 0;
            input.value = Math.max(0, Math.round((cur + parseFloat(btn.dataset.step)) * 100) / 100);
            refreshRow(row); refreshCount();
        }));
    });

    search.addEventListener('input', applyFilter);
    document.querySelectorAll('.filter-btn').forEach(btn => btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filter = btn.dataset.filter;
        applyFilter();
    }));

    document.getElementById('resetBtn').addEventListener('click', () => {
        if (!hasChanges() || !confirm('Annuler toutes les modifications ?')) return;
        rows.forEach(r => { const i = r.querySelector('.qty-input'); i.value = i.dataset.original; refreshRow(r); });
        refreshCount(); applyFilter();
    });

    form.addEventListener('submit', e => {
        const changed = rows.filter(r => r.dataset.changed === '1');
        if (rows.some(r => rowState(r).invalid)) {
            e.preventDefault(); alert('Certaines quantités sont invalides (négatives ou non numériques).'); return;
        }
        if (!changed.length || !confirm(changed.length + ' produit(s) seront ajustés. Confirmer ?')) {
            e.preventDefault(); return;
        }
        // N'envoyer que les lignes modifiées
        rows.forEach(r => { if (r.dataset.changed !== '1') r.querySelector('.qty-input').disabled = true; });
        saveBtn.disabled = true;
    });

    window.addEventListener('beforeunload', e => {
        if (!leaving && !saveBtn.disabled && hasChanges()) { e.preventDefault(); e.returnValue = ''; }
    });
})();
</script>
@endpush
