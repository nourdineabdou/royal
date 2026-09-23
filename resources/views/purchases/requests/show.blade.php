@extends('layouts.purchases')
@section('title', $purchaseRequest->reference)

@section('content')
@php
    $badges = [
        'draft' => 'bg-slate-100 text-slate-600',
        'pending' => 'bg-amber-100 text-amber-700',
        'partially_ordered' => 'bg-blue-100 text-blue-700',
        'ordered' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];
    $labels = [
        'draft' => 'Brouillon',
        'pending' => 'En attente',
        'partially_ordered' => 'Partiellement commandée',
        'ordered' => 'Entièrement commandée',
        'cancelled' => 'Annulée',
    ];
    $hasRemaining = $purchaseRequest->items->contains(fn($i) => $i->remaining_quantity > 0.001);
@endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('purchases.requests') }}" class="text-slate-400 hover:text-slate-600 transition">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="text-xl font-bold text-slate-800">{{ $purchaseRequest->reference }}</h2>
    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badges[$purchaseRequest->status] ?? 'bg-slate-100 text-slate-600' }}">
        {{ $labels[$purchaseRequest->status] ?? $purchaseRequest->status }}
    </span>
    <a href="{{ route('purchases.requests.print', $purchaseRequest) }}" target="_blank"
       class="ml-auto flex items-center gap-2 bg-slate-600 hover:bg-slate-700 text-white px-3 py-2 rounded-xl text-sm font-medium transition">
        <i class="fa-solid fa-print"></i> Imprimer
    </a>
</div>

