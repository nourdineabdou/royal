@extends('layouts.event')
@section('title', 'Nouvel événement')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('event.index') }}" class="text-slate-400 hover:text-slate-600">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h2 class="text-xl font-bold text-slate-800">Nouvel événement</h2>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('event.store') }}"
          class="bg-white rounded-2xl shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Client <span class="text-red-500">*</span></label>
                <select name="client_id" required
                        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    <option value="">— Sélectionner un client —</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}{{ $c->company ? ' ('.$c->company.')' : '' }}
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">
                    <a href="{{ route('event.clients') }}" target="_blank" class="text-violet-500 hover:underline">
                        + Ajouter un client
                    </a>
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Type d'événement <span class="text-red-500">*</span></label>
                <input type="text" name="event_type" list="eventTypesList" required
                       value="{{ old('event_type') }}" placeholder="Ex: Mariage, Baptême…"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                <datalist id="eventTypesList">
                    @foreach($eventTypes as $t)<option value="{{ $t }}">@endforeach
                </datalist>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Date <span class="text-red-500">*</span></label>
                <input type="date" name="event_date" required value="{{ old('event_date') }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre de convives <span class="text-red-500">*</span></label>
                <input type="number" name="guest_count" required min="1" value="{{ old('guest_count') }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Stock lié</label>
            <select name="stock_id"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                <option value="">— Aucun (à définir plus tard) —</option>
                @foreach($stocks as $s)
                <option value="{{ $s->id }}" {{ old('stock_id') == $s->id ? 'selected' : '' }}>
                    {{ $s->name }}{{ $s->location ? ' — '.$s->location : '' }}
                </option>
                @endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">Le stock est requis pour la vérification et la clôture de l'événement.</p>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t">
            <a href="{{ route('event.index') }}"
               class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</a>
            <button type="submit"
                    class="px-5 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">
                <i class="fa-solid fa-check mr-1"></i> Créer l'événement
            </button>
        </div>
    </form>
</div>

@endsection
