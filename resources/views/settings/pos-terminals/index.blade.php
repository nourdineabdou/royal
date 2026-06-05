<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terminaux POS — Paramètres — Complex Royal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">

{{-- ── Header ── --}}
<header class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ url('/dashboard-modern') }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div class="w-10 h-10 rounded-xl bg-violet-600 flex items-center justify-center">
            <i class="fas fa-store text-white"></i>
        </div>
        <div>
            <h1 class="font-bold text-lg leading-tight">Terminaux POS</h1>
            <p class="text-slate-400 text-xs">Paramétrage des points de vente & affectation des caissiers</p>
        </div>
    </div>
    <a href="{{ route('settings.pos-terminals.create') }}"
       class="flex items-center gap-2 px-4 py-2 bg-violet-600 hover:bg-violet-500 rounded-xl text-sm font-semibold transition shadow-lg">
        <i class="fas fa-plus"></i> Nouveau terminal
    </a>
</header>

{{-- Alertes ── --}}
@foreach(['success','error','info'] as $type)
    @if(session($type))
        @php $colors = ['success'=>'emerald','error'=>'red','info'=>'blue']; $c=$colors[$type]; @endphp
        <div class="mx-6 mt-4 p-4 bg-{{$c}}-500/20 border border-{{$c}}-500/40 rounded-xl text-{{$c}}-300 text-sm flex items-center gap-3">
            <i class="fas fa-{{ $type==='success'?'check-circle':($type==='error'?'exclamation-circle':'info-circle') }}"></i>
            {{ session($type) }}
        </div>
    @endif
@endforeach

{{-- Corps ── --}}
<main class="max-w-6xl mx-auto px-6 py-6">

    @if($terminals->isEmpty())
        <div class="text-center py-20">
            <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-store text-3xl text-slate-500"></i>
            </div>
            <h2 class="text-lg font-semibold text-slate-300 mb-2">Aucun terminal configuré</h2>
            <p class="text-slate-500 text-sm mb-6">Créez votre premier point de vente et assignez-lui des caissiers.</p>
            <a href="{{ route('settings.pos-terminals.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-violet-600 hover:bg-violet-500 rounded-xl font-semibold text-sm transition">
                <i class="fas fa-plus"></i> Créer un terminal
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($terminals as $terminal)
                @php
                    $color    = $terminal->typeColor;
                    $isOpen   = $terminal->activeSession !== null;
                    $session  = $terminal->activeSession;
                @endphp
                <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden
                    {{ !$terminal->is_active ? 'opacity-50' : '' }}">

                    {{-- Tête de la carte ──────────────────────── --}}
                    <div class="p-5 border-b border-slate-700 flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-{{ $color }}-600/20 border border-{{ $color }}-500/30
                                flex items-center justify-center">
                                <i class="fas {{ $terminal->typeIcon }} text-{{ $color }}-400 text-lg"></i>
                            </div>
                            <div>
                                <h2 class="font-bold text-slate-100 leading-tight">{{ $terminal->label }}</h2>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $color }}-500/20 text-{{ $color }}-300">
                                        {{ $terminal->typeLabel }}
                                    </span>
                                    <span class="text-xs text-slate-500">{{ $terminal->moduleLabel }}</span>
                                </div>
                            </div>
                        </div>
                        {{-- Badge statut ──────────────────────── --}}
                        @if($isOpen)
                            <span class="flex items-center gap-1.5 text-xs px-2.5 py-1 bg-emerald-500/20 border border-emerald-500/30 rounded-full text-emerald-300 font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Ouverte
                            </span>
                        @elseif(!$terminal->is_active)
                            <span class="text-xs px-2.5 py-1 bg-slate-700 rounded-full text-slate-500">Inactif</span>
                        @else
                            <span class="text-xs px-2.5 py-1 bg-slate-700/50 rounded-full text-slate-400">Fermée</span>
                        @endif
                    </div>

                    {{-- Détails catering ──────────────────────── --}}
                    @if($terminal->client || $terminal->stock)
                        <div class="px-5 py-3 bg-slate-750 border-b border-slate-700 flex gap-4 text-xs text-slate-400">
                            @if($terminal->client)
                                <span><i class="fas fa-building mr-1 text-amber-400"></i>{{ $terminal->client->name }}</span>
                            @endif
                            @if($terminal->stock)
                                <span><i class="fas fa-boxes mr-1 text-blue-400"></i>{{ $terminal->stock->name }}</span>
                            @endif
                        </div>
                    @endif

                    {{-- Session ouverte ───────────────────────── --}}
                    @if($isOpen)
                        <div class="px-5 py-2.5 bg-emerald-900/20 border-b border-emerald-700/30 flex items-center gap-2 text-xs text-emerald-300">
                            <i class="fas fa-user-circle"></i>
                            Session de <strong>{{ $session->user?->name ?? '–' }}</strong>
                            ouverte à {{ $session->opened_at?->format('H:i') }}
                        </div>
                    @endif

                    {{-- Caissiers affectés ────────────────────── --}}
                    <div class="px-5 py-4 space-y-2.5">
                        {{-- Matin ──────── --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                                    <i class="fas fa-sun text-yellow-400 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 leading-none">Caissier matin</p>
                                    <p class="text-sm font-medium text-slate-200 leading-tight mt-0.5">
                                        {{ $terminal->cashierMorning?->name ?? '— Non assigné —' }}
                                    </p>
                                </div>
                            </div>
                            @if($terminal->cashierMorning)
                                <form method="POST" action="{{ route('settings.pos-terminals.dissociate', $terminal) }}"
                                      onsubmit="return confirm('Dissocier ce caissier ?')">
                                    @csrf
                                    <input type="hidden" name="shift" value="morning">
                                    <button type="submit"
                                        class="text-xs text-red-400 hover:text-red-300 px-2 py-1 rounded-lg hover:bg-red-500/10 transition">
                                        <i class="fas fa-unlink"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Soir ───────── --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                                    <i class="fas fa-moon text-indigo-400 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 leading-none">Caissier soir</p>
                                    <p class="text-sm font-medium text-slate-200 leading-tight mt-0.5">
                                        {{ $terminal->cashierEvening?->name ?? '— Non assigné —' }}
                                    </p>
                                </div>
                            </div>
                            @if($terminal->cashierEvening)
                                <form method="POST" action="{{ route('settings.pos-terminals.dissociate', $terminal) }}"
                                      onsubmit="return confirm('Dissocier ce caissier ?')">
                                    @csrf
                                    <input type="hidden" name="shift" value="evening">
                                    <button type="submit"
                                        class="text-xs text-red-400 hover:text-red-300 px-2 py-1 rounded-lg hover:bg-red-500/10 transition">
                                        <i class="fas fa-unlink"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Actions ────────────────────────────────── --}}
                    <div class="px-5 py-3 border-t border-slate-700 flex items-center justify-between">
                        <a href="{{ route('settings.pos-terminals.edit', $terminal) }}"
                           class="flex items-center gap-1.5 text-xs text-slate-400 hover:text-slate-200 px-3 py-1.5 rounded-lg hover:bg-slate-700 transition">
                            <i class="fas fa-pen"></i> Modifier
                        </a>
                        @if(!$isOpen)
                            <form method="POST" action="{{ route('settings.pos-terminals.destroy', $terminal) }}"
                                  onsubmit="return confirm('Supprimer ce terminal définitivement ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="flex items-center gap-1.5 text-xs text-red-400 hover:text-red-300 px-3 py-1.5 rounded-lg hover:bg-red-500/10 transition">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-slate-600 italic">Session en cours</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>
</body>
</html>