@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-5">

        {{-- Lignes de la demande --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-700">Produits demandés</h3>
                @if($hasRemaining)
                <button type="button" onclick="document.getElementById('newOrderModal').classList.remove('hidden')"
                        class="flex items-center gap-2 text-sm bg-orange-500 hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg font-medium transition">
                    <i class="fa-solid fa-file-invoice"></i> Créer un Bon de Commande
                </button>
                @endif
            </div>
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-400 uppercase border-b">
                    <tr>
                        <th class="pb-2 text-left">Produit</th>
                        <th class="pb-2 text-right">Demandé</th>
                        <th class="pb-2 text-right">Commandé</th>
                        <th class="pb-2 text-right">Restant</th>
                        <th class="pb-2 text-left">Progression</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseRequest->items as $item)
                    @php $pct = $item->quantity > 0 ? min(100, ($item->ordered_quantity / $item->quantity) * 100) : 0; @endphp
                    <tr class="border-b border-slate-50">
                        <td class="py-2 font-medium text-slate-700">
                            {{ $item->product->name }}
                            @if($item->notes)<div class="text-xs text-slate-400">{{ $item->notes }}</div>@endif
                        </td>
                        <td class="py-2 text-right text-slate-600">{{ rtrim(rtrim(number_format($item->quantity,2),'0'),'.') }} {{ $item->product->unit->symbol ?? '' }}</td>
                        <td class="py-2 text-right text-slate-600">{{ rtrim(rtrim(number_format($item->ordered_quantity,2),'0'),'.') }} {{ $item->product->unit->symbol ?? '' }}</td>
                        <td class="py-2 text-right {{ $item->remaining_quantity > 0 ? 'text-amber-600 font-semibold' : 'text-emerald-600' }}">
                            {{ rtrim(rtrim(number_format($item->remaining_quantity,2),'0'),'.') }} {{ $item->product->unit->symbol ?? '' }}
                        </td>
                        <td class="py-2">
                            <div class="h-2 w-32 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Devis reçus — comparaison fournisseurs --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-700">Devis reçus <span class="text-xs font-normal text-slate-400">— comparez avant de choisir un fournisseur</span></h3>
                <button type="button" onclick="document.getElementById('newQuoteModal').classList.remove('hidden')"
                        class="flex items-center gap-2 text-sm bg-slate-700 hover:bg-slate-800 text-white px-3 py-1.5 rounded-lg font-medium transition">
                    <i class="fa-solid fa-plus"></i> Ajouter un devis
                </button>
            </div>

            @if($purchaseRequest->quotes->isEmpty())
            <p class="text-slate-400 text-sm">Aucun devis enregistré pour l'instant.</p>
            @else
            @php
                // Pour chaque ligne de la demande, le prix le plus bas parmi les devis reçus
                $lowestByItem = [];
                foreach ($purchaseRequest->items as $reqItem) {
                    $prices = $purchaseRequest->quotes->flatMap->items->where('purchase_request_item_id', $reqItem->id)->pluck('unit_price');
                    if ($prices->isNotEmpty()) $lowestByItem[$reqItem->id] = $prices->min();
                }
            @endphp
            <div class="overflow-x-auto">
                <table class="w-full text-sm mb-2">
                    <thead class="text-xs text-slate-400 uppercase border-b">
                        <tr>
                            <th class="pb-2 text-left">Produit</th>
                            @foreach($purchaseRequest->quotes as $quote)
                            <th class="pb-2 text-right px-3">
                                {{ $quote->supplier->name }}
                                @if($quote->reference)<div class="text-[10px] normal-case text-slate-400">{{ $quote->reference }}</div>@endif
                                <button type="button" onclick="if(confirm('Supprimer ce devis ?')) document.getElementById('delQuote{{ $quote->id }}').submit();" class="text-red-400 hover:text-red-600 normal-case font-normal text-[10px]">
                                    <i class="fa-solid fa-trash"></i> supprimer
                                </button>
                                <form id="delQuote{{ $quote->id }}" method="POST" action="{{ route('purchases.requests.quotes.destroy', $quote) }}" class="hidden">@csrf @method('DELETE')</form>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseRequest->items as $reqItem)
                        <tr class="border-b border-slate-50">
                            <td class="py-2 font-medium text-slate-700">{{ $reqItem->product->name }}</td>
                            @foreach($purchaseRequest->quotes as $quote)
                                @php $qi = $quote->items->firstWhere('purchase_request_item_id', $reqItem->id); @endphp
                                <td class="py-2 px-3 text-right {{ $qi && isset($lowestByItem[$reqItem->id]) && (float)$qi->unit_price === (float)$lowestByItem[$reqItem->id] ? 'bg-emerald-50 text-emerald-700 font-bold rounded-lg' : 'text-slate-500' }}">
                                    {{ $qi ? number_format($qi->unit_price, 2, ',', ' ') : '—' }}
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-slate-400"><span class="inline-block w-3 h-3 bg-emerald-50 border border-emerald-200 rounded-sm align-middle mr-1"></span> Prix le plus bas par produit.</p>
            @endif
        </div>

        {{-- BC liés à cette demande --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4">
                Bons de commande liés à cette demande
                <span class="text-xs font-normal text-slate-400">— tous répondent au même besoin</span>
            </h3>
            @forelse($purchaseRequest->orders as $order)
            <a href="{{ route('purchases.orders.show', $order) }}"
               class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition">
                <div>
                    <span class="font-semibold text-orange-600">{{ $order->reference }}</span>
                    <span class="text-slate-500 text-sm ml-2">→ {{ $order->supplier->name ?? 'Fournisseur non défini' }}</span>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-600">{{ ucfirst($order->status) }}</span>
            </a>
            @empty
            <p class="text-slate-400 text-sm">Aucun Bon de Commande créé pour l'instant.</p>
            @endforelse
        </div>

    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4">Informations</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Demandé par</dt><dd class="font-medium text-slate-800">{{ $purchaseRequest->requestedBy->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Créée le</dt><dd class="font-medium text-slate-800">{{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Bons de commande</dt><dd class="font-medium text-slate-800">{{ $purchaseRequest->orders->count() }}</dd></div>
            </dl>
            @if($purchaseRequest->notes)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-500 mb-1">Notes</p>
                <p class="text-sm text-slate-700">{{ $purchaseRequest->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL: Ajouter un devis fournisseur --}}
<div id="newQuoteModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white">
            <h3 class="font-bold text-slate-800">Ajouter un devis</h3>
            <button onclick="document.getElementById('newQuoteModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('purchases.requests.quotes.store', $purchaseRequest) }}" class="p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Fournisseur <span class="text-red-500">*</span></label>
                <select name="supplier_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    <option value="">— Choisir —</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Référence du devis <span class="text-slate-400 font-normal">(optionnel)</span></label>
                <input type="text" name="reference" placeholder="Ex: Devis-2026-045" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            </div>

            <p class="text-xs text-slate-500 mb-2">Prix unitaire proposé par ce fournisseur pour chaque produit :</p>
            <table class="w-full text-sm mb-4">
                <tbody>
                    @foreach($purchaseRequest->items as $reqItem)
                    <tr class="border-b border-slate-50">
                        <td class="py-2 text-slate-700">
                            {{ $reqItem->product->name }}
                            <input type="hidden" name="items[{{ $loop->index }}][purchase_request_item_id]" value="{{ $reqItem->id }}">
                        </td>
                        <td class="py-2 text-right w-32">
                            <input type="number" step="0.01" min="0" name="items[{{ $loop->index }}][unit_price]" placeholder="Prix"
                                   class="w-full text-right border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="submit" class="w-full bg-slate-700 hover:bg-slate-800 text-white py-3 rounded-xl font-semibold text-sm transition shadow">
                <i class="fa-solid fa-save mr-2"></i> Enregistrer ce devis
            </button>
        </form>
    </div>
</div>

{{-- MODAL: Créer un BC à partir des lignes restantes --}}
@if($hasRemaining)
<div id="newOrderModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white">
            <h3 class="font-bold text-slate-800">Nouveau Bon de Commande</h3>
            <button onclick="document.getElementById('newOrderModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('purchases.requests.orders.store', $purchaseRequest) }}" class="p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Fournisseur <span class="text-slate-400 font-normal">(optionnel — à préciser ici ou plus tard)</span></label>
                <select name="supplier_id" id="daSupplierSelect" onchange="refreshAllPriceHints()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    <option value="">— Pas encore connu —</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <p class="text-xs text-slate-500 mb-3">Cochez les lignes à inclure dans ce BC et indiquez la quantité (vous pouvez ne prendre qu'une partie du restant, ex: quelques bouteilles au lieu d'une caisse entière).</p>

            <table class="w-full text-sm mb-4">
                <thead class="text-xs text-slate-400 uppercase border-b">
                    <tr>
                        <th class="pb-2 w-8"></th>
                        <th class="pb-2 text-left">Produit</th>
                        <th class="pb-2 text-left w-40">Emballage</th>
                        <th class="pb-2 text-right w-24">Quantité</th>
                        <th class="pb-2 text-right w-28">Prix unitaire</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseRequest->items as $item)
                        @if($item->remaining_quantity > 0.001)
                        @php
                            $pkgs = $item->product->productPackagings()->with('packaging')->get();
                        @endphp
                        <tr class="border-b border-slate-50 order-line-row" data-remaining="{{ $item->remaining_quantity }}" data-unit="{{ $item->product->unit->symbol ?? '' }}" data-product-id="{{ $item->product_id }}">
                            <td class="py-2">
                                <input type="checkbox" class="line-check" onchange="toggleLine(this)">
                            </td>
                            <td class="py-2 font-medium text-slate-700">
                                {{ $item->product->name }}
                                <div class="text-xs text-slate-400">Restant: {{ rtrim(rtrim(number_format($item->remaining_quantity,2),'0'),'.') }} {{ $item->product->unit->symbol ?? '' }}</div>
                                <input type="hidden" class="request-item-id" value="{{ $item->id }}">
                            </td>
                            <td class="py-2">
                                <select class="packaging-select w-full border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none" disabled onchange="recalcLine(this)">
                                    <option value="">— Vrac ({{ $item->product->unit->symbol ?? '' }}) —</option>
                                    @foreach($pkgs as $pp)
                                    <option value="{{ $pp->packaging_id }}" data-qty="{{ $pp->quantity }}">{{ $pp->packaging->name }} ({{ rtrim(rtrim(number_format($pp->quantity,2),'0'),'.') }} {{ $item->product->unit->symbol ?? '' }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2 text-right">
                                <input type="number" step="0.01" min="0.01" disabled
                                       class="qty-input w-full text-right border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none"
                                       oninput="recalcLine(this)">
                                <p class="actual-hint text-xs text-emerald-600 mt-0.5 hidden"></p>
                            </td>
                            <td class="py-2 text-right">
                                <input type="number" step="0.01" min="0" disabled placeholder="0"
                                       class="price-input w-full text-right border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none"
                                       oninput="syncHiddenInputs()">
                                <p class="price-hint text-xs text-indigo-500 mt-0.5 hidden"></p>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <div id="orderItemsContainer"></div>

            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-semibold text-sm transition shadow">
                <i class="fa-solid fa-paper-plane mr-2"></i> Créer ce Bon de Commande
            </button>
        </form>
    </div>
</div>

<script>
function toggleLine(checkbox) {
    const row = checkbox.closest('tr');
    const enabled = checkbox.checked;
    row.querySelector('.packaging-select').disabled = !enabled;
    row.querySelector('.qty-input').disabled = !enabled;
    row.querySelector('.price-input').disabled = !enabled;
    if (!enabled) {
        row.querySelector('.qty-input').value = '';
        row.querySelector('.price-input').value = '';
        row.querySelector('.actual-hint').classList.add('hidden');
        row.querySelector('.price-hint').classList.add('hidden');
    } else {
        fetchPriceHint(row);
    }
    syncHiddenInputs();
}

// Historique de prix (produit x fournisseur) — pré-remplit le prix si le champ est vide
function fetchPriceHint(row) {
    const hintEl = row.querySelector('.price-hint');
    const priceInput = row.querySelector('.price-input');
    const productId = row.dataset.productId;
    const supplierId = document.getElementById('daSupplierSelect')?.value || '';

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
                syncHiddenInputs();
            }
        })
        .catch(() => hintEl.classList.add('hidden'));
}

