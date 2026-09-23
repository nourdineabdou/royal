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

@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif

{{-- Filtre statut --}}
<div class="bg-white rounded-2xl shadow-sm p-4 mb-5">
    <form method="GET" class="flex gap-2 items-center">
        <select name="status" onchange="this.form.submit()" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <option value="">Tous les statuts</option>
            @foreach(['pending' => 'En attente', 'partially_ordered' => 'Partiellement commandée', 'ordered' => 'Entièrement commandée', 'cancelled' => 'Annulée'] as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </form>
</div>

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
                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badges[$req->status] ?? 'bg-slate-100 text-slate-600' }}">
                        {{ $labels[$req->status] ?? $req->status }}
                    </span>
                </td>
                <td class="px-5 py-3 text-slate-500">{{ $req->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-10 text-center text-slate-400">Aucune demande d'achat pour l'instant.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $requests->links() }}</div>
@endsection
