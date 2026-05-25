<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestion des Utilisateurs') - Complex Royal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-8">
                    <h1 class="text-2xl font-bold">
                        <a href="{{ route('dashboard') }}">Complex Royal</a>
                    </h1>
                    <div class="flex gap-4">
                        <a href="{{ route('dashboard') }}" class="hover:text-blue-200">
                            <i class="fas fa-chart-line mr-1"></i> Dashboard
                        </a>
                        <a href="{{ route('users.index') }}" class="hover:text-blue-200">
                            <i class="fas fa-users mr-1"></i> Utilisateurs
                        </a>
                        <a href="{{ route('roles.index') }}" class="hover:text-blue-200">
                            <i class="fas fa-shield-alt mr-1"></i> Rôles
                        </a>
                        <a href="{{ route('permissions.index') }}" class="hover:text-blue-200">
                            <i class="fas fa-key mr-1"></i> Permissions
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @if(auth()->check())
                        <span class="text-sm">Bienvenue, {{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-700 px-4 py-2 rounded text-sm">
                                <i class="fas fa-sign-out-alt mr-1"></i> Déconnexion
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="bg-green-500 hover:bg-green-700 px-4 py-2 rounded text-sm">
                            Connexion
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center py-4 mt-8">
        <div class="container mx-auto px-4">
            <p>&copy; 2026 Complex Royal - Gestion des Utilisateurs, Rôles et Permissions</p>
            <p class="text-sm text-gray-400">Construit avec Laravel et Spatie/Laravel-Permission</p>
        </div>
    </footer>
</body>
</html>
