<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Modules de Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(160deg, #f8fafc 0%, #eef2ff 45%, #f8fafc 100%);
        }
        .card-hover {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -10px rgba(30, 41, 59, 0.15);
        }
        .card-hover:hover .module-icon-box {
            transform: scale(1.08);
        }
        .module-icon-box {
            transition: transform 0.25s ease;
        }
        .module-locked {
            transition: transform 0.25s ease;
        }
        .module-locked:hover {
            transform: translateY(-3px);
        }
        .locked-overlay {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .module-locked:hover .locked-overlay {
            opacity: 1;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up {
            animation: fadeInUp 0.4s ease both;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="max-w-screen-2xl mx-auto p-4 sm:p-6 md:p-10 lg:p-12">

        {{-- ── En-tête ──────────────────────────────────────────────────────── --}}
        <div class="mb-8 md:mb-12">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 md:mb-8">
                {{-- Logo & Nom --}}
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="bg-white rounded-xl shadow-md p-1.5 sm:p-2 shrink-0">
                        <img src="{{ asset('royal_complex_groupe.png') }}" alt="Royal Complex Group"
                             class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 object-contain">
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-2xl md:text-4xl font-bold text-gray-900 truncate">Complex Royal</h1>
                        <p class="text-xs sm:text-sm md:text-base text-gray-500">Système de Gestion Intégré</p>
                    </div>
                </div>

                {{-- Utilisateur & Déconnexion --}}
                <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-4">
                    <div class="flex items-center gap-3">
                        <div class="hidden xs:flex sm:flex w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 text-white items-center justify-center font-bold shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->getRoleNames()->first() ?? 'Utilisateur' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 sm:px-4 py-2 rounded-lg transition text-sm sm:text-base whitespace-nowrap shadow-sm">
                            <i class="fas fa-sign-out-alt sm:mr-2"></i><span class="hidden sm:inline">Déconnexion</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Bandeau de bienvenue --}}
            <div class="relative overflow-hidden bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl p-5 sm:p-6 md:p-8 shadow-lg">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-16 -left-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
                <div class="relative">
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-1.5 sm:mb-2">Bienvenue à Complex Royal! 👋</h2>
                    <p class="text-sm sm:text-base md:text-lg text-blue-50">Choisissez un module de gestion pour continuer</p>
                </div>
            </div>
        </div>

        {{-- ── Grille des modules ──────────────────────────────────────────── --}}
        @php
            $modules = [
                ['permission' => 'pos.view',              'route' => 'modules.pos',         'icon' => 'fa-cash-register', 'title' => 'Point de Vente', 'desc' => 'Gestion des POS et transactions', 'color' => 'blue'],
                ['permission' => 'production.dashboard',  'route' => 'modules.production',  'icon' => 'fa-industry',      'title' => 'Production',     'desc' => 'Suivi de la production',          'color' => 'orange'],
                ['permission' => null,                     'route' => 'modules.residence',    'icon' => 'fa-home',          'title' => 'Résidence',      'desc' => 'Gestion des résidences',          'color' => 'green'],
                ['permission' => 'pos.accounting.view',    'route' => 'modules.accounting',   'icon' => 'fa-calculator',    'title' => 'Comptabilité',    'desc' => 'Gestion comptable',               'color' => 'red'],
                ['permission' => 'stock.view',             'route' => 'modules.stock',        'icon' => 'fa-warehouse',     'title' => 'Stock',          'desc' => 'Gestion des stocks',              'color' => 'indigo'],
                ['permission' => 'purchases.dashboard',    'route' => 'modules.purchases',    'icon' => 'fa-shopping-cart', 'title' => 'Achats',         'desc' => 'Gestion des achats',              'color' => 'cyan'],
                ['permission' => 'hr.dashboard',           'route' => 'modules.hr',           'icon' => 'fa-users-cog',     'title' => 'RH',             'desc' => 'Ressources Humaines',             'color' => 'pink'],
                ['permission' => 'catering.dashboard',     'route' => 'modules.catering',     'icon' => 'fa-utensils',      'title' => 'Catering',       'desc' => 'Contrats repas & codes',          'color' => 'teal'],
                ['permission' => 'events.dashboard',       'route' => 'modules.event',        'icon' => 'fa-calendar-star', 'title' => 'Événements',     'desc' => 'Réceptions & banquets',           'color' => 'violet'],
                ['permission' => 'users.view',             'route' => 'modules.settings',     'icon' => 'fa-sliders-h',     'title' => 'Paramètres',     'desc' => 'Configuration',                   'color' => 'gray'],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 sm:gap-5 md:gap-6">
            @foreach ($modules as $i => $m)
                @php $unlocked = $m['permission'] === null || auth()->user()->can($m['permission']); @endphp

                @if ($unlocked)
                    <a href="{{ route($m['route']) }}"
                       style="animation-delay: {{ $i * 40 }}ms"
                       class="fade-in-up card-hover bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm block group">
                        <div class="module-icon-box bg-gradient-to-br from-{{ $m['color'] }}-100 to-{{ $m['color'] }}-50 w-14 h-14 sm:w-16 sm:h-16 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas {{ $m['icon'] }} text-2xl sm:text-3xl text-{{ $m['color'] }}-600"></i>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-1.5">{{ $m['title'] }}</h3>
                        <p class="text-gray-500 text-sm mb-4">{{ $m['desc'] }}</p>
                        <div class="flex items-center text-{{ $m['color'] }}-600 font-semibold text-sm sm:text-base">
                            <span>Accéder</span>
                            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>
                @else
                    <div style="animation-delay: {{ $i * 40 }}ms"
                         class="fade-in-up relative module-locked bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 cursor-not-allowed opacity-70">
                        <div class="locked-overlay absolute inset-0 rounded-2xl bg-red-50/95 flex items-center justify-center z-10">
                            <div class="bg-red-600 text-white rounded-full w-12 h-12 sm:w-14 sm:h-14 flex items-center justify-center shadow-lg">
                                <i class="fas fa-ban text-xl sm:text-2xl"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-gray-100 to-gray-50 w-14 h-14 sm:w-16 sm:h-16 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas {{ $m['icon'] }} text-2xl sm:text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-500 mb-1.5">{{ $m['title'] }}</h3>
                        <p class="text-gray-400 text-sm mb-4">{{ $m['desc'] }}</p>
                        <div class="flex items-center text-gray-400 font-semibold text-sm">
                            <i class="fas fa-lock mr-2"></i><span>Accès restreint</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</body>
</html>
