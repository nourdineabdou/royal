</body>
<script>
function openModal(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}
function closeModal(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = 'none';
}
function submitOpenRegister() {
    const shift = document.getElementById('openShift').value;
    const module = document.getElementById('openModule').value;
    const cashier = document.getElementById('openCashierUser').value;
    const balance = document.getElementById('openBalance').value;
    document.getElementById('formShift').value = shift;
    document.getElementById('formModule').value = module;
    document.getElementById('formCashierUser').value = cashier;
    document.getElementById('formBalance').value = balance;
    document.getElementById('openRegisterForm').submit();
}
</script>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Comptabilité')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        .sidebar-link.active {
            background: linear-gradient(90deg,#f87171 0,#fbbf24 100%);
            color: #fff !important;
        }
        .sidebar-link {
            transition: background .2s, color .2s;
        }
        .sidebar-link:hover {
            background: #f3f4f6;
            color: #b91c1c;
        }
        .sidebar {
            min-height: 100vh;
            box-shadow: 2px 0 8px rgba(0,0,0,.04);
        }
        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #b91c1c;
            letter-spacing: 1px;
        }
    </style>
    @stack('head')
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
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow flex items-center justify-between px-8 py-3 h-16 sticky top-0 z-30">
        <div class="flex items-center gap-4">
            <span class="sidebar-logo flex items-center gap-2">
                <i class="fas fa-calculator text-2xl text-red-600"></i>
                <span>Comptabilité</span>
            </span>
        </div>
        <div class="flex items-center gap-4">
            @auth
                <span class="text-gray-600 text-sm font-semibold">{{ auth()->user()->name }}</span>
                <a href="{{ route('logout') }}" class="text-red-500 hover:underline text-sm">Déconnexion</a>
            @endauth
        </div>
    </header>
    <div class="flex">
        <!-- Sidebar -->
        <aside class="sidebar w-60 bg-white border-r border-gray-200 flex flex-col py-8 px-4">
            <nav class="flex-1">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('accounting.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.dashboard')) active @endif">
                            <i class="fas fa-home"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.transactions') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.transactions')) active @endif">
                            <i class="fas fa-list"></i> <span>Transactions</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('modules.accounting') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('modules.accounting')) active @endif">
                            <i class="fas fa-cash-register"></i> <span>Sessions de caisse</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.journal') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.journal')) active @endif">
                            <i class="fas fa-book"></i> <span>Journal</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.ledger') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.ledger')) active @endif">
                            <i class="fas fa-columns"></i> <span>Grand Livre</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.balance') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.balance')) active @endif">
                            <i class="fas fa-balance-scale"></i> <span>Balance</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.chart-of-accounts') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('settings.chart-of-accounts')) active @endif">
                            <i class="fas fa-sitemap"></i> <span>Plan comptable</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.balance-sheet') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.balance-sheet')) active @endif">
                            <i class="fas fa-landmark"></i> <span>Bilan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.income-statement') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.income-statement')) active @endif">
                            <i class="fas fa-chart-line"></i> <span>Compte de Résultat</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.bank-reconciliation') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.bank-reconciliation')) active @endif">
                            <i class="fas fa-money-check-dollar"></i> <span>Rapprochement Bancaire</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.supplier-invoices') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.supplier-invoices*')) active @endif">
                            <i class="fas fa-file-invoice-dollar"></i> <span>Factures Fournisseurs</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.supplier-balances') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.supplier-balances*')) active @endif">
                            <i class="fas fa-scale-balanced"></i> <span>Soldes Fournisseurs</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('catering.billing.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('catering.billing.*')) active @endif">
                            <i class="fas fa-file-invoice"></i> <span>Factures Catering</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.client-balances') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.client-balances*')) active @endif">
                            <i class="fas fa-people-arrows"></i> <span>Soldes Clients</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('accounting.expenses.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg font-semibold @if(request()->routeIs('accounting.expenses.*')) active @endif">
                            <i class="fas fa-receipt"></i> <span>Dépenses</span>
                        </a>
                    </li>
                    <!-- Ajoutez d'autres liens ici si besoin -->
                </ul>
            </nav>
            <div class="mt-8 text-xs text-gray-400 text-center">Royal Complex Groupe &copy; {{ date('Y') }}</div>
        </aside>
        <!-- Content -->
        <main class="flex-1 p-6 md:p-10 bg-gray-50 min-h-screen">
            @yield('accounting_content')
        </main>
    </div>
</body>
<script>
function openModal(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = 'block';
}
function closeModal(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = 'none';
}
</script>
</html>
