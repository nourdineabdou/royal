@extends('layouts.purchases')
@section('title', "Demande d'achat automatique")

@section('content')
@php use App\Support\Qty; @endphp
<div class="max-w-4xl mx-auto space-y-5 pb-28">

    <div>
        <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2"><i class="fa-solid fa-wand-magic-sparkles text-orange-500"></i> Demande d'achat automatique</h1>
        <p class="text-sm text-slate-500 mt-1">
            Le système prépare cette demande tout seul avec <strong>tous les produits du {{ $ref->name ?? 'stock' }} arrivés à leur seuil minimum</strong>, sauf ceux déjà en commande.
            Vous pouvez aussi <strong>ajouter d'autres produits</strong> en bas de la page. Relisez les quantités, puis envoyez-la :
            @if(auth()->user()->can('purchases.requests.validate'))
                envoyée par vous (responsable des achats), elle est <strong>validée d'office</strong> : le bon de commande peut être fait aussitôt.
            @elseif(auth()->user()->can('purchases.requests.approve'))
                envoyée par vous (acheteur), elle part directement à la <strong>validation du responsable des achats</strong>.
            @else
                elle sera approuvée par l'acheteur, puis validée par le responsable des achats.
            @endif
        </p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    @php $stockOf = collect($catalog)->keyBy('id'); @endphp
    <form method="POST" action="{{ route('purchases.requests.auto.save') }}" id="autoForm" class="space-y-3">
        @csrf
    @if(!$draft)
        @if($covered->isNotEmpty())
            {{-- Des produits sont sous le seuil, mais déjà couverts par une DA ou un BC en cours : on dit lesquels --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                <p class="font-semibold text-amber-900"><i class="fa-solid fa-circle-info mr-1"></i>
                    {{ $covered->count() }} produit{{ $covered->count() > 1 ? 's sont' : ' est' }} sous le seuil, mais déjà dans une demande ou une commande en cours :
                    rien de nouveau à préparer.</p>
                <ul class="mt-3 space-y-2">
                    @foreach($coveringDocs as $doc)
                        <li class="flex flex-wrap items-center gap-2 text-sm">
                            <a href="{{ $doc['kind'] === 'da' ? route('purchases.requests.show', $doc['id']) : route('purchases.orders.show', $doc['id']) }}"
                               class="font-semibold text-orange-700 hover:underline">{{ $doc['reference'] }}</a>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-white border border-amber-200 text-amber-800">{{ $doc['state'] }}</span>
                            <span class="text-amber-800">· {{ $doc['count'] }} produit{{ $doc['count'] > 1 ? 's' : '' }}</span>
                            @if($doc['kind'] === 'da' && $doc['state'] === \App\Models\PurchaseRequest::STATUSES['approved'])
                                <span class="text-xs text-amber-700">→ il reste à faire le <strong>bon de commande</strong> depuis cette demande.</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <p class="text-xs text-amber-700 mt-3">Pour commander autre chose en plus, ajoutez un produit ci-dessous : une nouvelle demande est créée en brouillon.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center">
                <i class="fa-solid fa-circle-check text-emerald-500 text-4xl mb-3 block"></i>
                <p class="font-semibold text-slate-700">Aucun produit sous le seuil pour le moment.</p>
                <p class="text-sm text-slate-500 mt-1">Pour commander quand même, ajoutez un produit ci-dessous : la demande est créée en brouillon.</p>
            </div>
        @endif
    @else
            <div class="flex items-center justify-between gap-2 text-sm">
                <span class="font-semibold text-slate-700">{{ $draft->reference }} · <span class="text-slate-500">brouillon · {{ $draft->items->count() }} produit(s)</span></span>
                <span class="text-xs text-slate-400">Mis à jour le {{ $draft->updated_at->format('d/m/Y à H:i') }}</span>
            </div>

            @foreach($draft->items as $item)
                @php
                    $p = $item->product; $u = $p->unit->symbol ?? ''; $du = Qty::displayUnit($u);
                    $a = $info[$p->id] ?? null;
                    $pack = $p->productPackagings->filter(fn ($pp) => (float) $pp->quantity > 0)->sortByDesc(fn ($pp) => (float) $pp->quantity)->first();
                    // Saisie dans l'emballage principal (sacs, cartons…) ; sinon en kg / L / pièces
                    $mult  = $pack ? (float) $pack->quantity : (float) $du['factor'];
                    $label = $pack ? ($pack->packaging->name ?? 'emballage') : $du['label'];
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col sm:flex-row sm:items-center gap-3 auto-row"
                     data-mult="{{ $mult }}" data-unit="{{ $u }}" data-by-pack="{{ $pack ? 1 : 0 }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold text-slate-800">{{ $p->name }}</span>
                            @if($a && $a['level'] === 'rupture')
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">Rupture</span>
                            @elseif($a && $a['needs_order'])
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Sous le seuil</span>
                            @endif
                            @if($item->added_manually)
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-sky-100 text-sky-700" title="{{ $item->notes }}">Ajouté à la main</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Stock : <strong>{{ Qty::format($a['total'] ?? ($stockOf[$p->id]['stock'] ?? 0), $u) }}</strong>
                            · Seuil : {{ Qty::format($a['min'] ?? $p->effectiveMinStock(), $u) }}@if($a['min_default'] ?? $p->usesDefaultMinStock()) <span class="text-slate-400">(par défaut)</span>@endif
                            @if(($a['incoming'] ?? 0) > 0) · En commande : {{ Qty::format($a['incoming'], $u) }} @endif
                        </p>
                        <label class="inline-flex items-center gap-1 text-xs text-slate-400 mt-1 cursor-pointer">
                            <input type="checkbox" name="remove[{{ $item->id }}]" value="1" class="accent-red-600 remove-chk"> Retirer de la demande
                        </label>
                    </div>
                    <div class="shrink-0 sm:text-right">
                        <div class="flex items-stretch border-2 border-orange-300 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-orange-400">
                            <button type="button" class="step w-9 bg-orange-50 text-lg text-slate-600" data-d="-0.5" aria-label="Moins un demi">−</button>
                            <input type="number" inputmode="decimal" min="0" step="any" class="disp w-20 text-center font-bold py-2 px-1 focus:outline-none" style="font-size:18px"
                                   value="{{ round($item->quantity / $mult, 3) }}">
                            <button type="button" class="step w-9 bg-orange-50 text-lg text-slate-600" data-d="0.5" aria-label="Plus un demi">+</button>
                            <span class="px-3 text-xs font-semibold text-slate-600 bg-orange-50 flex items-center max-w-[11rem] leading-tight">{{ $label }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 pack-hint"></p>
                        <input type="hidden" name="quantities[{{ $item->id }}]" class="base" value="{{ (float) $item->quantity }}">
                    </div>
                </div>
            @endforeach
    @endif

            {{-- Ajouter un produit à la main (comme dans « Nouvelle demande ») --}}
            <div class="bg-white rounded-2xl border border-orange-200 p-4">
                <p class="text-sm font-semibold text-slate-700 mb-2"><i class="fa-solid fa-plus-circle text-orange-500 mr-1"></i>Ajouter un produit à la demande</p>
                <div class="grid grid-cols-12 gap-2 items-start">
                    <div class="col-span-12 sm:col-span-5">
                        <select name="add_product_id" id="addProduct" style="width:100%">
                            <option value="">— Produit —</option>
                            @foreach($catalog as $c)<option value="{{ $c['id'] }}">{{ $c['name'] }}</option>@endforeach
                        </select>
                        <p id="addHint" class="text-[11px] text-slate-400 mt-1"></p>
                    </div>
                    <div class="col-span-7 sm:col-span-4">
                        <select id="addPack" style="width:100%" disabled><option value="">— emballage —</option></select>
                    </div>
                    <div class="col-span-5 sm:col-span-3">
                        <input type="number" id="addQty" inputmode="decimal" min="0" step="any" placeholder="Quantité"
                               class="w-full text-right font-bold border border-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <p id="addEq" class="text-[11px] text-emerald-700 mt-1 text-right"></p>
                        <input type="hidden" name="add_quantity" id="addBase">
                    </div>
                </div>
                <button name="action" value="add" id="addBtn" disabled
                        class="mt-3 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 disabled:bg-slate-300 text-white text-sm font-semibold">
                    <i class="fa-solid fa-plus mr-1"></i> Ajouter à la demande
                </button>
            </div>

    @if($draft)
            <div>
                <label for="notes" class="block text-xs font-semibold text-slate-500 mb-1">Motif / remarque (lue par l'acheteur et le responsable des achats)</label>
                <textarea id="notes" name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">{{ old('notes', $draft->notes) }}</textarea>
            </div>

            <div class="fixed bottom-0 left-0 right-0 md:left-64 z-30 bg-white border-t border-slate-200 p-3 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]">
                <div class="max-w-4xl mx-auto flex flex-wrap items-center justify-end gap-2">
                    <button name="action" value="save" class="px-4 py-3 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold bg-white">Enregistrer le brouillon</button>
                    <button name="action" value="send" class="px-5 py-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold"
                            onclick="return confirm('Envoyer cette demande d\'achat pour approbation ?')">
                        <i class="fa-solid fa-paper-plane mr-1"></i> Envoyer pour approbation
                    </button>
                </div>
            </div>
    @endif
    </form>

    @if($covered->isNotEmpty())
        <section class="space-y-2">
            <h2 class="text-sm font-semibold text-slate-600"><i class="fa-solid fa-truck text-slate-400 mr-1"></i> Sous le seuil mais déjà demandé ou commandé ({{ $covered->count() }})</h2>
            <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100">
                @foreach($covered as $a)
                    @php $u = $a['product']->unit->symbol ?? ''; @endphp
                    <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-3 text-sm">
                        <span class="text-slate-700">{{ $a['product']->name }}</span>
                        <span class="text-xs text-slate-500 sm:text-right">
                            stock {{ Qty::format($a['total'], $u) }} · attendu {{ Qty::format($a['incoming'], $u) }}
                            @foreach($sources[$a['product']->id] ?? [] as $s)
                                · <a href="{{ $s['kind'] === 'da' ? route('purchases.requests.show', $s['id']) : route('purchases.orders.show', $s['id']) }}"
                                     class="text-orange-700 hover:underline">{{ $s['reference'] }}</a> <span class="text-slate-400">({{ $s['state'] }})</span>
                            @endforeach
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
    const nf = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 });
    // Quantité en unité de base, affichée en kg / L / pièces
    const friendly = (q, u) => {
        const f = ({ g: ['kg', 1000], ml: ['L', 1000] })[(u || '').toLowerCase()];
        return f ? nf.format(q / f[1]) + ' ' + f[0] : nf.format(q) + ' ' + u;
    };
    document.querySelectorAll('.auto-row').forEach(row => {
        const disp = row.querySelector('.disp'), base = row.querySelector('.base'), hint = row.querySelector('.pack-hint');
        const mult = +row.dataset.mult, byPack = row.dataset.byPack === '1';
        const refresh = () => {
            const n = parseFloat(String(disp.value).replace(',', '.')) || 0;
            const b = Math.round(n * mult * 100) / 100;
            base.value = b;
            // On saisit des emballages (sacs, cartons, moitiés possibles) : on montre le total réel
            hint.textContent = byPack ? (b > 0 ? '= ' + friendly(b, row.dataset.unit) : '') : '';
            row.classList.toggle('opacity-50', row.querySelector('.remove-chk').checked);
        };
        disp.addEventListener('input', refresh);
        row.querySelectorAll('.step').forEach(btn => btn.addEventListener('click', () => {
            const n = parseFloat(String(disp.value).replace(',', '.')) || 0;
            disp.value = Math.max(0, Math.round((n + +btn.dataset.d) * 1000) / 1000);
            refresh();
        }));
        row.querySelector('.remove-chk').addEventListener('change', refresh);
        refresh();
    });

    // ── Ajouter un produit : emballage principal proposé, quantité en emballages → unité de base ──
    const CATALOG = new Map(@json($catalog).map(p => [String(p.id), p]));
    const disp = u => ({ g: ['kg', 1000], ml: ['L', 1000] })[(u || '').toLowerCase()] || [u || 'unité', 1];
    const prod = document.getElementById('addProduct'), pack = document.getElementById('addPack');
    const qty = document.getElementById('addQty'), baseIn = document.getElementById('addBase');
    function addRefresh() {
        const p = CATALOG.get(prod.value), opt = pack.selectedOptions[0];
        const n = parseFloat(String(qty.value).replace(',', '.')) || 0;
        const b = p && opt ? Math.round(n * (parseFloat(opt.dataset.f) || 1) * 100) / 100 : 0;
        baseIn.value = b > 0 ? b : '';
        document.getElementById('addEq').textContent = p && b > 0 && opt.value !== 'v' ? '= ' + friendly(b, p.unit) : '';
        document.getElementById('addBtn').disabled = !(p && b > 0);
    }
    jQuery(prod).on('change', function () {
        const p = CATALOG.get(this.value);
        if (!p) { pack.innerHTML = '<option value="">— emballage —</option>'; pack.disabled = true; document.getElementById('addHint').textContent = ''; }
        else {
            const d = disp(p.unit);
            pack.innerHTML = p.packs.map((pk, i) => '<option value="' + i + '" data-f="' + pk.qty + '">' + pk.name + ' (' + friendly(pk.qty, p.unit) + ')</option>').join('')
                + '<option value="v" data-f="' + d[1] + '"' + (p.packs.length ? '' : ' selected') + '>En vrac (' + d[0] + ')</option>';
            pack.disabled = false;
            document.getElementById('addHint').textContent = 'Stock : ' + friendly(p.stock, p.unit) + ' · seuil ' + friendly(p.min, p.unit);
            setTimeout(() => qty.focus(), 0);
        }
        if (jQuery(pack).hasClass('select2-hidden-accessible')) jQuery(pack).trigger('change.select2');
        addRefresh();
    });
    jQuery(pack).on('change', addRefresh);
    qty.addEventListener('input', addRefresh);
    // Entrée dans la quantité : ajoute le produit (au lieu d'envoyer la demande)
    qty.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); if (!document.getElementById('addBtn').disabled) document.getElementById('addBtn').click(); } });
})();
</script>
@endpush
