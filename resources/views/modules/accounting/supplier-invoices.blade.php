@extends('layouts.accounting')

@section('title', 'Factures Fournisseurs — Complex Royal')
@section('accounting_content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
            <i class="fas fa-file-invoice-dollar"></i> Factures Fournisseurs
        </h1>
        <div class="text-gray-500 mt-1">Toutes les commandes d'achat confirmées, vues côté comptabilité — contrôle et paiement se font ici comme dans le module Achats.</div>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fournisseur</label>
            <select name="supplier_id" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Tous</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">État paiement</label>
            <select name="payment_status" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Tous</option>
                <option value="unpaid"  {{ request('payment_status') === 'unpaid'  ? 'selected' : '' }}>Impayé</option>
                <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partiel</option>
                <option value="paid"    {{ request('payment_status') === 'paid'    ? 'selected' : '' }}>Payé</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-5 py-2 rounded-lg">
            <i class="fas fa-filter"></i> Filtrer
        </button>
    </form>

    @if(session('success'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Fournisseur</th>
                    <th class="px-5 py-3 text-left">Facture</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-right">Montant</th>
                    <th class="px-5 py-3 text-right">Payé</th>
                    <th class="px-5 py-3 text-right">Reste</th>
                    <th class="px-5 py-3 text-center">État</th>
                    <th class="px-5 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $order)
                @php
                    if ($order->status === 'ordered') {
                        $etat = ['label' => 'En attente de livraison', 'class' => 'bg-blue-100 text-blue-700'];
                    } elseif (!$order->invoice_validated_at) {
                        $etat = ['label' => 'À contrôler', 'class' => 'bg-amber-100 text-amber-700'];
                    } elseif ($order->payment_status === 'paid') {
                        $etat = ['label' => 'Payée', 'class' => 'bg-emerald-100 text-emerald-700'];
                    } else {
                        $etat = ['label' => 'À payer', 'class' => 'bg-red-100 text-red-700'];
                    }
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="px-5 py-3 font-medium text-gray-700">{{ $order->supplier->name ?? 'Fournisseur non défini' }}</td>
                    <td class="px-5 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $order->reference }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $order->created_at->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-right font-semibold">{{ number_format($order->total_amount, 0, ',', ' ') }} MRU</td>
                    <td class="px-5 py-3 text-right text-emerald-600">{{ number_format($order->paid_amount, 0, ',', ' ') }} MRU</td>
                    <td class="px-5 py-3 text-right {{ $order->remaining_amount > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                        {{ number_format($order->remaining_amount, 0, ',', ' ') }} MRU
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $etat['class'] }}">{{ $etat['label'] }}</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <a href="{{ route('purchases.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">
                            <i class="fas fa-arrow-right"></i> Traiter
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">Aucune facture fournisseur pour l'instant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $invoices->links() }}</div>
</div>
@endsection
