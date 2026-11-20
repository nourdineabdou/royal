@extends('layouts.purchases')
@section('title', 'Commande ' . $order->reference)

@section('content')

{{-- Header --}}
<div class="flex items-center gap-3 mb-6 flex-wrap">
    <a href="{{ route('purchases.orders') }}" class="text-slate-400 hover:text-slate-600">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="text-xl font-bold text-slate-800 grow">Commande {{ $order->reference }}</h2>
    @if($order->status === 'pending')
    <p class="w-full order-last text-sm bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-2">
        <i class="fa-solid fa-circle-info mr-1"></i>
        @can('purchases.orders.confirm')
            Étape suivante : vérifiez le fournisseur et les prix, puis cliquez sur <strong>« Confirmer & envoyer »</strong> quand la commande est passée au fournisseur.
        @else
            <strong>En attente de la confirmation du responsable des achats.</strong> Vous pouvez encore corriger les prix et joindre le devis ;
            une fois confirmée, la commande passe au magasinier pour la réception.
        @endcan
        Le bouton <strong>« Réceptionner (BL) »</strong> apparaîtra ensuite ici et dans le menu <strong>Réceptions (BL)</strong>.
    </p>
    @endif

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
        <a href="{{ route('purchases.receptions.create', $order) }}"
           class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
            <i class="fa-solid fa-truck-ramp-box"></i> Réceptionner (BL)
        </a>
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

        {{-- Prix et documents ──────────────────────────────────────────── --}}
        @php $canPrice = auth()->user()->can('purchases.orders.create') && !$order->invoice_validated_at; @endphp
        @if($order->hasMissingPrices())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center gap-3">
            <p class="flex-1 text-sm text-amber-800"><i class="fa-solid fa-triangle-exclamation mr-1"></i>
                <strong>Prix à saisir</strong> pour {{ $order->items->whereNull('price')->count() }} produit(s). Saisissez-les quand vous avez le devis ou la facture : le total et le reste à payer se mettront à jour.</p>
            @if($canPrice)
            <button type="button" onclick="document.getElementById('pricesModal').classList.remove('hidden')" class="shrink-0 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl text-sm font-semibold">
                <i class="fa-solid fa-tags mr-1"></i> Saisir les prix
            </button>
            @endif
        </div>
        @elseif($canPrice)
        <div class="text-right -mt-2">
            <button type="button" onclick="document.getElementById('pricesModal').classList.remove('hidden')" class="text-xs text-slate-500 hover:text-orange-600 font-semibold"><i class="fa-solid fa-pen mr-1"></i>Modifier les prix</button>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="font-semibold text-slate-700 flex items-center gap-2"><i class="fa-solid fa-paperclip text-slate-500"></i> Documents <span class="text-xs font-normal text-slate-400">devis, facture, BL…</span></h3>
            </div>
            @if($order->attachments->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                @foreach($order->attachments as $doc)
                <div class="border border-slate-200 rounded-xl overflow-hidden flex flex-col">
                    <a href="{{ route('purchases.orders.attachments.show', $doc) }}" target="_blank" class="block bg-slate-50 h-28 flex items-center justify-center overflow-hidden">
                        @if($doc->isImage())
                            <img src="{{ route('purchases.orders.attachments.show', $doc) }}" alt="{{ $doc->original_name }}" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <i class="fa-solid fa-file-pdf text-4xl text-red-500"></i>
                        @endif
                    </a>
                    <div class="p-2 text-xs">
                        <span class="inline-block px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 font-semibold">{{ $doc->type_label }}</span>
                        <p class="text-slate-600 truncate mt-1" title="{{ $doc->original_name }}">{{ $doc->original_name }}</p>
                        <p class="text-slate-400">{{ $doc->created_at->format('d/m/Y H:i') }} · {{ $doc->uploader->name ?? '—' }}</p>
                        @if($doc->uploaded_by === auth()->id() || auth()->user()->can('purchases.orders.create'))
                        <form method="POST" action="{{ route('purchases.orders.attachments.destroy', $doc) }}" onsubmit="return confirm('Supprimer ce document ?')" class="mt-1">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700">Supprimer</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-400 mb-4">Aucun document joint.</p>
            @endif

            @if(auth()->user()->canAny(['purchases.orders.create', 'purchases.orders.receipt', 'purchases.orders.validate-invoice']))
            <form method="POST" action="{{ route('purchases.orders.attachments.store', $order) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2 sm:items-center rounded-xl border border-dashed border-slate-300 p-3 bg-slate-50">
                @csrf
                <select name="type" class="border border-slate-200 rounded-xl px-3 py-2 text-sm sm:w-40">
                    @foreach(\App\Models\PurchaseOrderAttachment::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
                <input type="file" name="files[]" multiple required accept="image/*,application/pdf" class="flex-1 text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-orange-100 file:text-orange-700 file:font-semibold">
                <button class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-semibold"><i class="fa-solid fa-upload mr-1"></i> Joindre</button>
            </form>
            <p class="text-[11px] text-slate-400 mt-1">Sur téléphone : « Choisir un fichier » propose l'appareil photo. Photo (JPG, PNG, HEIC) ou PDF, 15 Mo maximum.</p>
            @endif
        </div>

        {{-- Réceptions --}}        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-emerald-500"></i> Réceptions
            </h3>
            @forelse($order->goodsReceipts as $receipt)
            <div class="border border-slate-100 rounded-xl p-4 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-500 font-medium">
                        <i class="fa-solid fa-calendar-day mr-1"></i>
                        {{ \Carbon\Carbon::parse($receipt->received_at ?? $receipt->created_at)->format('d/m/Y H:i') }}
                        · <strong>{{ $receipt->reference }}</strong>
                        @if($receipt->supplier_delivery_note) · BL {{ $receipt->supplier_delivery_note }} @endif
                        @if($receipt->receivedBy) · {{ $receipt->receivedBy->name }} @endif
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
                                {{ rtrim(rtrim(number_format($ri->quantity, 2, ',', ' '), '0'), ',') }} {{ $ri->product->unit->symbol ?? '' }}
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

{{-- MODAL : saisie des prix (par emballage commandé) --}}
@if(auth()->user()->can('purchases.orders.create') && !$order->invoice_validated_at)
<div id="pricesModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white">
            <h3 class="font-bold text-slate-800">Prix du bon de commande</h3>
            <button type="button" onclick="document.getElementById('pricesModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('purchases.orders.prices.update', $order) }}" class="p-6 space-y-3" id="pricesForm">
            @csrf
            <p class="text-xs text-slate-500">Prix donné par le devis ou la facture, <strong>par emballage commandé</strong>. Laissez vide un prix encore inconnu.</p>
            @foreach($order->items as $item)
            <div class="flex items-center gap-3 border-b border-slate-50 pb-3 price-row" data-qty="{{ (float) $item->quantity }}">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $item->product->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', ' '), '0'), ',') }} × {{ $item->packaging->name ?? ($item->product->unit->symbol ?? 'unité') }}</p>
                </div>
                <div class="text-right">
                    <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-orange-500">
                        <input type="number" step="0.01" min="0" name="prices[{{ $item->id }}]" value="{{ $item->price !== null ? (float) $item->price : '' }}" placeholder="Prix"
                               class="w-28 text-right px-2 py-1.5 text-sm focus:outline-none" oninput="recalcPrices()">
                        <span class="px-2 text-[11px] text-slate-500 bg-slate-50 self-stretch flex items-center whitespace-nowrap">MRU / {{ $item->packaging->name ?? ($item->product->unit->symbol ?? 'unité') }}</span>
                    </div>
                    <p class="line-total text-[11px] text-slate-500 mt-1"></p>
                </div>
            </div>
            @endforeach
            <div class="flex items-center justify-between pt-1">
                <span class="text-sm font-semibold text-slate-600">Total</span>
                <span id="pricesTotal" class="text-xl font-bold text-orange-600"></span>
            </div>
            <button class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-semibold text-sm"><i class="fa-solid fa-save mr-1"></i> Enregistrer les prix</button>
        </form>
    </div>
</div>
<script>
function recalcPrices() {
    let total = 0;
    document.querySelectorAll('#pricesForm .price-row').forEach(r => {
        const p = parseFloat(r.querySelector('input').value);
        const t = isNaN(p) ? null : p * parseFloat(r.dataset.qty);
        r.querySelector('.line-total').textContent = t === null ? 'prix inconnu' : '= ' + t.toLocaleString('fr-FR', { maximumFractionDigits: 2 }) + ' MRU';
        total += t || 0;
    });
    document.getElementById('pricesTotal').textContent = total.toLocaleString('fr-FR', { maximumFractionDigits: 2 }) + ' MRU';
}
recalcPrices();
</script>
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



@endsection
