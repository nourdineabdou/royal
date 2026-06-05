@extends('layouts.catering')
@section('title', 'Facture ' . $invoice->invoice_number)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <a href="{{ route('catering.billing.index') }}" class="text-xs text-slate-500 hover:text-slate-700">
            <i class="fa-solid fa-arrow-left"></i> Retour facturation
        </a>
        <h1 class="text-xl font-bold text-slate-800 mt-1">{{ $invoice->invoice_number }}</h1>
        <p class="text-sm text-slate-500">{{ $invoice->client->name ?? '-' }} | Periode {{ str_pad((string) $invoice->period_month, 2, '0', STR_PAD_LEFT) }}/{{ $invoice->period_year }}</p>
    </div>
    <div class="text-right">
        <p class="text-xs text-slate-500">Statut</p>
        <p class="text-sm font-bold uppercase {{ $invoice->status === 'paid' ? 'text-emerald-700' : ($invoice->status === 'partial' ? 'text-amber-700' : 'text-blue-700') }}">{{ $invoice->status }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <p class="text-xs text-slate-500">Total facture</p>
        <p class="text-xl font-bold text-slate-800">{{ number_format($invoice->total_amount, 0, ',', ' ') }} MRU</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <p class="text-xs text-slate-500">Paye (valide)</p>
        <p class="text-xl font-bold text-emerald-700">{{ number_format($invoice->paid_amount, 0, ',', ' ') }} MRU</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <p class="text-xs text-slate-500">Reste a payer</p>
        <p class="text-xl font-bold text-orange-600">{{ number_format(max(0, $invoice->total_amount - $invoice->paid_amount), 0, ',', ' ') }} MRU</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Lignes facture (selon consommations)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Article</th>
                        <th class="px-4 py-3 text-right">Qte</th>
                        <th class="px-4 py-3 text-right">P.U</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoice->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-slate-700">{{ $item->label }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($item->line_total, 0, ',', ' ') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-400">Aucune ligne.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <h2 class="font-semibold text-slate-800 mb-3">Ajouter un paiement</h2>
            <form method="POST" action="{{ route('catering.billing.payments.store', $invoice) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Mode de paiement</label>
                    <select name="payment_type_id" class="w-full rounded-xl border-slate-300 text-sm" required>
                        <option value="">Selectionner</option>
                        @foreach($paymentTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Montant</label>
                    <input type="number" min="0.01" step="0.01" max="{{ max(0, $invoice->total_amount - $invoice->paid_amount) }}" name="amount" class="w-full rounded-xl border-slate-300 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Date paiement</label>
                    <input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="w-full rounded-xl border-slate-300 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Note</label>
                    <textarea name="notes" rows="2" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Optionnel"></textarea>
                </div>
                <button class="w-full h-10 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold">
                    Enregistrer (en attente validation)
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800 text-sm">Paiements</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($invoice->payments->sortByDesc('id') as $payment)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ number_format($payment->amount, 0, ',', ' ') }} MRU</p>
                            <p class="text-xs text-slate-500">{{ $payment->paymentType->name ?? '-' }} | {{ optional($payment->payment_date)->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Saisi par {{ $payment->creator->name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            @php
                                $badge = match($payment->status) {
                                    'validated' => 'bg-emerald-100 text-emerald-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-red-100 text-red-700',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ strtoupper($payment->status) }}</span>
                        </div>
                    </div>

                    @if($payment->status === 'pending' && $canValidatePayment)
                    <form method="POST" action="{{ route('catering.billing.payments.validate', [$invoice, $payment]) }}" class="mt-3">
                        @csrf
                        <button class="w-full h-9 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">
                            Valider paiement (Comptabilite)
                        </button>
                    </form>
                    @endif

                    @if($payment->status === 'validated')
                    <p class="text-xs text-emerald-700 mt-2">Valide par {{ $payment->validator->name ?? '-' }} le {{ optional($payment->validated_at)->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
                @empty
                <div class="p-6 text-center text-slate-400 text-sm">Aucun paiement pour cette facture.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
