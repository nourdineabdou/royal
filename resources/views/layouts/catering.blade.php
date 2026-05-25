<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering — @yield('title', 'Module Catering')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 150ms;
            text-decoration: none;
            color: #ccfbf1;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.2); color: #ffffff; }
        .sidebar-link.active { background: #ffffff; color: #0f766e; box-shadow: 0 1px 4px rgba(0,0,0,0.15); }
        .sidebar-link.active i { color: #14b8a6; }
        .sidebar-link i { width: 1rem; text-align: center; flex-shrink: 0; }
        .sidebar-badge {
            margin-left: auto;
            background: rgba(255,255,255,0.25);
            color: #fff;
            font-size: 0.65rem;
            padding: 0.1rem 0.4rem;
            border-radius: 999px;
            font-weight: 700;
        }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
    @stack('head')
</head>
<body class="bg-slate-100 min-h-screen">

<div class="flex min-h-screen">

    {{-- ── SIDEBAR ──────────────────────────────────────────────────────── --}}
    <aside class="hidden md:flex flex-col w-64 bg-gradient-to-b from-teal-700 to-teal-500 text-white shadow-xl shrink-0">

        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-white/20">
            <a href="{{ route('dashboard-modern') }}" class="flex items-center gap-3">
                <img src="{{ asset('catering.jpeg') }}" alt="Catering" class="w-9 h-9 rounded-xl object-cover shadow-lg">
                <div>
                    <p class="font-bold text-sm leading-none">Catering</p>
                    <p class="text-xs text-teal-100 mt-0.5">Module Catering</p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('catering.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('catering.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Tableau de bord
            </a>
            <a href="{{ route('catering.contracts') }}"
               class="sidebar-link {{ request()->routeIs('catering.contracts*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-contract"></i> Contrats
            </a>
            <a href="{{ route('catering.validate') }}"
               class="sidebar-link {{ request()->routeIs('catering.validate') ? 'active' : '' }}">
                <i class="fa-solid fa-qrcode"></i> Valider un code
                <span class="sidebar-badge">Caissier</span>
            </a>
            <a href="{{ route('catering.consumptions') }}"
               class="sidebar-link {{ request()->routeIs('catering.consumptions*') ? 'active' : '' }}">
                <i class="fa-solid fa-list-check"></i> Consommations
            </a>
        </nav>

        {{-- Footer --}}
        <div class="px-4 py-4 border-t border-white/20">
            <a href="{{ route('dashboard-modern') }}"
               class="flex items-center gap-2 text-teal-100 text-xs hover:text-white transition">
                <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ─────────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top bar --}}
        <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-3 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <i class="fa-solid fa-utensils text-teal-500"></i>
                <span class="hidden sm:inline">Catering /</span>
                <span class="font-semibold text-slate-700">@yield('title', 'Tableau de bord')</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">{{ now()->format('d/m/Y') }}</span>
                <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-user text-teal-600 text-xs"></i>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-4 md:mx-6 mt-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="ml-auto text-emerald-400 hover:text-emerald-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        @endif
        @if(session('error'))
        <div class="mx-4 md:mx-6 mt-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        @endif
        @if($errors->any())
        <div class="mx-4 md:mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <i class="fa-solid fa-circle-exclamation mr-2"></i>
            @foreach($errors->all() as $error)
                <span class="block">{{ $error }}</span>
            @endforeach
        </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 p-4 md:p-6 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
