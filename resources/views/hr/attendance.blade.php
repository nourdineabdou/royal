@extends('layouts.hr')
@section('title', 'Présences')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Présences</h1>
        <p class="text-gray-500 text-sm mt-1">{{ \Carbon\Carbon::parse($date)->translatedFormat('l d F Y') }}</p>
    </div>
    <div class="flex items-center gap-3">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-end">
            <div class="flex gap-2 items-center">
                <label class="text-sm text-gray-600">Date</label>
                <input name="date" type="date" value="{{ $date }}" class="border rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex gap-2 items-center">
                <label class="text-sm text-gray-600">Emplacement</label>
                <select name="site_id" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="">Tous</option>
                    @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ isset($siteId) && $siteId == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-2 rounded-lg text-sm">
                <i class="fas fa-search"></i>
            </button>
        </form>
        @can('hr.attendance.record')
        <button onclick="document.getElementById('modalAdd').classList.remove('hidden')"
                class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i> Enregistrer
        </button>
        @endcan
    </div>
</div>

@if($selfEmployee)
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow p-5 border border-slate-200">
        <p class="text-sm text-slate-500">Mon pointage aujourd'hui</p>
        <h2 class="text-xl font-semibold text-slate-800 mt-2">{{ $selfEmployee->full_name }}</h2>
        <p class="text-sm text-slate-500 mt-1">Emplacement : {{ $selfEmployee->site->name ?? 'Non défini' }}</p>
        <div class="mt-4 space-y-2 text-sm text-slate-600">
            <p><strong>Entrée :</strong> {{ $selfAttendance->check_in ?? '—' }}</p>
            <p><strong>Sortie :</strong> {{ $selfAttendance->check_out ?? '—' }}</p>
            <p><strong>Statut :</strong> {{ $selfAttendance?->status ? ucfirst($selfAttendance->status) : 'Non pointé' }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-5 border border-slate-200">
        <p class="text-sm text-slate-500">Pointage rapide</p>
        <p class="text-sm text-slate-500 mt-2">Heure de référence : 08:00</p>
        <div class="mt-4 flex flex-col gap-3">
            <form method="POST" action="{{ route('hr.attendance.clock') }}">
                @csrf
                <input type="hidden" name="action" value="check_in">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg font-medium"
                        {{ $selfAttendance && $selfAttendance->check_in ? 'disabled' : '' }}>
                    <i class="fas fa-play mr-2"></i> Début de travail
                </button>
            </form>
            <form method="POST" action="{{ route('hr.attendance.clock') }}">
                @csrf
                <input type="hidden" name="action" value="check_out">
                <button type="submit" class="w-full bg-slate-700 hover:bg-slate-800 text-white px-4 py-3 rounded-lg font-medium"
                        {{ $selfAttendance && $selfAttendance->check_out ? 'disabled' : '' }}>
                    <i class="fas fa-stop mr-2"></i> Fin de travail
                </button>
            </form>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-5 border border-slate-200">
        <p class="text-sm text-slate-500">Rappel</p>
        <ul class="list-disc list-inside text-sm text-slate-600 mt-3 space-y-2">
            <li>Cliquez sur <strong>Début de travail</strong> en arrivant.</li>
            <li>Cliquez sur <strong>Fin de travail</strong> en partant.</li>
            <li>Le statut passe automatiquement à Retard si vous pointez après 08:00.</li>
        </ul>
    </div>
</div>
@endif

{{-- Stats --}}
@php
    $presentC = $attendances->getCollection()->where('status','present')->count();
    $absentC  = $attendances->getCollection()->where('status','absent')->count();
    $lateC    = $attendances->getCollection()->where('status','late')->count();
@endphp
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
        <p class="text-3xl font-bold text-green-600">{{ $presentC }}</p>
        <p class="text-xs text-green-500 font-medium mt-1">Présents</p>
    </div>
    <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
        <p class="text-3xl font-bold text-red-500">{{ $absentC }}</p>
        <p class="text-xs text-red-400 font-medium mt-1">Absents</p>
    </div>
    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
        <p class="text-3xl font-bold text-amber-500">{{ $lateC }}</p>
        <p class="text-xs text-amber-400 font-medium mt-1">Retards</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Employé</th>
                    <th class="px-4 py-3 text-center">Emplacement</th>
                    <th class="px-4 py-3 text-center">Poste</th>
                    <th class="px-4 py-3 text-center">Récession</th>
                    <th class="px-4 py-3 text-center">Entrée</th>
                    <th class="px-4 py-3 text-center">Sortie</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($attendances as $att)
                @php
                    $statusClass = ['present'=>'bg-green-100 text-green-700','absent'=>'bg-red-100 text-red-500','late'=>'bg-amber-100 text-amber-700'][$att->status] ?? '';
                    $statusLabel = ['present'=>'Présent','absent'=>'Absent','late'=>'Retard'][$att->status] ?? $att->status;
                    $shiftLabel  = ['morning'=>'Matin','evening'=>'Soir'][$att->shift] ?? $att->shift;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $att->employee->full_name ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $att->employee->site->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $att->employee->jobTitle->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-600">{{ $shiftLabel }}</span>
                    </td>
                    <td class="px-4 py-3 text-center font-mono text-gray-600">{{ $att->check_in ?? '—' }}</td>
                    <td class="px-4 py-3 text-center font-mono text-gray-600">{{ $att->check_out ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-10 text-gray-400">Aucune présence enregistrée pour cette date.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t">{{ $attendances->appends(['date'=>$date])->links() }}</div>
</div>

{{-- MODAL ADD --}}
<div id="modalAdd" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Enregistrer une présence</h3>
            <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('hr.attendance.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Employé *</label>
                <select name="employee_id" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400">
                    <option value="">-- Choisir --</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Date *</label>
                    <input name="date" type="date" value="{{ $date }}" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Récession *</label>
                    <select name="shift" required class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="morning">Matin</option>
                        <option value="evening">Soir</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Heure entrée</label>
                    <input name="check_in" type="time" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Heure sortie</label>
                    <input name="check_out" type="time" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
            </div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Statut *</label>
                <select name="status" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="present">Présent</option>
                    <option value="absent">Absent</option>
                    <option value="late">Retard</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg text-gray-600 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-4 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
