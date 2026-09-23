@extends('layouts.catering')
@section('title', 'Plats Catering')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Plats Catering</h1>
        <p class="text-sm text-slate-500">Cochez les plats destinés en priorité aux contrats de catering — les autres restent des extras vendables hors contrat.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
@endif

<form method="GET" class="bg-white rounded-2xl shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Rechercher</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom du plat…"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Filtre</label>
        <select name="is_catering" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
            <option value="">Tous les plats</option>
            <option value="1" {{ request('is_catering') === '1' ? 'selected' : '' }}>Catering seulement</option>
            <option value="0" {{ request('is_catering') === '0' ? 'selected' : '' }}>Extras seulement</option>
        </select>
    </div>
    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-sm font-semibold">
        <i class="fa-solid fa-filter mr-1"></i> Filtrer
    </button>
</form>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100 text-xs text-slate-500 uppercase">
            <tr>
                <th class="px-5 py-3 text-left">Plat</th>
                <th class="px-5 py-3 text-left">Catégorie</th>
                <th class="px-5 py-3 text-right">Prix</th>
                <th class="px-5 py-3 text-center">Statut</th>
                <th class="px-5 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($meals as $meal)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                <td class="px-5 py-3 font-medium text-slate-700">{{ $meal->name }}</td>
                <td class="px-5 py-3 text-slate-500">{{ $meal->category->name ?? '—' }}</td>
                <td class="px-5 py-3 text-right text-slate-600">{{ number_format($meal->price, 0, ',', ' ') }} MRU</td>
                <td class="px-5 py-3 text-center">
                    @if($meal->is_catering)
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-700"><i class="fa-solid fa-truck-fast mr-1"></i>Catering</span>
                    @else
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">Extra</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    <form method="POST" action="{{ route('catering.meals.toggle-catering', $meal) }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold {{ $meal->is_catering ? 'text-slate-500 hover:text-slate-700' : 'text-teal-600 hover:text-teal-800' }}">
                            {{ $meal->is_catering ? 'Retirer du catering' : 'Marquer catering' }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-10 text-slate-400">Aucun plat trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $meals->links() }}</div>
@endsection
