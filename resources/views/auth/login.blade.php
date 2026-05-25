@extends('layouts.auth')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">
                <i class="fas fa-crown"></i> Complex Royal
            </h1>
            <p class="text-blue-100 text-lg">Système de Gestion Utilisateurs</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-8 py-6">
                <h2 class="text-2xl font-bold text-white text-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>Connexion
                </h2>
            </div>

            <!-- Card Body -->
            <form method="POST" action="{{ route('login') }}" class="px-8 py-8">
                @csrf

                <!-- Email Input -->
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-semibold mb-3">
                        <i class="fas fa-envelope mr-2 text-blue-500"></i>Adresse Email
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email"
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 transition-colors @error('email') border-red-500 @enderror"
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
                        <i class="fas fa-lock mr-2 text-blue-500"></i>Mot de Passe
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 transition-colors @error('password') border-red-500 @enderror"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password">
                        <button 
                            type="button" 
                            onclick="togglePasswordVisibility()" 
                            class="absolute right-3 top-3 text-gray-600 hover:text-gray-800">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-sm mt-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-6 flex items-center">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        id="remember"
                        {{ old('remember') ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                    <label for="remember" class="ml-3 text-gray-700 cursor-pointer">
                        Se souvenir de moi
                    </label>
                </div>

                <!-- Login Button -->
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>Se Connecter
                </button>

                <!-- Forgot Password Link -->
                <div class="text-center mt-6">
                    <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                        <i class="fas fa-question-circle mr-1"></i>Mot de passe oublié?
                    </a>
                </div>
            </form>
        </div>

        <!-- Test Credentials Info -->
        <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Identifiants de Test
            </h3>
            <div class="space-y-2 text-sm text-gray-700">
                <div>
                    <p class="font-semibold text-gray-800">Super Admin:</p>
                    <code class="bg-white px-2 py-1 rounded block text-xs my-1">admin@example.com</code>
                    <code class="bg-white px-2 py-1 rounded block text-xs">password123</code>
                </div>
                <div class="mt-3">
                    <p class="font-semibold text-gray-800">Admin:</p>
                    <code class="bg-white px-2 py-1 rounded block text-xs my-1">manager@example.com</code>
                    <code class="bg-white px-2 py-1 rounded block text-xs">password123</code>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-blue-100">
            <p class="text-sm">
                &copy; 2026 Complex Royal • Système de Gestion Utilisateurs
            </p>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
@endsection
