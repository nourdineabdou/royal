@extends('layouts.hr')

@section('title', 'Tableau de bord RH')

@section('content')

{{-- PAGE TITLE --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Tableau de bord RH</h1>
    <p class="text-gray-500 text-sm mt-1">Vue d'ensemble — {{ now()->translatedFormat('F Y') }}</p>
</div>

{{-- ===== STATS CARDS ROW 1 : EMPLOYÉS ===== --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4 border-l-4 border-emerald-500">
        <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-xl">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalEmployees }}</p>
            <p class="text-xs text-gray-500">Total employés</p>
            <p class="text-xs mt-0.5">
                <span class="text-emerald-600 font-semibold">{{ $activeEmployees }} actifs</span>
                <span class="text-gray-400 mx-1">·</span>
                <span class="text-red-400">{{ $inactiveEmployees }} inactifs</span>
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4 border-l-4 border-blue-500">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl">
            <i class="fas fa-briefcase"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalJobTitles }}</p>
            <p class="text-xs text-gray-500">Postes / Fonctions</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4 border-l-4 border-amber-500">
        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-xl">
            <i class="fas fa-calendar-minus"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingLeaves }}</p>
            <p class="text-xs text-gray-500">Congés en attente</p>
            <p class="text-xs mt-0.5 text-teal-600 font-semibold">{{ $leavesToday }} absent(s) aujourd'hui</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4 border-l-4 border-violet-500">
        <div class="w-12 h-12 rounded-full bg-violet-100 flex items-center justify-center text-violet-600 text-xl">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingAdvances }}</p>
            <p class="text-xs text-gray-500">Avances en attente</p>
            <p class="text-xs mt-0.5 text-violet-600 font-semibold">{{ number_format($pendingAdvancesAmount, 0, ',', ' ') }} MRU</p>
        </div>
    </div>
</div>

