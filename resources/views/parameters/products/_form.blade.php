{{--
    Formulaire produit (création et modification), pensé d'abord pour le téléphone :
    grands champs, un bloc par emballage, bouton « Scanner » (caméra) à côté de chaque code-barres,
    bouton d'enregistrement toujours visible en bas de l'écran.
    Variables : $product (null en création), $units, $packagings, $action, $method, $title, $submitLabel.
--}}
@php
    $input = 'w-full px-4 py-3 border border-gray-300 rounded-xl bg-white text-base focus:outline-none focus:ring-2 focus:ring-indigo-500';
    $label = 'block text-sm font-bold text-gray-800 mb-1.5';
    $help  = 'text-xs text-gray-500 mt-1.5 leading-relaxed';
    $scanBtn = 'bc-cam-btn shrink-0 inline-flex items-center justify-center gap-2 px-4 rounded-xl bg-slate-800 active:bg-slate-900 hover:bg-slate-900 text-white font-semibold';

    $oldIds = old('packaging_id');
    $rows = $oldIds !== null
        ? collect($oldIds)->map(fn ($id, $i) => ['packaging_id' => $id, 'quantity' => old('packaging_quantity')[$i] ?? null, 'barcode' => old('packaging_barcode')[$i] ?? null])
        : ($product ? $product->productPackagings->map(fn ($pp) => ['packaging_id' => $pp->packaging_id, 'quantity' => $pp->quantity, 'barcode' => $pp->barcode]) : collect());
    $minValue = old('min_stock_display', $product && $product->min_stock !== null
        ? round($product->min_stock / \App\Support\Qty::displayUnit($product->unit->symbol ?? '')['factor'], 3) : '');
@endphp

