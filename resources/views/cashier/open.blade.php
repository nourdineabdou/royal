<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ouvrir ma caisse — Complex Royal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-glow-amber { box-shadow: 0 0 40px rgba(245,158,11,0.15); }
        .card-glow-indigo { box-shadow: 0 0 40px rgba(99,102,241,0.15); }
    </style>
    {{-- NOUVELLE VERSION : terminal assigné automatiquement par l'admin --}}
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Logo ── --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-store text-2xl text-slate-300"></i>
        </div>
        <h1 class="text-2xl font-bold text-white">Complex Royal</h1>
        <p class="text-slate-400 text-sm mt-1">Bonjour, <span class="text-slate-200 font-medium">{{ $user->name }}</span></p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-500/20 border border-red-500/40 rounded-2xl text-red-300 text-sm flex items-start gap-3">
            <i class="fas fa-exclamation-circle mt-0.5 text-red-400 flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-4 p-4 bg-blue-500/20 border border-blue-500/40 rounded-2xl text-blue-300 text-sm flex items-start gap-3">
            <i class="fas fa-info-circle mt-0.5 text-blue-400 flex-shrink-0"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-500/20 border border-red-500/40 rounded-2xl text-red-300 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- CAS 1 : Pas de terminal assigné ─────────────────────────────────── --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    @if(!$terminal)
        <div class="bg-slate-800 border border-slate-700 rounded-3xl p-8 text-center space-y-4">
            <div class="w-16 h-16 bg-red-500/10 border border-red-500/20 rounded-2xl flex items-center justify-center mx-auto">
                <i class="fas fa-exclamation-triangle text-2xl text-red-400"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-100 mb-2">Aucun terminal assigné</h2>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Vous n'êtes actuellement assigné à aucun point de vente.<br>
                    Veuillez contacter votre administrateur pour qu'il configure votre accès.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ url('/dashboard-modern') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm font-medium transition">
                    <i class="fas fa-arrow-left"></i> Retour au tableau de bord
                </a>
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- CAS 2 : Terminal assigné — formulaire d'ouverture ──────────────── --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    @else
        @php
            $isCatering = $terminal->isCateringPos();
            $color      = $terminal->typeColor;
            $shift      = $terminal->shiftFor($user->id);
            $shiftLabel = $shift === 'morning' ? 'Matin' : 'Soir';
            $shiftIcon  = $shift === 'morning' ? 'fa-sun' : 'fa-moon';
            $shiftColor = $shift === 'morning' ? 'yellow' : 'indigo';
        @endphp

        @if($activeTerminalSession && $activeTerminalSession->user_id !== $user->id)
            <div class="mb-4 p-4 bg-amber-500/20 border border-amber-500/40 rounded-2xl text-amber-300 text-sm flex items-start gap-3">
                <i class="fas fa-user-lock mt-0.5 text-amber-400 flex-shrink-0"></i>
                <span>
                    Cette caisse est deja ouverte par <strong>{{ $activeTerminalSession->user->name ?? 'un autre caissier' }}</strong>
                    depuis {{ $activeTerminalSession->opened_at?->format('d/m/Y H:i') ?? '---' }}.
                    La session en cours doit etre fermee avant une nouvelle ouverture.
                </span>
            </div>
        @endif

        {{-- Carte terminal ─────────────────────────────────────────────── --}}
        <div class="bg-slate-800 border border-{{ $color }}-500/30 rounded-3xl overflow-hidden
            {{ $isCatering ? 'card-glow-amber' : 'card-glow-indigo' }} mb-5">

            {{-- En-tête --}}
            <div class="px-6 pt-6 pb-5
                {{ $isCatering
                    ? 'bg-gradient-to-br from-amber-900/40 to-slate-800'
                    : 'bg-gradient-to-br from-indigo-900/40 to-slate-800' }}
                border-b border-{{ $color }}-500/20">

                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-{{ $color }}-600/20 border border-{{ $color }}-500/30
                        flex items-center justify-center flex-shrink-0">
                        <i class="fas {{ $terminal->typeIcon }} text-{{ $color }}-400 text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="font-bold text-white text-lg leading-tight truncate">
                            {{ $terminal->label }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $color }}-500/20 text-{{ $color }}-300 font-medium">
                                {{ $terminal->typeLabel }}
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $shiftColor }}-500/20 text-{{ $shiftColor }}-300 font-medium">
                                <i class="fas {{ $shiftIcon }} mr-1"></i>Shift {{ $shiftLabel }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $terminal->moduleLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Détails catering ── --}}
            @if($terminal->client || $terminal->stock)
                <div class="px-6 py-3 bg-amber-900/10 border-b border-amber-700/20 flex gap-5 text-sm">
                    @if($terminal->client)
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-amber-500/20 flex items-center justify-center">
                                <i class="fas fa-building text-amber-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 leading-none">Client</p>
                                <p class="text-slate-200 font-medium">{{ $terminal->client->name }}</p>
                            </div>
                        </div>
                    @endif
                    @if($terminal->stock)
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                <i class="fas fa-boxes text-blue-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 leading-none">Stock</p>
                                <p class="text-slate-200 font-medium">{{ $terminal->stock->name }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Transferts en attente (catering) ── --}}
            @if($isCatering && $terminal->client_id)
                @php
                    $pendingCount = \App\Models\PosTransfer::where('status', 'pending')
                        ->where(function($q) use ($terminal) {
                            $q->whereNull('cash_register_id')
                              ->where('client_id', $terminal->client_id);
                        })->count();
                @endphp
                @if($pendingCount > 0)
                    <div class="px-6 py-3 bg-orange-900/20 border-b border-orange-700/20 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-500/20 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-inbox text-orange-400 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Après ouverture</p>
                            <p class="text-sm text-orange-300 font-semibold">
                                {{ $pendingCount }} transfert{{ $pendingCount > 1 ? 's' : '' }} en attente de validation
                            </p>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Formulaire d'ouverture ── --}}
            <form method="POST" action="{{ route('cashier.start') }}" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                        Fonds de caisse (MRU)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-money-bill text-slate-400"></i>
                        </div>
                        <input type="number" name="opening_balance" min="0" step="0.01"
                            value="{{ old('opening_balance', 0) }}"
                            class="w-full bg-slate-700 border border-slate-600 rounded-xl pl-10 pr-4 py-3 text-lg font-semibold
                                focus:outline-none focus:ring-2 focus:ring-{{ $color }}-500 text-white"
                            placeholder="0">
                    </div>
                    <p class="mt-1.5 text-xs text-slate-500">
                        Comptez et saisissez le montant de votre caisse avant l'ouverture.
                    </p>
                </div>

                <button type="submit"
                    @if($activeTerminalSession && $activeTerminalSession->user_id !== $user->id) disabled @endif
                    class="w-full flex items-center justify-center gap-3 py-4 rounded-2xl font-bold text-base transition-all
                        {{ $isCatering
                            ? 'bg-amber-600 hover:bg-amber-500 shadow-amber-500/30'
                            : 'bg-indigo-600 hover:bg-indigo-500 shadow-indigo-500/30' }}
                        text-white shadow-xl hover:shadow-lg active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-slate-600 disabled:shadow-none">
                    <i class="fas fa-lock-open text-lg"></i>
                    Ouvrir ma caisse
                </button>
            </form>
        </div>

        <div class="text-center flex items-center justify-center gap-4">
            <a href="{{ route('cashier.history') }}"
               class="text-sm text-slate-500 hover:text-slate-300 transition">
                <i class="fas fa-clock-rotate-left mr-1"></i> Mes sessions
            </a>
            <span class="text-slate-700">·</span>
            <a href="{{ url('/dashboard-modern') }}"
               class="text-sm text-slate-500 hover:text-slate-300 transition">
                <i class="fas fa-arrow-left mr-1"></i> Retour au tableau de bord
            </a>
        </div>
    @endif

</div>
</body>
</html>
