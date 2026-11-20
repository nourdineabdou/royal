@extends('layouts.purchases')
@section('title', "Demandes d'achat")

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold text-slate-800">Demandes d'achat</h2>
    <a href="{{ route('purchases.requests.create') }}"
       class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
        <i class="fa-solid fa-plus-circle"></i> Nouvelle demande
    </a>
</div>


{{-- Filtres : ce qui reste à traiter (statut), période, n° de DA --}}
@php
    $status = $filters['status'] ?? null;
    $keep = fn (array $x) => array_filter(array_merge(['from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null, 'q' => $filters['q'] ?? null, 'status' => $status], $x));
    $mine = auth()->user()->can('purchases.requests.validate') ? 'to_validate' : (auth()->user()->can('purchases.requests.approve') ? 'pending' : null);
@endphp
@if($mine && ($counts[$mine] ?? 0) > 0 && $status !== $mine)
<a href="{{ route('purchases.requests', $keep(['status' => $mine])) }}"
   class="flex items-center gap-3 bg-violet-600 hover:bg-violet-700 text-white rounded-2xl px-5 py-3 mb-4 shadow-sm">
    <i class="fa-solid fa-hourglass-half"></i>
    <span class="flex-1 font-semibold">{{ $counts[$mine] }} demande{{ $counts[$mine] > 1 ? 's' : '' }} d'achat attend{{ $counts[$mine] > 1 ? 'ent' : '' }} votre {{ $mine === 'to_validate' ? 'validation' : 'approbation' }}</span>
    <i class="fa-solid fa-arrow-right"></i>
</a>
@endif
<form method="GET" class="bg-white rounded-2xl shadow-sm p-4 mb-5 space-y-3">
    @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
    <div class="flex flex-wrap items-end gap-2">
        <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">N° de DA</label>
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="DA-…" class="w-40 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Du</label>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Au</label>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
        </div>
        <button class="flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-medium"><i class="fa-solid fa-filter"></i> Filtrer</button>
        @if(array_filter($filters))
            <a href="{{ route('purchases.requests') }}" class="ml-auto text-sm text-red-600 hover:text-red-700 py-2"><i class="fa-solid fa-xmark mr-1"></i>Effacer les filtres</a>
        @endif
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('purchases.requests', $keep(['status' => null])) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold border {{ !$status ? 'bg-slate-800 text-white border-slate-800' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">Toutes · {{ $counts->sum() }}</a>
        @foreach(\App\Models\PurchaseRequest::STATUSES as $val => $label)
            @continue(!($counts[$val] ?? 0) && $status !== $val)
            <a href="{{ route('purchases.requests', $keep(['status' => $val])) }}"
               class="px-3 py-1.5 rounded-full text-xs font-semibold border {{ $status === $val ? 'bg-slate-800 text-white border-slate-800' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">{{ $label }} · {{ $counts[$val] ?? 0 }}</a>
        @endforeach
    </div>
</form>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="text-xs text-slate-400 uppercase border-b bg-slate-50">
            <tr>
                <th class="px-5 py-3 text-left">Référence</th>
                <th class="px-5 py-3 text-left">Demandé par</th>
                <th class="px-5 py-3 text-center">Lignes</th>
                <th class="px-5 py-3 text-center">BC liés</th>
                <th class="px-5 py-3 text-left">Statut</th>
                <th class="px-5 py-3 text-left">Créée le</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition cursor-pointer" onclick="location.href='{{ route('purchases.requests.show', $req) }}'">
                <td class="px-5 py-3 font-semibold text-orange-600">{{ $req->reference }}</td>
                <td class="px-5 py-3 text-slate-600">{{ $req->requestedBy->name ?? '—' }}</td>
                <td class="px-5 py-3 text-center text-slate-600">{{ $req->items->count() }}</td>
                <td class="px-5 py-3 text-center text-slate-600">{{ $req->orders_count }}</td>
                <td class="px-5 py-3">
                    @php
                        $badges = [
                            'draft' => 'bg-slate-100 text-slate-600',
                            'pending' => 'bg-amber-100 text-amber-700',
                            'partially_ordered' => 'bg-blue-100 text-blue-700',
                            'ordered' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                        $labels = [
                            'draft' => 'Brouillon',
                            'pending' => 'En attente',
                            'partially_ordered' => 'Partiellement commandée',
                            'ordered' => 'Entièrement commandée',
                            'cancelled' => 'Annulée',
                        ];
                    @endphp
                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $req->status_badge }}">
                        {{ $req->status_label }}
                    </span>
                </td>
                <td class="px-5 py-3 text-slate-500">{{ $req->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-10 text-center text-slate-400">{{ array_filter($filters) ? 'Aucune demande d’achat pour ces filtres.' : 'Aucune demande d’achat pour l’instant.' }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $requests->links() }}</div>
@endsection
