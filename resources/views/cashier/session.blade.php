<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Session — {{ $register->label ?? 'Caisse' }} — Complex Royal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">

{{-- ── Header ── --}}
<header class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center
            {{ $register->isCateringPos() ? 'bg-amber-600' : 'bg-indigo-600' }}">
            <i class="fas {{ $register->isCateringPos() ? 'fa-truck' : 'fa-cash-register' }} text-white"></i>
        </div>
        <div>
            <h1 class="font-bold text-lg leading-tight">
                {{ $register->label ?? ($register->isCateringPos() ? 'Point de vente catering' : 'Caisse ordinaire') }}
            </h1>
            <p class="text-slate-400 text-xs">
                Shift {{ $register->shift === 'morning' ? 'Matin' : 'Soir' }} ·
                Ouverte {{ $register->opened_at->format('d/m/Y H:i') }}
                @if($register->isCateringPos() && $register->client)
                    · <span class="text-amber-400">{{ $register->client->name }}</span>
                @endif
            </p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('cashier.report', $register->id) }}"
           class="flex items-center gap-2 bg-emerald-600/20 border border-emerald-500/40 hover:bg-emerald-600/40 text-emerald-300 px-4 py-2 rounded-xl text-sm font-semibold transition-all">
            <i class="fas fa-file-invoice"></i> Rapport
        </a>

        {{-- Transferts en attente (catering_pos uniquement) --}}
        @if($register->isCateringPos())
            @php $pendingCount = $register->pendingTransfers->count(); @endphp
            <a href="{{ route('pos-transfer.pending', $register->id) }}"
               class="relative flex items-center gap-2 bg-amber-600/20 border border-amber-500/40 hover:bg-amber-600/40 text-amber-300 px-4 py-2 rounded-xl text-sm font-semibold transition-all">
                <i class="fas fa-inbox"></i> Transferts
                @if($pendingCount > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 rounded-full text-xs flex items-center justify-center text-white font-bold animate-bounce">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>
        @endif

        {{-- Fermer la session --}}
        <button onclick="document.getElementById('closeModal').classList.remove('hidden')"
                class="flex items-center gap-2 bg-red-600/20 border border-red-500/40 hover:bg-red-600/40 text-red-300 px-4 py-2 rounded-xl text-sm font-semibold transition-all">
            <i class="fas fa-door-open"></i> Fermer la session
        </button>
    </div>
</header>

{{-- ── Alertes --}}
<div class="px-6 pt-4">
    @if(session('success'))
        <div class="mb-4 bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 rounded-xl px-4 py-3 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- Transferts en attente — alerte catering --}}
    @if($register->isCateringPos() && $register->pendingTransfers->count() > 0)
        <div class="mb-4 bg-amber-500/20 border border-amber-500/40 text-amber-300 rounded-xl px-4 py-3 text-sm flex items-center justify-between">
            <span><i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>{{ $register->pendingTransfers->count() }}</strong> transfert(s) de production en attente de validation</span>
            <a href="{{ route('pos-transfer.pending', $register->id) }}"
               class="bg-amber-500 hover:bg-amber-400 text-white px-3 py-1 rounded-lg text-xs font-bold transition-all">
                Voir &amp; Valider
            </a>
        </div>
    @endif
</div>

{{-- ── Statistiques --}}
<div class="px-6 py-4 grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="bg-slate-800 rounded-2xl p-4 border border-slate-700">
        <p class="text-slate-400 text-xs uppercase tracking-wider">Solde ouverture</p>
        <p class="text-2xl font-bold text-white mt-1">{{ number_format($register->opening_balance, 0, ',', ' ') }} MRU</p>
    </div>
    <div class="bg-slate-800 rounded-2xl p-4 border border-slate-700">
        <p class="text-slate-400 text-xs uppercase tracking-wider">Total encaissé</p>
        <p class="text-2xl font-bold text-emerald-400 mt-1">{{ number_format($totalSales, 0, ',', ' ') }} MRU</p>
    </div>
    <div class="bg-slate-800 rounded-2xl p-4 border border-slate-700">
        <p class="text-slate-400 text-xs uppercase tracking-wider">Espèces</p>
        <p class="text-2xl font-bold text-blue-400 mt-1">{{ number_format($cashTotal, 0, ',', ' ') }} MRU</p>
    </div>
    <div class="bg-slate-800 rounded-2xl p-4 border border-slate-700">
        <p class="text-slate-400 text-xs uppercase tracking-wider">Commandes</p>
        <p class="text-2xl font-bold text-purple-400 mt-1">{{ $sessionOrders->count() }}</p>
    </div>
</div>

{{-- ── Contenu restaurant : lien vers le POS --}}
@if(!$register->isCateringPos())
<div class="px-6 pb-6">
    <div class="bg-slate-800 border border-indigo-500/30 rounded-2xl p-6 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-utensils text-indigo-400"></i>
                Point de vente — {{ $register->label }}
            </h2>
            <p class="text-slate-400 text-sm mt-1">Session #{{ $register->id }} · {{ $sessionOrders->count() }} commande(s) cette session</p>
        </div>
        <a href="{{ url('modules/pos') }}"
           class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-indigo-900/40">
            <i class="fas fa-cash-register"></i> Prendre des commandes
        </a>
    </div>
</div>
@endif

{{-- ── Contenu catering_pos : transferts + vente libre --}}
@if($register->isCateringPos())

{{-- Deux colonnes d'action rapide --}}
<div class="px-6 pb-4 grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Carte : Vente libre (boissons, extras) --}}
    <a href="{{ route('pos.catering') }}"
       class="group relative flex items-center gap-5 bg-gradient-to-br from-amber-600 to-amber-800 rounded-2xl p-5 shadow-lg shadow-amber-900/40 hover:shadow-amber-700/60 hover:-translate-y-0.5 transition-all overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent pointer-events-none"></div>
        <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-shopping-cart text-white text-2xl"></i>
        </div>
        <div>
            <h3 class="font-bold text-white text-lg leading-tight">Vente libre</h3>
            <p class="text-amber-200 text-sm">Boissons, desserts, extras — sans contrat</p>
        </div>
        <i class="fas fa-arrow-right text-white/50 ml-auto group-hover:text-white transition-colors text-xl"></i>
    </a>

    {{-- Carte : Retour en production --}}
    <a href="{{ route('pos-transfer.return.create', $register->id) }}"
       class="group relative flex items-center gap-5 bg-gradient-to-br from-rose-700 to-rose-900 rounded-2xl p-5 shadow-lg shadow-rose-900/40 hover:shadow-rose-700/60 hover:-translate-y-0.5 transition-all overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent pointer-events-none"></div>
        <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-undo text-white text-2xl"></i>
        </div>
        <div>
            <h3 class="font-bold text-white text-lg leading-tight">Retour production</h3>
            <p class="text-rose-200 text-sm">Renvoyer le stock non utilisé</p>
        </div>
        <i class="fas fa-arrow-right text-white/50 ml-auto group-hover:text-white transition-colors text-xl"></i>
    </a>
</div>

<div class="px-6 pb-6">

    @php
        $contractStock = $terminalStockItems->where('item_type', 'contract');
        $extraStock    = $terminalStockItems->where('item_type', 'extra');
    @endphp

    @if($terminalStockItems->isEmpty())
        {{-- État vide stylé --}}
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-10 text-center">
            <div class="w-20 h-20 bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-box-open text-3xl text-slate-500"></i>
            </div>
            <p class="text-slate-300 font-semibold text-lg">Aucun stock au terminal</p>
            <p class="text-slate-500 text-sm mt-2">Le stock s'alimente lors de la validation des transferts de production.</p>
            <a href="{{ route('pos-transfer.pending', $register->id) }}"
               class="inline-flex items-center gap-2 mt-4 bg-amber-600 hover:bg-amber-500 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition">
                <i class="fas fa-inbox"></i> Voir les transferts en attente
            </a>
        </div>
    @else

        {{-- ── Plats contrat : cartes visuelles --}}
        @if($contractStock->count() > 0)
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 bg-amber-600/30 rounded-lg flex items-center justify-center">
                    <i class="fas fa-utensils text-amber-400 text-sm"></i>
                </div>
                <h2 class="font-bold text-white text-base">Plats contrat — Distribution</h2>
                <span class="ml-auto text-xs text-slate-500 bg-slate-800 px-3 py-1 rounded-full border border-slate-700">
                    {{ $contractStock->count() }} article(s)
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($contractStock as $si)
                @php
                    $pct = $si->quantity_received > 0 ? round(($si->available_qty / $si->quantity_received) * 100) : 0;
                    $barColor = $pct > 50 ? 'bg-emerald-500' : ($pct > 20 ? 'bg-amber-500' : 'bg-red-500');
                @endphp
                <div class="bg-slate-800 border border-amber-500/20 rounded-2xl p-5 hover:border-amber-500/50 transition-all" id="stock-row-{{ $si->id }}">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="font-bold text-white text-sm leading-tight">{{ $si->label }}</p>
                            <span class="inline-block mt-1 text-xs bg-amber-600/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded-full">Contrat</span>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black" id="remaining-s-{{ $si->id }}"
                               style="color: {{ $pct > 50 ? '#34d399' : ($pct > 20 ? '#fbbf24' : '#f87171') }}">
                                {{ $si->available_qty }}
                            </p>
                            <p class="text-xs text-slate-500">restant(s)</p>
                        </div>
                    </div>
                    {{-- Barre de progression --}}
                    <div class="h-2 bg-slate-700 rounded-full mb-3 overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $barColor }}" style="width:{{ $pct }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
                        <span>Reçu: <strong class="text-white">{{ $si->quantity_received }}</strong></span>
                        <span>Distribué: <strong class="text-blue-300" id="served-s-{{ $si->id }}">{{ $si->quantity_served }}</strong></span>
                    </div>
                    <button onclick="openDistributeModal({{ $si->id }}, '{{ addslashes($si->label) }}')"
                            class="w-full flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-500 active:bg-amber-700 text-white py-2.5 rounded-xl text-sm font-bold transition-all">
                        <i class="fas fa-minus-circle"></i> Distribuer 1
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Produits extra : cartes visuelles --}}
        @if($extraStock->count() > 0)
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 bg-emerald-600/30 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tag text-emerald-400 text-sm"></i>
                </div>
                <h2 class="font-bold text-white text-base">Produits hors contrat — Vente</h2>
                <span class="ml-auto text-xs text-slate-500 bg-slate-800 px-3 py-1 rounded-full border border-slate-700">
                    {{ $extraStock->count() }} article(s)
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($extraStock as $si)
                @php
                    $pct = $si->quantity_received > 0 ? round(($si->available_qty / $si->quantity_received) * 100) : 0;
                    $barColor = $pct > 50 ? 'bg-emerald-500' : ($pct > 20 ? 'bg-amber-500' : 'bg-red-500');
                @endphp
                <div class="bg-slate-800 border border-emerald-500/20 rounded-2xl p-5 hover:border-emerald-500/50 transition-all" id="stock-row-{{ $si->id }}">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="font-bold text-white text-sm leading-tight">{{ $si->label }}</p>
                            <span class="inline-block mt-1 text-xs bg-emerald-600/20 text-emerald-300 border border-emerald-500/30 px-2 py-0.5 rounded-full">Extra</span>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black" id="remaining-s-{{ $si->id }}"
                               style="color: {{ $pct > 50 ? '#34d399' : ($pct > 20 ? '#fbbf24' : '#f87171') }}">
                                {{ $si->available_qty }}
                            </p>
                            <p class="text-xs text-slate-500">restant(s)</p>
                        </div>
                    </div>
                    <div class="h-2 bg-slate-700 rounded-full mb-3 overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $barColor }}" style="width:{{ $pct }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
                        <span>Reçu: <strong class="text-white">{{ $si->quantity_received }}</strong></span>
                        <span>Vendu: <strong class="text-emerald-300" id="sold-s-{{ $si->id }}">{{ $si->quantity_sold }}</strong></span>
                    </div>
                    <button onclick="openSellStockModal({{ $si->id }}, '{{ addslashes($si->label) }}')"
                            class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white py-2.5 rounded-xl text-sm font-bold transition-all">
                        <i class="fas fa-shopping-cart"></i> Vendre
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Transferts validés --}}
        @if($validatedTransfers->count() > 0)
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 bg-slate-700/40 flex items-center gap-3">
                <i class="fas fa-history text-slate-400"></i>
                <span class="font-semibold text-slate-300 text-sm">
                    Transferts validés cette session
                </span>
                <span class="ml-auto text-xs bg-amber-600/20 text-amber-300 px-2 py-0.5 rounded-full">
                    {{ $validatedTransfers->count() }}
                </span>
            </div>
            @foreach($validatedTransfers as $transfer)
            <div class="px-5 py-3 border-t border-slate-700/50 flex items-center gap-4 text-sm hover:bg-slate-700/20 transition">
                <div class="w-8 h-8 bg-amber-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-file-alt text-amber-400 text-xs"></i>
                </div>
                <div class="flex-1">
                    <span class="text-amber-300 font-bold">{{ $transfer->reference }}</span>
                    <span class="text-slate-500 ml-2">— {{ $transfer->items->count() }} articles</span>
                </div>
                <span class="text-slate-500 text-xs">{{ $transfer->transfer_date->format('d/m H:i') }}</span>
                <a href="{{ route('pos-transfer.print', $transfer->id) }}" target="_blank"
                   class="flex items-center gap-1 text-xs bg-slate-700 hover:bg-slate-600 text-blue-300 px-3 py-1.5 rounded-lg transition">
                    <i class="fas fa-print"></i> PDF
                </a>
            </div>
            @endforeach
        </div>
        @endif

    @endif
