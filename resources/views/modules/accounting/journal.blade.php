@extends('layouts.accounting')

@section('title', 'Journal — Complex Royal')
@section('accounting_content')
<style>
    @media print {
        header, aside, .no-print { display: none !important; }
        body, main { background: white !important; }
        .print-card { box-shadow: none !important; border: 1px solid #e5e7eb !important; }
    }
</style>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
                <i class="fas fa-book"></i> Journal des écritures
            </h1>
            <div class="text-gray-500 mt-1">Toutes les écritures comptables générées automatiquement (ventes, achats, règlements)</div>
        </div>
        <div class="no-print flex gap-3">
            <button type="button" onclick="window.print()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-md border-2 border-blue-700 transition-all">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <a href="{{ route('accounting.journal.export', request()->query()) }}" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-md border-2 border-green-700 transition-all">
                <i class="fas fa-file-excel"></i> Exporter Excel
            </a>
        </div>
    </div>

    <form method="GET" class="no-print bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Journal</label>
            <select name="journal_code" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Tous</option>
                @foreach(['VE' => 'Ventes', 'AC' => 'Achats', 'CA' => 'Caisse', 'BQ' => 'Banque', 'OD' => 'Opérations diverses'] as $code => $label)
                    <option value="{{ $code }}" {{ $journalCode === $code ? 'selected' : '' }}>{{ $code }} — {{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Du</label>
            <input type="date" name="from_date" value="{{ $fromDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Au</label>
            <input type="date" name="to_date" value="{{ $toDate }}" class="border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-5 py-2 rounded-lg">
            <i class="fas fa-filter"></i> Filtrer
        </button>
    </form>

    <div class="print-card bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Journal</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Référence</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Libellé</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Compte</th>
                        <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Débit</th>
                        <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Crédit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($entries as $entry)
                        @foreach($entry->lines as $line)
                            <tr class="{{ $loop->first ? 'border-t-2 border-gray-200' : '' }}">
                                @if($loop->first)
                                    <td class="px-4 py-2 text-gray-700" rowspan="{{ $entry->lines->count() }}">{{ $entry->entry_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2" rowspan="{{ $entry->lines->count() }}">
                                        <span class="text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-700 font-semibold">{{ $entry->journal_code }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-700" rowspan="{{ $entry->lines->count() }}">{{ $entry->reference }}</td>
                                    <td class="px-4 py-2 text-gray-700" rowspan="{{ $entry->lines->count() }}">{{ $entry->label }}</td>
                                @endif
                                <td class="px-4 py-2 text-gray-600">{{ $line->chartOfAccount->code }} — {{ $line->chartOfAccount->label }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-700">{{ $line->debit > 0 ? number_format($line->debit, 2, ',', ' ') : '' }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-700">{{ $line->credit > 0 ? number_format($line->credit, 2, ',', ' ') : '' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-400">Aucune écriture pour cette période.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 no-print">{{ $entries->links() }}</div>
    </div>
</div>
@endsection