<div class="max-w-2xl mx-auto -mx-1 sm:mx-auto pb-28 sm:pb-6">
    <div class="flex items-center gap-3 mb-4 sm:mb-6">
        <a href="{{ route('purchases.products.index') }}" class="w-10 h-10 shrink-0 rounded-xl bg-white shadow-sm flex items-center justify-center text-indigo-600" aria-label="Retour">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-xl sm:text-3xl font-bold text-gray-800 leading-tight">{{ $title }}</h1>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm space-y-1">
            @foreach($errors->all() as $e)<p><i class="fas fa-triangle-exclamation mr-1"></i>{{ $e }}</p>@endforeach
        </div>
    @endif

    <form action="{{ $action }}" method="POST" id="productForm" class="space-y-4">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        {{-- ── Le produit ──────────────────────────────────────────────── --}}
        <section class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 space-y-5">
            <div>
                <label for="name" class="{{ $label }}">Nom du produit <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" required placeholder="Ex : Eau 0,5 L"
                       value="{{ old('name', $product->name ?? '') }}" class="{{ $input }} @error('name') border-red-500 @enderror">
            </div>

            <div>
                <label for="unit_id" class="{{ $label }}">Unité de stock <span class="text-red-500">*</span></label>
                <select id="unit_id" name="unit_id" required style="width:100%" class="{{ $input }} @error('unit_id') border-red-500 @enderror">
                    <option value="">— Choisir l'unité —</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('unit_id', $product->unit_id ?? null) == $unit->id)>{{ $unit->name }} ({{ $unit->symbol }})</option>
                    @endforeach
                </select>
                <p class="{{ $help }}">Unité divisible (kg, g, L, mL, pièce…), jamais un emballage comme « bouteille » ou « caisse ».</p>
            </div>

            <div>
                <label for="barcode" class="{{ $label }}"><i class="fas fa-barcode mr-1 text-gray-500"></i>Code-barres de l'unité <span class="font-normal text-gray-400">(optionnel)</span></label>
                <div class="flex gap-2">
                    <input type="text" id="barcode" name="barcode" data-bc-cam="1" inputmode="numeric" maxlength="64" autocomplete="off"
                           placeholder="Scanner ou taper" value="{{ old('barcode', $product->barcode ?? '') }}"
                           class="{{ $input }} flex-1 min-w-0 font-mono @error('barcode') border-red-500 @enderror">
                    <button type="button" class="{{ $scanBtn }}" title="Scanner avec la caméra"><i class="fas fa-camera"></i><span>Scanner</span></button>
                </div>
                <p class="{{ $help }}">1 scan = 1 unité. Pour une bouteille, une caisse, un carton… mettez le code sur l'emballage, plus bas.</p>
            </div>

            <div>
                <label for="min_stock_display" class="{{ $label }}">Seuil minimum <span class="font-normal text-gray-400">(alerte d'achat)</span></label>
                <div class="flex items-center gap-2">
                    <input type="number" step="any" min="0" id="min_stock_display" name="min_stock_display" inputmode="decimal"
                           value="{{ $minValue }}" placeholder="vide = {{ $product ? $product->defaultMinLabel() : '3 emballages' }}"
                           class="{{ $input }} @error('min_stock_display') border-red-500 @enderror">
                    <span id="min_stock_unit" class="text-base font-semibold text-gray-600 whitespace-nowrap"></span>
                </div>
                <p class="{{ $help }}">Comparé au Stock Catering. Vide = 3 emballages d'achat. En kg pour les produits en grammes, en L pour les produits en mL.</p>
            </div>
        </section>

        {{-- ── Emballages et leurs codes-barres ─────────────────────────── --}}
        <section class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 space-y-3">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-gray-800"><i class="fas fa-cube mr-1 text-indigo-500"></i> Emballages et codes-barres</h2>
                <p class="{{ $help }} !mt-1">Un code par emballage : scanner un carton compte tout le carton, scanner une bouteille compte une bouteille.</p>
            </div>

            <div id="packaging-rows" class="space-y-3">
                @foreach($rows as $row)
                    @include('parameters.products._packaging-row', ['row' => $row])
                @endforeach
            </div>
            <p id="no-packaging" class="{{ $rows->isEmpty() ? '' : 'hidden' }} text-sm text-gray-400 text-center border border-dashed border-gray-300 rounded-xl py-4">Aucun emballage.</p>

            <button type="button" id="add-packaging-row"
                    class="w-full py-3 rounded-xl border-2 border-dashed border-indigo-300 text-indigo-700 font-semibold bg-indigo-50/50 hover:bg-indigo-50">
                <i class="fas fa-plus mr-1"></i> Ajouter un emballage
            </button>
        </section>

        {{-- ── Vente au POS ─────────────────────────────────────────────── --}}
        <section class="bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-6 space-y-3">
            <label class="flex items-center gap-3 text-base font-bold text-gray-800">
                <input type="checkbox" id="is_consumable" name="is_consumable" value="1" @checked(old('is_consumable', $product->is_consumable ?? false))
                       class="w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                Vendable en POS catering
            </label>
            <div>
                <label for="sale_price" class="{{ $label }}">Prix de vente unitaire (MRU)</label>
                <input type="number" id="sale_price" name="sale_price" min="0" step="0.01" inputmode="decimal" placeholder="Ex : 150"
                       value="{{ old('sale_price', $product->sale_price ?? '') }}" class="{{ $input }} @error('sale_price') border-red-500 @enderror">
                <p class="{{ $help }}">Optionnel : une alerte s'affiche à la vente tant qu'il n'est pas renseigné.</p>
            </div>
        </section>

        {{-- ── Enregistrer : fixé en bas sur téléphone ──────────────────── --}}
        <div class="fixed sm:static bottom-0 inset-x-0 z-40 bg-white/95 sm:bg-transparent backdrop-blur sm:backdrop-blur-none border-t sm:border-0 border-gray-200 p-3 sm:p-0 flex gap-3"
             style="padding-bottom: max(.75rem, env(safe-area-inset-bottom))">
            <a href="{{ route('purchases.products.index') }}" class="px-5 py-3.5 rounded-xl bg-gray-200 text-gray-700 font-semibold text-center">Annuler</a>
            <button type="submit" class="flex-1 sm:flex-none sm:px-8 py-3.5 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold text-base">
                <i class="fas fa-save mr-2"></i>{{ $submitLabel }}
            </button>
        </div>
    </form>
</div>

<template id="packaging-row-template">
    @include('parameters.products._packaging-row', ['row' => ['packaging_id' => null, 'quantity' => null, 'barcode' => null]])
</template>

<script>
(function () {
    const unitSel = document.getElementById('unit_id');
    const unitSymbol = () => {
        const m = (unitSel.options[unitSel.selectedIndex]?.text || '').match(/\(([^)]+)\)\s*$/);
        return m ? m[1].trim() : '';
    };
    // Unité du seuil (kg pour g, L pour mL) et de la contenance des emballages (unité de stock)
    function showUnits() {
        const sym = unitSymbol();
        document.getElementById('min_stock_unit').textContent = ({ g: 'kg', ml: 'L' })[sym.toLowerCase()] || sym;
        document.querySelectorAll('.pkg-unit').forEach(s => s.textContent = sym || 'unités');
    }
    unitSel.addEventListener('change', showUnits);
    if (window.jQuery) jQuery(unitSel).on('change', showUnits);

    const rows = document.getElementById('packaging-rows');
    function renumber() {
        const list = rows.querySelectorAll('.packaging-row');
        list.forEach((r, i) => r.querySelector('.pkg-num').textContent = i + 1);
        document.getElementById('no-packaging').classList.toggle('hidden', list.length > 0);
        list.forEach(r => {
            const code = r.querySelector('input[name="packaging_barcode[]"]').value.trim();
            r.querySelector('.pkg-code-state').innerHTML = code
                ? '<i class="fas fa-circle-check"></i> code enregistré'
                : '<i class="far fa-circle"></i> pas de code';
            r.querySelector('.pkg-code-state').className = 'pkg-code-state text-xs font-semibold ' + (code ? 'text-emerald-600' : 'text-gray-400');
        });
        showUnits();
    }
    document.getElementById('add-packaging-row').addEventListener('click', () => {
        rows.appendChild(document.getElementById('packaging-row-template').content.cloneNode(true));
        renumber();
        const added = rows.lastElementChild;
        added.scrollIntoView({ behavior: 'smooth', block: 'center' });
        added.querySelector('select').focus({ preventScroll: true });
    });
    rows.addEventListener('click', e => {
        const del = e.target.closest('.pkg-remove');
        if (!del) return;
        const row = del.closest('.packaging-row');
        const name = row.querySelector('select').selectedOptions[0]?.text || 'cet emballage';
        if (row.querySelector('select').value && !confirm('Retirer « ' + name + ' » ?')) return;
        row.remove();
        renumber();
    });
    rows.addEventListener('input', renumber);
    rows.addEventListener('change', renumber);

    // Douchettes : « Entrée » après le code ne doit pas envoyer le formulaire
    document.addEventListener('keydown', e => {
        if (e.key === 'Enter' && e.target.matches('input[name="barcode"], input[name="packaging_barcode[]"]')) e.preventDefault();
    });

    const consumable = document.getElementById('is_consumable'), price = document.getElementById('sale_price');
    const syncPrice = () => { if (!consumable.checked) price.value = ''; };
    consumable.addEventListener('change', syncPrice);
    syncPrice();
    renumber();
})();
</script>

{{-- Caméra pour les codes-barres (les boutons « Scanner » sont déjà dans le formulaire) --}}
@include('partials.barcode-camera')