</div>
@endif

{{-- ── Commandes ordinaires (ordinary) --}}
@if($register->isOrdinary() && $sessionOrders->count() > 0)
<div class="px-6 pb-6">
    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
        <i class="fas fa-receipt text-indigo-400"></i>
        Commandes de la session
    </h2>
    <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-slate-400 font-semibold">Commande</th>
                    <th class="px-4 py-3 text-left text-slate-400 font-semibold">Table</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-semibold">Montant</th>
                    <th class="px-4 py-3 text-left text-slate-400 font-semibold">Heure</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($sessionOrders as $order)
                <tr>
                    <td class="px-4 py-3 text-white font-medium">#{{ $order->id }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $order->customer_number ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-emerald-400">{{ number_format($order->total_amount, 0, ',', ' ') }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $order->paid_at?->format('H:i') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Modal fermeture session --}}
<div id="closeModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
    <div class="bg-slate-800 border border-slate-600 rounded-2xl w-full max-w-md p-6">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <i class="fas fa-door-open text-red-400"></i> Fermer la session
        </h2>
        <form action="{{ route('cashier.close', $register->id) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Espèces comptées en caisse (MRU)</label>
                    <input type="number" name="closing_balance" step="0.01" min="0" required
                           placeholder="0.00"
                           class="w-full bg-slate-700 border border-slate-600 text-white rounded-xl px-4 py-3 text-xl font-bold focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Note de clôture <span class="font-normal text-slate-500">(optionnelle)</span></label>
                    <textarea name="accounting_note" rows="3" placeholder="Remarques..."
                              class="w-full bg-slate-700 border border-slate-600 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-red-500 outline-none resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="button" onclick="document.getElementById('closeModal').classList.add('hidden')"
                        class="flex-1 bg-slate-600 hover:bg-slate-500 text-white py-3 rounded-xl font-semibold transition-all">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-500 text-white py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-lock"></i> Confirmer la fermeture
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal distribution plat contrat (code ticket optionnel) --}}
<div id="distributeModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
    <div class="bg-slate-800 border border-slate-600 rounded-2xl w-full max-w-sm p-6">
        <h2 class="text-xl font-bold text-white mb-1" id="distributeModalTitle">Distribuer un plat</h2>
        <p class="text-slate-400 text-sm mb-4">Saisir le code ticket si le client en fournit un (optionnel).</p>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">Code ticket (optionnel)</label>
                <input type="text" id="ticketCodeInput" maxlength="100" placeholder="Ex: TK-EMP-45892"
                       class="w-full bg-slate-700 border border-slate-600 text-white rounded-xl px-4 py-3 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">Quantité</label>
                <input type="number" id="distributeQty" value="1" min="0.5" step="0.5"
                       class="w-full bg-slate-700 border border-slate-600 text-white rounded-xl px-4 py-3 outline-none">
            </div>
        </div>
        <div class="flex gap-3 mt-5">
            <button onclick="closeDistributeModal()"
                    class="flex-1 bg-slate-600 hover:bg-slate-500 text-white py-3 rounded-xl font-semibold transition-all">
                Annuler
            </button>
            <button id="distributeConfirmBtn"
                    class="flex-1 bg-amber-600 hover:bg-amber-500 text-white py-3 rounded-xl font-bold transition-all">
                <i class="fas fa-check mr-2"></i>Distribuer
            </button>
        </div>
    </div>
