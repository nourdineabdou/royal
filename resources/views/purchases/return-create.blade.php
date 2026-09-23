@extends('layouts.purchases')
@section('title', 'Retour fournisseur — ' . $order->reference)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('purchases.orders.show', $order) }}" class="text-slate-400 hover:text-slate-600 transition">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="text-xl font-bold text-slate-800">Retour fournisseur — {{ $order->reference }}</h2>
</div>

@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
</div>
@endif

@if($returnableItems->isEmpty())
<div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl text-sm">
    Aucun produit reçu n'est disponible au retour pour cette commande (rien de reçu, ou tout a déjà été retourné).
</div>
@else
<form method="POST" action="{{ route('purchases.orders.return.store', $order) }}" class="bg-white rounded-2xl shadow-sm p-6 max-w-3xl">
    @csrf
    <div class="mb-4">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Dépôt de stock (d'où sortent les produits retournés) <span class="text-red-500">*</span></label>
        <select name="stock_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <option value="">— Choisir un stock —</option>
            @foreach($stocks as $stock)
            <option value="{{ $stock->id }}">{{ $stock->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Motif du retour</label>
        <textarea name="reason" rows="2" placeholder="Ex: produits défectueux, erreur de livraison…"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
    </div>

    <p class="text-xs text-slate-500 mb-3">Cochez les produits à retourner et indiquez la quantité (plafonnée à ce qui a été reçu et pas déjà retourné).</p>

    <table class="w-full text-sm mb-4">
        <thead class="text-xs text-slate-400 uppercase border-b">
            <tr>
                <th class="pb-2 w-8"></th>
                <th class="pb-2 text-left">Produit</th>
                <th class="pb-2 text-left">Emballage</th>
                <th class="pb-2 text-right">Retournable</th>
                <th class="pb-2 text-right w-28">Quantité</th>
                <th class="pb-2 text-right">Prix unit.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returnableItems as $item)
            <tr class="border-b border-slate-50 return-row" data-returnable="{{ $item->returnable_quantity }}">
                <td class="py-2">
                    <input type="checkbox" class="line-check" onchange="toggleLine(this)">
                </td>
                <td class="py-2 font-medium text-slate-700">
                    {{ $item->product->name }}
                    <input type="hidden" class="order-item-id" value="{{ $item->id }}">
                    <input type="hidden" class="product-id" value="{{ $item->product_id }}">
                </td>
                <td class="py-2 text-xs text-slate-500">{{ $item->packaging->name ?? 'Vrac' }}</td>
                <td class="py-2 text-right text-slate-600">
                    {{ rtrim(rtrim(number_format($item->returnable_quantity,2),'0'),'.') }}
                    {{ $item->packaging ? 'colis' : ($item->product->unit->symbol ?? '') }}
                </td>
                <td class="py-2 text-right">
                    <input type="number" step="0.01" min="0.01" disabled
                           class="qty-input w-full text-right border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </td>
                <td class="py-2 text-right text-slate-500">{{ number_format($item->price ?? 0, 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div id="returnItemsContainer"></div>

    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-semibold text-sm transition shadow">
        <i class="fa-solid fa-rotate-left mr-2"></i> Enregistrer le retour
    </button>
</form>

<script>
function toggleLine(checkbox) {
    const row = checkbox.closest('tr');
    row.querySelector('.qty-input').disabled = !checkbox.checked;
    if (!checkbox.checked) row.querySelector('.qty-input').value = '';
}

document.querySelector('form').addEventListener('submit', function (e) {
    const container = document.getElementById('returnItemsContainer');
    container.innerHTML = '';
    let i = 0;
    let hasAny = false;
    document.querySelectorAll('.return-row').forEach(row => {
        const checked = row.querySelector('.line-check').checked;
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        if (!checked || qty <= 0) return;
        hasAny = true;
        const orderItemId = row.querySelector('.order-item-id').value;
        const productId = row.querySelector('.product-id').value;
        container.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="items[${i}][order_item_id]" value="${orderItemId}">
            <input type="hidden" name="items[${i}][product_id]" value="${productId}">
            <input type="hidden" name="items[${i}][quantity]" value="${qty}">
        `);
        i++;
    });
    if (!hasAny) {
        e.preventDefault();
        alert('Cochez au moins un produit à retourner.');
    }
});
</script>
@endif
@endsection
