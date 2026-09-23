@extends('layouts.accounting')

@section('title', 'Compte de Résultat — Complex Royal')
@section('accounting_content')
<style>
    @media print { header, aside, .no-print { display: none !important; } body, main { background: white !important; } }
</style>
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
                <i class="fas fa-chart-line"></i> Compte de Résultat
            </h1>
            <div class="text-gray-500 mt-1">Charges et produits sur une période — pour savoir si l'activité est bénéficiaire.</div>
        </div>
        <button onclick="window.print()" class="no-print flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-md border-2 border-blue-700 transition-all">
            <i class="fas fa-print"></i> Imprimer
        </button>
    </div>

    <form method="GET" class="no-print bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Du</label>
            <input type="date" name="from_date" value="{{ $fromDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Au</label>
            <input type="date" name="to_date" value="{{ $toDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-5 py-2 rounded-lg">
            <i class="fas fa-filter"></i> Afficher
        </button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- CHARGES --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="bg-red-600 text-white px-5 py-3 font-bold">CHARGES</div>
            <table class="w-full text-sm">
                <tbody>
                    @forelse($charges as $c)
                    <tr class="border-b border-gray-50">
                        <td class="px-5 py-2 text-gray-600">{{ $c->code }} — {{ $c->label }}</td>
                        <td class="px-5 py-2 text-right font-medium">{{ number_format($c->total_debit - $c->total_credit, 0, ',', ' ') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-6 text-center text-gray-400">Aucune charge sur la période.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold border-t-2 border-gray-200">
                        <td class="px-5 py-3">TOTAL CHARGES</td>
                        <td class="px-5 py-3 text-right">{{ number_format($totalCharges, 0, ',', ' ') }} MRU</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- PRODUITS --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="bg-emerald-600 text-white px-5 py-3 font-bold">PRODUITS</div>
            <table class="w-full text-sm">
                <tbody>
                    @forelse($produits as $p)
                    <tr class="border-b border-gray-50">
                        <td class="px-5 py-2 text-gray-600">{{ $p->code }} — {{ $p->label }}</td>
                        <td class="px-5 py-2 text-right font-medium">{{ number_format($p->total_credit - $p->total_debit, 0, ',', ' ') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-6 text-center text-gray-400">Aucun produit sur la période.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold border-t-2 border-gray-200">
                        <td class="px-5 py-3">TOTAL PRODUITS</td>
                        <td class="px-5 py-3 text-right">{{ number_format($totalProduits, 0, ',', ' ') }} MRU</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-xl shadow p-5 flex items-center justify-between">
        <span class="font-bold text-slate-700">RÉSULTAT NET ({{ $resultatNet >= 0 ? 'Bénéfice' : 'Perte' }})</span>
        <span class="text-2xl font-bold {{ $resultatNet >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
            {{ number_format($resultatNet, 0, ',', ' ') }} MRU
        </span>
    </div>
</div>
@endsection
