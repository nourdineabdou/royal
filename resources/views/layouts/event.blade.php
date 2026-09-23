<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Events — @yield('title', 'Module Event')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        body { font-family: 'Inter', system-ui, sans-serif; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 16px; border-radius: 10px;
            color: rgba(255,255,255,0.75); font-size: 14px; font-weight: 500;
            text-decoration: none; transition: all .2s;
            margin-bottom: 2px;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.15); color: #fff; }
        .sidebar-link.active { background: rgba(255,255,255,0.2); color: #fff; font-weight: 600; }
        .sidebar-link i { width: 18px; text-align: center; font-size: 15px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-64 min-h-screen bg-gradient-to-b from-violet-800 to-purple-700 flex flex-col shrink-0">
        <div class="p-5 border-b border-white/10">
            <a href="{{ route('event.dashboard') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-star text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-white font-bold text-sm leading-none">Events</p>
                    <p class="text-white/60 text-xs mt-0.5">Module réceptions</p>
                </div>
            </a>
        </div>

        <nav class="flex-1 p-3 space-y-0.5">
            <a href="{{ route('event.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('event.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            <a href="{{ route('event.index') }}"
               class="sidebar-link {{ request()->routeIs('event.index','event.show','event.create') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i> Événements
            </a>
            <a href="{{ route('event.clients') }}"
               class="sidebar-link {{ request()->routeIs('event.clients') ? 'active' : '' }}">
                <i class="fa-solid fa-address-book"></i> Clients
            </a>
            <a href="{{ route('event.services') }}"
               class="sidebar-link {{ request()->routeIs('event.services') ? 'active' : '' }}">
                <i class="fa-solid fa-briefcase"></i> Catalogue Services
            </a>
        </nav>

        <div class="p-4 border-t border-white/10">
            <a href="{{ route('dashboard-modern') }}"
               class="flex items-center gap-2 text-white/60 hover:text-white text-xs transition">
                <i class="fa-solid fa-arrow-left"></i> Retour au menu
            </a>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
        {{-- Top bar --}}
        <header class="bg-white border-b border-slate-100 px-6 py-3 flex items-center justify-between">
            <h1 class="text-sm font-semibold text-slate-700">@yield('title', 'Module Event')</h1>
            <div class="flex items-center gap-3 text-sm text-slate-500">
                <i class="fa-regular fa-user"></i>
                <span>{{ auth()->user()?->name ?? 'Utilisateur' }}</span>
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="px-6 pt-4">
            @if(session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm mb-2">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm mb-2">
                <i class="fa-solid fa-circle-xmark text-red-500"></i>
                {{ session('error') }}
            </div>
            @endif
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm mb-2">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif
        </div>

        {{-- Content --}}
        <main class="flex-1 px-6 py-4">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
