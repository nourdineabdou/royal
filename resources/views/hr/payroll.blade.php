@extends('layouts.hr')
@section('title', 'Paie')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Fiches de paie</h1>
        <p class="text-gray-500 text-sm mt-1">
            {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
        </p>
    </div>

    <div class="flex items-center gap-3">
        {{-- Month/Year filter --}}
        <form method="GET" class="flex gap-2">
            <select name="month" class="border rounded-lg px-3 py-2 text-sm focus:outline-none">
                @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" {{ $m==$month?'selected':'' }}>{{ \Carbon\Carbon::createFromDate(null,$m,1)->translatedFormat('F') }}</option>
                @endfor
            </select>
            <input name="year" type="number" value="{{ $year }}" class="border rounded-lg px-3 py-2 text-sm w-24">
            <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm">
                <i class="fas fa-search"></i>
            </button>
        </form>

        {{-- Generate payroll --}}
        @can('hr.payroll.generate')
        <form method="POST" action="{{ route('hr.payroll.generate') }}"
              onsubmit="return confirm('Générer les fiches de paie pour ce mois ?')">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                <i class="fas fa-cogs"></i> Générer paie
            </button>
        </form>
        @endcan
    </div>
</div>

{{-- Summary bar --}}
@php
    $totalNet   = $payrolls->getCollection()->sum('net_salary');
    $paidCount  = $payrolls->getCollection()->where('status','paid')->count();
    $pendCount  = $payrolls->getCollection()->where('status','pending')->count();
@endphp
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-100">
        <p class="text-xs text-indigo-500 font-medium">Masse salariale nette</p>
        <p class="text-2xl font-bold text-indigo-700 mt-1">{{ number_format($payrolls->sum('net_salary'),0,',',' ') }} MRU</p>
    </div>
    <div class="bg-green-50 rounded-xl p-4 border border-green-100">
        <p class="text-xs text-green-600 font-medium">Fiches payées</p>
        <p class="text-2xl font-bold text-green-700 mt-1">{{ $paidCount }}</p>
    </div>
    <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
        <p class="text-xs text-amber-600 font-medium">En attente</p>
        <p class="text-2xl font-bold text-amber-700 mt-1">{{ $pendCount }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Employé</th>
                    <th class="px-4 py-3 text-right">Salaire base</th>
                    <th class="px-4 py-3 text-right">Bonus</th>
                    <th class="px-4 py-3 text-right">Déductions</th>
                    <th class="px-4 py-3 text-right">Avances</th>
                    <th class="px-4 py-3 text-right">Net à payer</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($payrolls as $p)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $p->employee->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $p->employee->jobTitle->name ?? '' }}</p>
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-gray-700">{{ number_format($p->base_salary,0,',',' ') }}</td>
                    <td class="px-4 py-3 text-right text-green-600 font-mono">+{{ number_format($p->bonus,0,',',' ') }}</td>
                    <td class="px-4 py-3 text-right text-red-500 font-mono">-{{ number_format($p->deduction,0,',',' ') }}</td>
                    <td class="px-4 py-3 text-right text-orange-500 font-mono">-{{ number_format($p->advance_deduction,0,',',' ') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-indigo-700 font-mono">{{ number_format($p->net_salary,0,',',' ') }} MRU</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $p->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $p->status === 'paid' ? 'Payé' : 'En attente' }}
                        </span>
                        @if($p->paid_at)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $p->paid_at->format('d/m/Y') }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($p->status === 'pending')
                        @can('hr.payroll.mark-paid')
                        <form method="POST" action="{{ route('hr.payroll.paid', $p) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg">
                                Marquer payé
                            </button>
                        </form>
                        @endcan
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-10 text-gray-400">Aucune fiche de paie. Cliquez sur "Générer paie".</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t">{{ $payrolls->appends(['month'=>$month,'year'=>$year])->links() }}</div>
</div>
@endsection
