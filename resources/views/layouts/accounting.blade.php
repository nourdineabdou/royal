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
