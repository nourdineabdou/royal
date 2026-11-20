@extends('layouts.purchases')
@section('title', "Nouvelle demande d'achat")

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('purchases.requests') }}" class="text-slate-400 hover:text-slate-600 transition">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="text-xl font-bold text-slate-800">Nouvelle demande d'achat</h2>
</div>

<p class="text-sm text-slate-500 mb-5 max-w-2xl">
    Exprimez ici le besoin. Pour chaque produit, l'<strong>emballage principal</strong> (sac, carton…) est proposé :
    tapez le nombre d'emballages (les moitiés sont possibles : 10,5). Le fournisseur se choisit ensuite, au moment des bons de commande.
</p>

@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
</div>
@endif

<form method="POST" action="{{ route('purchases.requests.store') }}">
@csrf

<div class="bg-white rounded-2xl shadow-sm p-5 mb-5">
    <label class="block text-xs font-semibold text-slate-600 mb-1">Notes (optionnel)</label>
    <input type="text" name="notes" placeholder="Ex: réapprovisionnement suite à rupture de stock…"
           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
</div>

<div class="bg-white rounded-2xl shadow-sm p-5 mb-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-slate-700">Produits nécessaires</h3>
        <button type="button" onclick="addLine()" class="flex items-center gap-2 text-sm text-orange-600 hover:text-orange-700 font-medium">
            <i class="fa-solid fa-plus-circle"></i> Ajouter une ligne
        </button>
    </div>

    <table class="w-full text-sm">
        <thead class="text-xs text-slate-400 uppercase border-b">
            <tr>
                <th class="pb-2 text-left">Produit</th>
                <th class="pb-2 text-left w-56">Emballage</th>
                <th class="pb-2 text-right w-36">Quantité</th>
                <th class="pb-2 text-left">Note</th>
                <th class="pb-2 w-10"></th>
            </tr>
        </thead>
        <tbody id="linesBody"></tbody>
    </table>
    <p id="noLinesMsg" class="text-center text-slate-400 py-6 text-sm">Aucune ligne — cliquez sur « Ajouter une ligne »</p>
</div>

<button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white py-3 px-6 rounded-xl font-semibold text-sm transition shadow">
    <i class="fa-solid fa-paper-plane mr-2"></i> Créer la demande
</button>
<a href="{{ route('purchases.requests') }}" class="text-sm text-slate-400 hover:text-slate-600 ml-4">Annuler</a>
</form>

<script>
const PRODUCTS = @json($catalog);
const REF_NAME = @json($refName);
const byId = new Map(PRODUCTS.map(p => [String(p.id), p]));
let lineCount = 0;

// Unité d'affichage : kg pour g, L pour mL
const disp = sym => ({ g: { l: 'kg', f: 1000 }, ml: { l: 'L', f: 1000 } })[(sym || '').toLowerCase()] || { l: sym || '', f: 1 };
const nice = (q, sym) => { const d = disp(sym); return (q / d.f).toLocaleString('fr-FR', { maximumFractionDigits: 3 }) + ' ' + d.l; };

function addLine() {
    lineCount++;
    document.getElementById('noLinesMsg').style.display = 'none';
    const i = lineCount - 1;
    const options = PRODUCTS.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
    const row = document.createElement('tr');
    row.className = 'border-b border-slate-50 line-row align-top';
    row.innerHTML = `
        <td class="py-2 pr-2">
            <select name="items[${i}][product_id]" required onchange="productChanged(this)" class="product-select w-full border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <option value="">— Produit —</option>${options}
            </select>
            <p class="stock-hint text-[11px] text-slate-400 mt-1"></p>
        </td>
        <td class="py-2 px-2">
            <select class="pack-select w-full border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none" onchange="recalcQty(this)" disabled>
                <option value="">— choisir un produit —</option>
            </select>
        </td>
        <td class="py-2 px-2 text-right">
            <input type="number" step="any" min="0.01" required placeholder="0" oninput="recalcQty(this)"
                   class="pack-qty w-full text-right border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <input type="hidden" name="items[${i}][quantity]" class="base-qty">
            <p class="qty-hint text-[11px] text-emerald-700 mt-1"></p>
        </td>
        <td class="py-2 px-2">
            <input type="text" name="items[${i}][notes]" placeholder="optionnel"
                   class="w-full border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none">
        </td>
        <td class="py-2 pl-2">
            <button type="button" onclick="removeLine(this)" class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </td>
    `;
    document.getElementById('linesBody').appendChild(row);
}

