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
    @php
        $totalCodes = $menu->days->flatMap->meals->flatMap->codes->count();
        $usedCodes  = $menu->days->flatMap->meals->flatMap->codes->where('is_used', true)->count();
    @endphp
    <div class="border border-slate-200 rounded-xl p-4 mb-3 hover:border-teal-200 transition">
        <div class="flex items-start justify-between flex-wrap gap-3">
            <div>
                <p class="font-semibold text-slate-800">{{ $menu->week_label }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $menu->days->count() }} jour(s) planifié(s)</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="text-right">
                    <p class="text-xs text-slate-500">Codes</p>
                    <p class="text-sm font-bold text-slate-700">{{ $usedCodes }} / {{ $totalCodes }} utilisés</p>
                </div>
                @if($totalCodes > 0)
                <div class="w-24">
                    @php $pct = $totalCodes > 0 ? round($usedCodes/$totalCodes*100) : 0; @endphp
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-teal-500 rounded-full" style="width:{{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-slate-400 text-right mt-0.5">{{ $pct }}%</p>
                </div>
                @endif
                <div class="flex gap-1.5">
                    <a href="{{ route('catering.weekly-menu.show', $menu) }}"
                       class="w-8 h-8 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-600 inline-flex items-center justify-center transition"
                       title="Voir le menu">
                        <i class="fa-solid fa-eye text-xs"></i>
                    </a>
                    @if($usedCodes === 0)
                    @can('catering.weekly-menu.delete')
                    <form method="POST" action="{{ route('catering.weekly-menu.destroy', $menu) }}" class="inline"
                          onsubmit="return confirm('Supprimer ce menu et tous ses codes ?')">
                        @csrf @method('DELETE')
                        <button class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 inline-flex items-center justify-center transition"
                                title="Supprimer">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </form>
                    @endcan
                    @endif
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

@section('content')

{{-- Header --}}
<div class="flex items-center gap-3 mb-6 flex-wrap">
    <a href="{{ route('catering.contracts') }}" class="text-slate-400 hover:text-slate-600">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div class="flex-1">
        <h2 class="text-xl font-bold text-slate-800">{{ $contract->client_name }}</h2>
        <p class="text-sm text-slate-500">{{ $contract->company ?? '' }} · {{ $contract->start_date->format('d/m/Y') }} → {{ $contract->end_date->format('d/m/Y') }}</p>
    </div>
    <div class="flex items-center gap-2">
        @if($contract->status === 'active')
            <span class="px-3 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700 font-semibold">Actif</span>
        @else
            <span class="px-3 py-1 rounded-full text-xs bg-slate-100 text-slate-600 font-semibold">Inactif</span>
        @endif
        <span class="px-3 py-1 rounded-full text-xs bg-teal-100 text-teal-700 font-semibold">
            {{ number_format($contract->price_per_meal ?? 0, 0, ',', ' ') }} MRU / repas
        </span>
    </div>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-teal-700">{{ $contract->meals->count() }}</p>
        <p class="text-xs text-slate-500 mt-1">Repas planifiés</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-blue-700">{{ $totalCodes }}</p>
        <p class="text-xs text-slate-500 mt-1">Codes générés</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-emerald-700">{{ $usedCodes }}</p>
        <p class="text-xs text-slate-500 mt-1">Codes utilisés</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-orange-600">{{ number_format($usedCodes * ($contract->price_per_meal ?? 0), 0, ',', ' ') }}</p>
        <p class="text-xs text-slate-500 mt-1">MRU générés</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left: Meal planning --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Add meal plan --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-teal-500"></i> Ajouter / modifier un repas
            </h3>
            <form method="POST" action="{{ route('catering.contracts.meal-plan', $contract) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" required
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div class="flex flex-col justify-end">
                        <label class="block text-xs font-semibold text-slate-600 mb-2">Repas inclus</label>
                        <div class="flex items-center gap-4 text-sm">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="has_breakfast" value="1" class="rounded text-teal-500 w-4 h-4">
                                <span class="text-slate-600"><i class="fa-solid fa-coffee text-amber-500 mr-1"></i>Matin</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="has_lunch" value="1" class="rounded text-teal-500 w-4 h-4">
                                <span class="text-slate-600"><i class="fa-solid fa-bowl-food text-orange-500 mr-1"></i>Midi</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="has_dinner" value="1" class="rounded text-teal-500 w-4 h-4">
                                <span class="text-slate-600"><i class="fa-solid fa-moon text-indigo-500 mr-1"></i>Soir</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-600 mb-2">Plats au menu (optionnel)</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($availableMeals as $m)
                        <label class="flex items-center gap-1.5 text-xs cursor-pointer px-2.5 py-1.5 border border-slate-200 rounded-lg hover:border-teal-400 has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50 transition">
                            <input type="checkbox" name="meal_ids[]" value="{{ $m->id }}" class="w-3.5 h-3.5 rounded text-teal-500">
                            {{ $m->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                    <i class="fa-solid fa-check"></i> Enregistrer ce repas
                </button>
            </form>
        </div>

        {{-- Meal plan list --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-teal-500"></i> Planning des repas
                <span class="ml-auto text-xs text-slate-400 font-normal">{{ $contract->meals->count() }} jours</span>
            </h3>

            @forelse($contract->meals as $meal)
            <div class="border border-slate-100 rounded-xl p-4 mb-3 hover:border-teal-200 transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $meal->date->format('l d/m/Y') }}</p>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            @if($meal->has_breakfast)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700"><i class="fa-solid fa-coffee mr-1"></i>Matin</span>
                            @endif
                            @if($meal->has_lunch)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-orange-100 text-orange-700"><i class="fa-solid fa-bowl-food mr-1"></i>Midi</span>
                            @endif
                            @if($meal->has_dinner)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-indigo-100 text-indigo-700"><i class="fa-solid fa-moon mr-1"></i>Soir</span>
                            @endif
                            @if($meal->items->count() > 0)
                                <span class="text-xs text-slate-400">· {{ $meal->items->pluck('meal.name')->filter()->join(', ') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Codes info --}}
                        @php
                            $total  = $meal->consumptions->count();
                            $used   = $meal->consumptions->where('is_used', true)->count();
                        @endphp
                        <div class="text-right text-xs">
                            <span class="text-teal-600 font-semibold">{{ $used }}/{{ $total }}</span>
                            <span class="text-slate-400 block">codes</span>
                        </div>
                        {{-- Generate codes --}}
                        <button onclick="openGenerateCodes({{ $meal->id }}, '{{ $meal->date->format('d/m/Y') }}')"
                                class="px-3 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 text-xs font-medium transition">
                            <i class="fa-solid fa-plus mr-1"></i>Codes
                        </button>
                        {{-- Delete --}}
                        @if($total === 0)
                        <form method="POST" action="{{ route('catering.meals.destroy', $meal) }}"
                              onsubmit="return confirm('Supprimer ce repas ?')">
                            @csrf @method('DELETE')
                            <button class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 inline-flex items-center justify-center transition">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                {{-- Codes list inline --}}
                @if($total > 0)
                <div class="mt-3 pt-3 border-t border-slate-50 flex flex-wrap gap-1.5">
                    @foreach($meal->consumptions->take(12) as $code)
                    <span class="font-mono text-xs px-2 py-0.5 rounded-lg {{ $code->is_used ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $code->code }}
                    </span>
                    @endforeach
                    @if($total > 12)
                    <span class="text-xs text-slate-400 px-2 py-0.5">+ {{ $total - 12 }} autres…</span>
                    @endif
                </div>
                @endif
            </div>
            @empty
            <p class="text-center text-slate-400 text-sm py-6">Aucun repas planifié — ajoutez-en un ci-dessus.</p>
            @endforelse
        </div>
    </div>

    {{-- Right: Summary --}}
    <div class="space-y-5">
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4">Informations contrat</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Client</dt>
                    <dd class="font-medium text-slate-800">{{ $contract->client_name }}</dd>
                </div>
                @if($contract->company)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Entreprise</dt>
                    <dd class="font-medium text-slate-800">{{ $contract->company }}</dd>
                </div>
                @endif
                <div class="flex justify-between border-t pt-3">
                    <dt class="text-slate-500">Début</dt>
                    <dd>{{ $contract->start_date->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Fin</dt>
                    <dd>{{ $contract->end_date->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Durée</dt>
                    <dd>{{ $contract->start_date->diffInDays($contract->end_date) }} jours</dd>
                </div>
                <div class="flex justify-between border-t pt-3">
                    <dt class="text-slate-500">Prix / repas</dt>
                    <dd class="font-bold text-teal-700">{{ number_format($contract->price_per_meal ?? 0, 0, ',', ' ') }} MRU</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Revenu total</dt>
                    <dd class="font-bold text-orange-600">{{ number_format($usedCodes * ($contract->price_per_meal ?? 0), 0, ',', ' ') }} MRU</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-3">Taux de consommation</h3>
            @php $pct = $totalCodes > 0 ? round(($usedCodes / $totalCodes) * 100) : 0; @endphp
            <div class="h-4 bg-slate-100 rounded-full overflow-hidden mb-2">
                <div class="h-full bg-teal-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-slate-500">
                <span>{{ $usedCodes }} validés</span>
                <span>{{ $pct }}%</span>
                <span>{{ $totalCodes }} total</span>
            </div>
        </div>
    </div>
</div>

{{-- GENERATE CODES MODAL --}}
<div id="modalGenerate" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Générer des codes</h3>
            <button onclick="document.getElementById('modalGenerate').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" id="generateForm" class="p-6 space-y-4">
            @csrf
            <p id="generateLabel" class="text-sm text-slate-600 font-medium"></p>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre de codes à générer <span class="text-red-500">*</span></label>
                <input type="number" name="count" min="1" max="500" value="1" required
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <p class="text-xs text-slate-400 mt-1">Max 500 codes par génération</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalGenerate').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-teal-600 hover:bg-teal-700 text-white font-medium transition">
                    <i class="fa-solid fa-ticket mr-1"></i> Générer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openGenerateCodes(mealId, dateLabel) {
    document.getElementById('generateLabel').textContent = 'Repas du ' + dateLabel;
    document.getElementById('generateForm').action = '/catering/meals/' + mealId + '/generate-codes';
    document.getElementById('modalGenerate').classList.remove('hidden');
}
</script>
@endpush

@endsection
