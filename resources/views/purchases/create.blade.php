@extends('layouts.purchases')
@section('title', 'Nouvelle commande')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('purchases.orders') }}" class="text-slate-400 hover:text-slate-600 transition">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="text-xl font-bold text-slate-800">Nouvelle commande d'achat</h2>
</div>

<form method="POST" action="{{ route('purchases.orders.store') }}" id="orderForm">
@csrf
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- LEFT: Order details --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow-sm p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Fournisseur <span class="text-slate-400 font-normal">(optionnel)</span></label>
                <select name="supplier_id" id="supplierSelect" onchange="refreshAllPriceHints()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    <option value="">— Pas encore connu —</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Vous pourrez le renseigner plus tard, au moment du bon de livraison.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                <input type="text" name="notes" placeholder="Notes optionnelles…"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-700">Articles à commander</h3>
                <button type="button" onclick="addLine()"
                        class="flex items-center gap-2 text-sm text-orange-600 hover:text-orange-700 font-medium">
                    <i class="fa-solid fa-plus-circle"></i> Ajouter une ligne
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="linesTable">
                    <thead class="text-xs text-slate-400 uppercase border-b border-slate-100">
                        <tr>
                            <th class="pb-2 text-left">Produit</th>
                            <th class="pb-2 text-left w-44">Emballage</th>
                            <th class="pb-2 text-right w-24">Quantité</th>
                            <th class="pb-2 text-right w-28">Prix unitaire</th>
                            <th class="pb-2 text-right w-28">Montant</th>
                            <th class="pb-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody">
                        {{-- dynamically added --}}
                    </tbody>

                </table>
            </div>
            <p id="noLinesMsg" class="text-center text-slate-400 py-6 text-sm">Aucun article ajouté — cliquez sur « Ajouter une ligne »</p>
        </div>

    </div>

    {{-- RIGHT: Summary & submit --}}
    <div class="space-y-5">
        <div class="bg-white rounded-2xl shadow-sm p-5 sticky top-6">
            <h3 class="font-semibold text-slate-700 mb-4">Résumé</h3>
            <div class="space-y-3 text-sm mb-5">
                <div class="flex justify-between text-slate-600">
                    <span>Nb articles</span>
                    <span id="summaryLines" class="font-semibold">0</span>
                </div>
                <div class="flex justify-between text-slate-800 font-bold border-t border-slate-100 pt-3">
                    <span>Montant total</span>
                    <span id="summaryTotal">0 MRU</span>
                </div>

            </div>
            <button type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-semibold text-sm transition shadow">
                <i class="fa-solid fa-paper-plane mr-2"></i> Créer la commande
            </button>
            <a href="{{ route('purchases.orders') }}"
               class="block text-center text-sm text-slate-400 hover:text-slate-600 mt-3 transition">Annuler</a>
        </div>
    </div>

</div>
</form>

{{-- Products data --}}
<script>
@php
    $productsForJs = [];
    foreach ($products as $p) {
        $packagings = [];
        foreach ($p->productPackagings as $pp) {
            $packagings[] = [
                'id' => $pp->packaging_id,
                'name' => $pp->packaging->name ?? '',
                'qty' => (float) $pp->quantity,
            ];
        }

        $productsForJs[] = [
            'id' => $p->id,
            'name' => $p->name,
            'unit' => $p->unit->symbol ?? '',
            'packagings' => $packagings,
        ];
    }
@endphp
const PRODUCTS = @json($productsForJs);
let lineCount = 0;

function addLine() {
    lineCount++;
    document.getElementById('noLinesMsg').style.display = 'none';
    const i = lineCount - 1;
    const options = PRODUCTS.map(p => `<option value="${p.id}">${p.name} (${p.unit})</option>`).join('');
    const row = document.createElement('tr');
    row.className = 'border-b border-slate-50 line-row';
    row.dataset.index = i;
    row.innerHTML = `
        <td class="py-2 pr-2">
            <select name="items[${i}][product_id]" required onchange="onProductChange(this)"
                    class="w-full border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <option value="">— Produit —</option>${options}
            </select>
        </td>
        <td class="py-2 px-2">
            <select name="items[${i}][packaging_id]" onchange="onPackagingChange(this)"
                    class="w-full border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none packaging-select">
                <option value="">— Vrac —</option>
            </select>
            <p class="packaging-hint text-xs text-orange-500 mt-0.5 hidden"></p>
        </td>
        <td class="py-2 px-2">
            <input type="number" name="items[${i}][quantity]" step="0.01" min="0.01" required placeholder="0"
                   oninput="recalc()" class="w-full text-right border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none qty-input">
            <p class="actual-units text-xs text-emerald-600 mt-0.5 hidden"></p>
        </td>
        <td class="py-2 px-2">
            <input type="number" name="items[${i}][unit_price]" step="0.01" min="0" placeholder="0"
                   oninput="recalc()" class="w-full text-right border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none price-input">
            <p class="price-hint text-xs text-indigo-500 mt-0.5 hidden"></p>
        </td>
        <td class="py-2 px-2 text-right text-xs font-semibold text-slate-700 line-total">0</td>
        <td class="py-2 pl-2">
            <button type="button" onclick="removeLine(this)" class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </td>
    `;
    document.getElementById('linesBody').appendChild(row);
    recalc();
}

