@extends('layouts.event')
@section('title', 'Événements')

@section('content')

<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Événements</h2>
        <p class="text-sm text-slate-500 mt-0.5">{{ $events->total() }} résultat(s)</p>
    </div>
    @can('events.create')
    <a href="{{ route('event.create') }}"
       class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
        <i class="fa-solid fa-plus"></i> Nouvel événement
    </a>
    @endcan
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('event.index') }}"
      class="bg-white rounded-2xl shadow-sm p-4 mb-5 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Statut</label>
        <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            <option value="">Tous</option>
            <option value="draft"       {{ request('status') === 'draft'       ? 'selected' : '' }}>Brouillon</option>
            <option value="validated"   {{ request('status') === 'validated'   ? 'selected' : '' }}>Validé</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
            <option value="completed"   {{ request('status') === 'completed'   ? 'selected' : '' }}>Clôturé</option>
            <option value="cancelled"   {{ request('status') === 'cancelled'   ? 'selected' : '' }}>Annulé</option>
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Recherche</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Client, type d'événement…"
               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-medium transition">
            <i class="fa-solid fa-filter mr-1"></i> Filtrer
        </button>
        @if(request()->hasAny(['status','search']))
        <a href="{{ route('event.index') }}"
           class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition">
            <i class="fa-solid fa-xmark mr-1"></i> Effacer
        </a>
        @endif
    </div>
</form>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Client</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Convives</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Montant</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Statut</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($events as $ev)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800">{{ $ev->client?->name ?? '—' }}</p>
                        @if($ev->client?->company)
                        <p class="text-xs text-slate-400">{{ $ev->client->company }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $ev->event_type }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $ev->event_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $ev->guest_count }}</td>
                    <td class="px-4 py-3">
                        @if($ev->total_amount > 0)
                        <span class="font-semibold text-violet-700">{{ number_format((float)$ev->total_amount, 0, ',', ' ') }} MRU</span>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $ev->status_color }}">
                            {{ $ev->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('event.show', $ev) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-medium transition">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-400 py-10">
                        <i class="fa-solid fa-calendar-xmark text-2xl block mb-2"></i>
                        Aucun événement trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($events->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $events->links() }}</div>
    @endif
</div>

@endsection
