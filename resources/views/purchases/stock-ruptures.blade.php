@extends('layouts.purchases')
@section('title', 'Ruptures de stock')

@section('content')
@php
    use App\Support\Qty;
    $ruptures = $alerts->where('level', 'rupture');
    $below    = $alerts->where('level', 'below');
@endphp

<div class="max-w-5xl mx-auto space-y-5">
    <div>
        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-red-500"></i> Ruptures et stock sous le seuil
        </h2>
        <p class="text-sm text-slate-500">Stock du <strong>{{ $ref->name ?? 'magasin' }}</strong> comparé au seuil minimum de chaque produit.</p>
    </div>

    {{-- ── La demande d'achat préparée automatiquement ─────────────── --}}
    @can('purchases.requests.create')
    <a href="{{ route('purchases.requests.auto') }}"
       class="flex items-center gap-4 rounded-2xl p-4 border shadow-sm transition hover:shadow-md {{ $draft ? 'bg-orange-600 border-orange-600 text-white' : 'bg-white border-slate-200 text-slate-700' }}">
        <i class="fa-solid fa-wand-magic-sparkles text-2xl"></i>
        <div class="flex-1 min-w-0">
            @if($draft)
                <p class="font-bold text-lg">Demande d'achat prête : {{ $draft->items->count() }} produit(s) à commander</p>
                <p class="text-sm text-orange-50">Préparée automatiquement · {{ $draft->reference }} · relisez-la et envoyez-la pour approbation</p>
            @else
                <p class="font-bold">Aucune demande à préparer</p>
                <p class="text-sm text-slate-500">Les produits sous le seuil sont déjà en commande, ou rien ne manque.</p>
            @endif
        </div>
        <i class="fa-solid fa-arrow-right"></i>
    </a>
    @endcan

    <div class="grid grid-cols-2 gap-3">
        <div class="bg-red-50 border border-red-100 rounded-2xl p-4">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">En rupture</p>
            <p class="text-3xl font-bold text-red-700">{{ $ruptures->count() }}</p>
            <p class="text-xs text-red-500">stock à zéro</p>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
            <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Sous le seuil</p>
            <p class="text-3xl font-bold text-amber-700">{{ $below->count() }}</p>
            <p class="text-xs text-amber-600">à surveiller ou commander</p>
        </div>
    </div>

    @if($noThreshold > 0)
        <div class="bg-sky-50 border border-sky-100 text-sky-800 rounded-xl px-4 py-3 text-sm">
            <i class="fa-solid fa-circle-info mr-1"></i>
            {{ $noThreshold }} produit(s) utilisent le <strong>seuil par défaut : 3 emballages d'achat</strong> (3 sacs, 3 cartons…).
            Pour un seuil précis : <a href="{{ route('purchases.products.index') }}" class="underline font-semibold">Produits › Modifier</a>.
        </div>
    @endif

    @if($alerts->isEmpty())
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-500">
            <i class="fa-solid fa-circle-check text-emerald-500 text-3xl mb-2 block"></i>
            Aucun produit en rupture ni sous son seuil.
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 divide-y divide-slate-100">
            @foreach($alerts as $a)
                @php $p = $a['product']; $unit = $p->unit->symbol ?? ''; @endphp
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold text-slate-800">{{ $p->name }}</span>
                            @if($a['level'] === 'rupture')
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">Rupture</span>
                            @else
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Sous le seuil</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Stock : <strong class="{{ $a['level'] === 'rupture' ? 'text-red-600' : 'text-amber-700' }}">{{ Qty::format($a['total'], $unit) }}</strong>
                            · Seuil : {{ Qty::format($a['min'], $unit) }}@if($a['min_default']) <span class="text-slate-400">({{ $a['min_label'] }})</span>@endif
                            @if($a['incoming'] > 0) · En commande : <strong>{{ Qty::format($a['incoming'], $unit) }}</strong> @endif
                        </p>
                    </div>
                    <div class="sm:text-right shrink-0">
                        @if($a['needs_order'])
                            <p class="text-[11px] text-slate-400">Dans la demande automatique</p>
                            <p class="text-sm font-semibold text-orange-700">{{ $a['suggested_label'] }}</p>
                        @else
                            <p class="text-sm font-semibold {{ $a['incoming'] > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                                {{ $a['incoming'] > 0 ? '✓ Déjà en commande' : $a['suggested_label'] }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
