@extends('layouts.catering')
@section('title', $menu->week_label . ' — ' . ($menu->contract->client->name ?? ''))

@section('content')

<div class="flex items-center gap-3 mb-6 flex-wrap">
    <a href="{{ route('catering.contracts.show', $menu->contract) }}" class="text-slate-400 hover:text-slate-600 transition">
        <i class="fa-solid fa-arrow-left text-lg"></i>
    </a>
    <div class="flex-1 min-w-0">
        <h1 class="text-xl font-bold text-slate-800">{{ $menu->week_label }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            Client : <strong>{{ $menu->contract->client->name ?? '—' }}</strong>
            · {{ $menu->contract->guest_count }} convives
        </p>
    </div>
    @php
        $totalCodes = $menu->days->flatMap->meals->flatMap->codes->count();
        $usedCodes  = $menu->days->flatMap->meals->flatMap->codes->where('is_used', true)->count();
    @endphp
    <div class="flex items-center gap-3">
        <div class="text-sm text-slate-600">
            <span class="font-bold text-teal-700">{{ $usedCodes }}</span> / {{ $totalCodes }} codes utilisés
        </div>
        @if($usedCodes === 0)
        <form method="POST" action="{{ route('catering.weekly-menus.destroy', $menu) }}" class="inline"
              onsubmit="return confirm('Supprimer ce menu et tous ses codes ?')">
            @csrf @method('DELETE')
            <button class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-sm px-3 py-2 rounded-xl transition">
                <i class="fa-solid fa-trash text-xs"></i> Supprimer
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Days grid --}}
@forelse($menu->days->sortBy('date') as $day)
<div class="bg-white rounded-2xl shadow-sm mb-4 overflow-hidden">

    {{-- Day header --}}
    <div class="px-5 py-3 bg-gradient-to-r from-teal-600 to-teal-500 flex items-center justify-between">
        <div>
            <p class="font-bold text-white capitalize">{{ $day->day_label }}</p>
            <p class="text-teal-100 text-xs">{{ $day->date->format('d/m/Y') }}</p>
        </div>
        <span class="text-teal-100 text-sm">{{ $day->meals->count() }} type(s) de repas</span>
    </div>

    {{-- Meals --}}
    <div class="divide-y divide-slate-100">
        @forelse($day->meals->sortBy('type') as $meal)
        @php
            $mealUsed  = $meal->codes->where('is_used', true)->count();
            $mealTotal = $meal->codes->count();
            $price     = $menu->contract->getPriceFor($meal->type);
        @endphp
        <div class="px-5 py-4">
            <div class="flex items-start justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ $meal->type_icon }}</span>
                    <div>
                        <p class="font-semibold text-slate-800">{{ $meal->type_label }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $meal->quantity }} codes ·
                            <span class="text-teal-600 font-medium">{{ $mealUsed }} utilisés</span>
                            @if($price > 0)
                            · {{ number_format($price, 0, ',', ' ') }} MRU/code
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($mealTotal > 0)
                    <div class="text-right">
                        @php $pct = $mealTotal > 0 ? round($mealUsed/$mealTotal*100) : 0; @endphp
                        <div class="flex items-center gap-2">
                            <div class="w-20 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-teal-500 rounded-full" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-slate-500">{{ $pct }}%</span>
                        </div>
                    </div>
                    @endif
                    <a href="{{ route('catering.meals.print-codes', $meal) }}"
                       class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs px-3 py-1.5 rounded-xl transition font-medium"
                       target="_blank">
                        <i class="fa-solid fa-print"></i> Imprimer {{ $mealTotal }} codes
                    </a>
                </div>
            </div>

            {{-- Dishes --}}
            @if($meal->items->count() > 0)
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($meal->items as $item)
                <span class="bg-slate-100 text-slate-700 text-xs px-3 py-1 rounded-full font-medium">
                    {{ $item->meal->name ?? '—' }}
                </span>
                @endforeach
            </div>
            @else
            <p class="text-xs text-slate-400 mt-2 italic">Aucun plat associé</p>
            @endif

            {{-- Codes preview (first 5) --}}
            @if($meal->codes->count() > 0)
            <div class="mt-3">
                <p class="text-xs text-slate-400 mb-1.5">Aperçu codes :</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($meal->codes->take(8) as $code)
                    <span class="font-mono text-xs px-2.5 py-1 rounded-lg border
                        {{ $code->is_used
                            ? 'bg-emerald-50 border-emerald-200 text-emerald-700 line-through opacity-60'
                            : 'bg-white border-slate-200 text-slate-700' }}">
                        {{ $code->code }}
                    </span>
                    @endforeach
                    @if($meal->codes->count() > 8)
                    <span class="text-xs text-slate-400 self-center">+ {{ $meal->codes->count() - 8 }} autres</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="px-5 py-4 text-slate-400 text-sm">Aucun repas pour ce jour.</div>
        @endforelse
    </div>
</div>
@empty
<div class="bg-white rounded-2xl shadow-sm p-12 text-center text-slate-400">
    <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block opacity-30"></i>
    <p>Ce menu ne contient aucun jour planifié.</p>
</div>
@endforelse

@endsection
