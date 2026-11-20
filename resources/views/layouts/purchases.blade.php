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
            // Select2 + jQuery 3.6 : le curseur ne va pas tout seul dans la recherche à l'ouverture
            $(document).on('select2:open', function () {
                setTimeout(function () { var s = document.querySelector('.select2-container--open .select2-search__field'); if (s) s.focus(); }, 0);
            });
            if (window.MutationObserver) {
                new MutationObserver(function (mutations) {
                    mutations.forEach(function (m) {
                        m.addedNodes.forEach(function (node) {
                            if (node.nodeType !== 1) return;
                            if (node.matches && node.matches('select')) initSelect2(node.parentNode || document);
                            else if (node.querySelectorAll) initSelect2(node);
                        });
                    });
                }).observe(document.documentElement, { childList: true, subtree: true });
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
            @can('purchases.dashboard')
            <a href="{{ route('purchases.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('purchases.dashboard') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-chart-pie w-4 text-center"></i> Tableau de bord
            </a>
            @endcan
            @can('purchases.requests.view')
            <a href="{{ route('purchases.requests') }}"
               class="sidebar-link {{ request()->routeIs('purchases.requests*') && !request()->routeIs('purchases.requests.auto*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-clipboard-list w-4 text-center"></i> Demandes d'achat
            </a>
            @endcan
            @can('purchases.requests.create')
            <a href="{{ route('purchases.requests.auto') }}"
               class="sidebar-link {{ request()->routeIs('purchases.requests.auto') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-wand-magic-sparkles w-4 text-center"></i> Demande d'achat auto
                @php $__autoDa = \App\Models\PurchaseRequest::where('auto_generated', true)->where('status', 'draft')->withCount('items')->latest('id')->first(); @endphp
                @if($__autoDa && $__autoDa->items_count)<span class="ml-auto bg-white text-red-600 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $__autoDa->items_count }}</span>@endif
            </a>
            @endcan
            @can('purchases.orders.view')
            <a href="{{ route('purchases.orders') }}"
               class="sidebar-link {{ request()->routeIs('purchases.orders*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-file-invoice w-4 text-center"></i> Commandes
            </a>
            @endcan
            @can('purchases.orders.create')
            <a href="{{ route('purchases.orders.create') }}"
               class="sidebar-link {{ request()->routeIs('purchases.orders.create') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-plus-circle w-4 text-center"></i> Nouvelle commande
            </a>
            @endcan
            @can('purchases.orders.receipt')
            <a href="{{ route('purchases.deliveries') }}"
               class="sidebar-link {{ request()->routeIs('purchases.deliveries', 'purchases.receptions.*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-truck-ramp-box w-4 text-center"></i> Réceptions (BL) @php $__pendingBl = \App\Models\PurchaseOrder::whereIn('status', ['ordered', 'partial'])->count(); @endphp
                @if($__pendingBl)<span class="ml-auto bg-white text-emerald-700 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $__pendingBl }}</span>@endif
            </a>
            @endcan
            @can('purchases.suppliers.view')
            <a href="{{ route('purchases.suppliers') }}"
               class="sidebar-link {{ request()->routeIs('purchases.suppliers*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-truck w-4 text-center"></i> Fournisseurs
            </a>
            @endcan
            @can('products.view')
            <a href="{{ route('purchases.products.index') }}"
               class="sidebar-link {{ request()->routeIs('purchases.products.*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-box w-4 text-center"></i> Produits
            </a>
            @endcan
            @can('units.view')
            <a href="{{ route('purchases.units.index') }}"
               class="sidebar-link {{ request()->routeIs('purchases.units.*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-ruler w-4 text-center"></i> Unités
            </a>
            @endcan
            @can('packagings.view')
            <a href="{{ route('purchases.packagings.index') }}"
               class="sidebar-link {{ request()->routeIs('purchases.packagings.*') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-cube w-4 text-center"></i> Emballages
            </a>
            @endcan
            @can('purchases.stock-ruptures.view')
            <a href="{{ route('purchases.stock-ruptures') }}"
               class="sidebar-link {{ request()->routeIs('purchases.stock-ruptures') ? 'active' : 'text-orange-50' }}">
                <i class="fa-solid fa-triangle-exclamation w-4 text-center"></i> Ruptures de stock
            </a>
            @endcan
            <a href="{{ route('purchases.guide') }}" class="sidebar-link text-orange-50">
                <i class="fa-solid fa-book-open w-4 text-center"></i> Guide d'utilisation
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
        <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-3 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('dashboard-modern') }}"
                   class="shrink-0 inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-2 rounded-lg shadow-sm transition"
                   title="Retour à l'accueil">
                    <i class="fa-solid fa-house"></i> <span class="hidden sm:inline">Accueil</span>
                </a>
                <div class="flex items-center gap-2 text-sm text-slate-500 min-w-0 truncate">
                    <i class="fa-solid fa-cart-shopping text-orange-500"></i>
                    <span class="hidden sm:inline">Achats /</span>
                    <span class="font-semibold text-slate-700">@yield('title', 'Tableau de bord')</span>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span class="hidden sm:inline text-xs text-slate-400">{{ now()->translatedFormat('d M Y') }}</span>
                <a href="{{ route('password.edit') }}" title="Mon compte — {{ auth()->user()->name ?? '' }}" class="h-8 px-2 bg-orange-100 hover:bg-orange-200 rounded-full flex items-center gap-1.5 text-orange-600">
                    <i class="fa-solid fa-user text-xs"></i><i class="fa-solid fa-key text-[10px]"></i>
                </a>
            </div>
        </header>

        {{-- Navigation mobile / tablette (la barre latérale est masquée sous md) --}}
        @php $__menuPerms = array (
  'purchases.dashboard' => 'purchases.dashboard',
  'purchases.requests' => 'purchases.requests.view',
  'purchases.orders' => 'purchases.orders.view',
  'purchases.orders.create' => 'purchases.orders.create',
  'purchases.suppliers' => 'purchases.suppliers.view',
  'purchases.products.index' => 'products.view',
  'purchases.units.index' => 'units.view',
  'purchases.packagings.index' => 'packagings.view',
  'purchases.stock-ruptures' => 'purchases.stock-ruptures.view',
  'purchases.deliveries' => 'purchases.orders.receipt',
); @endphp
        <nav class="md:hidden bg-orange-600 text-white flex gap-1 overflow-x-auto px-2 py-2 text-xs font-medium whitespace-nowrap">
            @foreach([
                ['purchases.dashboard',      'purchases.dashboard',        'fa-chart-pie',             'Tableau de bord'],
                ['purchases.requests',       'purchases.requests*',        'fa-clipboard-list',        'Demandes'],
                ['purchases.orders',         'purchases.orders*',          'fa-file-invoice',          'Commandes'],
                ['purchases.deliveries',     'purchases.deliveries',       'fa-truck-ramp-box',        'Réceptions'],
                ['purchases.suppliers',      'purchases.suppliers*',       'fa-truck',                 'Fournisseurs'],
                ['purchases.products.index', 'purchases.products.*',       'fa-box',                   'Produits'],
                ['purchases.units.index',    'purchases.units.*',          'fa-ruler',                 'Unités'],
                ['purchases.packagings.index','purchases.packagings.*',    'fa-cube',                  'Emballages'],
                ['purchases.stock-ruptures', 'purchases.stock-ruptures',   'fa-triangle-exclamation',  'Ruptures'],
                ['purchases.guide',          'purchases.guide',            'fa-book-open',             'Guide'],
            ] as [$r, $pattern, $icon, $label])
                @continue(isset($__menuPerms[$r]) && !auth()->user()?->can($__menuPerms[$r]))
                <a href="{{ route($r) }}"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg {{ request()->routeIs($pattern) ? 'bg-white text-orange-600' : 'hover:bg-white/20' }}">
                    <i class="fa-solid {{ $icon }}"></i> {{ $label }}
                </a>
            @endforeach
        </nav>

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