</div>

{{-- ── Modal vente article extra --}}
<div id="sellModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
    <div class="bg-slate-800 border border-slate-600 rounded-2xl w-full max-w-sm p-6">
        <h2 class="text-xl font-bold text-white mb-1" id="sellModalTitle">Vendre</h2>
        <p class="text-slate-400 text-sm mb-4" id="sellModalPrice"></p>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">Quantité</label>
                <input type="number" id="sellQty" value="1" min="1" step="1"
                       class="w-full bg-slate-700 border border-slate-600 text-white rounded-xl px-4 py-3 text-xl font-bold outline-none">
            </div>
            <p class="text-emerald-400 font-bold text-lg" id="sellTotal"></p>
        </div>
        <div class="flex gap-3 mt-5">
            <button onclick="document.getElementById('sellModal').classList.add('hidden')"
                    class="flex-1 bg-slate-600 hover:bg-slate-500 text-white py-3 rounded-xl font-semibold transition-all">
                Annuler
            </button>
            <button id="sellConfirmBtn"
                    class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white py-3 rounded-xl font-bold transition-all">
                <i class="fas fa-check mr-2"></i>Encaisser
            </button>
        </div>
    </div>
</div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const BASE_URL = '{{ rtrim(url('/'), '/') }}';
    let currentSellItemId = null;
    let currentUnitPrice  = 0;
    let currentSellType   = 'transfer'; // 'transfer' ou 'stock'
    let currentDistributeStockItemId = null;

    // ── Distribution stock terminal (plat contrat) ─────────────────────
    function openDistributeModal(stockItemId, label) {
        currentDistributeStockItemId = stockItemId;
        document.getElementById('distributeModalTitle').textContent = `Distribuer: ${label}`;
        document.getElementById('ticketCodeInput').value = '';
        document.getElementById('distributeQty').value = '1';
        document.getElementById('distributeModal').classList.remove('hidden');
    }

    function closeDistributeModal() {
        document.getElementById('distributeModal').classList.add('hidden');
    }

    function distributeStock(stockItemId, qty, ticketCode = null) {
        fetch(`${BASE_URL}/pos-terminal-stock/${stockItemId}/distribute`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ qty, ticket_code: ticketCode }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const servedEl  = document.getElementById(`served-s-${stockItemId}`);
                const remainEl  = document.getElementById(`remaining-s-${stockItemId}`);
                if (servedEl) servedEl.textContent = (parseFloat(servedEl.textContent) + qty).toFixed(0);
                if (remainEl) remainEl.textContent = data.remaining.toFixed(0);
                closeDistributeModal();
            } else {
                alert(data.error || 'Erreur lors de la distribution.');
            }
        });
    }

    document.getElementById('distributeConfirmBtn').addEventListener('click', () => {
        const qty = parseFloat(document.getElementById('distributeQty').value);
        const ticketCode = (document.getElementById('ticketCodeInput').value || '').trim();

        if (!currentDistributeStockItemId || !qty || qty <= 0) {
            return;
        }

        distributeStock(currentDistributeStockItemId, qty, ticketCode || null);
    });

    // ── Vente stock terminal (produit extra) ───────────────────────────
    function openSellStockModal(stockItemId, label) {
        currentSellItemId = stockItemId;
        currentSellType   = 'stock';
        currentUnitPrice  = 0;
        document.getElementById('sellModalTitle').textContent = label;
        document.getElementById('sellModalPrice').textContent = 'Quantité à marquer comme vendue';
        updateSellTotal();
        document.getElementById('sellModal').classList.remove('hidden');
    }

    function serveItem(itemId, qty) {
        fetch(`/pos-transfer/items/${itemId}/serve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ qty }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const servedEl = document.getElementById(`served-${itemId}`);
                const remainEl = document.getElementById(`remaining-${itemId}`);
                if (servedEl) servedEl.textContent = parseFloat(servedEl.textContent) + qty;
                if (remainEl) remainEl.textContent = data.remaining;
            } else {
                alert(data.error || 'Erreur lors du service.');
            }
        });
    }

    function openSellModal(itemId, label, unitPrice) {
        currentSellItemId = itemId;
        currentSellType   = 'transfer';
        currentUnitPrice  = unitPrice;
        document.getElementById('sellModalTitle').textContent = label;
        document.getElementById('sellModalPrice').textContent = unitPrice.toLocaleString() + ' MRU / unité';
        updateSellTotal();
        document.getElementById('sellModal').classList.remove('hidden');
    }

    function updateSellTotal() {
        const qty = parseFloat(document.getElementById('sellQty').value) || 0;
        if (currentUnitPrice > 0) {
            document.getElementById('sellTotal').textContent = 'Total : ' + (qty * currentUnitPrice).toLocaleString() + ' MRU';
        } else {
            document.getElementById('sellTotal').textContent = 'Quantité : ' + qty;
        }
    }

    document.getElementById('sellQty').addEventListener('input', updateSellTotal);

    document.getElementById('sellConfirmBtn').addEventListener('click', () => {
        const qty = parseFloat(document.getElementById('sellQty').value);
        if (!qty || qty <= 0) return;

        if (currentSellType === 'stock') {
            fetch(`${BASE_URL}/pos-terminal-stock/${currentSellItemId}/sell`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ qty }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('sellModal').classList.add('hidden');
                    location.reload();
                } else {
                    alert(data.error || 'Erreur.');
                }
            });
        } else {
            fetch(`/pos-transfer/items/${currentSellItemId}/sell`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ qty }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const remainEl = document.getElementById(`remaining-${currentSellItemId}`);
                    if (remainEl) remainEl.textContent = data.remaining;
                    document.getElementById('sellModal').classList.add('hidden');
                    location.reload();
                } else {
                    alert(data.error || 'Erreur lors de la vente.');
                }
            });
        }
    });
</script>
</body>
</html>
