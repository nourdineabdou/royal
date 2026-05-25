@extends('layouts.catering')
@section('title', 'Consommations')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Historique des consommations</h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ $consumptions->total() }} consommation(s)</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('catering.consumptions') }}"
      class="bg-white rounded-2xl shadow-sm p-4 mb-6 flex flex-wrap items-end gap-3">

    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Client</label>
        <select name="client_id"
                class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none min-w-[180px] bg-white">
            <option value="">Tous les clients</option>
            @foreach($clients as $cl)
            <option value="{{ $cl->id }}" {{ request('client_id') == $cl->id ? 'selected' : '' }}>
                {{ $cl->name }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Date</label>
        <input type="date" name="date" value="{{ request('date') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
    </div>

    <div class="flex gap-2">
        <button type="submit"
                class="inline-flex items-center gap-1.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
            <i class="fa-solid fa-filter"></i> Filtrer
        </button>
        @if(request()->hasAny(['client_id','date']))
        <a href="{{ route('catering.consumptions') }}"
           class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm px-4 py-2 rounded-xl transition">
            <i class="fa-solid fa-xmark"></i> RÃƒÂ©initialiser
        </a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Code</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Client</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Repas</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Date</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Montant</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">ValidÃƒÂ© par</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($consumptions as $c)
                @php
                    $contract = $c->mealCode?->menuMeal?->menuDay?->weeklyMenu?->contract;
                    $price    = $contract ? $contract->getPriceFor($c->mealCode->menuMeal->type) : 0;
                @endphp
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-slate-800 text-xs tracking-wider">
                        {{ $c->mealCode->code ?? 'Ã¢â‚¬â€' }}
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-700">{{ $contract?->client?->name ?? 'Ã¢â‚¬â€' }}</p>
                        <p class="text-xs text-slate-400">{{ $contract?->client?->company ?? '' }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $c->mealCode?->menuMeal?->type === 'breakfast' ? 'bg-amber-100 text-amber-700' :
                               ($c->mealCode?->menuMeal?->type === 'lunch' ? 'bg-blue-100 text-blue-700' : 'bg-indigo-100 text-indigo-700') }}">
                            {{ $c->mealCode?->menuMeal?->type_icon ?? '' }}
                            {{ $c->mealCode?->menuMeal?->type_label ?? 'Ã¢â‚¬â€' }}
                        </span>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $c->mealCode?->menuMeal?->menuDay?->date?->format('d/m/Y') ?? '' }}
                        </p>
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-xs">
                        {{ $c->consumed_at?->format('d/m/Y H:i') ?? 'Ã¢â‚¬â€' }}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-teal-700">
                        {{ $price > 0 ? number_format($price, 0, ',', ' ') . ' MRU' : 'Ã¢â‚¬â€' }}
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-xs">
                        {{ $c->user?->name ?? 'Ã¢â‚¬â€' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-list-check text-3xl mb-2 block opacity-30"></i>
                        Aucune consommation trouvÃƒÂ©e
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($consumptions->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $consumptions->links() }}
    </div>
    @endif
</div>

@endsection
