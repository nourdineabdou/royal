<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résidence — @yield('title', 'Module Résidence')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px; border: 1px solid #d1d5db; border-radius: 0.5rem;
            display: flex; align-items: center; padding: 0 0.5rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal; padding-left: 0.25rem; color: #111827;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,0.3);
        }
    </style>
    <script>
        (function ($) {
            function initSelect2(scope) {
                $(scope || document).find('select:not(.select2-hidden-accessible)').each(function () {
                    $(this).select2({ width: 'resolve' });
                });
            }
            $(function () { initSelect2(); });
            if (window.MutationObserver) {
                new MutationObserver(function (mutations) {
                    mutations.forEach(function (m) {
                        m.addedNodes.forEach(function (node) {
                            if (node.nodeType !== 1) return;
                            if (node.matches && node.matches('select')) initSelect2(node.parentNode || document);
                            else if (node.querySelectorAll) initSelect2(node);
                        });
                    });
                }).observe(document.body, { childList: true, subtree: true });
            }
        })(jQuery);
    </script>
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
            color: #ede9fe;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.2); color: #ffffff; }
        .sidebar-link.active { background: #ffffff; color: #7c3aed; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
        .sidebar-link.active i { color: #8b5cf6; }
        .sidebar-link i { width: 1rem; text-align: center; flex-shrink: 0; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius:10px; }
    </style>
    @stack('head')
</head>
<body class="bg-slate-100 min-h-screen">

<div class="flex min-h-screen">

    {{-- ── SIDEBAR ─────────────────────────────────────────────── --}}
    <aside class="hidden md:flex flex-col w-64 bg-gradient-to-b from-violet-700 to-purple-600 text-white shadow-xl shrink-0">

        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-white/20">
            <a href="{{ route('dashboard-modern') }}" class="flex items-center gap-3">
                <img src="{{ asset('royal_palm.jpeg') }}" alt="Royal Palm" class="w-9 h-9 rounded-xl object-cover shadow-lg">
                <div>
                    <p class="font-bold text-sm leading-none">Royal Palm</p>
                    <p class="text-xs text-violet-200 mt-0.5">Module Résidence</p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('residence.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('residence.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Tableau de bord
            </a>
            <a href="{{ route('residence.calendar') }}"
               class="sidebar-link {{ request()->routeIs('residence.calendar') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i> Calendrier
            </a>

            <div class="pt-2 pb-1 px-3">
                <p class="text-xs font-semibold text-violet-300 uppercase tracking-wider">Réservations</p>
            </div>
            <a href="{{ route('residence.bookings') }}"
               class="sidebar-link {{ request()->routeIs('residence.bookings') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-list"></i> Toutes les réservations
            </a>
            <a href="{{ route('residence.bookings.create') }}"
               class="sidebar-link {{ request()->routeIs('residence.bookings.create') ? 'active' : '' }}">
                <i class="fa-solid fa-plus-circle"></i> Nouvelle réservation
            </a>

            <div class="pt-2 pb-1 px-3">
                <p class="text-xs font-semibold text-violet-300 uppercase tracking-wider">Chambres</p>
            </div>
            <a href="{{ route('residence.rooms') }}"
               class="sidebar-link {{ request()->routeIs('residence.rooms') ? 'active' : '' }}">
                <i class="fa-solid fa-door-closed"></i> Chambres
            </a>
            <a href="{{ route('residence.room-types') }}"
               class="sidebar-link {{ request()->routeIs('residence.room-types') ? 'active' : '' }}">
                <i class="fa-solid fa-tags"></i> Types de chambre
            </a>

            <div class="pt-2 pb-1 px-3">
                <p class="text-xs font-semibold text-violet-300 uppercase tracking-wider">Caisse</p>
            </div>
            <a href="{{ route('residence.caisse') }}"
               class="sidebar-link {{ request()->routeIs('residence.caisse') ? 'active' : '' }}">
                <i class="fa-solid fa-cash-register"></i> Caisse résidence
            </a>
        </nav>

        {{-- Footer --}}
        <div class="px-4 py-4 border-t border-white/20">
            <a href="{{ route('dashboard-modern') }}"
               class="flex items-center gap-2 text-violet-200 text-xs hover:text-white transition">
                <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top bar --}}
        <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-3 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <i class="fa-solid fa-building text-violet-600"></i>
                    <span class="hidden sm:inline">Résidence /</span>
                    <span class="font-semibold text-slate-700">@yield('title', 'Tableau de bord')</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">{{ now()->translatedFormat('d M Y') }}</span>
                <div class="w-8 h-8 bg-violet-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-user text-violet-600 text-xs"></i>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-4 md:mx-6 mt-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="mx-4 md:mx-6 mt-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <i class="fa-solid fa-circle-xmark"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif
        @if($errors->any())
        <div class="mx-4 md:mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-4 md:p-6 overflow-auto">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
