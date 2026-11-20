@extends('layouts.purchases')
@section('title', $purchaseRequest->reference)

@section('content')
@php
    $canOrder = $purchaseRequest->canBeOrdered() && auth()->user()->can('purchases.orders.create');
    // Prix de chaque devis ramené à l'unité de base (un devis peut être donné par sac, par carton…)
    $quoteBase = function ($qi) {
        $item = $qi->requestItem ?? null;
        $pp = $qi->packaging_id ? $item?->product->productPackagings->firstWhere('packaging_id', $qi->packaging_id) : null;
        return $pp && (float) $pp->quantity > 0 ? (float) $qi->unit_price / (float) $pp->quantity : (float) $qi->unit_price;
    };
    $quotesByItem = [];
    foreach ($purchaseRequest->quotes as $q) {
        foreach ($q->items as $qi) {
            $qi->setRelation('requestItem', $purchaseRequest->items->firstWhere('id', $qi->purchase_request_item_id));
            $quotesByItem[$qi->purchase_request_item_id][] = ['supplier_id' => $q->supplier_id, 'supplier' => $q->supplier->name ?? '—', 'base' => $quoteBase($qi)];
        }
    }
    foreach ($quotesByItem as &$list) { usort($list, fn ($a, $b) => $a['base'] <=> $b['base']); } unset($list);
    $hasRemaining = $purchaseRequest->items->contains(fn($i) => $i->remaining_quantity > 0.001);
@endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('purchases.requests') }}" class="text-slate-400 hover:text-slate-600 transition">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="text-xl font-bold text-slate-800">{{ $purchaseRequest->reference }}</h2>
    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $purchaseRequest->status_badge }}">
        {{ $purchaseRequest->status_label }}
    </span>
    <a href="{{ route('purchases.requests.print', $purchaseRequest) }}" target="_blank"
       class="ml-auto flex items-center gap-2 bg-slate-600 hover:bg-slate-700 text-white px-3 py-2 rounded-xl text-sm font-medium transition">
        <i class="fa-solid fa-print"></i> Imprimer
    </a>
</div>

@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
</div>
@endif

