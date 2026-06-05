@extends('layouts.catering')
@section('title', 'Programmation Hebdomadaire')

@section('content')
<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Programmation hebdomadaire clients</h1>
        <p class="text-sm text-slate-500">
            Semaine du {{ $weekStart->format('d/m/Y') }} au {{ $weekEnd->format('d/m/Y') }}
        </p>
    </div>

    <form method="GET" action="{{ route('catering.planning.index') }}" class="flex items-end gap-2">
        <div>
            <label class="block text-xs text-slate-500 mb-1">Semaine (lundi)</label>
            <input type="date" name="week_start" value="{{ $weekStart->toDateString() }}"
                   class="h-10 rounded-xl border-slate-300 text-sm" />
        </div>
        <button class="h-10 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold">
            Afficher
        </button>
    </form>
</div>

<div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 text-sm">
    Astuce: cette page est faite pour programmer la semaine le samedi ou dimanche. Par defaut, le week-end affiche la semaine suivante.
</div>

<div class="space-y-5">
    @forelse($contractsOverview as $entry)
        @php
            $contract = $entry['contract'];
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <p class="text-base font-bold text-slate-800">
                        {{ $contract->client->name ?? 'Client' }}
                        @if($contract->client?->company)
                            <span class="text-sm font-medium text-slate-500">- {{ $contract->client->company }}</span>
                        @endif
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Contrat: {{ $contract->start_date->format('d/m/Y') }} -> {{ $contract->end_date->format('d/m/Y') }}
                        | Convives: {{ $contract->guest_count }}
                        | Repas/jour attendus: {{ $entry['expected_per_day'] }}
                    </p>
                    <div class="mt-2 flex items-center gap-3 flex-wrap">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $entry['completion_pct'] >= 100 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            Completude: {{ $entry['programmed_total'] }}/{{ $entry['expected_total'] }} ({{ $entry['completion_pct'] }}%)
                        </span>
                        <div class="w-44 h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $entry['completion_pct'] >= 100 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $entry['completion_pct'] }}%"></div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('catering.weekly-menu.create', ['contract' => $contract->id, 'week_start' => $weekStart->toDateString()]) }}"
                   class="inline-flex items-center gap-2 px-4 h-10 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold w-fit">
                    <i class="fa-solid fa-calendar-plus"></i>
                    Programmer cette semaine
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            @foreach($days as $d)
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">{{ $d['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="align-top">
                            @foreach($days as $d)
                                @php
                                    $cell = $entry['by_date'][$d['date']];
                                    $expected = $cell['expected_count'];
                                    $programmed = $cell['programmed'];
                                @endphp

                                <td class="px-3 py-3 border-r border-slate-50 last:border-r-0">
                                    @if($expected === 0)
                                        <div class="text-xs text-slate-400">Hors contrat / jour inactif</div>
                                    @else
                                        <div class="mb-2">
                                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                                Attendu: {{ $expected }} repas type(s)
                                            </span>
                                        </div>

                                        @if(empty($programmed))
                                            <div class="text-xs text-red-600 font-medium">Non programme</div>
                                        @else
                                            <div class="space-y-1">
                                                @foreach($programmed as $pm)
                                                    <div class="text-xs rounded-lg px-2 py-1 bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                        {{ $pm['label'] }}
                                                        <span class="text-emerald-600/80">({{ $pm['items_count'] }} plat(s))</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 text-center text-slate-400">
            Aucun contrat client entreprise actif sur cette semaine.
        </div>
    @endforelse
</div>
@endsection