// Produit choisi : emballages du produit (le principal sélectionné), plus « vrac » en kg / L / pièces
function productChanged(sel) {
    const row = sel.closest('tr'), p = byId.get(sel.value);
    const packSel = row.querySelector('.pack-select');
    if (!p) { packSel.innerHTML = '<option value="">— choisir un produit —</option>'; packSel.disabled = true; row.querySelector('.stock-hint').textContent = ''; recalcQty(sel); return; }
    const d = disp(p.unit);
    packSel.innerHTML = p.packs.map((pk, k) => `<option value="${pk.qty}"${k === 0 ? ' selected' : ''}>${pk.name} (${nice(pk.qty, p.unit)})</option>`).join('')
        + `<option value="${d.f}"${p.packs.length ? '' : ' selected'}>En vrac (${d.l})</option>`;
    packSel.disabled = false;
    if (window.jQuery && jQuery(packSel).hasClass('select2-hidden-accessible')) jQuery(packSel).trigger('change.select2');
    row.querySelector('.stock-hint').textContent = `${REF_NAME} : ${nice(p.stock, p.unit)} · seuil ${nice(p.min, p.unit)}`;
    row.querySelector('.stock-hint').className = 'stock-hint text-[11px] mt-1 ' + (p.stock <= p.min ? 'text-amber-600 font-semibold' : 'text-slate-400');
    recalcQty(sel);
}

// Quantité saisie en emballages → quantité enregistrée en unité de base
function recalcQty(el) {
    const row = el.closest('tr'), p = byId.get(row.querySelector('.product-select').value);
    const n = parseFloat(String(row.querySelector('.pack-qty').value).replace(',', '.')) || 0;
    const factor = parseFloat(row.querySelector('.pack-select').value) || 1;
    const base = Math.round(n * factor * 100) / 100;
    row.querySelector('.base-qty').value = base > 0 ? base : '';
    row.querySelector('.qty-hint').textContent = p && base > 0 ? '= ' + nice(base, p.unit) : '';
}

/** Ligne pré-remplie (depuis Ruptures) : quantité de base convertie dans l'emballage principal. */
function fillLine(i, pid, baseQty) {
    const row = document.querySelectorAll('.line-row')[i];
    const sel = row.querySelector('.product-select');
    sel.value = pid;
    productChanged(sel);
    const factor = parseFloat(row.querySelector('.pack-select').value) || 1;
    row.querySelector('.pack-qty').value = Math.round(baseQty / factor * 100) / 100;
    recalcQty(sel);
}

function removeLine(btn) {
    btn.closest('tr').remove();
    if (!document.querySelectorAll('.line-row').length) {
        document.getElementById('noLinesMsg').style.display = '';
    }
}

const PREFILL = @json($prefill ?? []);
if (Object.keys(PREFILL).length) {
    // Lignes proposées depuis la page Ruptures (quantités en unité de base, modifiables)
    Object.entries(PREFILL).forEach(([pid, qty]) => {
        addLine();
        fillLine(document.querySelectorAll('.line-row').length - 1, pid, parseFloat(qty));
    });
    const notes = document.querySelector('[name="notes"]');
    if (notes && !notes.value) notes.value = 'Réapprovisionnement : produits sous le seuil minimum';
} else {
    addLine();
}
</script>
@endsection