function onProductChange(sel) {
    const row = sel.closest('tr');
    const productId = parseInt(sel.value);
    const prod = PRODUCTS.find(p => p.id === productId);
    const pkgSel  = row.querySelector('.packaging-select');
    const pkgHint = row.querySelector('.packaging-hint');

    // Reset packaging options
    pkgSel.innerHTML = '<option value="">— Vrac —</option>';
    pkgHint.classList.add('hidden');
    pkgHint.textContent = '';
    row.querySelector('.actual-units').classList.add('hidden');

    if (prod && prod.packagings.length > 0) {
        prod.packagings.forEach(pkg => {
            const opt = document.createElement('option');
            opt.value = pkg.id;
            opt.dataset.qty  = pkg.qty;
            opt.dataset.unit = prod.unit;
            opt.textContent  = `${pkg.name} (${pkg.qty} ${prod.unit}/colis)`;
            pkgSel.appendChild(opt);
        });
    }
    recalc();
    fetchPriceHint(row);
}

// Historique de prix (produit x fournisseur) — pré-remplit le prix si le champ est vide
function fetchPriceHint(row) {
    const productSelect = row.querySelector('select[name$="[product_id]"]');
    const hintEl = row.querySelector('.price-hint');
    const priceInput = row.querySelector('.price-input');
    const productId = productSelect ? productSelect.value : '';
    const supplierId = document.getElementById('supplierSelect')?.value || '';

    if (!productId) { hintEl.classList.add('hidden'); return; }

    const params = new URLSearchParams({ product_id: productId });
    if (supplierId) params.set('supplier_id', supplierId);

    fetch(`{{ route('purchases.price-history') }}?${params}`)
        .then(r => r.json())
        .then(data => {
            if (data.last_price === null || data.last_price === undefined) {
                hintEl.classList.add('hidden');
                return;
            }
            const last = data.history[0];
            hintEl.textContent = `Dernier prix : ${Number(last.price).toLocaleString('fr-FR')} MRU (${last.supplier}, ${last.date})`;
            hintEl.classList.remove('hidden');
            if (!priceInput.value) {
                priceInput.value = data.last_price;
                recalc();
            }
        })
        .catch(() => hintEl.classList.add('hidden'));
}

function refreshAllPriceHints() {
    document.querySelectorAll('.line-row').forEach(row => fetchPriceHint(row));
}

function onPackagingChange(sel) {
    const row     = sel.closest('tr');
    const pkgHint = row.querySelector('.packaging-hint');
    const option  = sel.selectedOptions[0];

    if (sel.value && option.dataset.qty) {
        pkgHint.textContent = `1 colis = ${option.dataset.qty} ${option.dataset.unit}`;
        pkgHint.classList.remove('hidden');
    } else {
        pkgHint.classList.add('hidden');
        pkgHint.textContent = '';
    }
    recalc();
}

function removeLine(btn) {
    btn.closest('tr').remove();
    recalc();
    if (!document.querySelectorAll('.line-row').length) {
        document.getElementById('noLinesMsg').style.display = '';
    }
}

function recalc() {
    let count = 0;
    let grandTotal = 0;
    document.querySelectorAll('.line-row').forEach(row => {
        const qty    = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price  = parseFloat(row.querySelector('.price-input').value) || 0;

        // Show actual units when packaging is selected
        const pkgSel   = row.querySelector('.packaging-select');
        const actualEl = row.querySelector('.actual-units');
        const option   = pkgSel ? pkgSel.selectedOptions[0] : null;
        if (pkgSel && pkgSel.value && option && option.dataset.qty && qty > 0) {
            const pkgQty    = parseFloat(option.dataset.qty) || 1;
            const realUnits = qty * pkgQty;
            actualEl.textContent = `→ ${realUnits.toLocaleString('fr-FR')} ${option.dataset.unit || ''} réels`;
            actualEl.classList.remove('hidden');
        } else if (actualEl) {
            actualEl.classList.add('hidden');
        }

        const lineTotal = qty * price;
        row.querySelector('.line-total').textContent = lineTotal.toLocaleString('fr-FR', {minimumFractionDigits: 0});
        grandTotal += lineTotal;
        count++;
    });
    document.getElementById('summaryLines').textContent = count;
    document.getElementById('summaryTotal').textContent = grandTotal.toLocaleString('fr-FR', {minimumFractionDigits: 0}) + ' MRU';
}

// Start with one line
addLine();
</script>

@endsection
