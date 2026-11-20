@extends('layouts.kitchen')
@section('title', 'Mes demandes')

@section('content')
@php
    use App\Support\Qty;
    $badge = [
        'pending'   => 'bg-sky-100 text-sky-700',
        'prepared'  => 'bg-amber-100 text-amber-800',
        'validated' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-slate-100 text-slate-500',
    ];
@endphp

@can('kitchen.requests.create')
<a href="{{ route('kitchen.requests.create') }}"
   class="flex items-center justify-center gap-2 w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-4 rounded-2xl shadow-sm text-base">
    <i class="fa-solid fa-plus"></i> Nouvelle demande au magasin
</a>
@endcan

{{-- ── À confirmer : marchandise remise par le magasinier ─────────────── --}}
@can('kitchen.requests.validate')
<section class="space-y-2">
    <h2 class="font-bold text-slate-800 flex items-center gap-2">
        <i class="fa-solid fa-box-open text-amber-600"></i> À confirmer
        <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">{{ $toConfirm->count() }}</span>
    </h2>
    @forelse($toConfirm as $kr)
        <a href="{{ route('kitchen.requests.show', $kr) }}" class="block bg-white rounded-2xl border-2 border-amber-300 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-bold text-slate-800">{{ $kr->kitchen }} · {{ $kr->reference }}</p>
                    <p class="text-xs text-slate-500">{{ $kr->requested_by === $kr->prepared_by ? 'Saisie et remise par' : 'Remis par' }} {{ $kr->preparer->name ?? '—' }} · {{ $kr->prepared_at?->format('d/m H:i') }}</p>
                </div>
                <span class="shrink-0 text-xs font-bold px-2 py-1 rounded-full bg-amber-500 text-white">Je vérifie et je prends</span>
            </div>
            <p class="text-sm text-slate-600 mt-2">
                {{ $kr->items->filter(fn ($i) => $i->delivered_quantity > 0)->map(fn ($i) => ($i->product->name ?? '—') . ' ' . Qty::format($i->delivered_quantity, $i->product->unit->symbol ?? ''))->implode(' · ') }}
            </p>
        </a>
    @empty
        <p class="text-sm text-slate-500 bg-white rounded-2xl border border-dashed border-slate-300 p-5 text-center">Rien à confirmer pour le moment.</p>
    @endforelse
</section>
@endcan

{{-- ── Mes demandes ─────────────────────────────────────────────────── --}}
@can('kitchen.requests.create')
<section class="space-y-2">
    <h2 class="font-bold text-slate-800"><i class="fa-solid fa-list-check text-slate-400 mr-1"></i> Mes demandes</h2>
    <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100">
        @forelse($mine as $kr)
            <a href="{{ route('kitchen.requests.show', $kr) }}" class="flex items-center gap-3 p-4 hover:bg-stone-50">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-800 text-sm">{{ $kr->reference }} · {{ $kr->kitchen }}</p>
                    <p class="text-xs text-slate-500 truncate">
                        {{ $kr->created_at->format('d/m H:i') }} · {{ $kr->items->count() }} produit(s)
                        @if($kr->bs_number) · {{ $kr->bs_number }} @endif
                        @if($kr->status === 'pending' && $kr->contest_note) · <span class="text-red-600">écart signalé</span> @endif
                    </p>
                </div>
                <span class="shrink-0 text-[11px] font-bold px-2 py-1 rounded-full {{ $badge[$kr->status] ?? '' }}">{{ $kr->status_label }}</span>
            </a>
        @empty
            <p class="p-6 text-center text-sm text-slate-400">Aucune demande pour l'instant.</p>
        @endforelse
    </div>
</section>
@endcan
@endsection