{{-- ── Circuit d'approbation ─────────────────────────────────────────── --}}
@php
    $pr = $purchaseRequest;
    $steps = [
        ['Demandée', $pr->requestedBy->name ?? 'Système (DA automatique)', $pr->created_at, true],
        ['Approuvée — acheteur', $pr->achatApprover->name ?? null, $pr->achat_approved_at, (bool) $pr->achat_approved_at],
        ['Validée — resp. des achats', $pr->rhApprover->name ?? null, $pr->rh_approved_at, (bool) $pr->rh_approved_at],
        ['Commandée', $pr->orders->count() ? $pr->orders->count() . ' BC' : null, $pr->orders->max('created_at'), $pr->orders->isNotEmpty()],
    ];
@endphp
<div class="bg-white rounded-2xl shadow-sm p-5 mb-6 space-y-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach($steps as $i => [$title, $who, $when, $done])
            <div class="rounded-xl border p-3 {{ $done ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200' }}">
                <p class="text-xs font-semibold {{ $done ? 'text-emerald-700' : 'text-slate-400' }}">
                    <i class="fa-solid {{ $done ? 'fa-circle-check' : 'fa-circle' }} mr-1"></i>{{ $i + 1 }}. {{ $title }}
                </p>
                <p class="text-sm text-slate-700 mt-1">{{ $done ? ($who ?? '—') : 'En attente' }}</p>
                @if($done && $when)<p class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($when)->format('d/m/Y H:i') }}</p>@endif
            </div>
        @endforeach
    </div>

    @if($pr->status === 'rejected')
        <p class="text-sm bg-red-50 text-red-700 rounded-xl px-4 py-3">
            <i class="fa-solid fa-circle-xmark mr-1"></i> Refusée par {{ $pr->rejecter->name ?? '—' }} le {{ $pr->rejected_at?->format('d/m/Y H:i') }} : {{ $pr->rejection_reason }}
        </p>
    @endif

    @php
        $canApprove  = $pr->status === 'pending' && auth()->user()->can('purchases.requests.approve');
        $canValidate = $pr->status === 'to_validate' && auth()->user()->can('purchases.requests.validate');
    @endphp
    @if($canApprove || $canValidate)
        <div class="flex flex-col sm:flex-row gap-2 sm:items-start">
            <form method="POST" action="{{ route($canApprove ? 'purchases.requests.approve' : 'purchases.requests.validate', $pr) }}">
                @csrf
                <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl text-sm font-semibold">
                    <i class="fa-solid fa-check"></i> {{ $canApprove ? 'Approuver (acheteur)' : 'Valider la demande (responsable des achats)' }}
                </button>
            </form>
            <details class="flex-1 bg-red-50 border border-red-100 rounded-xl">
                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-red-700">Refuser</summary>
                <form method="POST" action="{{ route('purchases.requests.reject', $pr) }}" class="px-4 pb-4 space-y-2">
                    @csrf
                    <textarea name="rejection_reason" required rows="2" placeholder="Motif du refus" class="w-full border border-red-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:outline-none"></textarea>
                    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-sm font-semibold">Confirmer le refus</button>
                </form>
            </details>
        </div>
    @elseif($pr->status === 'pending')
        <p class="text-sm text-amber-700 bg-amber-50 rounded-xl px-4 py-3"><i class="fa-solid fa-hourglass-half mr-1"></i> En attente de l'approbation de l'acheteur.</p>
    @elseif($pr->status === 'to_validate')
        <p class="text-sm text-violet-700 bg-violet-50 rounded-xl px-4 py-3"><i class="fa-solid fa-hourglass-half mr-1"></i> En attente de la validation du responsable des achats.</p>
    @elseif($pr->canBeOrdered())
        <p class="text-sm text-sky-700 bg-sky-50 rounded-xl px-4 py-3"><i class="fa-solid fa-cart-shopping mr-1"></i> Validée : l'acheteur apporte les devis, le responsable des achats fixe les prix et crée les bons de commande (un par fournisseur).</p>
    @endif
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-5">

        {{-- Lignes de la demande --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-700">Produits demandés</h3>
                @if($hasRemaining && $canOrder)
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
                        @php $sym = $item->product->unit->symbol ?? ''; @endphp
                        <td class="py-2 text-right text-slate-600">
                            {{ \App\Support\Qty::format($item->quantity, $sym) }}
                            @if($p = $item->product->inMainPackaging((float) $item->quantity))<div class="text-[11px] text-slate-400">{{ $p }}</div>@endif
                        </td>
                        <td class="py-2 text-right text-slate-600">{{ \App\Support\Qty::format($item->ordered_quantity, $sym) }}</td>
                        <td class="py-2 text-right {{ $item->remaining_quantity > 0 ? 'text-amber-600 font-semibold' : 'text-emerald-600' }}">
                            {{ \App\Support\Qty::format($item->remaining_quantity, $sym) }}
                            @if($item->remaining_quantity > 0 && ($p = $item->product->inMainPackaging((float) $item->remaining_quantity)))<div class="text-[11px] font-normal">{{ $p }}</div>@endif
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
                @canany(['purchases.quotes.manage', 'purchases.orders.create'])
                <button type="button" onclick="document.getElementById('newQuoteModal').classList.remove('hidden')"
                        class="flex items-center gap-2 text-sm bg-slate-700 hover:bg-slate-800 text-white px-3 py-1.5 rounded-lg font-medium transition">
                    <i class="fa-solid fa-plus"></i> Ajouter un devis
                </button>
                @endcanany
            </div>

            @if($purchaseRequest->quotes->isEmpty())
            <p class="text-slate-400 text-sm">Aucun devis enregistré pour l'instant.</p>
            @else
            @php
                // Pour chaque ligne de la demande, le prix le plus bas parmi les devis reçus
                $lowestByItem = [];
                foreach ($purchaseRequest->items as $reqItem) {
                    if (!empty($quotesByItem[$reqItem->id])) $lowestByItem[$reqItem->id] = $quotesByItem[$reqItem->id][0]['base'];
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
                                <td class="py-2 px-3 text-right {{ $qi && isset($lowestByItem[$reqItem->id]) && abs($quoteBase($qi) - $lowestByItem[$reqItem->id]) < 1e-9 ? 'bg-emerald-50 text-emerald-700 font-bold rounded-lg' : 'text-slate-500' }}">
                                    {{ $qi ? number_format($qi->unit_price, 0, ',', ' ') : '—' }}
                                    @if($qi)<div class="text-[10px] font-normal text-slate-400">/ {{ $qi->packaging_id ? ($reqItem->product->productPackagings->firstWhere('packaging_id', $qi->packaging_id)?->packaging->name ?? 'emballage') : ($reqItem->product->unit->symbol ?? 'unité') }}</div>@endif
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
                <div class="flex justify-between"><dt class="text-slate-500">Demandé par</dt><dd class="font-medium text-slate-800">{{ $purchaseRequest->requestedBy->name ?? '—' }}@if($purchaseRequest->auto_generated) <span class="text-xs text-slate-400">(DA automatique)</span>@endif</dd></div>
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

            <p class="text-xs text-slate-500 mb-2">Prix proposé par ce fournisseur, <strong>par emballage</strong> indiqué (sac, carton…). Laissez vide si le fournisseur ne propose pas le produit.</p>
            <table class="w-full text-sm mb-4">
                <tbody>
                    @foreach($purchaseRequest->items as $reqItem)
                    <tr class="border-b border-slate-50">
                        <td class="py-2 text-slate-700">
                            {{ $reqItem->product->name }}
                            <input type="hidden" name="items[{{ $loop->index }}][purchase_request_item_id]" value="{{ $reqItem->id }}">
                            @php $mainPp = $reqItem->product->mainPackaging(); @endphp
                            <input type="hidden" name="items[{{ $loop->index }}][packaging_id]" value="{{ $mainPp?->packaging_id }}">
                        </td>
                        <td class="py-2 text-right w-56">
                            <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-orange-500">
                                <input type="number" step="0.01" min="0" name="items[{{ $loop->index }}][unit_price]" placeholder="Prix"
                                       class="w-full text-right px-2 py-1.5 text-xs focus:outline-none">
                                <span class="px-2 text-[11px] text-slate-500 bg-slate-50 self-stretch flex items-center whitespace-nowrap">MRU / {{ $mainPp->packaging->name ?? ($reqItem->product->unit->symbol ?? 'unité') }}</span>
                            </div>
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
@if($hasRemaining && $canOrder)
<div id="newOrderModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white">
            <h3 class="font-bold text-slate-800">Nouveau Bon de Commande</h3>
            <button onclick="document.getElementById('newOrderModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('purchases.requests.orders.store', $purchaseRequest) }}" class="p-6" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Fournisseur <span class="text-slate-400 font-normal">(optionnel — à préciser ici ou plus tard)</span></label>
                <select name="supplier_id" id="daSupplierSelect" onchange="onSupplierChange(); refreshAllPriceHints()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    <option value="">— Pas encore connu —</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="quoteSuggest" class="hidden mb-3 text-xs bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-3 py-2"></div>
            <div class="flex items-start justify-between gap-3 mb-3">
                <p class="text-xs text-slate-500">L'emballage et la quantité restante de la demande sont <strong>proposés par défaut</strong>. Cochez les lignes de ce fournisseur et ajustez si besoin (une partie seulement, autre emballage…).</p>
                <button type="button" onclick="checkAllLines()" class="shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg border border-orange-300 text-orange-700 bg-orange-50 hover:bg-orange-100">Tout sélectionner</button>
            </div>

            <table class="w-full text-sm mb-4">
                <thead class="text-xs text-slate-400 uppercase border-b">
                    <tr>
                        <th class="pb-2 w-8"></th>
                        <th class="pb-2 text-left">Produit</th>
                        <th class="pb-2 text-left w-40">Emballage</th>
                        <th class="pb-2 text-right w-24">Quantité</th>
                        <th class="pb-2 text-right w-28">Prix <span class="normal-case font-normal">(facultatif)</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseRequest->items as $item)
                        @if($item->remaining_quantity > 0.001)
                        @php
                            $pkgs = $item->product->productPackagings;
                            $def  = $item->product->suggestOrderLine((float) $item->remaining_quantity);
                            $sym  = $item->product->unit->symbol ?? '';
                        @endphp
                        <tr class="border-b border-slate-50 order-line-row" data-remaining="{{ $item->remaining_quantity }}" data-unit="{{ $sym }}" data-product-id="{{ $item->product_id }}" data-item-id="{{ $item->id }}"
                            data-def-pkg="{{ $def['packaging_id'] }}" data-def-qty="{{ $def['quantity'] }}"
                            data-prev-factor="{{ $def['packaging_id'] ? (float) ($pkgs->firstWhere('packaging_id', $def['packaging_id'])?->quantity ?? 1) : 1 }}">
                            <td class="py-2">
                                <input type="checkbox" class="line-check" onchange="toggleLine(this)">
                            </td>
                            <td class="py-2 font-medium text-slate-700">
                                {{ $item->product->name }}
                                <div class="text-xs text-slate-400">Restant : {{ \App\Support\Qty::format($item->remaining_quantity, $sym) }}@if($mp = $item->product->inMainPackaging((float) $item->remaining_quantity)) = {{ $mp }}@endif</div>
                                @if(!empty($quotesByItem[$item->id]))<div class="text-[11px] text-emerald-700"><i class="fa-solid fa-star"></i> Moins cher : {{ $quotesByItem[$item->id][0]['supplier'] }}</div>@endif
                                <input type="hidden" class="request-item-id" value="{{ $item->id }}">
                            </td>
                            <td class="py-2">
                                <select class="packaging-select w-full border border-slate-200 rounded-xl px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none" disabled onchange="recalcLine(this)">
                                    <option value="">— Vrac ({{ $item->product->unit->symbol ?? '' }}) —</option>
                                    @foreach($pkgs as $pp)
                                    <option value="{{ $pp->packaging_id }}" data-qty="{{ $pp->quantity }}" @selected($pp->packaging_id == $def['packaging_id'])>{{ $pp->packaging->name }} ({{ \App\Support\Qty::format($pp->quantity, $sym) }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2 text-right">
                                <input type="number" step="any" min="0.01" disabled value="{{ $def['quantity'] }}"
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

            <div class="mb-4 rounded-xl border border-dashed border-slate-300 p-3 bg-slate-50">
                <label class="block text-xs font-semibold text-slate-600 mb-1"><i class="fa-solid fa-paperclip mr-1"></i>Devis du fournisseur <span class="font-normal text-slate-400">(photo ou PDF, facultatif)</span></label>
                <input type="file" name="devis_files[]" multiple accept="image/*,application/pdf" class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-orange-100 file:text-orange-700 file:font-semibold">
                <p class="text-[11px] text-slate-400 mt-1">Prenez le devis en photo avec le téléphone ou joignez le scan. Les prix pourront être saisis plus tard sur la page du bon de commande.</p>
            </div>

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
        row.querySelector('.price-input').value = '';
        row.querySelector('.actual-hint').classList.add('hidden');
        row.querySelector('.price-hint').classList.add('hidden');
    } else {
        // Valeurs proposées : emballage principal et quantité restante de la demande
        const qty = row.querySelector('.qty-input');
        if (!qty.value) {
            const sel = row.querySelector('.packaging-select');
            sel.value = row.dataset.defPkg || '';
            qty.value = row.dataset.defQty || '';
            row.dataset.prevFactor = sel.selectedOptions[0]?.dataset.qty || 1;
        }
        recalcLine(qty);
        fetchPriceHint(row);
    }
    syncHiddenInputs();
}

function checkAllLines() {
    document.querySelectorAll('.order-line-row .line-check').forEach(c => { if (!c.checked) { c.checked = true; toggleLine(c); } });
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
            if (!priceInput.value && !(window.quoteFor && quoteFor(row, document.getElementById('daSupplierSelect')?.value))) {
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
    if (el.classList && el.classList.contains('packaging-select')) {
        // Changement d'emballage : on garde la même quantité réelle (10 sacs → 250 000 g, 2 cartons → 24 bouteilles)
        const newF = parseFloat(el.selectedOptions[0]?.dataset.qty || 1);
        const oldF = parseFloat(row.dataset.prevFactor || newF);
        const q = row.querySelector('.qty-input');
        if (q.value && oldF !== newF) q.value = Math.round(parseFloat(q.value) * oldF / newF * 1000) / 1000;
        row.dataset.prevFactor = newF;
        if (window.applyQuotePrice) applyQuotePrice(row);
    }
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
        const nice = q => ({ g: [1000, 'kg'], ml: [1000, 'L'] })[unit.toLowerCase()] && Math.abs(q) >= 1000 ? (q / 1000).toLocaleString('fr-FR') + ' ' + ({ g: 'kg', ml: 'L' })[unit.toLowerCase()] : q.toLocaleString('fr-FR') + ' ' + unit;
        hint.textContent = `= ${nice(baseQty)} (restant : ${nice(remaining)})`;
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
<script>
// Devis par ligne de la demande, du moins cher au plus cher (prix ramené à l'unité de base)
const QUOTES = @json($quotesByItem);
const suppliersSelect = () => document.getElementById('daSupplierSelect');

function packFactor(row) {
    const opt = row.querySelector('.packaging-select').selectedOptions[0];
    return opt && opt.dataset.qty ? parseFloat(opt.dataset.qty) : 1;
}
function quoteFor(row, supplierId) {
    return (QUOTES[row.dataset.itemId] || []).find(q => String(q.supplier_id) === String(supplierId));
}
/** Prix du devis pour l'emballage choisi sur la ligne (ex : prix du sac → prix du carton si on change). */
function applyQuotePrice(row) {
    const q = quoteFor(row, suppliersSelect().value);
    if (!q) return false;
    row.querySelector('.price-input').value = Math.round(q.base * packFactor(row) * 100) / 100;
    const hint = row.querySelector('.price-hint');
    hint.textContent = 'Prix du devis ' + q.supplier;
    hint.classList.remove('hidden');
    return true;
}

/** Fournisseur choisi : on coche les lignes où il est le moins cher et on reporte ses prix de devis. */
function onSupplierChange() {
    const sid = suppliersSelect().value;
    let best = 0, quoted = 0;
    document.querySelectorAll('.order-line-row').forEach(row => {
        const list = QUOTES[row.dataset.itemId] || [];
        const mine = list.find(q => String(q.supplier_id) === String(sid));
        const isBest = mine && String(list[0].supplier_id) === String(sid);
        const box = row.querySelector('.line-check');
        if (sid && list.length) {
            if (box.checked !== !!isBest) { box.checked = !!isBest; toggleLine(box); }
        }
        if (box.checked && mine) applyQuotePrice(row);
        if (mine) quoted++;
        if (isBest) best++;
    });
    const banner = document.getElementById('quoteSuggest');
    if (sid && quoted) {
        const name = suppliersSelect().selectedOptions[0].textContent.replace(/\s*★.*$/, '');
        banner.innerHTML = '<i class="fa-solid fa-star mr-1"></i><strong>' + name + '</strong> a le meilleur prix pour <strong>' + best + '</strong> produit(s) de cette demande : ces lignes sont cochées avec les prix de son devis. Créez ce BC, puis rouvrez la fenêtre pour le fournisseur suivant.';
        banner.classList.remove('hidden');
    } else {
        banner.classList.add('hidden');
    }
    syncHiddenInputs();
}

// À l'ouverture : on marque dans la liste les fournisseurs les moins chers et on présélectionne le meilleur
(function () {
    const count = {};
    document.querySelectorAll('.order-line-row').forEach(row => {
        const list = QUOTES[row.dataset.itemId] || [];
        if (list.length) count[list[0].supplier_id] = (count[list[0].supplier_id] || 0) + 1;
    });
    const sel = suppliersSelect();
    if (!sel || !Object.keys(count).length) return;
    [...sel.options].forEach(o => { if (count[o.value]) o.textContent += ' ★ moins cher pour ' + count[o.value] + ' produit(s)'; });
    const top = Object.entries(count).sort((a, b) => b[1] - a[1])[0][0];
    sel.value = top;
    if (window.jQuery && jQuery(sel).hasClass('select2-hidden-accessible')) jQuery(sel).trigger('change.select2');
    const openBtn = document.querySelector('button[onclick*="newOrderModal"]');
    if (openBtn) openBtn.addEventListener('click', () => setTimeout(onSupplierChange, 50));
})();
</script>
@endsection
