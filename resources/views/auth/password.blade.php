@extends('layouts.auth')
@section('title', 'Mon mot de passe')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-10 bg-slate-50">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <img src="{{ asset('royal_complex_groupe.png') }}" alt="Royal Complex Group" class="w-28 h-auto mx-auto bg-white rounded-xl p-3 shadow">
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-slate-800">
                <i class="fa-solid fa-circle-user text-blue-600 mr-2"></i>{{ $forced ? 'Choisissez votre mot de passe' : 'Mon compte' }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">{{ auth()->user()->name }} · {{ auth()->user()->email }}</p>

            @if(session('success'))
                <p class="mt-4 text-sm bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3"><i class="fa-solid fa-circle-check mr-1"></i>{{ session('success') }}</p>
            @endif

            {{-- Nom d'utilisateur : pour se connecter sans e-mail --}}
            <form method="POST" action="{{ route('account.username') }}" class="mt-5 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                @csrf
                @method('PUT')
                <label for="username" class="block text-sm font-semibold text-slate-700 mb-1">
                    <i class="fa-solid fa-user mr-1 text-blue-600"></i>Nom d'utilisateur (pour se connecter)
                </label>
                <div class="flex gap-2">
                    <input id="username" name="username" type="text" required maxlength="30" autocapitalize="none" autocorrect="off" spellcheck="false"
                           value="{{ old('username', auth()->user()->username) }}"
                           class="flex-1 min-w-0 px-4 py-2.5 border border-slate-300 rounded-xl text-base font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 @error('username') border-red-400 @enderror">
                    <button class="shrink-0 px-4 py-2.5 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold">Changer</button>
                </div>
                @error('username')
                    <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p>
                @else
                    <p class="text-xs text-slate-500 mt-1.5">Vous pouvez mettre un numéro (ex : 0042). Lettres sans accent, chiffres, point ou tiret, 3 à 30 caractères. L'e-mail marche toujours aussi.</p>
                @enderror
            </form>

            @if($forced)
                <p class="mt-4 text-sm bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Votre mot de passe actuel est provisoire. Choisissez-en un nouveau, connu de vous seul, pour accéder au système.
                </p>
            @endif

            @php $pwErrors = collect($errors->getMessages())->except('username')->flatten(); @endphp
            @if($pwErrors->isNotEmpty())
                <div class="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm space-y-1">
                    @foreach($pwErrors as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif

            <h2 class="mt-6 text-sm font-semibold text-slate-700"><i class="fa-solid fa-key mr-1 text-blue-600"></i>Mot de passe</h2>

            <form method="POST" action="{{ route('password.change') }}" class="mt-3 space-y-4">
                @csrf
                @method('PUT')
                @foreach([
                    ['current_password', $forced ? 'Mot de passe provisoire' : 'Mot de passe actuel', 'current-password'],
                    ['password', 'Nouveau mot de passe (8 caractères minimum)', 'new-password'],
                    ['password_confirmation', 'Retapez le nouveau mot de passe', 'new-password'],
                ] as [$name, $label, $ac])
                <div>
                    <label for="{{ $name }}" class="block text-sm font-semibold text-slate-700 mb-1">{{ $label }}</label>
                    <div class="relative">
                        <input id="{{ $name }}" name="{{ $name }}" type="password" required autocomplete="{{ $ac }}" {{ $loop->first ? 'autofocus' : '' }}
                               class="w-full px-4 py-3 pr-12 border border-slate-300 rounded-xl text-base focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="const i=document.getElementById('{{ $name }}');i.type=i.type==='password'?'text':'password';this.firstElementChild.classList.toggle('fa-eye');this.firstElementChild.classList.toggle('fa-eye-slash')"
                                class="absolute inset-y-0 right-0 px-4 text-slate-400 hover:text-slate-600" aria-label="Afficher le mot de passe"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
                @endforeach

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3.5 rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-lg shadow-blue-600/20">
                    <i class="fa-solid fa-check mr-2"></i>Enregistrer le mot de passe
                </button>
            </form>

            <div class="mt-5 flex items-center justify-between text-sm">
                @unless($forced)
                    <a href="{{ route('dashboard-modern') }}" class="text-slate-500 hover:text-slate-700"><i class="fa-solid fa-arrow-left mr-1"></i>Retour</a>
                @else
                    <span></span>
                @endunless
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-red-600 hover:text-red-700"><i class="fa-solid fa-right-from-bracket mr-1"></i>Déconnexion</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
