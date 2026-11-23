@extends('layouts.kitchen')
@section('title', 'Mes demandes de sortie')

@section('content')

@can('kitchen.requests.create')
{{-- Ordinateur : bouton en haut. Téléphone : bouton fixe en bas de l'écran (voir plus bas). --}}
<a href="{{ route('kitchen.requests.create') }}"
   class="hidden sm:flex items-center justify-center gap-2 w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-4 rounded-2xl shadow-sm text-lg">
    <i class="fa-solid fa-plus"></i> Nouvelle demande de sortie
</a>
@endcan

{{-- ── 1. À confirmer : le magasinier m'a remis la marchandise ─────────── --}}
@can('kitchen.requests.validate')
<section class="space-y-3">
    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
        <i class="fa-solid fa-box-open text-amber-600"></i> À confirmer
        <span class="text-sm bg-amber-500 text-white px-2.5 py-0.5 rounded-full">{{ $toConfirm->count() }}</span>
    </h2>
    <p class="text-sm text-slate-600 -mt-1">Le magasinier vous a remis ces produits. Vérifiez, puis confirmez la réception.</p>
    @forelse($toConfirm as $kr)
        @include('kitchen._card', ['kr' => $kr, 'highlight' => true])
    @empty
        <p class="text-base text-slate-500 bg-white rounded-2xl border border-dashed border-slate-300 p-5 text-center">Rien à confirmer pour le moment.</p>
    @endforelse
</section>
@endcan

{{-- ── 2. En cours : en attente d'approbation ou chez le magasinier ───────── --}}
<section class="space-y-3">
    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
        <i class="fa-solid fa-hourglass-half text-violet-600"></i> En cours
        <span class="text-sm bg-violet-100 text-violet-800 px-2.5 py-0.5 rounded-full">{{ $inProgress->count() }}</span>
    </h2>
    <p class="text-sm text-slate-600 -mt-1">Demandes pas encore reçues : à approuver par le responsable des achats, ou en préparation chez le magasinier.</p>
    @forelse($inProgress as $kr)
        @include('kitchen._card', ['kr' => $kr])
    @empty
        <p class="text-base text-slate-500 bg-white rounded-2xl border border-dashed border-slate-300 p-5 text-center">Aucune demande en cours.</p>
    @endforelse
</section>

{{-- ── 3. Historique ───────────────────────────────────────────────────── --}}
<section class="space-y-3">
    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Déjà reçues ou annulées
    </h2>
    @forelse($history as $kr)
        @include('kitchen._card', ['kr' => $kr, 'compact' => true])
    @empty
        <p class="text-base text-slate-500 bg-white rounded-2xl border border-dashed border-slate-300 p-5 text-center">Aucune demande pour l'instant.</p>
    @endforelse
</section>

@can('kitchen.requests.create')
<div class="sm:hidden fixed bottom-0 inset-x-0 z-30 p-3 bg-stone-100/95 backdrop-blur border-t border-stone-200" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
    <a href="{{ route('kitchen.requests.create') }}"
       class="flex items-center justify-center gap-2 w-full bg-amber-600 active:bg-amber-700 text-white font-bold py-4 rounded-2xl shadow-lg text-lg">
        <i class="fa-solid fa-plus"></i> Nouvelle demande de sortie
    </a>
</div>
@endcan
@endsection
