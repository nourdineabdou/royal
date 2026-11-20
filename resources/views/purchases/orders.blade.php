@extends('layouts.purchases')
@section('title', 'Commandes d\'achat')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold text-slate-800">Commandes d'achat</h2>
    @can('purchases.orders.create')
    <a href="{{ route('purchases.orders.create') }}"
       class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium shadow-sm transition">
        <i class="fa-solid fa-plus"></i> Nouvelle commande
    </a>
    @endcan
</div>

{{-- À traiter : ce qui attend une action --}}
@php
    $todoLabels = [
        'to_confirm' => ['À confirmer', 'fa-paper-plane', 'Créés, pas encore envoyés au fournisseur'],
        'to_receive' => ['À réceptionner', 'fa-truck', 'Envoyés, livraison attendue'],
        'to_check'   => ['Facture à contrôler', 'fa-clipboard-check', 'Reçus, facture pas encore validée'],
        'to_pay'     => ['À payer', 'fa-money-bill', 'Facture validée, pas encore payée'],
    ];
    $keepQ = fn (array $x) => array_filter(array_merge(request()->only('supplier_id', 'status', 'payment_status', 'packaging_type', 'invoice', 'from', 'to', 'todo'), $x));
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    @foreach($todoLabels as $key => [$label, $icon, $hint])
        @php $active = request('todo') === $key; @endphp
        <a href="{{ route('purchases.orders', $keepQ(['todo' => $active ? null : $key])) }}"
           class="rounded-2xl border p-3 transition {{ $active ? 'bg-slate-800 border-slate-800 text-white' : 'bg-white border-slate-200 hover:border-orange-400' }}">
            <p class="text-xs font-semibold {{ $active ? 'text-slate-200' : 'text-slate-500' }}"><i class="fa-solid {{ $icon }} mr-1"></i>{{ $label }}</p>
            <p class="text-2xl font-bold {{ $active ? 'text-white' : ($todoCounts[$key] ? 'text-orange-600' : 'text-slate-300') }}">{{ $todoCounts[$key] }}</p>
            <p class="text-[11px] {{ $active ? 'text-slate-300' : 'text-slate-400' }}">{{ $hint }}</p>
        </a>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-2xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
    @if(request('todo'))<input type="hidden" name="todo" value="{{ request('todo') }}">@endif
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Fournisseur</label>
        <select name="supplier_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <option value="">Tous</option>
            @foreach($suppliers as $s)
            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Statut</label>
        <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <option value="">Tous</option>
            <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>En attente</option>
            <option value="ordered"   {{ request('status') == 'ordered'   ? 'selected' : '' }}>Envoyée</option>
            <option value="partial"   {{ request('status') == 'partial'   ? 'selected' : '' }}>Reçue partiellement</option>
            <option value="received"  {{ request('status') == 'received'  ? 'selected' : '' }}>Reçue</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Paiement</label>
        <select name="payment_status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <option value="">Tous</option>
            <option value="unpaid"  {{ request('payment_status') == 'unpaid'  ? 'selected' : '' }}>Impayé</option>
            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partiel</option>
            <option value="paid"    {{ request('payment_status') == 'paid'    ? 'selected' : '' }}>Payé</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Type produit</label>
        <select name="packaging_type" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <option value="">Tous</option>
            <option value="packaged" {{ request('packaging_type') == 'packaged' ? 'selected' : '' }}>Avec emballage</option>
            <option value="bulk" {{ request('packaging_type') == 'bulk' ? 'selected' : '' }}>Vrac uniquement</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Facture</label>
        <select name="invoice" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <option value="">Toutes</option>
            <option value="to_check"  {{ request('invoice') == 'to_check'  ? 'selected' : '' }}>Non validée</option>
            <option value="validated" {{ request('invoice') == 'validated' ? 'selected' : '' }}>Validée</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Du</label>
        <input type="date" name="from" value="{{ request('from') }}" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Au</label>
        <input type="date" name="to" value="{{ request('to') }}" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
    </div>
    <button type="submit" class="flex items-center gap-2 bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition hover:bg-slate-800">
        <i class="fa-solid fa-magnifying-glass"></i> Filtrer
    </button>
    <a href="{{ route('purchases.orders') }}" class="text-xs text-slate-400 hover:text-slate-600 py-2">Réinitialiser</a>
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr class="text-xs text-slate-400 uppercase">
                    <th class="px-5 py-3 text-left">Référence</th>
                    <th class="px-5 py-3 text-left">Demande d'achat</th>
                    <th class="px-5 py-3 text-left">Fournisseur</th>
                    <th class="px-5 py-3 text-right">Total</th>
                    <th class="px-5 py-3 text-right">Payé</th>
                    <th class="px-5 py-3 text-right">Reste</th>
                    <th class="px-5 py-3 text-center">Statut livraison</th>
                    <th class="px-5 py-3 text-center">Statut paiement</th>
                    <th class="px-5 py-3 text-center">Type</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $packagedCount = $order->items->whereNotNull('packaging_id')->count();
                    $bulkCount = $order->items->whereNull('packaging_id')->count();
                @endphp
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                    <td class="px-5 py-3 font-mono text-xs font-medium text-orange-600">{{ $order->reference }}</td>
                    <td class="px-5 py-3 text-xs">
                        @if($order->purchase_request_id)
                        <a href="{{ route('purchases.requests.show', $order->purchase_request_id) }}" class="text-slate-600 hover:text-orange-600 font-mono">
                            {{ $order->purchaseRequest->reference ?? '#' . $order->purchase_request_id }}
                        </a>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 font-medium text-slate-700">{{ $order->supplier->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-semibold">{{ number_format($order->total_amount, 0, ',', ' ') }}</td>
                    <td class="px-5 py-3 text-right text-emerald-600">{{ number_format($order->paid_amount, 0, ',', ' ') }}</td>
                    <td class="px-5 py-3 text-right {{ $order->remaining_amount > 0 ? 'text-red-600 font-bold' : 'text-slate-400' }}">
                        {{ number_format($order->remaining_amount, 0, ',', ' ') }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        @switch($order->status)
                            @case('pending')  <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700 font-medium">En attente</span> @break
                            @case('ordered')  <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700 font-medium">Envoyée</span> @break
                            @case('partial')  <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700 font-medium">Reçue partiellement</span> @break
                            @case('received') <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700 font-medium">Reçue</span> @break
                            @case('cancelled')<span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700 font-medium">Annulée</span> @break
                            @default          <span class="px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-600">{{ $order->status }}</span>
                        @endswitch
                    </td>
                    <td class="px-5 py-3 text-center">
                        @switch($order->payment_status)
                            @case('unpaid')  <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700 font-medium">Impayé</span>  @break
                            @case('partial') <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700 font-medium">Partiel</span> @break
                            @case('paid')    <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700 font-medium">Payé</span> @break
                        @endswitch
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($packagedCount > 0)
                            <span class="px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-700 font-medium">Colis {{ $packagedCount }}</span>
                        @endif
                        @if($bulkCount > 0)
                            <span class="px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-700 font-medium">Vrac {{ $bulkCount }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-400 text-xs">{{ $order->created_at->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-center">
                        <a href="{{ route('purchases.orders.show', $order) }}"
                           class="w-8 h-8 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-600 inline-flex items-center justify-center transition">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="px-5 py-10 text-center text-slate-400">Aucune commande trouvée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $orders->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
