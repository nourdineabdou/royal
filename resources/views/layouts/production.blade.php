<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Production') - Complex Royal</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery -->
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
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-indigo-700 via-indigo-600 to-blue-600 text-white shadow-2xl fixed h-full overflow-y-auto">
            <!-- Logo Section -->
            <div class="p-6 border-b border-indigo-500">
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('royal grill.jpeg') }}" alt="Royal Grill" class="w-12 h-12 rounded-lg object-cover shadow-lg">
                    <div>
                        <h1 class="text-xl font-bold">Royal Grill</h1>
                        <p class="text-indigo-200 text-xs">Production</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="p-4 space-y-2">
                <!-- Dashboard -->
                <a href="{{ route('production.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition duration-300 {{ request()->routeIs('production.dashboard') ? 'bg-white text-indigo-700 shadow-lg font-bold' : 'text-indigo-100 hover:bg-indigo-600' }}">
                    <i class="fas fa-chart-line text-lg"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Management Section -->
                <div class="pt-4 mt-4 border-t border-indigo-500">
                    <p class="px-4 text-indigo-300 text-xs font-bold uppercase tracking-wider mb-3">Gestions</p>

                    <!-- Meals -->
                    <div class="mb-2">
                        <button onclick="toggleSubmenu('meals')"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition duration-300 {{ request()->routeIs('meals.*') ? 'bg-white text-indigo-700 shadow-lg font-bold' : 'text-indigo-100 hover:bg-indigo-600' }}">
                            <span class="flex items-center gap-3">
                                <i class="fas fa-utensils text-lg"></i>
                                <span>Repas</span>
                            </span>
                            <i class="fas fa-chevron-right text-sm transition-transform duration-300 {{ request()->routeIs('meals.*') ? 'rotate-90' : '' }}" id="chevron-meals"></i>
                        </button>
                        <div id="meals-submenu" class="hidden pl-4 space-y-1 mt-1 {{ request()->routeIs('meals.*') ? 'block' : '' }}">
                            <a href="{{ route('meals.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-indigo-100 hover:bg-indigo-600 transition {{ request()->routeIs('meals.index') ? 'bg-indigo-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-list"></i>
                                <span>Liste des Repas</span>
                            </a>
                            <a href="{{ route('meals.create') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-indigo-100 hover:bg-indigo-600 transition {{ request()->routeIs('meals.create') ? 'bg-indigo-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Ajouter Repas</span>
                            </a>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="mb-2">
                        <button onclick="toggleSubmenu('categories')"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition duration-300 {{ request()->routeIs('categories.*') ? 'bg-white text-indigo-700 shadow-lg font-bold' : 'text-indigo-100 hover:bg-indigo-600' }}">
                            <span class="flex items-center gap-3">
                                <i class="fas fa-layer-group text-lg"></i>
                                <span>Catégories</span>
                            </span>
                            <i class="fas fa-chevron-right text-sm transition-transform duration-300 {{ request()->routeIs('categories.*') ? 'rotate-90' : '' }}" id="chevron-categories"></i>
                        </button>
                        <div id="categories-submenu" class="hidden pl-4 space-y-1 mt-1 {{ request()->routeIs('categories.*') ? 'block' : '' }}">
                            <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-indigo-100 hover:bg-indigo-600 transition {{ request()->routeIs('categories.index') ? 'bg-indigo-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-list"></i>
                                <span>Lister les Catégories</span>
                            </a>
                            <a href="{{ route('categories.create') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-indigo-100 hover:bg-indigo-600 transition {{ request()->routeIs('categories.create') ? 'bg-indigo-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Ajouter Catégorie</span>
                            </a>
                        </div>
                    </div>

                    <!-- Accompaniments -->
                    <div class="mb-2">
                        <button onclick="toggleSubmenu('accompaniments')"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition duration-300 {{ request()->routeIs('accompaniments.*') ? 'bg-white text-indigo-700 shadow-lg font-bold' : 'text-indigo-100 hover:bg-indigo-600' }}">
                            <span class="flex items-center gap-3">
                                <i class="fas fa-star text-lg"></i>
                                <span>Accompagnements</span>
                            </span>
                            <i class="fas fa-chevron-right text-sm transition-transform duration-300 {{ request()->routeIs('accompaniments.*') ? 'rotate-90' : '' }}" id="chevron-accompaniments"></i>
                        </button>
                        <div id="accompaniments-submenu" class="hidden pl-4 space-y-1 mt-1 {{ request()->routeIs('accompaniments.*') ? 'block' : '' }}">
                            <a href="{{ route('accompaniments.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-indigo-100 hover:bg-indigo-600 transition {{ request()->routeIs('accompaniments.index') ? 'bg-indigo-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-list"></i>
                                <span>Lister les Accompagnements</span>
                            </a>
                            <a href="{{ route('accompaniments.create') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-indigo-100 hover:bg-indigo-600 transition {{ request()->routeIs('accompaniments.create') ? 'bg-indigo-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Ajouter Accompagnement</span>
                            </a>
                        </div>
                    </div>

                    <!-- Pertes / Périmés -->
                    <a href="{{ route('production.waste.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition duration-300 mb-2 {{ request()->routeIs('production.waste.*') ? 'bg-white text-indigo-700 shadow-lg font-bold' : 'text-indigo-100 hover:bg-indigo-600' }}">
                        <i class="fas fa-trash-alt text-lg"></i>
                        <span>Pertes / Périmés</span>
                    </a>
                </div>

                <!-- POS Section -->
                <div class="pt-4 mt-6 border-t border-indigo-500">
                    <p class="px-4 text-indigo-300 text-xs font-bold uppercase tracking-wider mb-3">Point de Vente</p>
                    <a href="{{ route('modules.pos') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-indigo-100 hover:bg-indigo-600 transition duration-300 {{ request()->routeIs('modules.pos') ? 'bg-white text-indigo-700 shadow-lg font-bold' : '' }}">
                        <i class="fas fa-cash-register text-lg"></i>
                        <span>POS</span>
                    </a>
                    <a href="{{ route('pos.orders') }}" class="flex items-center gap-3 px-4 py-3 text-sm rounded-lg text-indigo-100 hover:bg-indigo-600 transition duration-300 {{ request()->routeIs('pos.orders') ? 'bg-indigo-500 text-white font-semibold' : '' }}">
                        <i class="fas fa-history text-sm"></i>
                        <span>Historique des Commandes</span>
                    </a>
                </div>

                <!-- Return Section -->
                <div class="pt-4 mt-4 border-t border-indigo-500">
                    <a href="{{ route('dashboard-modern') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-indigo-100 hover:bg-indigo-600 transition duration-300">
                        <i class="fas fa-arrow-left"></i>
                        <span>Retour au Dashboard</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="ml-64 w-full flex flex-col">
            <!-- Fixed Header -->
            <header class="bg-white shadow-lg sticky top-0 z-40">
                <div class="px-8 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">@yield('page_title', 'Production')</h2>
                        <p class="text-gray-500 text-sm">@yield('page_subtitle', '')</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-gray-700 font-semibold">{{ Auth::user()->name ?? 'Utilisateur' }}</p>
                            <p class="text-gray-500 text-xs">{{ Auth::user()->email ?? '' }}</p>
                        </div>
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-blue-500 rounded-full flex items-center justify-center text-white">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <h3 class="text-red-800 font-bold mb-2">
                            <i class="fas fa-exclamation-circle mr-2"></i>Erreurs
                        </h3>
                        <ul class="text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li><i class="fas fa-times-circle text-sm mr-2"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded flex items-center justify-between">
                        <div class="text-green-700 flex items-center">
                            <i class="fas fa-check-circle mr-3 text-lg"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.style.display='none'" class="text-green-700 hover:text-green-900">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSubmenu(menu) {
            const submenu = document.getElementById(menu + '-submenu');
            const chevron = document.getElementById('chevron-' + menu);

            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                submenu.classList.add('block');
                chevron.style.transform = 'rotate(90deg)';
            } else {
                submenu.classList.add('hidden');
                submenu.classList.remove('block');
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        // Auto-open active submenu on page load
        document.addEventListener('DOMContentLoaded', function() {
            const activeMenus = ['meals', 'categories', 'accompaniments', 'products', 'units', 'packagings'];
            activeMenus.forEach(menu => {
                const submenu = document.getElementById(menu + '-submenu');
                if (submenu && submenu.classList.contains('block')) {
                    const chevron = document.getElementById('chevron-' + menu);
                    if (chevron) {
                        chevron.style.transform = 'rotate(90deg)';
                    }
                }
            });
        });
    </script>
</body>
</html>
