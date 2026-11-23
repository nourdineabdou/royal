<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuisine — @yield('title', 'Demandes au magasin')</title>
    <link rel="icon" href="{{ asset('royal_complex_groupe.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .k-input { font-size: 16px; } /* pas de zoom iOS */
        .k-input::-webkit-outer-spin-button, .k-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .k-input[type=number] { -moz-appearance: textfield; }
    </style>
    @stack('head')
</head>
<body class="bg-stone-100 min-h-screen">

<header class="bg-amber-700 text-white shadow">
    <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
        <a href="{{ route('kitchen.requests.index') }}" class="flex items-center gap-3 min-w-0">
            <span class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0"><i class="fa-solid fa-utensils text-lg"></i></span>
            <span class="min-w-0">
                <span class="block font-bold leading-tight truncate">Mes demandes de sortie</span>
                <span class="block text-xs text-amber-100 truncate">{{ auth()->user()->name ?? '' }}</span>
            </span>
        </a>
        <div class="flex items-center gap-2 shrink-0">
            @unless(auth()->user()?->isCookOnly())<a href="{{ route('dashboard-modern') }}" class="w-10 h-10 rounded-xl bg-white/15 hover:bg-white/25 flex items-center justify-center" title="Accueil"><i class="fa-solid fa-house"></i></a>@endunless
            <a href="{{ route('password.edit') }}" class="w-10 h-10 rounded-xl bg-white/15 hover:bg-white/25 flex items-center justify-center" title="Mon compte"><i class="fa-solid fa-circle-user"></i></a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-10 h-10 rounded-xl bg-white/15 hover:bg-white/25 flex items-center justify-center" title="Déconnexion"><i class="fa-solid fa-right-from-bracket"></i></button>
            </form>
        </div>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-4 pb-32 space-y-4">
    @if(session('success'))
        <div class="flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">
            <i class="fa-solid fa-circle-check mt-0.5"></i><span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i><span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm space-y-1">
            @foreach($errors->all() as $e)<p><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $e }}</p>@endforeach
        </div>
    @endif

    @yield('content')
</main>

@stack('scripts')
</body>
</html>
