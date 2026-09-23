@extends('layouts.purchases')
@section('title', 'Commande ' . $order->reference)

@section('content')

{{-- Header --}}
<div class="flex items-center gap-3 mb-6 flex-wrap">
    <a href="{{ route('purchases.orders') }}" class="text-slate-400 hover:text-slate-600">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="text-xl font-bold text-slate-800 grow">Commande {{ $order->reference }}</h2>

    <div class="flex items-center gap-2 flex-wrap">
        @if($order->status === 'pending')
        @can('purchases.orders.confirm')
        <form method="POST" action="{{ route('purchases.orders.confirm', $order) }}">
            @csrf
            <button type="submit" class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                <i class="fa-solid fa-paper-plane"></i> Confirmer & envoyer
            </button>
        </form>
        @endcan
        @can('purchases.orders.cancel')
        <form method="POST" action="{{ route('purchases.orders.cancel', $order) }}" onsubmit="return confirm('Annuler cette commande ?')">
            @csrf
            <button type="submit" class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                <i class="fa-solid fa-ban"></i> Annuler
            </button>
        </form>
        @endcan
        @elseif(in_array($order->status, ['ordered', 'partial']))
        @can('purchases.orders.receipt')
        <button onclick="document.getElementById('receiptModal').classList.remove('hidden')"
                class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
            <i class="fa-solid fa-boxes-stacked"></i> Enregistrer réception
        </button>
        @endcan
        @endif

        @if(in_array($order->status, ['received', 'partial']))
        @can('purchases.orders.receipt')
        <a href="{{ route('purchases.orders.return.create', $order) }}"
           class="flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-sm font-medium transition border border-red-200">
            <i class="fa-solid fa-rotate-left"></i> Retour fournisseur
        </a>
        @endcan
        @endif

        @if(in_array($order->status, ['received', 'partial']) && !$order->invoice_validated_at)
        @can('purchases.orders.validate-invoice')
        <form method="POST" action="{{ route('purchases.orders.validate-invoice', $order) }}">
            @csrf
            <button type="submit" class="flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                <i class="fa-solid fa-clipboard-check"></i> Valider la facture (contrôle comptable)
            </button>
        </form>
        @endcan
        @endif

        @if($order->remaining_amount > 0 && $order->invoice_validated_at)
        @can('purchases.orders.payment')
        <button onclick="document.getElementById('paymentModal').classList.remove('hidden')"
                class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
            <i class="fa-solid fa-money-bill"></i> Enregistrer paiement
        </button>
        @endcan
        @elseif($order->remaining_amount > 0 && !$order->invoice_validated_at)
        <span class="flex items-center gap-2 bg-slate-100 text-slate-400 px-4 py-2 rounded-xl text-sm font-medium">
            <i class="fa-solid fa-lock"></i> Paiement verrouillé (facture non validée)
        </span>
        @endif

        {{-- Print buttons --}}
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('purchases.orders.print.commande', $order) }}" target="_blank"
               class="flex items-center gap-2 bg-slate-600 hover:bg-slate-700 text-white px-3 py-2 rounded-xl text-sm font-medium transition">
                <i class="fa-solid fa-print"></i> Bon de commande
            </a>
            @if($order->status !== 'pending')
            <a href="{{ route('purchases.orders.print.facture', $order) }}" target="_blank"
               class="flex items-center gap-2 bg-slate-600 hover:bg-slate-700 text-white px-3 py-2 rounded-xl text-sm font-medium transition">
                <i class="fa-solid fa-file-invoice"></i> Facture
            </a>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Main content --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Articles --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-list text-orange-500"></i> Articles commandés
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-slate-400 uppercase border-b border-slate-100">
                        <tr>
                            <th class="pb-2 text-left">Produit</th>
                            <th class="pb-2 text-left">Emballage</th>
                            <th class="pb-2 text-right">Qté</th>
                            <th class="pb-2 text-right">Prix unit.</th>
                            <th class="pb-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        @php
                            $pkgQtyPerUnit = null;
                            if ($item->packaging_id) {
                                $pkgQtyPerUnit = \App\Models\ProductPackaging::where('product_id', $item->product_id)
                                                    ->where('packaging_id', $item->packaging_id)
                                                    ->value('quantity');
                            }
                        @endphp
                        <tr class="border-b border-slate-50">
                            <td class="py-2 font-medium text-slate-700">{{ $item->product->name ?? '—' }}</td>
                            <td class="py-2 text-slate-600 text-xs">
                                @if($item->packaging)
                                    <span class="inline-flex items-center gap-1 bg-orange-50 text-orange-700 px-2 py-0.5 rounded-full font-medium">
                                        <i class="fa-solid fa-box text-xs"></i>
                                        {{ $item->packaging->name }}
                                    </span>
                                    @if($pkgQtyPerUnit)
                                    <span class="block text-slate-400 mt-0.5">{{ $pkgQtyPerUnit }} {{ $item->product->unit->symbol ?? '' }}/colis</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">Vrac</span>
                                @endif
                            </td>
                            <td class="py-2 text-right text-slate-600">
                                {{ $item->quantity }}
                                @if($item->packaging)
                                    <span class="text-xs text-slate-400"> colis</span>
                                    @if($pkgQtyPerUnit)
                                    <span class="block text-xs text-emerald-600">→ {{ number_format($item->quantity * $pkgQtyPerUnit, 2, ',', ' ') }} {{ $item->product->unit->symbol ?? '' }}</span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400"> {{ $item->product->unit->symbol ?? '' }}</span>
                                @endif
                            </td>
                            <td class="py-2 text-right text-slate-600">
                                @if($item->price !== null)
                                    {{ number_format($item->price, 0, ',', ' ') }} MRU
                                @else
                                    <span class="text-slate-300 text-xs">— livraison —</span>
                                @endif
                            </td>
                            <td class="py-2 text-right font-semibold">
                                @if($item->total !== null)
                                    {{ number_format($item->total, 0, ',', ' ') }} MRU
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200">
                            <td colspan="3" class="pt-3 text-right font-bold text-slate-700">Total :</td>
                            <td class="pt-3 text-right font-bold text-xl {{ $order->total_amount > 0 ? 'text-orange-600' : 'text-slate-300' }}">
                                @if($order->total_amount > 0)
                                    {{ number_format($order->total_amount, 0, ',', ' ') }} MRU
                                @else
                                    À confirmer à la livraison
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Réceptions --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-emerald-500"></i> Réceptions
            </h3>
            @forelse($order->goodsReceipts as $receipt)
            <div class="border border-slate-100 rounded-xl p-4 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-500 font-medium">
                        <i class="fa-solid fa-calendar-day mr-1"></i>
                        {{ \Carbon\Carbon::parse($receipt->received_at ?? $receipt->created_at)->format('d/m/Y H:i') }}
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full font-medium">
                            {{ $receipt->stock->name ?? 'Stock inconnu' }}
                        </span>
                        <a href="{{ route('purchases.orders.print.livraison', [$order, $receipt]) }}" target="_blank"
                           class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-full font-medium transition flex items-center gap-1">
                            <i class="fa-solid fa-print"></i> Imprimer
                        </a>
                    </div>
                </div>
                <table class="w-full text-xs">
                    <thead class="text-slate-400">
                        <tr>
                            <th class="text-left pb-1">Produit</th>
                            <th class="text-right pb-1">Qté reçue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipt->items as $ri)
                        <tr class="border-t border-slate-50">
                            <td class="py-1 text-slate-700">{{ $ri->product->name ?? '—' }}</td>
                            <td class="py-1 text-right font-semibold text-emerald-700">
                                {{ $ri->quantity }} {{ $ri->product->unit->symbol ?? '' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @empty
            <p class="text-slate-400 text-sm text-center py-4">Aucune réception enregistrée</p>
            @endforelse
        </div>

        {{-- Retours fournisseur --}}
        @if($order->supplierReturns->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-rotate-left text-red-500"></i> Retours fournisseur
            </h3>
            @foreach($order->supplierReturns as $ret)
            <div class="border border-slate-100 rounded-xl p-4 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-500 font-medium">
                        <i class="fa-solid fa-calendar-day mr-1"></i>
                        {{ $ret->returned_at->format('d/m/Y H:i') }}
                        <span class="ml-2 font-mono text-red-600">{{ $ret->reference }}</span>
                    </span>
                    <span class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded-full font-medium">
                        − {{ number_format($ret->total_amount, 0, ',', ' ') }} MRU
                    </span>
                </div>
                @if($ret->reason)<p class="text-xs text-slate-500 mb-2">{{ $ret->reason }}</p>@endif
                <table class="w-full text-xs">
                    <tbody>
                        @foreach($ret->items as $ri)
                        <tr class="border-t border-slate-50">
                            <td class="py-1 text-slate-700">{{ $ri->product->name ?? '—' }}</td>
                            <td class="py-1 text-right font-semibold text-red-600">{{ $ri->quantity }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Paiements --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-money-bill-wave text-blue-500"></i> Paiements
            </h3>
            @forelse($order->supplierPayments as $pay)
            <div class="flex items-center justify-between px-4 py-2 border border-slate-100 rounded-xl mb-2">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ number_format($pay->amount, 0, ',', ' ') }} MRU</p>
                    <p class="text-xs text-slate-400">{{ $pay->paymentType->name ?? '—' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($pay->paid_at ?? $pay->created_at)->format('d/m/Y') }}</span>
                    <a href="{{ route('purchases.orders.print.paiement', [$order, $pay]) }}" target="_blank"
                       class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-full font-medium transition flex items-center gap-1">
                        <i class="fa-solid fa-print"></i> Reçu
                    </a>
                </div>
            </div>
            @empty
            <p class="text-slate-400 text-sm text-center py-4">Aucun paiement enregistré</p>
            @endforelse
        </div>

    </div>

    {{-- Sidebar: order info --}}
    <div class="space-y-5">

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4">Détails</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Fournisseur</dt>
                    <dd class="font-medium text-slate-800">{{ $order->supplier->name ?? '—' }}</dd>
                </div>
                @if($order->purchase_request_id)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Demande d'achat</dt>
                    <dd class="font-medium">
                        <a href="{{ route('purchases.requests.show', $order->purchase_request_id) }}" class="text-orange-600 hover:text-orange-700">
                            {{ $order->purchaseRequest->reference ?? '#' . $order->purchase_request_id }}
                        </a>
                    </dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-slate-500">Statut livraison</dt>
                    <dd>
                        @switch($order->status)
                            @case('pending')  <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700 font-medium">En attente</span> @break
                            @case('ordered')  <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 font-medium">Envoyée</span> @break
                            @case('partial')  <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 font-medium">Reçue partiellement</span> @break
                            @case('received') <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700 font-medium">Reçue</span> @break
                            @case('cancelled')<span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 font-medium">Annulée</span> @break
                        @endswitch
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Contrôle comptable</dt>
                    <dd>
                        @if($order->invoice_validated_at)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-indigo-100 text-indigo-700 font-medium" title="Par {{ $order->invoiceValidatedBy->name ?? '—' }} le {{ $order->invoice_validated_at->format('d/m/Y H:i') }}">
                            Facture validée
                        </span>
                        @else
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-500 font-medium">En attente</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Statut paiement</dt>
                    <dd>
                        @switch($order->payment_status)
                            @case('unpaid')  <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 font-medium">Impayé</span> @break
                            @case('partial') <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 font-medium">Partiel</span> @break
                            @case('paid')    <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700 font-medium">Payé</span> @break
                        @endswitch
                    </dd>
                </div>
                <div class="flex justify-between border-t pt-3">
                    <dt class="text-slate-500">Total commandé</dt>
                    <dd class="font-bold {{ $order->total_amount > 0 ? 'text-slate-800' : 'text-slate-400 italic text-xs' }}">
                        {{ $order->total_amount > 0 ? number_format($order->total_amount, 0, ',', ' ') . ' MRU' : 'À confirmer à la livraison' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Payé</dt>
                    <dd class="font-medium text-emerald-600">{{ number_format($order->paid_amount, 0, ',', ' ') }} MRU</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Reste à payer</dt>
                    <dd class="font-bold {{ $order->remaining_amount > 0 ? 'text-red-600' : 'text-slate-400' }}">
                        {{ number_format($order->remaining_amount, 0, ',', ' ') }} MRU
                    </dd>
                </div>
                <div class="flex justify-between border-t pt-3">
                    <dt class="text-slate-500">Créée le</dt>
                    <dd class="text-slate-600">{{ $order->created_at->format('d/m/Y') }}</dd>
                </div>
            </dl>
        </div>

        @if($order->total_amount > 0)
        {{-- Progress bar --}}
        @php $pct = min(100, ($order->paid_amount / $order->total_amount) * 100); @endphp
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-3">Progression paiement</h3>
            <div class="h-4 bg-slate-100 rounded-full overflow-hidden mb-2">
                <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-slate-500">
                <span>Payé: {{ number_format($pct, 0) }}%</span>
                <span>{{ number_format($order->remaining_amount, 0, ',', ' ') }} MRU restant</span>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- RECEIPT MODAL --}}
@php $remainingItems = $order->items->filter(fn($i) => $i->remaining_quantity > 0.001)->values(); @endphp
@if(in_array($order->status, ['ordered', 'partial']) && $remainingItems->count())
<div id="receiptModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white">
            <h3 class="font-bold text-slate-800">Enregistrer une réception</h3>
            <button onclick="document.getElementById('receiptModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('purchases.orders.receipt', $order) }}" class="p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Fournisseur <span class="text-red-500">*</span></label>
                <select name="supplier_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    <option value="">— Choisir un fournisseur —</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ $order->supplier_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
                @if(!$order->supplier_id)
                <p class="text-xs text-slate-400 mt-1">Obligatoire à la réception : c'est le fournisseur qui a effectivement livré cette commande.</p>
                @endif
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dépôt de stock <span class="text-red-500">*</span></label>
                <select name="stock_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    <option value="">— Choisir un stock —</option>
                    @foreach($stocks as $stock)
                    <option value="{{ $stock->id }}">{{ $stock->name }}</option>
                    @endforeach
                </select>
            </div>

            <p class="text-xs text-slate-500 mb-3">Le prix a déjà été fixé sur le Bon de Commande. Cette étape ne sert qu'à confirmer ce qui est physiquement arrivé — vous pouvez recevoir en plusieurs fois si le fournisseur livre partiellement.</p>
                <table class="w-full text-sm mb-4" id="receiptTable">
                <thead class="text-xs text-slate-400 uppercase border-b">
                    <tr>
                        <th class="pb-2 text-left">Produit</th>
                        <th class="pb-2 text-left">Emballage</th>
                        <th class="pb-2 text-right">Qté commandée</th>
                        <th class="pb-2 text-right">Qté restante</th>
                        <th class="pb-2 text-right">Qté reçue</th>
                        <th class="pb-2 text-right">Valeur</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($remainingItems as $i => $item)
                    @php
                        $ppReceipt = null;
                        if ($item->packaging_id) {
                            $ppReceipt = \App\Models\ProductPackaging::where('product_id', $item->product_id)
                                            ->where('packaging_id', $item->packaging_id)
                                            ->value('quantity');
                        }
                    @endphp
                    <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
                    <input type="hidden" name="items[{{ $i }}][order_item_id]" value="{{ $item->id }}">
                    <tr class="border-b border-slate-50 receipt-row" data-price="{{ $item->price ?? 0 }}">
                        <td class="py-2 font-medium text-slate-700">{{ $item->product->name ?? '—' }}</td>
                        <td class="py-2 text-xs">
                            @if($item->packaging)
                                <span class="inline-flex items-center gap-1 bg-orange-50 text-orange-700 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-box text-xs"></i> {{ $item->packaging->name }}
                                </span>
                                @if($ppReceipt)
                                <span class="block text-slate-400 mt-0.5">1 colis = {{ $ppReceipt }} {{ $item->product->unit->symbol ?? '' }}</span>
                                @endif
                            @else
                                <span class="text-slate-400">Vrac</span>
                            @endif
                        </td>
                        <td class="py-2 text-right text-slate-500">
                            {{ $item->quantity }}
                            {{ $item->packaging ? 'colis' : ($item->product->unit->symbol ?? '') }}
                        </td>
                        <td class="py-2 text-right text-amber-600 font-semibold">
                            {{ rtrim(rtrim(number_format($item->remaining_quantity,2),'0'),'.') }}
                            {{ $item->packaging ? 'colis' : ($item->product->unit->symbol ?? '') }}
                        </td>
                        <td class="py-2 text-right">
                            <input type="number" name="items[{{ $i }}][quantity]" value="{{ $item->remaining_quantity }}"
                                   step="0.01" min="0.01" max="{{ $item->remaining_quantity }}"
                                   oninput="recalcReceipt()"
                                   class="w-24 text-right border border-slate-200 rounded-xl px-2 py-1.5 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none receipt-qty">
                            @if($item->packaging)
                            <span class="block text-xs text-slate-400 mt-0.5">nb de colis</span>
                            @endif
                        </td>
                        <td class="py-2 text-right font-semibold text-slate-700 receipt-line-total">0 MRU</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200">
                        <td colspan="5" class="pt-3 text-right font-bold text-slate-700 text-sm">Valeur de cette livraison :</td>
                        <td class="pt-3 text-right font-bold text-orange-600" id="receiptGrandTotal">0 MRU</td>
                    </tr>
                </tfoot>
                </table>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('receiptModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-emerald-500 hover:bg-emerald-600 text-white font-medium transition">
                    Valider la réception
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- PAYMENT MODAL --}}
@if($order->remaining_amount > 0)
<div id="paymentModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Enregistrer un paiement</h3>
            <button onclick="document.getElementById('paymentModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('purchases.orders.payment', $order) }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Mode de paiement <span class="text-red-500">*</span></label>
                <select name="payment_type_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    <option value="">— Choisir —</option>
                    @foreach($paymentTypes as $pt)
                    <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">
                    Montant (MRU) <span class="text-slate-400 font-normal">max: {{ number_format($order->remaining_amount, 0, ',', ' ') }}</span>
                </label>
                <input type="number" name="amount" step="0.01" min="0.01" max="{{ $order->remaining_amount }}"
                       value="{{ $order->remaining_amount }}" required
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-orange-500 hover:bg-orange-600 text-white font-medium transition">
                    Valider le paiement
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function recalcReceipt() {
    let grand = 0;
    document.querySelectorAll('#receiptTable .receipt-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('.receipt-qty')?.value) || 0;
        const price = parseFloat(row.dataset.price) || 0;
        const total = qty * price;
        const totalEl = row.querySelector('.receipt-line-total');
        if (totalEl) totalEl.textContent = total.toLocaleString('fr-FR') + ' MRU';
        grand += total;
    });
    const grandEl = document.getElementById('receiptGrandTotal');
    if (grandEl) grandEl.textContent = grand.toLocaleString('fr-FR') + ' MRU';
}
document.addEventListener('DOMContentLoaded', recalcReceipt);
</script>

@endsection
