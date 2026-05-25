@extends('layouts.auth')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-600 to-green-800 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">
                <i class="fas fa-crown"></i> Complex Royal
            </h1>
            <p class="text-green-100 text-lg">Créer un nouveau compte</p>
        </div>

        <!-- Register Card -->
        <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-8 py-6">
                <h2 class="text-2xl font-bold text-white text-center">
                    <i class="fas fa-user-plus mr-2"></i>Inscription
                </h2>
            </div>

            <!-- Card Body -->
            <form method="POST" action="{{ route('register') }}" class="px-8 py-8">
                @csrf

                <!-- Name Input -->
                <div class="mb-6">
                    <label for="name" class="block text-gray-700 font-semibold mb-3">
                        <i class="fas fa-user mr-2 text-green-500"></i>Nom Complet
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors @error('name') border-red-500 @enderror"
                        placeholder="Jean Dupont"
                        required
                        autocomplete="name">
                    @error('name')
                        <p class="text-red-500 text-sm mt-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email Input -->
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-semibold mb-3">
                        <i class="fas fa-envelope mr-2 text-green-500"></i>Adresse Email
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors @error('email') border-red-500 @enderror"
                        placeholder="votre@email.com"
                        required
                        autocomplete="email">
                    @error('email')
                        <p class="text-red-500 text-sm mt-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-semibold mb-3">
                        <i class="fas fa-lock mr-2 text-green-500"></i>Mot de Passe
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors @error('password') border-red-500 @enderror"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password">
                        <button
                            type="button"
                            onclick="togglePasswordVisibility('password', 'toggleIcon1')"
                            class="absolute right-3 top-3 text-gray-600 hover:text-gray-800">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    <p class="text-gray-500 text-xs mt-2">
                        <i class="fas fa-info-circle mr-1"></i>Au moins 8 caractères
                    </p>
                    @error('password')
                        <p class="text-red-500 text-sm mt-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Confirmation Input -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-gray-700 font-semibold mb-3">
                        <i class="fas fa-lock mr-2 text-green-500"></i>Confirmer le Mot de Passe
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-green-500 transition-colors"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password">
                        <button
                            type="button"
                            onclick="togglePasswordVisibility('password_confirmation', 'toggleIcon2')"
                            class="absolute right-3 top-3 text-gray-600 hover:text-gray-800">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                </div>

                <!-- Terms -->
                <div class="mb-6 flex items-start">
                    <input
                        type="checkbox"
                        name="terms"
                        id="terms"
                        class="h-4 w-4 text-green-600 rounded focus:ring-green-500 cursor-pointer mt-1"
                        required>
                    <label for="terms" class="ml-3 text-gray-700 cursor-pointer text-sm">
                        J'accepte les <a href="#" class="text-green-600 hover:text-green-800 font-semibold">conditions d'utilisation</a>
                    </label>
                </div>

                <!-- Register Button -->
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-bold py-3 px-4 rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i>Créer un Compte
                </button>
            </form>

            <!-- Divider -->
            <div class="relative height-10 bg-gray-50 px-8 py-4">
                <div class="flex items-center justify-center">
                    <div class="absolute inset-y-0 left-0 right-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative bg-gray-50 px-2 text-sm text-gray-500">ou</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-6 bg-gray-50 text-center">
                <p class="text-gray-700 text-sm">
                    Vous avez déjà un compte?
                    <a href="{{ route('login') }}" class="text-green-600 hover:text-green-800 font-bold">
                        Se connecter
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-green-100">
            <p class="text-sm">
                &copy; 2026 Complex Royal • Système de Gestion Utilisateurs
            </p>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection
