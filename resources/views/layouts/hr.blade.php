<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ressources Humaines') - Complex Royal</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); }
        .sidebar-link.active { background: rgba(255,255,255,0.2); border-left: 3px solid #fff; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    {{-- TOP NAV --}}
    <header class="bg-gradient-to-r from-emerald-700 to-teal-600 text-white shadow-lg z-20 relative">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard-modern') }}" class="text-white/70 hover:text-white transition text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Accueil
                </a>
                <span class="text-white/40">|</span>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm leading-tight">Ressources Humaines</p>
                        <p class="text-white/60 text-xs">Complex Royal</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-white/80 text-sm hidden sm:block">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ now()->translatedFormat('l d F Y') }}
                </span>
                <div class="flex items-center gap-2 bg-white/10 rounded-full px-3 py-1">
                    <i class="fas fa-user-circle"></i>
                    <span class="text-sm">{{ auth()->user()->name ?? 'Admin' }}</span>
                </div>
            </div>
        </div>

        {{-- SUB-NAV --}}
        <nav class="flex gap-1 px-4 pb-2 overflow-x-auto">
            <a href="{{ route('hr.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }} flex items-center gap-2 px-3 py-1.5 rounded-t text-sm text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>
            <a href="{{ route('hr.employees') }}"
               class="sidebar-link {{ request()->routeIs('hr.employees') ? 'active' : '' }} flex items-center gap-2 px-3 py-1.5 rounded-t text-sm text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                <i class="fas fa-id-card"></i> Employés
            </a>
            <a href="{{ route('hr.attendance') }}"
               class="sidebar-link {{ request()->routeIs('hr.attendance') ? 'active' : '' }} flex items-center gap-2 px-3 py-1.5 rounded-t text-sm text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                <i class="fas fa-clock"></i> Présences
            </a>
            <a href="{{ route('hr.leaves') }}"
               class="sidebar-link {{ request()->routeIs('hr.leaves') ? 'active' : '' }} flex items-center gap-2 px-3 py-1.5 rounded-t text-sm text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                <i class="fas fa-calendar-minus"></i> Congés
            </a>
            <a href="{{ route('hr.payroll') }}"
               class="sidebar-link {{ request()->routeIs('hr.payroll') ? 'active' : '' }} flex items-center gap-2 px-3 py-1.5 rounded-t text-sm text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                <i class="fas fa-file-invoice-dollar"></i> Paie
            </a>
            <a href="{{ route('hr.advances') }}"
               class="sidebar-link {{ request()->routeIs('hr.advances') ? 'active' : '' }} flex items-center gap-2 px-3 py-1.5 rounded-t text-sm text-white/90 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                <i class="fas fa-hand-holding-usd"></i> Avances
            </a>
        </nav>
    </header>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-800 px-4 py-3 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-800 px-4 py-3 text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- MAIN CONTENT --}}
    <main class="flex-1 p-4 lg:p-6">
        @yield('content')
    </main>

    <footer class="text-center text-xs text-gray-400 py-3">
        Complex Royal &copy; {{ date('Y') }} &mdash; Module RH
    </footer>

    @stack('scripts')
</body>
</html>
