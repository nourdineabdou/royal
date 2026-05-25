@extends('layouts.accounting')

@section('title', 'Toutes les transactions')

@section('accounting_content')
<div class="max-w-7xl mx-auto py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Toutes les transactions</h1>
            <p class="text-gray-500 text-sm mt-1">Liste filtrable et exportable de toutes les opérations comptables</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('accounting.transactions.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="bg-white border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-file-csv"></i> Exporter CSV</a>
            <a href="{{ route('accounting.transactions.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}" class="bg-white border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-file-excel"></i> Exporter XLSX</a>
        </div>
    </div>
    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end bg-gradient-to-br from-red-100 to-red-50 p-4 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <label class="block text-xs font-medium text-red-700 mb-1">Module</label>
            <select name="module" class="border border-red-200 rounded px-3 py-2 bg-white focus:ring-2 focus:ring-red-300">
                <option value="">Tous</option>
                @foreach($modules as $m)
                    <option value="{{ $m ?? 'inconnu' }}" {{ ($filters['module'] ?? '') == $m ? 'selected' : '' }}>{{ $m ? ucfirst($m) : 'Inconnu' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-red-700 mb-1">Type</label>
            <select name="type" class="border border-red-200 rounded px-3 py-2 bg-white focus:ring-2 focus:ring-red-300">
                <option value="">Tous</option>
                @foreach($types as $t)
                    <option value="{{ $t }}" {{ ($filters['type'] ?? '') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-red-700 mb-1">Du</label>
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="border border-red-200 rounded px-3 py-2 bg-white focus:ring-2 focus:ring-red-300">
        </div>
        <div>
            <label class="block text-xs font-medium text-red-700 mb-1">Au</label>
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="border border-red-200 rounded px-3 py-2 bg-white focus:ring-2 focus:ring-red-300">
        </div>
        <div class="flex items-end h-full">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-bold shadow transition">Filtrer</button>
        </div>
    </form>
            <a href="{{ route('accounting.transactions.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="bg-slate-100 px-3 py-2 rounded border">Exporter CSV</a>
            <a href="{{ route('accounting.transactions.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}" class="bg-slate-100 px-3 py-2 rounded border">Exporter XLSX</a>
        </div>
    </form>
    <div class="overflow-x-auto bg-white rounded-2xl shadow border border-gray-100">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100 sticky top-0 z-10">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Module</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Type</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Montant</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Référence</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                <tr class="hover:bg-emerald-50 transition">
                    <td class="px-6 py-3 whitespace-nowrap">{{ $t->date }}</td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">{{ ucfirst($t->module) }}</span>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ ucfirst($t->type) }}</span>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap font-mono text-right text-gray-900">{{ number_format($t->amount, 2) }}</td>
                    <td class="px-6 py-3 whitespace-nowrap text-gray-500">{{ $t->reference }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-slate-400 py-8">Aucune transaction trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
@endsection
