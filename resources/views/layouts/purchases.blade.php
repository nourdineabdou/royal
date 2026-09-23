<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achats — @yield('title', 'Module Achats')</title>
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
            line-height: 1.25rem;
            font-weight: 500;
            transition: all 150ms;
            text-decoration: none;
            color: #fff0e6;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.2); color: #ffffff; }
        .sidebar-link.active { background: #ffffff; color: #ea580c; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
        .sidebar-link.active i { color: #f97316; }
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
    <aside class="hidden md:flex flex-col w-64 bg-gradient-to-b from-orange-600 to-amber-500 text-white shadow-xl shrink-0">

        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-white/20">
            <a href="{{ route('dashboard-modern') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center shadow">
                    <i class="fa-solid fa-cart-shopping text-orange-500 text-lg"></i>
                </div>
                <div>
                    <p class="font-bold text-sm leading-none">Complex Royal</p>
                    <p class="text-xs text-orange-100 mt-0.5">Module Achats</p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('purchases.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('purchases.dashboard') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-chart-pie w-4 text-center"></i> Tableau de bord
            </a>
            <a href="{{ route('purchases.requests') }}"
               class="sidebar-link {{ request()->routeIs('purchases.requests*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-clipboard-list w-4 text-center"></i> Demandes d'achat
            </a>
            <a href="{{ route('purchases.orders') }}"
               class="sidebar-link {{ request()->routeIs('purchases.orders*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-file-invoice w-4 text-center"></i> Commandes
            </a>
            <a href="{{ route('purchases.orders.create') }}"
               class="sidebar-link {{ request()->routeIs('purchases.orders.create') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-plus-circle w-4 text-center"></i> Nouvelle commande
            </a>
            <a href="{{ route('purchases.suppliers') }}"
               class="sidebar-link {{ request()->routeIs('purchases.suppliers*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-truck w-4 text-center"></i> Fournisseurs
            </a>
            <a href="{{ route('purchases.products.index') }}"
               class="sidebar-link {{ request()->routeIs('purchases.products.*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-box w-4 text-center"></i> Produits
            </a>
            <a href="{{ route('purchases.units.index') }}"
               class="sidebar-link {{ request()->routeIs('purchases.units.*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-ruler w-4 text-center"></i> Unités
            </a>
            <a href="{{ route('purchases.packagings.index') }}"
               class="sidebar-link {{ request()->routeIs('purchases.packagings.*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-cube w-4 text-center"></i> Emballages
            </a>
            <a href="{{ route('purchases.stock-ruptures') }}"
               class="sidebar-link {{ request()->routeIs('purchases.stock-ruptures') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-triangle-exclamation w-4 text-center"></i> Ruptures de stock
            </a>
        </nav>

        {{-- Footer --}}
        <div class="px-4 py-4 border-t border-white/20">
            <a href="{{ route('dashboard-modern') }}"
               class="flex items-center gap-2 text-orange-100 text-xs hover:text-white transition">
                <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top bar (mobile + breadcrumb) --}}
        <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-3 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                {{-- Mobile menu could go here --}}
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <i class="fa-solid fa-cart-shopping text-orange-500"></i>
                    <span class="hidden sm:inline">Achats /</span>
                    <span class="font-semibold text-slate-700">@yield('title', 'Tableau de bord')</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">{{ now()->translatedFormat('d M Y') }}</span>
                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-user text-orange-500 text-xs"></i>
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

        {{-- Page content --}}
        <main class="flex-1 p-4 md:p-6 overflow-auto">
            @yield('content')
        </main>
    </div>
</div>

{{-- Visionneuse d'image réutilisable (emballages, produits...) --}}
<div id="imageLightbox" class="hidden" style="position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,0.85);align-items:center;justify-content:center;flex-direction:column;padding:2rem;" onclick="closeImageLightbox()">
    <img id="imageLightboxImg" src="" alt="" style="max-width:min(90vw,600px);max-height:75vh;border-radius:1.5rem;box-shadow:0 20px 60px rgba(0,0,0,0.5);">
    <p id="imageLightboxTitle" style="color:#fff;font-weight:700;margin-top:1rem;font-size:1.1rem;"></p>
    <button onclick="closeImageLightbox()" style="position:absolute;top:1.5rem;right:1.5rem;color:#fff;background:rgba(255,255,255,0.15);border:none;width:2.5rem;height:2.5rem;border-radius:9999px;font-size:1.1rem;cursor:pointer;">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
<script>
    function openImageLightbox(url, title) {
        document.getElementById('imageLightboxImg').src = url;
        document.getElementById('imageLightboxTitle').textContent = title || '';
        const box = document.getElementById('imageLightbox');
        box.classList.remove('hidden');
        box.style.display = 'flex';
    }
    function closeImageLightbox() {
        const box = document.getElementById('imageLightbox');
        box.style.display = 'none';
        document.getElementById('imageLightboxImg').src = '';
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeImageLightbox();
    });
</script>

@stack('scripts')
</body>
</html>
