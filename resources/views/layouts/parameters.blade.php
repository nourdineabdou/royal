<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Paramètres') - Complex Royal</title>
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
        <div class="w-64 bg-gradient-to-b from-amber-700 via-amber-600 to-orange-600 text-white shadow-2xl fixed h-full overflow-y-auto">
            <!-- Logo Section -->
            <div class="p-6 border-b border-amber-500">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-yellow-300 rounded-lg flex items-center justify-center">
                        <i class="fas fa-crown text-amber-700 text-xl font-bold"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Complex Royal</h1>
                        <p class="text-amber-200 text-xs">Paramètres</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="p-4 space-y-2">
                <!-- Dashboard -->
                <a href="{{ route('dashboard-modern') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition duration-300 {{ request()->routeIs('dashboard-modern') ? 'bg-white text-amber-700 shadow-lg font-bold' : 'text-amber-100 hover:bg-amber-600' }}">
                    <i class="fas fa-chart-line text-lg"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Management Section -->
                <div class="pt-4 mt-4 border-t border-amber-500">
                    <p class="px-4 text-amber-300 text-xs font-bold uppercase tracking-wider mb-3">Gestions</p>

                    <!-- POS Terminals -->
                    <div class="mb-2">
                        <button onclick="toggleSubmenu('pos-terminals')"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition duration-300 {{ request()->routeIs('settings.pos-terminals.*') ? 'bg-white text-amber-700 shadow-lg font-bold' : 'text-amber-100 hover:bg-amber-600' }}">
                            <span class="flex items-center gap-3">
                                <i class="fas fa-store text-lg"></i>
                                <span>Points de Vente</span>
                            </span>
                            <i class="fas fa-chevron-right text-sm transition-transform duration-300 {{ request()->routeIs('settings.pos-terminals.*') ? 'rotate-90' : '' }}" id="chevron-pos-terminals"></i>
                        </button>
                        <div id="pos-terminals-submenu" class="hidden pl-4 space-y-1 mt-1 {{ request()->routeIs('settings.pos-terminals.*') ? 'block' : '' }}">
                            <a href="{{ route('settings.pos-terminals.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-amber-100 hover:bg-amber-600 transition {{ request()->routeIs('settings.pos-terminals.index') ? 'bg-amber-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-list"></i>
                                <span>Lister les terminaux</span>
                            </a>
                            <a href="{{ route('settings.pos-terminals.create') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-amber-100 hover:bg-amber-600 transition {{ request()->routeIs('settings.pos-terminals.create') ? 'bg-amber-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Nouveau terminal</span>
                            </a>
                        </div>
                    </div>

                    <!-- Payment Types -->
                    <div class="mb-2">
                        <button onclick="toggleSubmenu('payment-types')"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition duration-300 {{ request()->routeIs('payment-types.*') ? 'bg-white text-amber-700 shadow-lg font-bold' : 'text-amber-100 hover:bg-amber-600' }}">
                            <span class="flex items-center gap-3">
                                <i class="fas fa-credit-card text-lg"></i>
                                <span>Types de Paiement</span>
                            </span>
                            <i class="fas fa-chevron-right text-sm transition-transform duration-300 {{ request()->routeIs('payment-types.*') ? 'rotate-90' : '' }}" id="chevron-payment-types"></i>
                        </button>
                        <div id="payment-types-submenu" class="hidden pl-4 space-y-1 mt-1 {{ request()->routeIs('payment-types.*') ? 'block' : '' }}">
                            <a href="{{ route('payment-types.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-amber-100 hover:bg-amber-600 transition {{ request()->routeIs('payment-types.index') ? 'bg-amber-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-list"></i>
                                <span>Lister les Types</span>
                            </a>
                            <a href="{{ route('payment-types.create') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-amber-100 hover:bg-amber-600 transition {{ request()->routeIs('payment-types.create') ? 'bg-amber-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Ajouter Type</span>
                            </a>
                        </div>
                    </div>

                    <!-- Users -->
                    <div class="mb-2">
                        <button onclick="toggleSubmenu('users')"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition duration-300 {{ request()->routeIs('users.*') ? 'bg-white text-amber-700 shadow-lg font-bold' : 'text-amber-100 hover:bg-amber-600' }}">
                            <span class="flex items-center gap-3">
                                <i class="fas fa-users text-lg"></i>
                                <span>Utilisateurs</span>
                            </span>
                            <i class="fas fa-chevron-right text-sm transition-transform duration-300 {{ request()->routeIs('users.*') ? 'rotate-90' : '' }}" id="chevron-users"></i>
                        </button>
                        <div id="users-submenu" class="hidden pl-4 space-y-1 mt-1 {{ request()->routeIs('users.*') ? 'block' : '' }}">
                            <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-amber-100 hover:bg-amber-600 transition {{ request()->routeIs('users.index') ? 'bg-amber-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-list"></i>
                                <span>Lister les Utilisateurs</span>
                            </a>
                            <a href="{{ route('users.create') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-amber-100 hover:bg-amber-600 transition {{ request()->routeIs('users.create') ? 'bg-amber-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Ajouter Utilisateur</span>
                            </a>
                        </div>
                    </div>

                    <!-- Roles -->
                    <div class="mb-2">
                        <button onclick="toggleSubmenu('roles')"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition duration-300 {{ request()->routeIs('roles.*') ? 'bg-white text-amber-700 shadow-lg font-bold' : 'text-amber-100 hover:bg-amber-600' }}">
                            <span class="flex items-center gap-3">
                                <i class="fas fa-shield-alt text-lg"></i>
                                <span>Rôles</span>
                            </span>
                            <i class="fas fa-chevron-right text-sm transition-transform duration-300 {{ request()->routeIs('roles.*') ? 'rotate-90' : '' }}" id="chevron-roles"></i>
                        </button>
                        <div id="roles-submenu" class="hidden pl-4 space-y-1 mt-1 {{ request()->routeIs('roles.*') ? 'block' : '' }}">
                            <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-amber-100 hover:bg-amber-600 transition {{ request()->routeIs('roles.index') ? 'bg-amber-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-list"></i>
                                <span>Lister les Rôles</span>
                            </a>
                            <a href="{{ route('roles.create') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-amber-100 hover:bg-amber-600 transition {{ request()->routeIs('roles.create') ? 'bg-amber-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Ajouter Rôle</span>
                            </a>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="mb-2">
                        <button onclick="toggleSubmenu('permissions')"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition duration-300 {{ request()->routeIs('permissions.*') ? 'bg-white text-amber-700 shadow-lg font-bold' : 'text-amber-100 hover:bg-amber-600' }}">
                            <span class="flex items-center gap-3">
                                <i class="fas fa-key text-lg"></i>
                                <span>Permissions</span>
                            </span>
                            <i class="fas fa-chevron-right text-sm transition-transform duration-300 {{ request()->routeIs('permissions.*') ? 'rotate-90' : '' }}" id="chevron-permissions"></i>
                        </button>
                        <div id="permissions-submenu" class="hidden pl-4 space-y-1 mt-1 {{ request()->routeIs('permissions.*') ? 'block' : '' }}">
                            <a href="{{ route('permissions.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-amber-100 hover:bg-amber-600 transition {{ request()->routeIs('permissions.index') ? 'bg-amber-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-list"></i>
                                <span>Lister les Permissions</span>
                            </a>
                            <a href="{{ route('permissions.create') }}" class="flex items-center gap-3 px-4 py-2 text-sm rounded-lg text-amber-100 hover:bg-amber-600 transition {{ request()->routeIs('permissions.create') ? 'bg-amber-500 text-white font-semibold' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Ajouter Permission</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Return Section -->
                <div class="pt-4 mt-6 border-t border-amber-500">
                    <a href="{{ route('dashboard-modern') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-amber-100 hover:bg-amber-600 transition duration-300">
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
                        <h2 class="text-2xl font-bold text-gray-800">@yield('page_title', 'Paramètres')</h2>
                        <p class="text-gray-500 text-sm">@yield('page_subtitle', '')</p>
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
            const activeMenus = ['pos-terminals', 'payment-types', 'users', 'roles', 'permissions'];
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