function refreshAllPriceHints() {
    document.querySelectorAll('.order-line-row').forEach(row => {
        if (row.querySelector('.line-check').checked) fetchPriceHint(row);
    });
}

function recalcLine(el) {
    const row = el.closest('tr');
    const pkgSel = row.querySelector('.packaging-select');
    const qtyInput = row.querySelector('.qty-input');
    const hint = row.querySelector('.actual-hint');
    const remaining = parseFloat(row.dataset.remaining);
    const unit = row.dataset.unit;
    const option = pkgSel.selectedOptions[0];
    const qty = parseFloat(qtyInput.value) || 0;

    let baseQty = qty;
    if (pkgSel.value && option.dataset.qty) {
        baseQty = qty * parseFloat(option.dataset.qty);
    }

    if (baseQty > 0) {
        hint.textContent = `→ ${baseQty.toLocaleString('fr-FR')} ${unit} (restant: ${remaining.toLocaleString('fr-FR')} ${unit})`;
        hint.classList.remove('hidden');
        hint.classList.toggle('text-red-600', baseQty > remaining + 0.001);
        hint.classList.toggle('text-emerald-600', baseQty <= remaining + 0.001);
    } else {
        hint.classList.add('hidden');
    }
    syncHiddenInputs();
}

function syncHiddenInputs() {
    const container = document.getElementById('orderItemsContainer');
    container.innerHTML = '';
    let i = 0;
    document.querySelectorAll('.order-line-row').forEach(row => {
        const checked = row.querySelector('.line-check').checked;
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        if (!checked || qty <= 0) return;

        const requestItemId = row.querySelector('.request-item-id').value;
        const packagingId = row.querySelector('.packaging-select').value;
        const price = row.querySelector('.price-input').value || '';

        container.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="items[${i}][request_item_id]" value="${requestItemId}">
            <input type="hidden" name="items[${i}][packaging_id]" value="${packagingId}">
            <input type="hidden" name="items[${i}][quantity]" value="${qty}">
            <input type="hidden" name="items[${i}][unit_price]" value="${price}">
        `);
        i++;
    });
}
</script>
@endif
@endsection