{{-- ===== STATS CARDS ROW 2 : PRÉSENCE & PAIE ===== --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Présents aujourd'hui</p>
            <i class="fas fa-user-check text-green-400"></i>
        </div>
        <p class="text-3xl font-bold text-green-600">{{ $presentToday }}</p>
        <div class="flex gap-3 mt-2 text-xs">
            <span class="text-red-500"><i class="fas fa-times-circle mr-1"></i>{{ $absentToday }} absents</span>
            <span class="text-amber-500"><i class="fas fa-clock mr-1"></i>{{ $lateToday }} retards</span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 border-l-4 border-orange-400">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Paie en attente</p>
            <i class="fas fa-file-invoice-dollar text-orange-400"></i>
        </div>
        <p class="text-3xl font-bold text-orange-500">{{ $payrollsPending }}</p>
        <p class="text-xs text-gray-400 mt-1">fiches à valider ce mois</p>
    </div>

    <div class="bg-white rounded-xl shadow p-4 border-l-4 border-teal-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Paie payée</p>
            <i class="fas fa-check-double text-teal-400"></i>
        </div>
        <p class="text-3xl font-bold text-teal-600">{{ $payrollsPaid }}</p>
        <p class="text-xs text-gray-400 mt-1">fiches soldées ce mois</p>
    </div>

    <div class="bg-white rounded-xl shadow p-4 border-l-4 border-indigo-500">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Masse salariale</p>
            <i class="fas fa-coins text-indigo-400"></i>
        </div>
        <p class="text-2xl font-bold text-indigo-600">{{ number_format($totalPayrollAmount, 0, ',', ' ') }}</p>
        <p class="text-xs text-gray-400 mt-1">MRU ce mois</p>
    </div>
</div>

{{-- ===== CHARTS ROW ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Paie 6 derniers mois --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-bar text-indigo-400"></i>
            Masse salariale — 6 derniers mois
        </h2>
        <canvas id="payrollChart" height="200"></canvas>
    </div>

    {{-- Présence 7 jours --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-line text-green-400"></i>
            Présences — 7 derniers jours
        </h2>
        <canvas id="attendanceChart" height="200"></canvas>
    </div>
</div>

{{-- ===== BOTTOM ROW : Tables ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Derniers employés --}}
    <div class="bg-white rounded-xl shadow">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="font-bold text-gray-700 flex items-center gap-2">
                <i class="fas fa-id-card text-emerald-500"></i> Derniers employés
            </h2>
            <a href="{{ route('hr.employees') }}" class="text-xs text-emerald-600 hover:underline">Voir tout →</a>
        </div>
        <div class="divide-y">
            @forelse($recentEmployees as $emp)
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-sm shrink-0">
                    {{ strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $emp->full_name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $emp->jobTitle->name ?? '—' }}</p>
                </div>
                <div class="text-right shrink-0">
                    <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $emp->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-500' }}">
                        {{ $emp->status === 'active' ? 'Actif' : 'Inactif' }}
                    </span>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $emp->hire_date->format('d/m/Y') }}</p>
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-gray-400 text-sm">Aucun employé enregistré.</p>
            @endforelse
        </div>
    </div>

    {{-- Dernières demandes de congé --}}
    <div class="bg-white rounded-xl shadow">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="font-bold text-gray-700 flex items-center gap-2">
                <i class="fas fa-calendar-minus text-amber-500"></i> Demandes de congé récentes
            </h2>
            <a href="{{ route('hr.leaves') }}" class="text-xs text-amber-600 hover:underline">Voir tout →</a>
        </div>
        <div class="divide-y">
            @forelse($recentLeaves as $leave)
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-sm shrink-0">
                    {{ strtoupper(substr($leave->employee->first_name ?? '?',0,1).substr($leave->employee->last_name ?? '?',0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $leave->employee->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $leave->start_date->format('d/m') }} → {{ $leave->end_date->format('d/m/Y') }}
                        <span class="ml-1 text-gray-400">({{ $leave->days }}j)</span>
                    </p>
                </div>
                <div class="text-right shrink-0 flex flex-col items-end gap-1">
                    @php
                        $typeLabel = ['annual'=>'Annuel','sick'=>'Maladie','unpaid'=>'Non payé'][$leave->type] ?? $leave->type;
                        $statusClass = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-500'][$leave->status] ?? '';
                        $statusLabel = ['pending'=>'En attente','approved'=>'Approuvé','rejected'=>'Rejeté'][$leave->status] ?? $leave->status;
                    @endphp
                    <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $typeLabel }}</span>
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-gray-400 text-sm">Aucune demande de congé.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ===== EMPLOYÉS PAR POSTE ===== --}}
<div class="bg-white rounded-xl shadow p-5 mb-6">
    <h2 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
        <i class="fas fa-sitemap text-blue-400"></i> Répartition par poste
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @foreach($employeesByJobTitle as $jt)
        <div class="text-center p-3 rounded-lg bg-gray-50 border hover:border-emerald-300 transition">
            <p class="text-2xl font-bold text-emerald-600">{{ $jt->employees_count }}</p>
            <p class="text-xs text-gray-600 font-medium mt-1 truncate">{{ $jt->name }}</p>
        </div>
        @endforeach
        @if($employeesByJobTitle->isEmpty())
        <p class="col-span-full text-gray-400 text-sm text-center py-4">Aucun poste défini.</p>
        @endif
    </div>
</div>

{{-- ===== QUICK ACTIONS ===== --}}
<div class="bg-gradient-to-r from-emerald-700 to-teal-600 rounded-xl p-5 text-white">
    <h2 class="font-bold mb-4 text-lg flex items-center gap-2">
        <i class="fas fa-bolt"></i> Actions rapides
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
        <a href="{{ route('hr.employees') }}"
           class="flex flex-col items-center gap-2 bg-white/15 hover:bg-white/25 rounded-lg py-4 px-3 transition text-center">
            <i class="fas fa-user-plus text-2xl"></i>
            <span class="text-xs font-medium">Ajouter employé</span>
        </a>
        <a href="{{ route('hr.attendance') }}"
           class="flex flex-col items-center gap-2 bg-white/15 hover:bg-white/25 rounded-lg py-4 px-3 transition text-center">
            <i class="fas fa-clipboard-check text-2xl"></i>
            <span class="text-xs font-medium">Marquer présence</span>
        </a>
        <a href="{{ route('hr.leaves') }}"
           class="flex flex-col items-center gap-2 bg-white/15 hover:bg-white/25 rounded-lg py-4 px-3 transition text-center">
            <i class="fas fa-calendar-plus text-2xl"></i>
            <span class="text-xs font-medium">Demande congé</span>
        </a>
        <a href="{{ route('hr.payroll') }}"
           class="flex flex-col items-center gap-2 bg-white/15 hover:bg-white/25 rounded-lg py-4 px-3 transition text-center">
            <i class="fas fa-file-invoice-dollar text-2xl"></i>
            <span class="text-xs font-medium">Générer paie</span>
        </a>
        <a href="{{ route('hr.advances') }}"
           class="flex flex-col items-center gap-2 bg-white/15 hover:bg-white/25 rounded-lg py-4 px-3 transition text-center">
            <i class="fas fa-hand-holding-usd text-2xl"></i>
            <span class="text-xs font-medium">Nouvelle avance</span>
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ---- Payroll Chart ----
const payrollData = @json($payrollChart);
new Chart(document.getElementById('payrollChart'), {
    type: 'bar',
    data: {
        labels: payrollData.map(d => d.label),
        datasets: [{
            label: 'Masse salariale (MRU)',
            data: payrollData.map(d => d.amount),
            backgroundColor: 'rgba(99, 102, 241, 0.7)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => v.toLocaleString('fr-FR') + ' MRU' }
            }
        }
    }
});

// ---- Attendance Chart ----
const attData = @json($attendanceChart);
new Chart(document.getElementById('attendanceChart'), {
    type: 'line',
    data: {
        labels: attData.map(d => d.label),
        datasets: [
            {
                label: 'Présents',
                data: attData.map(d => d.present),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            },
            {
                label: 'Absents',
                data: attData.map(d => d.absent),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239,68,68,0.08)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            },
            {
                label: 'Retards',
                data: attData.map(d => d.late),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245,158,11,0.08)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
@endpush
