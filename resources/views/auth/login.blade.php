@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex flex-col lg:flex-row">

    {{-- ── Panneau de marque (masqué sur mobile, visible dès tablette) ────────── --}}
    <div class="relative lg:w-1/2 xl:w-[45%] bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-800 flex flex-col items-center justify-center px-6 py-10 lg:py-0 overflow-hidden">
        {{-- Décor --}}
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-16 w-80 h-80 bg-indigo-400/20 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 24px 24px;"></div>

        <div class="relative z-10 text-center max-w-sm">
            <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 inline-block mb-6 sm:mb-8">
                <img src="{{ asset('royal_complex_groupe.png') }}" alt="Royal Complex Group"
                     class="w-40 sm:w-52 lg:w-60 h-auto mx-auto">
            </div>
            <p class="text-blue-50 text-base sm:text-lg font-medium leading-relaxed">
                Système de gestion intégré
            </p>
            <p class="text-blue-200 text-sm mt-2">
                Restaurant &middot; Catering &middot; Événements &middot; Résidence
            </p>
        </div>
    </div>

    {{-- ── Formulaire ───────────────────────────────────────────────────────── --}}
    <div class="flex-1 flex items-center justify-center px-4 py-10 sm:px-6 bg-slate-50">
        <div class="w-full max-w-md">

            {{-- Logo visible uniquement sur mobile (le panneau ci-dessus est caché en dessous de lg) --}}
            <div class="lg:hidden text-center mb-8">
                <img src="{{ asset('royal_complex_groupe.png') }}" alt="Royal Complex Group" class="w-40 h-auto mx-auto mb-3">
                <p class="text-slate-500 text-sm">Système de gestion intégré</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 sm:p-8">
                <div class="mb-6 sm:mb-8">
                    <h2 class="text-2xl font-bold text-slate-800">
                        <i class="fas fa-right-to-bracket text-blue-600 mr-2"></i>Connexion
                    </h2>
                    <p class="text-slate-400 text-sm mt-1">Entrez vos identifiants pour accéder à votre espace.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-slate-700 font-semibold text-sm mb-2">
                            <i class="fas fa-envelope mr-1.5 text-blue-500"></i>Adresse Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition @error('email') border-red-400 @enderror"
                            placeholder="votre@email.com"
                            required
                            autocomplete="email">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1.5"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <label for="password" class="block text-slate-700 font-semibold text-sm mb-2">
                            <i class="fas fa-lock mr-1.5 text-blue-500"></i>Mot de Passe
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition pr-11 @error('password') border-red-400 @enderror"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password">
                            <button
                                type="button"
                                onclick="togglePasswordVisibility()"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1.5"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Se souvenir --}}
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="remember"
                                {{ old('remember') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            <span class="text-sm text-slate-600">Se souvenir de moi</span>
                        </label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Mot de passe oublié ?</a>
                    </div>

                    {{-- Bouton --}}
                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3.5 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg shadow-blue-600/20 active:scale-[0.98]">
                        <i class="fas fa-right-to-bracket mr-2"></i>Se Connecter
                    </button>
                </form>
            </div>

            <p class="text-center text-xs text-slate-400 mt-6">
                &copy; {{ date('Y') }} Royal Complex Group — Tous droits réservés
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
