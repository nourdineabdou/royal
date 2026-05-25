<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Modules de Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .module-icon {
            font-size: 3rem;
            opacity: 0.8;
        }
        .module-locked {
            transition: all 0.3s ease;
        }
        .module-locked:hover {
            transform: translateY(-4px);
        }
        .locked-overlay {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .module-locked:hover .locked-overlay {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <div class="p-4 md:p-12">
            <!-- Header with Logo Section -->
            <div class="mb-12">
                <div class="flex items-center justify-between mb-8">
                    <!-- Logo & Company Name -->
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-crown text-white text-3xl"></i>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold text-gray-900">Complex Royal</h1>
                            <p class="text-gray-500">Système de Gestion Intégré</p>
                        </div>
                    </div>

                    <!-- User Info & Logout -->
                    <div class="flex items-center gap-4">
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->getRoleNames()->first() ?? 'Utilisateur' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                                <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Welcome Message -->
                <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl p-8 border border-purple-200">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Bienvenue à Complex Royal! 👋</h2>
                    <p class="text-lg text-gray-600">Choisissez un module de gestion pour continuer</p>
                </div>
            </div>

            <!-- Modules Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                {{-- ── POS ─────────────────────────────────────────────────── --}}
                @can('pos.view')
                <a href="{{ route('modules.pos') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 block hover:text-blue-600 group">
                    <div class="bg-gradient-to-br from-blue-100 to-blue-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                        <i class="fas fa-cash-register module-icon text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Point de Vente</h3>
                    <p class="text-gray-600 text-sm mb-4">Gestion des POS et transactions</p>
                    <div class="flex items-center text-blue-600 font-semibold">
                        <span>Accéder</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </a>
                @else
                <div class="relative module-locked bg-white rounded-xl p-6 border border-gray-200 cursor-not-allowed opacity-70">
                    <div class="locked-overlay absolute inset-0 rounded-xl bg-red-50 flex items-center justify-center z-10">
                        <div class="bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                            <i class="fas fa-ban text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-cash-register module-icon text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-500 mb-2">Point de Vente</h3>
                    <p class="text-gray-400 text-sm mb-4">Gestion des POS et transactions</p>
                    <div class="flex items-center text-gray-400 font-semibold text-sm">
                        <i class="fas fa-lock mr-2"></i><span>Accès restreint</span>
                    </div>
                </div>
                @endcan

                {{-- ── Production ───────────────────────────────────────────── --}}
                @can('production.dashboard')
                <a href="{{ route('modules.production') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 block hover:text-orange-600 group">
                    <div class="bg-gradient-to-br from-orange-100 to-orange-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                        <i class="fas fa-industry module-icon text-orange-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Production</h3>
                    <p class="text-gray-600 text-sm mb-4">Suivi de la production</p>
                    <div class="flex items-center text-orange-600 font-semibold">
                        <span>Accéder</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </a>
                @else
                <div class="relative module-locked bg-white rounded-xl p-6 border border-gray-200 cursor-not-allowed opacity-70">
                    <div class="locked-overlay absolute inset-0 rounded-xl bg-red-50 flex items-center justify-center z-10">
                        <div class="bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                            <i class="fas fa-ban text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-industry module-icon text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-500 mb-2">Production</h3>
                    <p class="text-gray-400 text-sm mb-4">Suivi de la production</p>
                    <div class="flex items-center text-gray-400 font-semibold text-sm">
                        <i class="fas fa-lock mr-2"></i><span>Accès restreint</span>
                    </div>
                </div>
                @endcan

                {{-- ── Résidence (pas de permission spécifique → visible à tous) ── --}}
                <a href="{{ route('modules.residence') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 block hover:text-green-600 group">
                    <div class="bg-gradient-to-br from-green-100 to-green-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                        <i class="fas fa-home module-icon text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Résidence</h3>
                    <p class="text-gray-600 text-sm mb-4">Gestion des résidences</p>
                    <div class="flex items-center text-green-600 font-semibold">
                        <span>Accéder</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </a>

                {{-- ── Comptabilité ─────────────────────────────────────────── --}}
                @can('pos.accounting.view')
                <a href="{{ route('modules.accounting') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 block hover:text-red-600 group">
                    <div class="bg-gradient-to-br from-red-100 to-red-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                        <i class="fas fa-calculator module-icon text-red-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Comptabilité</h3>
                    <p class="text-gray-600 text-sm mb-4">Gestion comptable</p>
                    <div class="flex items-center text-red-600 font-semibold">
                        <span>Accéder</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </a>
                @else
                <div class="relative module-locked bg-white rounded-xl p-6 border border-gray-200 cursor-not-allowed opacity-70">
                    <div class="locked-overlay absolute inset-0 rounded-xl bg-red-50 flex items-center justify-center z-10">
                        <div class="bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                            <i class="fas fa-ban text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-calculator module-icon text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-500 mb-2">Comptabilité</h3>
                    <p class="text-gray-400 text-sm mb-4">Gestion comptable</p>
                    <div class="flex items-center text-gray-400 font-semibold text-sm">
                        <i class="fas fa-lock mr-2"></i><span>Accès restreint</span>
                    </div>
                </div>
                @endcan

                {{-- ── Stock ────────────────────────────────────────────────── --}}
                @can('stock.view')
                <a href="{{ route('modules.stock') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 block hover:text-indigo-600 group">
                    <div class="bg-gradient-to-br from-indigo-100 to-indigo-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                        <i class="fas fa-warehouse module-icon text-indigo-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Stock</h3>
                    <p class="text-gray-600 text-sm mb-4">Gestion des stocks</p>
                    <div class="flex items-center text-indigo-600 font-semibold">
                        <span>Accéder</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </a>
                @else
                <div class="relative module-locked bg-white rounded-xl p-6 border border-gray-200 cursor-not-allowed opacity-70">
                    <div class="locked-overlay absolute inset-0 rounded-xl bg-red-50 flex items-center justify-center z-10">
                        <div class="bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                            <i class="fas fa-ban text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-warehouse module-icon text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-500 mb-2">Stock</h3>
                    <p class="text-gray-400 text-sm mb-4">Gestion des stocks</p>
                    <div class="flex items-center text-gray-400 font-semibold text-sm">
                        <i class="fas fa-lock mr-2"></i><span>Accès restreint</span>
                    </div>
                </div>
                @endcan

                {{-- ── Achats ───────────────────────────────────────────────── --}}
                @can('purchases.dashboard')
                <a href="{{ route('modules.purchases') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 block hover:text-cyan-600 group">
                    <div class="bg-gradient-to-br from-cyan-100 to-cyan-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                        <i class="fas fa-shopping-cart module-icon text-cyan-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Achats</h3>
                    <p class="text-gray-600 text-sm mb-4">Gestion des achats</p>
                    <div class="flex items-center text-cyan-600 font-semibold">
                        <span>Accéder</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </a>
                @else
                <div class="relative module-locked bg-white rounded-xl p-6 border border-gray-200 cursor-not-allowed opacity-70">
                    <div class="locked-overlay absolute inset-0 rounded-xl bg-red-50 flex items-center justify-center z-10">
                        <div class="bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                            <i class="fas fa-ban text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-shopping-cart module-icon text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-500 mb-2">Achats</h3>
                    <p class="text-gray-400 text-sm mb-4">Gestion des achats</p>
                    <div class="flex items-center text-gray-400 font-semibold text-sm">
                        <i class="fas fa-lock mr-2"></i><span>Accès restreint</span>
                    </div>
                </div>
                @endcan

                {{-- ── RH ───────────────────────────────────────────────────── --}}
                @can('hr.dashboard')
                <a href="{{ route('modules.hr') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 block hover:text-pink-600 group">
                    <div class="bg-gradient-to-br from-pink-100 to-pink-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                        <i class="fas fa-users-cog module-icon text-pink-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">RH</h3>
                    <p class="text-gray-600 text-sm mb-4">Ressources Humaines</p>
                    <div class="flex items-center text-pink-600 font-semibold">
                        <span>Accéder</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </a>
                @else
                <div class="relative module-locked bg-white rounded-xl p-6 border border-gray-200 cursor-not-allowed opacity-70">
                    <div class="locked-overlay absolute inset-0 rounded-xl bg-red-50 flex items-center justify-center z-10">
                        <div class="bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                            <i class="fas fa-ban text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-users-cog module-icon text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-500 mb-2">RH</h3>
                    <p class="text-gray-400 text-sm mb-4">Ressources Humaines</p>
                    <div class="flex items-center text-gray-400 font-semibold text-sm">
                        <i class="fas fa-lock mr-2"></i><span>Accès restreint</span>
                    </div>
                </div>
                @endcan

                {{-- ── Catering ─────────────────────────────────────────────── --}}
                @can('catering.dashboard')
                <a href="{{ route('modules.catering') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 block hover:text-teal-600 group">
                    <div class="bg-gradient-to-br from-teal-100 to-teal-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                        <i class="fas fa-utensils module-icon text-teal-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Catering</h3>
                    <p class="text-gray-600 text-sm mb-4">Contrats repas & codes</p>
                    <div class="flex items-center text-teal-600 font-semibold">
                        <span>Accéder</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </a>
                @else
                <div class="relative module-locked bg-white rounded-xl p-6 border border-gray-200 cursor-not-allowed opacity-70">
                    <div class="locked-overlay absolute inset-0 rounded-xl bg-red-50 flex items-center justify-center z-10">
                        <div class="bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                            <i class="fas fa-ban text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-utensils module-icon text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-500 mb-2">Catering</h3>
                    <p class="text-gray-400 text-sm mb-4">Contrats repas & codes</p>
                    <div class="flex items-center text-gray-400 font-semibold text-sm">
                        <i class="fas fa-lock mr-2"></i><span>Accès restreint</span>
                    </div>
                </div>
                @endcan

                {{-- ── Événements ───────────────────────────────────────────── --}}
                @can('events.dashboard')
                <a href="{{ route('modules.event') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 block hover:text-violet-600 group">
                    <div class="bg-gradient-to-br from-violet-100 to-violet-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                        <i class="fas fa-calendar-star module-icon text-violet-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Événements</h3>
                    <p class="text-gray-600 text-sm mb-4">Réceptions & banquets</p>
                    <div class="flex items-center text-violet-600 font-semibold">
                        <span>Accéder</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </a>
                @else
                <div class="relative module-locked bg-white rounded-xl p-6 border border-gray-200 cursor-not-allowed opacity-70">
                    <div class="locked-overlay absolute inset-0 rounded-xl bg-red-50 flex items-center justify-center z-10">
                        <div class="bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                            <i class="fas fa-ban text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-calendar-star module-icon text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-500 mb-2">Événements</h3>
                    <p class="text-gray-400 text-sm mb-4">Réceptions & banquets</p>
                    <div class="flex items-center text-gray-400 font-semibold text-sm">
                        <i class="fas fa-lock mr-2"></i><span>Accès restreint</span>
                    </div>
                </div>
                @endcan

                {{-- ── Paramètres ───────────────────────────────────────────── --}}
                @can('users.view')
                <a href="{{ route('modules.settings') }}" class="card-hover bg-white rounded-xl p-6 border border-gray-200 block hover:text-gray-600 group">
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4 group-hover:shadow-lg transition">
                        <i class="fas fa-sliders-h module-icon text-gray-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Paramètres</h3>
                    <p class="text-gray-600 text-sm mb-4">Configuration</p>
                    <div class="flex items-center text-gray-600 font-semibold">
                        <span>Accéder</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </a>
                @else
                <div class="relative module-locked bg-white rounded-xl p-6 border border-gray-200 cursor-not-allowed opacity-70">
                    <div class="locked-overlay absolute inset-0 rounded-xl bg-red-50 flex items-center justify-center z-10">
                        <div class="bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                            <i class="fas fa-ban text-2xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-16 h-16 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-sliders-h module-icon text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-500 mb-2">Paramètres</h3>
                    <p class="text-gray-400 text-sm mb-4">Configuration</p>
                    <div class="flex items-center text-gray-400 font-semibold text-sm">
                        <i class="fas fa-lock mr-2"></i><span>Accès restreint</span>
                    </div>
                </div>
                @endcan

            </div>
        </div>
    </div>
</body>
</html>
