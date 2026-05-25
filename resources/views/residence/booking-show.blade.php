@extends('layouts.residence')
@section('title', 'Réservation #' . $booking->id)

@section('content')

@php
    $nights = \Carbon\Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
    $paid   = $booking->paid_amount ?? 0;
    $remaining = max(0, $booking->total_amount - $paid);
    $pct = $booking->total_amount > 0 ? min(100, round(($paid / $booking->total_amount) * 100)) : 0;

    $statusMap = [
        'pending'     => ['label'=>'En attente',  'class'=>'bg-yellow-100 text-yellow-700 border-yellow-200'],
        'checked_in'  => ['label'=>'Check-in',    'class'=>'bg-violet-100 text-violet-700 border-violet-200'],
        'checked_out' => ['label'=>'Check-out',   'class'=>'bg-emerald-100 text-emerald-700 border-emerald-200'],
        'cancelled'   => ['label'=>'Annulé',      'class'=>'bg-red-100 text-red-500 border-red-200'],
    ];
    $s = $statusMap[$booking->status] ?? ['label'=>$booking->status,'class'=>'bg-slate-100 text-slate-500 border-slate-200'];
@endphp

{{-- Header --}}
<div class="mb-6 flex items-start justify-between flex-wrap gap-3">
    <div>
        <a href="{{ route('residence.bookings') }}" class="text-sm text-slate-500 hover:text-violet-600 flex items-center gap-1 mb-1">
            <i class="fa-solid fa-arrow-left text-xs"></i> Retour
        </a>
        <h2 class="text-xl font-bold text-slate-800">
            Réservation #{{ $booking->id }}
            <span class="ml-2 px-3 py-0.5 rounded-full text-sm font-medium border {{ $s['class'] }}">{{ $s['label'] }}</span>
        </h2>
        <p class="text-sm text-slate-500 mt-0.5">Chambre {{ $booking->room->number }} · {{ $booking->room->roomType->name ?? '?' }}</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        @if($booking->status === 'pending')
            @can('residence.bookings.checkin')
            <form method="POST" action="{{ route('residence.bookings.checkin', $booking) }}">
                @csrf
                <button type="submit" class="flex items-center gap-1 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-medium transition">
                    <i class="fa-solid fa-right-to-bracket"></i> Check-in
                </button>
            </form>
            @endcan
            @can('residence.bookings.cancel')
            <form method="POST" action="{{ route('residence.bookings.cancel', $booking) }}"
                  onsubmit="return confirm('Annuler cette réservation ?')">
                @csrf
                <button type="submit" class="flex items-center gap-1 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-sm font-medium border border-red-200 transition">
                    <i class="fa-solid fa-ban"></i> Annuler
                </button>
            </form>
            @endcan
        @elseif($booking->status === 'checked_in')
            @can('residence.bookings.checkout')
            <form method="POST" action="{{ route('residence.bookings.checkout', $booking) }}"
                  onsubmit="return confirm('Effectuer le check-out ?')">
                @csrf
                <button type="submit" class="flex items-center gap-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition">
                    <i class="fa-solid fa-right-from-bracket"></i> Check-out
                </button>
            </form>
            @endcan
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left column: details + extras --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Client info --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user text-violet-500"></i> Client
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Nom</p>
                    <p class="font-semibold text-slate-800">{{ $booking->customer_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Téléphone</p>
                    <p class="text-slate-700">{{ $booking->customer_phone ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">N° Pièce d'identité</p>
                    <p class="text-slate-700">{{ $booking->identity_number ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Arrivée</p>
                    <p class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($booking->check_in)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Départ</p>
                    <p class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Durée</p>
                    <p class="font-medium text-slate-800">{{ $nights }} nuit{{ $nights > 1 ? 's' : '' }}</p>
                </div>
                @if($booking->num_guests)
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Nb personnes</p>
                    <p class="text-slate-700">{{ $booking->num_guests }}</p>
                </div>
                @endif
                @if($booking->notes)
                <div class="col-span-2 md:col-span-3">
                    <p class="text-xs text-slate-400 mb-0.5">Notes</p>
                    <p class="text-slate-700 italic">{{ $booking->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Extras / Additional charges --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-violet-500"></i> Suppléments
                </h3>
                @if(in_array($booking->status, ['pending','checked_in']))
                @can('residence.bookings.edit')
                <button onclick="document.getElementById('extraModal').classList.remove('hidden')"
                        class="text-xs text-violet-600 hover:text-violet-800 font-medium flex items-center gap-1">
                    <i class="fa-solid fa-plus"></i> Ajouter
                </button>
                @endcan
                @endif
            </div>

            @if($booking->details->isEmpty())
            <p class="text-sm text-slate-400 italic">Aucun supplément</p>
            @else
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-400 uppercase border-b border-slate-100">
                    <tr>
                        <th class="pb-2 text-left">Désignation</th>
                        <th class="pb-2 text-left text-slate-400">Description</th>
                        <th class="pb-2 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->details as $d)
                    <tr class="border-b border-slate-50">
                        <td class="py-2 font-medium text-slate-700">{{ $d->title }}</td>
                        <td class="py-2 text-slate-400 text-xs">{{ $d->description ?: '—' }}</td>
                        <td class="py-2 text-right text-violet-700 font-medium">{{ number_format($d->amount, 0, ',', ' ') }} MRU</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

    </div>

    {{-- Right column: payment --}}
    <div class="space-y-5">

        {{-- Payment summary --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-wallet text-violet-500"></i> Paiement
            </h3>
            <div class="space-y-2 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-slate-500">Séjour ({{ $nights }} nuit{{ $nights>1?'s':'' }})</span>
                    <span class="font-medium">{{ number_format($booking->total_amount - $booking->details->sum('amount'), 0, ',', ' ') }} MRU</span>
                </div>
                @foreach($booking->details as $d)
                <div class="flex justify-between text-slate-500">
                    <span class="truncate">+ {{ $d->title }}</span>
                    <span>{{ number_format($d->amount, 0, ',', ' ') }}</span>
                </div>
                @endforeach
                <div class="border-t border-slate-100 pt-2 flex justify-between font-bold text-base">
                    <span>Total</span>
                    <span>{{ number_format($booking->total_amount, 0, ',', ' ') }} MRU</span>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="mb-3">
                <div class="flex justify-between text-xs text-slate-500 mb-1">
                    <span>Payé : {{ number_format($paid, 0, ',', ' ') }} MRU</span>
                    <span>{{ $pct }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width:{{ $pct }}%"></div>
                </div>
                @if($remaining > 0)
                <p class="text-xs text-amber-600 mt-1">Reste : {{ number_format($remaining, 0, ',', ' ') }} MRU</p>
                @else
                <p class="text-xs text-emerald-600 mt-1 font-medium">Entièrement payé ✓</p>
                @endif
            </div>

            @if($remaining > 0 && in_array($booking->status, ['pending','checked_in']))
            @can('residence.bookings.payment')
            <button onclick="document.getElementById('paymentModal').classList.remove('hidden')"
                    class="w-full mt-2 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-medium transition">
                <i class="fa-solid fa-money-bill-wave mr-1"></i> Enregistrer un paiement
            </button>
            @endcan
            @endif
        </div>

        {{-- Payments list --}}
        @if($booking->payments->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-3 text-sm">Historique des paiements</h3>
            <div class="space-y-2">
                @foreach($booking->payments as $p)
                <div class="flex justify-between items-center text-sm border-b border-slate-50 pb-2">
                    <div>
                        <p class="font-medium text-slate-700">{{ number_format($p->amount, 0, ',', ' ') }} MRU</p>
                        <p class="text-xs text-slate-400">{{ $p->paymentType->name ?? '?' }} · {{ \Carbon\Carbon::parse($p->paid_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="text-emerald-500"><i class="fa-solid fa-check-circle"></i></span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Extra Modal --}}
@can('residence.bookings.edit')
<div id="extraModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Ajouter un supplément</h3>
            <button onclick="document.getElementById('extraModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('residence.bookings.extras.store', $booking) }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Désignation <span class="text-red-500">*</span></label>
                <input type="text" name="title" required placeholder="ex: Minibar, Room service…"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
                <input type="text" name="description" placeholder="Détail optionnel…"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Montant (MRU) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" required min="0" step="0.01"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('extraModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">Ajouter</button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- Payment Modal --}}
@can('residence.bookings.payment')
<div id="paymentModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Enregistrer un paiement</h3>
            <button onclick="document.getElementById('paymentModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('residence.bookings.payment', $booking) }}" class="p-6 space-y-4">
            @csrf
            @if($openRegister)
            <input type="hidden" name="cash_register_id" value="{{ $openRegister->id }}">
            @else
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-sm text-amber-700">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                Aucune caisse résidence ouverte — le paiement sera enregistré sans caisse.
            </div>
            @endif
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Mode de paiement <span class="text-red-500">*</span></label>
                <select name="payment_type_id" required
                        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    <option value="">— Choisir —</option>
                    @foreach($paymentTypes as $pt)
                    <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Montant (MRU) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" required min="0.01" step="0.01"
                       max="{{ $remaining }}" value="{{ $remaining }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                <p class="text-xs text-slate-400 mt-1">Reste dû : {{ number_format($remaining, 0, ',', ' ') }} MRU</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition">
                    <i class="fa-solid fa-check mr-1"></i> Valider
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

@endsection
