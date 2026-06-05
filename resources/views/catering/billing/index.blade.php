@extends('layouts.catering')
@section('title', 'Facturation')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm p-4 border border-slate-100">
        <p class="text-xs text-slate-500">Facture (mois)</p>
        <p class="text-xl font-bold text-slate-800">{{ number_format($monthlyTotals['billed'] ?? 0, 0, ',', ' ') }} MRU</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 border border-slate-100">
        <p class="text-xs text-slate-500">Encaisse valide (mois)</p>
        <p class="text-xl font-bold text-emerald-700">{{ number_format($monthlyTotals['paid'] ?? 0, 0, ',', ' ') }} MRU</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 border border-slate-100">
        <p class="text-xs text-slate-500">Reste (mois)</p>
        <p class="text-xl font-bold text-orange-600">{{ number_format(max(0, ($monthlyTotals['billed'] ?? 0) - ($monthlyTotals['paid'] ?? 0)), 0, ',', ' ') }} MRU</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-5 mb-6 border border-slate-100">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-semibold text-slate-800">Generer une facture mensuelle (contrat entreprise)</h2>
    </div>

    <form method="POST" action="{{ route('catering.billing.generate') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs text-slate-500 mb-1">Contrat</label>
            <select name="contract_id" class="w-full rounded-xl border-slate-300 text-sm" required>
                <option value="">Selectionner</option>
                @foreach($contracts as $contract)
                <option value="{{ $contract->id }}">{{ $contract->client->name ?? 'Client' }} ({{ $contract->start_date->format('d/m/Y') }} - {{ $contract->end_date->format('d/m/Y') }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Annee</label>
            <input type="number" min="2020" max="2100" name="year" value="{{ $year }}" class="w-full rounded-xl border-slate-300 text-sm" required>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Mois</label>
            <input type="number" min="1" max="12" name="month" value="{{ $month }}" class="w-full rounded-xl border-slate-300 text-sm" required>
        </div>
        <button class="h-10 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold">
            Generer
        </button>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-100">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800">Factures clients entreprise</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100 text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-left">Facture</th>
                    <th class="px-4 py-3 text-left">Client</th>
                    <th class="px-4 py-3 text-center">Periode</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Paye</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $invoice->invoice_number }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $invoice->client->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-slate-600">{{ str_pad((string) $invoice->period_month, 2, '0', STR_PAD_LEFT) }}/{{ $invoice->period_year }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-800">{{ number_format($invoice->total_amount, 0, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ number_format($invoice->paid_amount, 0, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $badge = match($invoice->status) {
                                'paid' => 'bg-emerald-100 text-emerald-700',
                                'partial' => 'bg-amber-100 text-amber-700',
                                'issued' => 'bg-blue-100 text-blue-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $badge }}">{{ strtoupper($invoice->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('catering.billing.show', $invoice) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 text-xs font-semibold">
                            <i class="fa-solid fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-400">Aucune facture generee.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
