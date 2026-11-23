@extends('layouts.stock')

@section('title', 'Demandes des cuisines')
@section('header', 'Demandes des cuisines')

@section('content')
@php use App\Support\Qty; @endphp
<div class="py-4 max-w-4xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-sm text-slate-500">Demandes envoyées par les cuisines, ou saisies ici au magasin.</p>
        <a href="{{ route('stock.kitchen-requests.create') }}" class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-xl text-sm font-semibold shrink-0">
            <i class="fa-solid fa-plus"></i> Nouvelle sortie vers une cuisine
        </a>
    </div>

    <p class="text-sm text-slate-600 bg-white rounded-xl border border-slate-100 px-4 py-3">
        <i class="fa-solid fa-circle-info text-emerald-600 mr-1"></i>
        La cuisine demande (ou vous saisissez la demande) et vous préparez les quantités réelles. <strong>Si un produit dépasse {{ \App\Models\KitchenRequest::thresholdLabel() }} du stock, le responsable des achats approuve</strong> (il peut corriger les quantités) : avant que la demande vous arrive, ou après votre préparation. <strong>Le stock ne diminue que lorsque le cuisinier valide</strong> qu'il a pris la marchandise. Le bon de sortie (BS) est alors créé.
    </p>

    {{-- ── À préparer ───────────────────────────────────────────────── --}}
    <section class="space-y-2">
        <h2 class="font-semibold text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-inbox text-sky-600"></i> À préparer
            <span class="text-xs bg-sky-100 text-sky-700 px-2 py-0.5 rounded-full">{{ $pending->count() }}</span>
        </h2>
        @forelse($pending as $kr)
            <a href="{{ route('stock.kitchen-requests.prepare', $kr) }}"
               class="block bg-white rounded-xl border {{ $kr->contest_note ? 'border-red-300' : 'border-slate-200' }} hover:border-emerald-400 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800">{{ $kr->kitchen }} · {{ $kr->reference }}
                            @if($kr->approved_at)<span class="ml-1 text-[11px] font-semibold bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full"><i class="fa-solid fa-stamp"></i> approuvée par {{ $kr->approver->name ?? 'resp. achats' }}</span>@endif
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ $kr->requester->name ?? '—' }} · demandé le {{ $kr->created_at->format('d/m H:i') }}{{ $kr->needed_on ? ' · pour le ' . $kr->needed_on->format('d/m') : '' }}
                        </p>
                    </div>
                    <span class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-lg bg-emerald-600 text-white">Préparer</span>
                </div>
                @if($kr->contest_note)
                    <p class="text-xs bg-red-50 text-red-700 rounded-lg px-2 py-1 mt-2"><i class="fa-solid fa-rotate-left mr-1"></i>Renvoyée — {{ $kr->contest_note }}</p>
                @endif
                <p class="text-sm text-slate-600 mt-2">
                    {{ $kr->items->map(fn ($i) => ($i->product->name ?? '—') . ' ' . Qty::format($i->requested_quantity, $i->product->unit->symbol ?? ''))->implode(' · ') }}
                </p>
            </a>
        @empty
            <p class="text-sm text-slate-400 bg-white rounded-xl border border-dashed border-slate-300 p-5 text-center">Aucune demande à préparer.</p>
        @endforelse
    </section>

    {{-- ── Préparées, en attente de l'approbation du responsable des achats ── --}}
    <section class="space-y-2">
        <h2 class="font-semibold text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-stamp text-violet-600"></i> En attente d'approbation (responsable des achats)
            <span class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full">{{ $toApprove->count() }}</span>
        </h2>
        <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100">
            @forelse($toApprove as $kr)
                <div class="p-3 flex flex-col sm:flex-row sm:items-center gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $kr->kitchen }} · {{ $kr->reference }}</p>
                        <p class="text-xs text-slate-500 truncate">
                            @if($kr->isPrepared())
                                Préparée{{ $kr->receiver ? ' pour ' . $kr->receiver->name : '' }} le {{ $kr->prepared_at?->format('d/m H:i') }} depuis {{ $kr->stock->name ?? '—' }} :
                            @else
                                Demande de {{ $kr->requester->name ?? '—' }} (arrivera ici à préparer une fois approuvée) :
                            @endif
                            {{ $kr->summary() }}
                        </p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        @can('kitchen.requests.approve')
                        <a href="{{ route('stock.kitchen-requests.approve', $kr) }}" class="text-xs px-3 py-2 rounded-lg bg-violet-600 text-white font-semibold">Approuver</a>
                        @endcan
                        <form method="POST" action="{{ route('kitchen.requests.cancel', $kr) }}" onsubmit="return confirm('Annuler cette sortie ? Les quantités réservées seront libérées.')">
                            @csrf
                            <button class="text-xs px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">Annuler</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-5 text-center text-sm text-slate-400">Rien en attente d'approbation.</p>
            @endforelse
        </div>
    </section>

    {{-- ── Remises, en attente du cuisinier ─────────────────────────── --}}
    <section class="space-y-2">
        <h2 class="font-semibold text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-hourglass-half text-amber-600"></i> Remises, en attente de validation par la cuisine
            <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">{{ $prepared->count() }}</span>
        </h2>
        <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100">
            @forelse($prepared as $kr)
                <div class="p-3 flex flex-col sm:flex-row sm:items-center gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $kr->kitchen }} · {{ $kr->reference }}
                            @if($kr->requested_by === $kr->prepared_by)<span class="ml-1 text-[10px] font-semibold bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">saisie au magasin</span>@endif
                        </p>
                        <p class="text-xs text-slate-500 truncate">
                            Remis{{ $kr->receiver ? ' à ' . $kr->receiver->name : '' }} le {{ $kr->prepared_at?->format('d/m H:i') }} depuis {{ $kr->stock->name ?? '—' }}{{ $kr->approved_at ? ' · approuvée par ' . ($kr->approver->name ?? '—') : '' }} :
                            {{ $kr->items->filter(fn ($i) => $i->delivered_quantity > 0)->map(fn ($i) => ($i->product->name ?? '—') . ' ' . Qty::format($i->delivered_quantity, $i->product->unit->symbol ?? ''))->implode(', ') }}
                        </p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('kitchen.requests.print', $kr) }}" target="_blank" class="text-xs px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
                            <i class="fa-solid fa-print mr-1"></i> Bon provisoire
                        </a>
                        <form method="POST" action="{{ route('kitchen.requests.cancel', $kr) }}" onsubmit="return confirm('Annuler cette remise ? Les quantités réservées seront libérées.')">
                            @csrf
                            <button class="text-xs px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">Annuler</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-5 text-center text-sm text-slate-400">Rien en attente.</p>
            @endforelse
        </div>
    </section>

    {{-- ── Sorties validées (BS) ────────────────────────────────────── --}}
    <section class="space-y-2">
        <h2 class="font-semibold text-slate-700 flex items-center gap-2"><i class="fa-solid fa-file-circle-check text-emerald-600"></i> Dernières sorties validées</h2>
        <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100">
            @forelse($validated as $kr)
                <div class="p-3 flex items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $kr->bs_number }} · {{ $kr->kitchen }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $kr->validated_at?->format('d/m H:i') }} · pris par {{ $kr->validator->name ?? '—' }} · remis par {{ $kr->preparer->name ?? '—' }}</p>
                    </div>
                    <a href="{{ route('kitchen.requests.print', $kr) }}" target="_blank" class="text-xs px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 shrink-0" title="Imprimer le BS">
                        <i class="fa-solid fa-print"></i>
                    </a>
                </div>
            @empty
                <p class="p-5 text-center text-sm text-slate-400">Aucune sortie validée.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
