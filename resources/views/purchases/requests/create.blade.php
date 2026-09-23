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
    Exprimez ici le besoin (produits + quantités), sans vous soucier du fournisseur ni de l'emballage —
    ça se décide ensuite, ligne par ligne, quand vous créerez un ou plusieurs Bons de Commande à partir de cette demande.
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
                <th class="pb-2 text-right w-32">Quantité</th>
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
@php
    $productsForJs = $products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'unit' => $p->unit->symbol ?? ''])->values();
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
    row.innerHTML = `
        <td class="py-2 pr-2">
            <select name="items[${i}][product_id]" required class="w-full border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <option value="">— Produit —</option>${options}
            </select>
        </td>
        <td class="py-2 px-2">
            <input type="number" name="items[${i}][quantity]" step="0.01" min="0.01" required placeholder="0"
                   class="w-full text-right border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none">
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

function removeLine(btn) {
    btn.closest('tr').remove();
    if (!document.querySelectorAll('.line-row').length) {
        document.getElementById('noLinesMsg').style.display = '';
    }
}

addLine();
</script>
@endsection
