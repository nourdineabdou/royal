@extends('layouts.stock')

@section('title', 'Consommation par site')
@section('header', 'Consommation par site')

@section('content')
@php use App\Support\Qty; @endphp
<div class="py-4 max-w-5xl mx-auto space-y-5">

    <p class="text-sm text-slate-600 bg-white rounded-xl border border-slate-100 px-4 py-3">
        <i class="fa-solid fa-circle-info text-violet-600 mr-1"></i>
        Produits reçus par les cuisiniers, par site. Un cuisinier rattaché à un seul site : tout est attribué à ce site.
        Rattaché à plusieurs : il indique la répartition quand il confirme la réception.
        Les sites d'un cuisinier se règlent dans <strong>Paramètres › Utilisateurs › Modifier</strong>.
    </p>

    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Du</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Au</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Site</label>
            <select name="site_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white">
                <option value="">Tous les sites</option>
                @foreach($sites as $s)<option value="{{ $s->id }}" @selected($siteId == $s->id)>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <button class="px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold">Afficher</button>
    </form>

    @if($unassigned)
        <p class="text-sm bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
            {{ $unassigned }} sortie{{ $unassigned > 1 ? 's' : '' }} validée{{ $unassigned > 1 ? 's' : '' }} sur la période sans site :
            le cuisinier n'était rattaché à aucun site.
        </p>
    @endif

    @forelse($rows as $siteName => $products)
        <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <h2 class="px-4 py-3 bg-violet-50 font-semibold text-violet-800 flex items-center gap-2">
                <i class="fa-solid fa-location-dot"></i> {{ $siteName }}
                <span class="ml-auto text-xs font-normal text-violet-600">{{ $products->count() }} produit{{ $products->count() > 1 ? 's' : '' }}</span>
            </h2>
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-500 uppercase">
                    <tr><th class="text-left px-4 py-2">Produit</th><th class="text-right px-4 py-2">Quantité reçue</th><th class="text-right px-4 py-2">Sorties</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $r)
                        <tr>
                            <td class="px-4 py-2 text-slate-800">{{ $r->product_name }}</td>
                            <td class="px-4 py-2 text-right font-bold">{{ Qty::format($r->qty, $r->unit ?? '') }}</td>
                            <td class="px-4 py-2 text-right text-slate-500">{{ $r->exits }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @empty
        <p class="text-sm text-slate-400 bg-white rounded-xl border border-dashed border-slate-300 p-6 text-center">
            Aucune sortie attribuée à un site du {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}.
        </p>
    @endforelse
</div>
@endsection
