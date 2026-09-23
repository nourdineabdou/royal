@extends('layouts.catering')
@section('title', ($contract->client->name ?? 'Contrat') . ' — Détail')

@section('content')

{{-- Header --}}
<div class="flex items-start gap-3 mb-6 flex-wrap">
    <a href="{{ route('catering.contracts') }}" class="mt-1 text-slate-400 hover:text-slate-600 transition">
        <i class="fa-solid fa-arrow-left text-lg"></i>
    </a>
    <div class="flex-1 min-w-0">
        <h1 class="text-xl font-bold text-slate-800">{{ $contract->client->name ?? '—' }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            {{ $contract->client->company ?? '' }}
            <span class="mx-1">·</span>
            {{ $contract->start_date->format('d/m/Y') }} → {{ $contract->end_date->format('d/m/Y') }}
            <span class="mx-1">·</span>
            {{ $contract->guest_count }} convive(s)
        </p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <span class="px-3 py-1 rounded-full text-xs font-semibold
            {{ $contract->status === 'active' ? 'bg-emerald-100 text-emerald-700' :
               ($contract->status === 'paused' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
            {{ $contract->status_label }}
        </span>
        @can('catering.weekly-menu.create')
        <a href="{{ route('catering.weekly-menu.create', $contract) }}"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
            <i class="fa-solid fa-calendar-plus"></i> Créer un menu
        </a>
        @endcan
    </div>
</div>

{{-- Info Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-2xl shadow-sm p-4">
        <p class="text-xs text-slate-500 mb-1">Jours actifs</p>
        <div class="flex flex-wrap gap-1 mt-1">
            @php
                $dayNames = [1=>'L',2=>'M',3=>'M',4=>'J',5=>'V',6=>'S',7=>'D'];
                $activeDaysRaw = $contract->active_days;
                if (is_array($activeDaysRaw)) {
                    $activeDays = $activeDaysRaw;
                } elseif (is_string($activeDaysRaw)) {
                    $decoded = json_decode($activeDaysRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $activeDays = $decoded;
                    } else {
                        $activeDays = array_filter(array_map('trim', explode(',', $activeDaysRaw)), fn ($v) => $v !== '');
                    }
                } else {
                    $activeDays = [];
                }
                $activeDays = array_map('intval', $activeDays);
            @endphp
            @foreach($dayNames as $n => $d)
            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                {{ in_array($n, $activeDays, true) ? 'bg-teal-500 text-white' : 'bg-slate-100 text-slate-400' }}">
                {{ $d }}
            </span>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4">
        <p class="text-xs text-slate-500 mb-2">Repas inclus</p>
        <div class="space-y-1">
            @if($contract->has_breakfast)<p class="text-xs text-amber-700 font-medium">🌅 Petit-déjeuner</p>@endif
            @if($contract->has_lunch)<p class="text-xs text-blue-700 font-medium">☀️ Déjeuner</p>@endif
            @if($contract->has_dinner)<p class="text-xs text-indigo-700 font-medium">🌙 Dîner</p>@endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4">
        <p class="text-xs text-slate-500 mb-2">Prix par type</p>
        @foreach($contract->prices as $price)
        <p class="text-xs text-slate-700">
            {{ $price->type_label }} :
            <span class="font-semibold text-teal-700">{{ number_format($price->price, 0, ',', ' ') }} MRU</span>
        </p>
        @endforeach
        @if($contract->prices->isEmpty())<p class="text-xs text-slate-400">Aucun prix défini</p>@endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4">
        <p class="text-xs text-slate-500 mb-1">Menus créés</p>
        <p class="text-2xl font-bold text-teal-700">{{ $contract->weeklyMenus->count() }}</p>
        <p class="text-xs text-slate-400 mt-1">semaines planifiées</p>
    </div>

</div>

{{-- Weekly Menus List --}}
<div class="bg-white rounded-2xl shadow-sm p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-calendar-week text-teal-500"></i> Menus hebdomadaires
        </h2>
        @can('catering.weekly-menu.create')
        <a href="{{ route('catering.weekly-menu.create', $contract) }}"
           class="text-sm text-teal-600 hover:underline font-medium">+ Nouveau menu</a>
        @endcan
    </div>

    @forelse($contract->weeklyMenus->sortByDesc('week_start_date') as $menu)
    <div class="border border-slate-200 rounded-xl p-4 mb-3 hover:border-teal-200 transition">
        <div class="flex items-start justify-between flex-wrap gap-3">
            <div>
                <p class="font-semibold text-slate-800">{{ $menu->week_label }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $menu->days->count() }} jour(s) planifié(s)</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex gap-1.5">
                    <a href="{{ route('catering.weekly-menu.show', $menu) }}"
                       class="w-8 h-8 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-600 inline-flex items-center justify-center transition"
                       title="Voir le menu">
                        <i class="fa-solid fa-eye text-xs"></i>
                    </a>
                    @can('catering.weekly-menu.delete')
                    <form method="POST" action="{{ route('catering.weekly-menu.destroy', $menu) }}" class="inline"
                          onsubmit="return confirm('Supprimer ce menu ?')">
                        @csrf @method('DELETE')
                        <button class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 inline-flex items-center justify-center transition"
                                title="Supprimer">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Days summary --}}
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($menu->days->sortBy('date') as $day)
            <div class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                <span class="font-semibold text-slate-700">{{ $day->day_label }}</span>
                <span class="text-slate-400 ml-1">
                    @foreach($day->meals as $meal)
                    <span class="inline-block bg-white border border-slate-200 rounded px-1 ml-0.5">{{ $meal->type_icon }}</span>
                    @endforeach
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="text-center py-10 text-slate-400">
        <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block opacity-30"></i>
        <p class="text-sm">Aucun menu créé pour ce contrat.</p>
        <a href="{{ route('catering.weekly-menu.create', $contract) }}"
           class="inline-block mt-3 text-sm text-teal-600 hover:underline font-medium">
            Créer le premier menu →
        </a>
    </div>
    @endforelse
</div>

@endsection
