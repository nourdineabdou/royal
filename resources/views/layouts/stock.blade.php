<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock — @yield('title', 'Module Stock')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
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
            color: #d1fae5;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.2); color: #ffffff; }
        .sidebar-link.active { background: #ffffff; color: #059669; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
        .sidebar-link.active i { color: #10b981; }
        .sidebar-link i { width: 1rem; text-align: center; flex-shrink: 0; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
    @stack('head')
</head>
<body class="bg-slate-100 min-h-screen">

<div class="flex min-h-screen">

    {{-- ── SIDEBAR ──────────────────────────────────────────────────────── --}}
    <aside class="hidden md:flex flex-col w-64 bg-gradient-to-b from-emerald-700 to-teal-500 text-white shadow-xl shrink-0">

        <div class="px-5 py-6 border-b border-white/20">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-boxes-stacked text-white text-base"></i>
                </div>
                <div>
                    <p class="font-bold text-sm leading-tight">Module Stock</p>
                    <p class="text-xs text-emerald-200">Inventaire & Mouvements</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('stock.index') }}"
               class="sidebar-link {{ request()->routeIs('stock.index') ? 'active' : '' }}">
                <i class="fa-solid fa-warehouse"></i> Tous les stocks
            </a>
            <a href="{{ route('stock.all-movements') }}"
               class="sidebar-link {{ request()->routeIs('stock.all-movements') ? 'active' : '' }}">
                <i class="fa-solid fa-arrows-rotate"></i> Tous les mouvements
            </a>
            <a href="{{ route('stock.transfer') }}"
               class="sidebar-link {{ request()->routeIs('stock.transfer') ? 'active' : '' }}">
                <i class="fa-solid fa-right-left"></i> Transfert de stock
            </a>
            <div class="pt-3 pb-1 px-2">
                <p class="text-xs font-semibold text-emerald-200 uppercase tracking-widest">Navigation</p>
            </div>
            <a href="{{ url('/dashboard-modern') }}" class="sidebar-link">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            <a href="{{ url('/modules/purchases') }}" class="sidebar-link">
                <i class="fa-solid fa-cart-shopping"></i> Achats
            </a>
        </nav>

        <div class="p-4 border-t border-white/20 text-xs text-emerald-200 flex items-center gap-2">
            <i class="fa-solid fa-user-circle text-base"></i>
            {{ auth()->user()->name ?? '—' }}
        </div>
    </aside>

    {{-- ── MAIN ─────────────────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-boxes-stacked text-emerald-600 text-xl"></i>
                <span class="font-semibold text-slate-800">@yield('header', 'Gestion des Stocks')</span>
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-xs text-slate-500 hover:text-red-600 flex items-center gap-1">
                        <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                    </button>
                </form>
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="px-6 pt-3">
            @if(session('success'))
                <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-2 text-sm mb-3">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2 text-sm mb-3">
                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i> {{ session('error') }}
                </div>
            @endif
        </div>

        <main class="flex-1 overflow-y-auto px-6 pb-8">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
