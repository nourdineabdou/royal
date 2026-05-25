@extends('layouts.hr')
@section('title', 'Présences')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Présences</h1>
        <p class="text-gray-500 text-sm mt-1">{{ \Carbon\Carbon::parse($date)->translatedFormat('l d F Y') }}</p>
    </div>
    <div class="flex items-center gap-3">
        <form method="GET" class="flex gap-2">
            <input name="date" type="date" value="{{ $date }}" class="border rounded-lg px-3 py-2 text-sm">
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
