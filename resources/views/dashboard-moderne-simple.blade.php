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
                    <a href="{{ route('password.edit') }}" title="Mon mot de passe" class="border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 px-3 py-2 rounded-lg text-sm sm:text-base whitespace-nowrap shadow-sm">
                        <i class="fas fa-circle-user sm:mr-2"></i><span class="hidden sm:inline">Mon compte</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 sm:px-4 py-2 rounded-lg transition text-sm sm:text-base whitespace-nowrap shadow-sm">
                            <i class="fas fa-sign-out-alt sm:mr-2"></i><span class="hidden sm:inline">Déconnexion</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Bandeau de bienvenue --}}
            @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm"><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>
            @endif
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
                ['permission' => 'kitchen.requests.create', 'route' => 'kitchen.requests.index', 'icon' => 'fa-utensils', 'title' => 'Demandes au magasin', 'desc' => 'Cuisine : demander et valider', 'color' => 'amber'],
                ['permission' => 'kitchen.requests.prepare', 'route' => 'stock.kitchen-requests.index', 'icon' => 'fa-utensils', 'title' => 'Demandes des cuisines', 'desc' => 'Préparer et remettre aux cuisines', 'color' => 'lime'],
                ['permission' => 'purchases.orders.receipt', 'route' => 'purchases.deliveries', 'icon' => 'fa-truck-ramp-box', 'title' => 'Réceptions (BL)', 'desc' => 'Marchandise qui arrive → stock', 'color' => 'emerald'],
                ['permission' => 'hr.dashboard',           'route' => 'modules.hr',           'icon' => 'fa-users-cog',     'title' => 'RH',             'desc' => 'Ressources Humaines',             'color' => 'pink'],
                ['permission' => 'catering.dashboard',     'route' => 'modules.catering',     'icon' => 'fa-utensils',      'title' => 'Catering',       'desc' => 'Contrats repas & codes',          'color' => 'teal'],
                ['permission' => 'events.dashboard',       'route' => 'modules.event',        'icon' => 'fa-calendar-star', 'title' => 'Événements',     'desc' => 'Réceptions & banquets',           'color' => 'violet'],
                ['permission' => 'users.view',             'route' => 'modules.settings',     'icon' => 'fa-sliders-h',     'title' => 'Paramètres',     'desc' => 'Configuration',                   'color' => 'gray'],
            ];
        @endphp

        {{-- ── Circuit d'achat : ce que j'ai à approuver, valider ou payer ─── --}}
        @php
            $u = auth()->user();
            $toApprove  = $u->can('purchases.requests.approve')  ? \App\Models\PurchaseRequest::where('status', 'pending')->count() : 0;
            $toValidate = $u->can('purchases.requests.validate') ? \App\Models\PurchaseRequest::where('status', 'to_validate')->count() : 0;
            $toPay      = $u->can('purchases.orders.payment')
                ? \App\Models\PurchaseOrder::whereNotNull('invoice_validated_at')->where('remaining_amount', '>', 0)->count() : 0;
            $toCheck    = $u->can('purchases.orders.validate-invoice')
                ? \App\Models\PurchaseOrder::whereIn('status', ['received', 'partial'])->whereNull('invoice_validated_at')->count() : 0;
            // BC préparés (par l'acheteur) qui attendent la confirmation du responsable des achats
            $toConfirmBc = $u->can('purchases.orders.confirm') ? \App\Models\PurchaseOrder::where('status', 'pending')->count() : 0;
            // Sorties vers les cuisines préparées par le magasinier, à approuver
            $toApproveOut = $u->can('kitchen.requests.approve') ? \App\Models\KitchenRequest::where('status', 'to_approve')->count() : 0;
        @endphp
        @if($toApprove || $toValidate || $toPay || $toCheck || $toConfirmBc || $toApproveOut)
        <div class="mb-4 grid sm:grid-cols-2 gap-3">
            @if($toApproveOut)
            <a href="{{ route('stock.kitchen-requests.approvals') }}" class="flex items-center gap-4 rounded-2xl p-4 bg-violet-700 text-white shadow-sm hover:shadow-md transition">
                <i class="fas fa-dolly text-2xl"></i>
                <div class="flex-1"><p class="font-bold text-lg">{{ $toApproveOut }} sortie{{ $toApproveOut > 1 ? 's' : '' }} de stock à approuver</p><p class="text-sm text-violet-100">en stock · ce qui sort · ce qui reste</p></div>
                <i class="fas fa-arrow-right"></i>
            </a>
            @endif
            @if($toConfirmBc)
            <a href="{{ route('purchases.orders', ['todo' => 'to_confirm']) }}" class="flex items-center gap-4 rounded-2xl p-4 bg-blue-600 text-white shadow-sm hover:shadow-md transition">
                <i class="fas fa-paper-plane text-2xl"></i>
                <div class="flex-1"><p class="font-bold text-lg">{{ $toConfirmBc }} bon{{ $toConfirmBc > 1 ? 's' : '' }} de commande à confirmer</p><p class="text-sm text-blue-100">fournisseur et prix à vérifier avant l’envoi</p></div>
                <i class="fas fa-arrow-right"></i>
            </a>
            @endif
            @if($toApprove)
            <a href="{{ route('purchases.requests', ['status' => 'pending']) }}" class="flex items-center gap-4 rounded-2xl p-4 bg-amber-500 text-white shadow-sm hover:shadow-md transition">
                <i class="fas fa-clipboard-check text-2xl"></i>
                <div class="flex-1"><p class="font-bold text-lg">{{ $toApprove }} demande{{ $toApprove > 1 ? 's' : '' }} d'achat à approuver</p><p class="text-sm text-amber-50">envoyées par le magasinier</p></div>
                <i class="fas fa-arrow-right"></i>
            </a>
            @endif
            @if($toValidate)
            <a href="{{ route('purchases.requests', ['status' => 'to_validate']) }}" class="flex items-center gap-4 rounded-2xl p-4 bg-violet-600 text-white shadow-sm hover:shadow-md transition">
                <i class="fas fa-stamp text-2xl"></i>
                <div class="flex-1"><p class="font-bold text-lg">{{ $toValidate }} demande{{ $toValidate > 1 ? 's' : '' }} d'achat à valider</p><p class="text-sm text-violet-100">approuvées par l'acheteur, avant le bon de commande</p></div>
                <i class="fas fa-arrow-right"></i>
            </a>
            @endif
            @if($toCheck)
            <a href="{{ route('purchases.orders', ['todo' => 'to_check']) }}" class="flex items-center gap-4 rounded-2xl p-4 bg-sky-600 text-white shadow-sm hover:shadow-md transition">
                <i class="fas fa-file-invoice text-2xl"></i>
                <div class="flex-1"><p class="font-bold text-lg">{{ $toCheck }} facture{{ $toCheck > 1 ? 's' : '' }} à contrôler</p><p class="text-sm text-sky-100">commandes reçues, facture non validée</p></div>
                <i class="fas fa-arrow-right"></i>
            </a>
            @endif
            @if($toPay)
            <a href="{{ route('purchases.orders', ['todo' => 'to_pay']) }}" class="flex items-center gap-4 rounded-2xl p-4 bg-emerald-700 text-white shadow-sm hover:shadow-md transition">
                <i class="fas fa-money-bill-wave text-2xl"></i>
                <div class="flex-1"><p class="font-bold text-lg">{{ $toPay }} commande{{ $toPay > 1 ? 's' : '' }} à payer</p><p class="text-sm text-emerald-100">facture validée, reste à payer</p></div>
                <i class="fas fa-arrow-right"></i>
            </a>
            @endif
        </div>
        @endif

        {{-- ── Magasin : demandes des cuisines et produits sous le seuil ───── --}}
        {{-- C'est le travail du magasinier : les autres (Direction…) ne le voient qu'en lien de remplacement plus bas --}}
        @php $isStorekeeper = auth()->user()->hasRole('magasinier'); @endphp
        @if($isStorekeeper && auth()->user()->can('kitchen.requests.prepare'))
            @php
                $krPending = \App\Models\KitchenRequest::where('status', 'pending')->count();
                $belowMin  = auth()->user()->can('purchases.stock-ruptures.view') ? app(\App\Services\StockAlertService::class)->count() : 0;
                // DA préparée automatiquement avec les produits du Stock Catering sous le seuil
                $autoDa    = auth()->user()->can('purchases.requests.create') ? app(\App\Services\AutoPurchaseRequestService::class)->sync() : null;
                $autoCount = $autoDa ? $autoDa->items->count() : 0;
            @endphp
            @if($krPending || $belowMin)
            <div class="mb-4 grid sm:grid-cols-2 gap-3">
                @if($krPending)
                <a href="{{ route('stock.kitchen-requests.index') }}" class="flex items-center gap-4 rounded-2xl p-4 bg-amber-500 text-white shadow-sm hover:shadow-md transition">
                    <i class="fas fa-utensils text-2xl"></i>
                    <div class="flex-1"><p class="font-bold text-lg">{{ $krPending }} demande{{ $krPending > 1 ? 's' : '' }} des cuisines</p><p class="text-sm text-amber-50">à préparer et remettre</p></div>
                    <i class="fas fa-arrow-right"></i>
                </a>
                @endif
                @if($autoCount)
                <a href="{{ route('purchases.requests.auto') }}" class="flex items-center gap-4 rounded-2xl p-4 bg-red-600 text-white shadow-sm hover:shadow-md transition">
                    <i class="fas fa-wand-magic-sparkles text-2xl"></i>
                    <div class="flex-1"><p class="font-bold text-lg">Demande d'achat prête : {{ $autoCount }} produit{{ $autoCount > 1 ? 's' : '' }}</p><p class="text-sm text-red-50">sous le seuil · à relire et envoyer</p></div>
                    <i class="fas fa-arrow-right"></i>
                </a>
                @elseif($belowMin)
                <a href="{{ route('purchases.stock-ruptures') }}" class="flex items-center gap-4 rounded-2xl p-4 bg-red-600 text-white shadow-sm hover:shadow-md transition">
                    <i class="fas fa-triangle-exclamation text-2xl"></i>
                    <div class="flex-1"><p class="font-bold text-lg">{{ $belowMin }} produit{{ $belowMin > 1 ? 's' : '' }} sous le seuil</p><p class="text-sm text-red-50">déjà en commande ou seuil à vérifier</p></div>
                    <i class="fas fa-arrow-right"></i>
                </a>
                @endif
            </div>
            @endif
        @endif

        {{-- ── Livraisons attendues : grand bandeau pour le magasinier ───── --}}
        @if($isStorekeeper && auth()->user()->can('purchases.orders.receipt'))
            @php
                $pendingReceptions = \App\Models\PurchaseOrder::with('supplier')
                    ->whereIn('status', ['ordered', 'partial'])->latest()->get();
            @endphp
            <a href="{{ route('purchases.deliveries') }}"
               class="mb-5 sm:mb-6 flex items-center gap-4 rounded-2xl p-4 sm:p-5 border shadow-sm transition hover:shadow-md
                      {{ $pendingReceptions->count() ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white border-gray-100 text-gray-700' }}">
                <div class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-xl flex items-center justify-center
                            {{ $pendingReceptions->count() ? 'bg-white/20' : 'bg-emerald-50 text-emerald-600' }}">
                    <i class="fas fa-truck-ramp-box text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    @if($pendingReceptions->count())
                        <p class="text-lg sm:text-xl font-bold">
                            {{ $pendingReceptions->count() }} commande{{ $pendingReceptions->count() > 1 ? 's' : '' }} à réceptionner
                        </p>
                        <p class="text-sm text-emerald-50 truncate">
                            {{ $pendingReceptions->take(3)->map(fn ($o) => $o->reference . ($o->supplier ? ' · ' . $o->supplier->name : ''))->implode('  —  ') }}{{ $pendingReceptions->count() > 3 ? '  …' : '' }}
                        </p>
                    @else
                        <p class="font-bold">Aucune livraison attendue</p>
                        <p class="text-sm text-gray-500">Achat du jour sans commande ? Faites une réception directe.</p>
                    @endif
                </div>
                <i class="fas fa-arrow-right text-xl shrink-0"></i>
            </a>
        @endif

        {{-- Remplacement du magasinier (ex : la Direction quand il est absent) : simple lien --}}
        @if(!$isStorekeeper && auth()->user()->can('purchases.orders.receipt'))
            @php $waitingBl = \App\Models\PurchaseOrder::whereIn('status', ['ordered', 'partial'])->count(); @endphp
            @if($waitingBl)
            <a href="{{ route('purchases.deliveries') }}" class="mb-5 inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-700">
                <i class="fas fa-truck-ramp-box"></i>
                Remplacer le magasinier : {{ $waitingBl }} commande{{ $waitingBl > 1 ? 's' : '' }} attend{{ $waitingBl > 1 ? 'ent' : '' }} la réception
                <i class="fas fa-arrow-right text-xs"></i>
            </a>
            @endif
        @endif

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
