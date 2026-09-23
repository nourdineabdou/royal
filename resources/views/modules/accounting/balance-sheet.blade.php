@extends('layouts.accounting')

@section('title', 'Bilan — Complex Royal')
@section('accounting_content')
<style>
    @media print { header, aside, .no-print { display: none !important; } body, main { background: white !important; } }
</style>
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
                <i class="fas fa-landmark"></i> Bilan
            </h1>
            <div class="text-gray-500 mt-1">Photo du patrimoine de l'entreprise (Actif / Passif) à une date donnée.</div>
        </div>
        <button onclick="window.print()" class="no-print flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-md border-2 border-blue-700 transition-all">
            <i class="fas fa-print"></i> Imprimer
        </button>
    </div>

    <form method="GET" class="no-print bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Arrêté au</label>
            <input type="date" name="to_date" value="{{ $toDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-5 py-2 rounded-lg">
            <i class="fas fa-filter"></i> Afficher
        </button>
    </form>

    <p class="text-sm text-gray-500 mb-4">Arrêté au {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- ACTIF --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="bg-indigo-700 text-white px-5 py-3 font-bold">ACTIF</div>
            <table class="w-full text-sm">
                <tbody>
                    @forelse($actif as $a)
                    <tr class="border-b border-gray-50">
                        <td class="px-5 py-2 text-gray-600">{{ $a->code }} — {{ $a->label }}</td>
                        <td class="px-5 py-2 text-right font-medium">{{ number_format($a->balance, 0, ',', ' ') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-6 text-center text-gray-400">Aucun compte d'actif mouvementé.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold border-t-2 border-gray-200">
                        <td class="px-5 py-3">TOTAL ACTIF</td>
                        <td class="px-5 py-3 text-right">{{ number_format($totalActif, 0, ',', ' ') }} MRU</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- PASSIF --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="bg-slate-700 text-white px-5 py-3 font-bold">PASSIF</div>
            <table class="w-full text-sm">
                <tbody>
                    @forelse($passif as $p)
                    <tr class="border-b border-gray-50">
                        <td class="px-5 py-2 text-gray-600">{{ $p->code }} — {{ $p->label }}</td>
                        <td class="px-5 py-2 text-right font-medium">{{ number_format(abs($p->balance), 0, ',', ' ') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-6 text-center text-gray-400">Aucun compte de passif mouvementé.</td></tr>
                    @endforelse
                    <tr class="border-b border-gray-50 bg-amber-50">
                        <td class="px-5 py-2 text-amber-800 font-medium">Résultat de l'exercice ({{ $resultatExercice >= 0 ? 'Bénéfice' : 'Perte' }})</td>
                        <td class="px-5 py-2 text-right font-bold text-amber-800">{{ number_format($resultatExercice, 0, ',', ' ') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold border-t-2 border-gray-200">
                        <td class="px-5 py-3">TOTAL PASSIF</td>
                        <td class="px-5 py-3 text-right">{{ number_format($totalPassif, 0, ',', ' ') }} MRU</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if(abs($totalActif - $totalPassif) > 1)
    <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        <i class="fas fa-triangle-exclamation mr-1"></i> Écart Actif/Passif de {{ number_format(abs($totalActif - $totalPassif), 0, ',', ' ') }} MRU — vérifier les écritures.
    </div>
    @else
    <div class="mt-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
        <i class="fas fa-circle-check mr-1"></i> Le bilan est équilibré.
    </div>
    @endif
</div>
@endsection
