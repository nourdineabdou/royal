@extends('layouts.hr')
@section('title', 'Pointage')

@section('showHrNav')
    {{ auth()->user()->can('hr.attendance.view') ? 'true' : 'false' }}
@endsection

@section('content')
<div class="flex items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Pointage</h1>
        <p class="text-gray-500 text-sm mt-1">
            @if($canView)
                Vue RH et pointage employé
            @else
                Page de pointage personnelle
            @endif
        </p>
    </div>
    @if($canView)
    <a href="{{ route('hr.attendance') }}" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-900">
        Voir tous les pointages
    </a>
    @endif
</div>

<div class="grid gap-4 lg:grid-cols-3 mb-5">
    <div class="bg-white rounded-xl shadow p-5 border border-slate-200">
        @if($selfEmployee)
            <p class="text-sm text-slate-500">Employé</p>
            <h2 class="text-xl font-semibold text-slate-800 mt-2">{{ $selfEmployee->full_name }}</h2>
            <p class="text-sm text-slate-500 mt-1">Site : {{ $selfEmployee->site->name ?? 'Non défini' }}</p>
            <p class="text-sm text-slate-500">Poste : {{ $selfEmployee->jobTitle->name ?? '—' }}</p>
            <div class="mt-4 space-y-2 text-sm text-slate-600">
                <p><strong>Entrée aujourd’hui :</strong> {{ $todayAttendance->check_in ?? '—' }}</p>
                <p><strong>Sortie aujourd’hui :</strong> {{ $todayAttendance->check_out ?? '—' }}</p>
                <p><strong>Statut :</strong> {{ $todayAttendance?->status ? ucfirst($todayAttendance->status) : 'Non pointé' }}</p>
            </div>
        @else
            <p class="text-sm text-slate-500">Responsable RH</p>
            <h2 class="text-xl font-semibold text-slate-800 mt-2">Accès complet aux pointages</h2>
            <p class="text-sm text-slate-500 mt-3">Utilisez cette page pour consulter toutes les entrées et sorties des employés.</p>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow p-5 border border-slate-200 lg:col-span-2">
        <p class="text-sm text-slate-500">Pointage rapide</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <form method="POST" action="{{ route('hr.attendance.clock') }}">
                @csrf
                <input type="hidden" name="action" value="check_in">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg font-medium"
                    {{ $todayAttendance && $todayAttendance->check_in ? 'disabled' : '' }}>
                    <i class="fas fa-play mr-2"></i> Début de travail
                </button>
            </form>
            <form method="POST" action="{{ route('hr.attendance.clock') }}">
                @csrf
                <input type="hidden" name="action" value="check_out">
                <button type="submit" class="w-full bg-slate-700 hover:bg-slate-900 text-white px-4 py-3 rounded-lg font-medium"
                    {{ $todayAttendance && $todayAttendance->check_out ? 'disabled' : '' }}>
                    <i class="fas fa-stop mr-2"></i> Fin de travail
                </button>
            </form>
        </div>
        <p class="text-xs text-slate-500 mt-3">Heure de référence : 08:00. Le statut passe à Retard si l'entrée est enregistrée après cette heure.</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    @if($canView)
                    <th class="px-4 py-3 text-left">Employé</th>
                    <th class="px-4 py-3 text-left">Site</th>
                    <th class="px-4 py-3 text-left">Poste</th>
                    @endif
                    <th class="px-4 py-3 text-center">Entrée</th>
                    <th class="px-4 py-3 text-center">Sortie</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($records as $att)
                @php
                    $statusClass = ['present'=>'bg-green-100 text-green-700','absent'=>'bg-red-100 text-red-500','late'=>'bg-amber-100 text-amber-700'][$att->status] ?? '';
                    $statusLabel = ['present'=>'Présent','absent'=>'Absent','late'=>'Retard'][$att->status] ?? $att->status;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-800">{{ $att->date->format('d/m/Y') }}</td>
                    @if($canView)
                    <td class="px-4 py-3 text-gray-700">{{ $att->employee->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $att->employee->site->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $att->employee->jobTitle->name ?? '—' }}</td>
                    @endif
                    <td class="px-4 py-3 text-center font-mono text-gray-600">{{ $att->check_in ?? '—' }}</td>
                    <td class="px-4 py-3 text-center font-mono text-gray-600">{{ $att->check_out ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="{{ $canView ? 7 : 4 }}" class="text-center py-10 text-gray-400">Aucun enregistrement.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t">{{ $records->links() }}</div>
</div>
@endsection
