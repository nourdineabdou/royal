@extends('layouts.stock')

@section('title', 'Sorties à approuver')
@section('header', 'Sorties à approuver')

@section('content')
@php use App\Support\Qty; @endphp
<div class="py-4 max-w-4xl mx-auto space-y-6">

    @can('kitchen.requests.threshold')
    <form method="POST" action="{{ route('stock.kitchen-requests.threshold') }}"
          class="bg-white rounded-xl border-2 border-violet-200 p-4 flex flex-col sm:flex-row sm:items-center gap-3">
        @csrf
        <div class="flex-1">
            <p class="font-semibold text-slate-800"><i class="fa-solid fa-sliders text-violet-600 mr-1"></i> Seuil d'approbation</p>
            <p class="text-xs text-slate-500">
                Une sortie qui dépasse ce pourcentage du stock d'un produit doit être approuvée par le responsable des achats.
                @if($thresholdSetting)
                    Modifié le {{ $thresholdSetting->updated_at->format('d/m/Y H:i') }}{{ $thresholdSetting->updater ? ' par ' . $thresholdSetting->updater->name : '' }}.
                @else
                    Valeur par défaut.
                @endif
            </p>
            @error('threshold')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <div class="flex items-center border-2 border-violet-300 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-violet-400">
                <input type="number" name="threshold" min="1" max="100" step="0.5" required
                       value="{{ old('threshold', \App\Models\KitchenRequest::thresholdPercent() + 0) }}"
                       class="w-20 text-right font-bold text-lg py-2 px-2 focus:outline-none">
                <span class="px-3 text-sm font-semibold text-slate-500 bg-violet-50 self-stretch flex items-center">%</span>
            </div>
            <button class="px-4 py-2.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold">Enregistrer</button>
        </div>
    </form>
    @endcan

    <p class="text-sm text-slate-600 bg-white rounded-xl border border-slate-100 px-4 py-3">
        <i class="fa-solid fa-circle-info text-emerald-600 mr-1"></i>
        Seules les sorties <strong>importantes</strong> (plus de {{ \App\Models\KitchenRequest::thresholdLabel() }} du stock d'un produit) arrivent ici :
        les demandes des cuisiniers avant qu'elles partent au magasinier, et les sorties préparées par le magasinier.
        Ouvrez-en une pour voir, produit par produit, <strong>ce qui est en stock, ce qui sort et ce qui reste</strong>. Vous pouvez corriger les quantités avant d'approuver.
    </p>

    <section class="space-y-2">
        <h2 class="font-semibold text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-stamp text-violet-600"></i> À approuver
            <span class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full">{{ $toApprove->count() }}</span>
        </h2>
        @forelse($toApprove as $kr)
            <a href="{{ route('stock.kitchen-requests.approve', $kr) }}"
               class="block bg-white rounded-xl border border-slate-200 hover:border-violet-400 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800">{{ $kr->kitchen }} · {{ $kr->reference }}
                            <span class="ml-1 text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $kr->isPrepared() ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700' }}">
                                {{ $kr->isPrepared() ? 'Sortie du magasinier' : 'Demande du cuisinier' }}
                            </span>
                        </p>
                        <p class="text-xs text-slate-500">
                            @if($kr->isPrepared())
                                Préparée par {{ $kr->preparer->name ?? '—' }} le {{ $kr->prepared_at?->format('d/m H:i') }}
                                · depuis {{ $kr->stock->name ?? '—' }}{{ $kr->receiver ? ' · pour ' . $kr->receiver->name : '' }}
                            @else
                                Demandée par {{ $kr->requester->name ?? '—' }} le {{ $kr->created_at->format('d/m H:i') }}{{ $kr->needed_on ? ' · pour le ' . $kr->needed_on->format('d/m') : '' }}
                            @endif
                        </p>
                    </div>
                    <span class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-lg bg-violet-600 text-white">Vérifier</span>
                </div>
                <p class="text-sm text-slate-600 mt-2">{{ $kr->summary(' · ') }}</p>
            </a>
        @empty
            <p class="text-sm text-slate-400 bg-white rounded-xl border border-dashed border-slate-300 p-5 text-center">Aucune sortie à approuver.</p>
        @endforelse
    </section>

    <section class="space-y-2">
        <h2 class="font-semibold text-slate-700 flex items-center gap-2"><i class="fa-solid fa-check-double text-emerald-600"></i> Dernières sorties approuvées</h2>
        <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100">
            @forelse($approved as $kr)
                <div class="p-3">
                    <p class="text-sm font-semibold text-slate-800">{{ $kr->bs_number ?? $kr->reference }} · {{ $kr->kitchen }}
                        <span class="ml-1 text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $kr->status === 'validated' ? 'bg-emerald-100 text-emerald-700' : ($kr->status === 'cancelled' ? 'bg-slate-100 text-slate-500' : 'bg-amber-100 text-amber-800') }}">{{ $kr->status_label }}</span>
                    </p>
                    <p class="text-xs text-slate-500 truncate">
                        Approuvée le {{ $kr->approved_at?->format('d/m H:i') }} par {{ $kr->approver->name ?? '—' }}{{ $kr->stock ? ' · ' . $kr->stock->name : '' }} :
                        {{ $kr->summary() }}
                    </p>
                </div>
            @empty
                <p class="p-5 text-center text-sm text-slate-400">Aucune sortie approuvée.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
