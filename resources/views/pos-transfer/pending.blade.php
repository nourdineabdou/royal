{{-- Vue : transferts en attente pour le caissier --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Transferts en attente — Complex Royal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">

<header class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('cashier.session', $register->id) }}"
           class="w-9 h-9 bg-slate-700 hover:bg-slate-600 rounded-xl flex items-center justify-center transition-all">
            <i class="fas fa-arrow-left text-slate-300"></i>
        </a>
        <div>
            <h1 class="font-bold text-lg">Transferts de production</h1>
            <p class="text-slate-400 text-xs">{{ $register->label ?? 'Caisse #'.$register->id }} · {{ $register->client?->name }}</p>
        </div>
    </div>
    <span class="bg-amber-600/30 text-amber-300 border border-amber-500/40 px-3 py-1 rounded-full text-sm font-semibold">
        {{ $transfers->count() }} en attente
    </span>
</header>

<div class="p-6 space-y-4">

    @if(session('success'))
        <div class="bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 rounded-xl px-4 py-3 text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @forelse($transfers as $transfer)
    <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
        {{-- En-tête du transfert --}}
        <div class="px-5 py-4 flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="font-bold text-white text-lg">{{ $transfer->reference }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                        {{ $transfer->status === 'pending' ? 'bg-orange-600/30 text-orange-300 border border-orange-500/40' : 'bg-blue-600/30 text-blue-300 border border-blue-500/40' }}">
                        {{ $transfer->status === 'pending' ? 'En attente' : 'En transit' }}
                    </span>
                </div>
                <p class="text-slate-400 text-sm">
                    <i class="fas fa-calendar-alt mr-1"></i> {{ $transfer->transfer_date->format('d/m/Y') }} &nbsp;|&nbsp;
                    <i class="fas fa-user-cog mr-1"></i> Préparé par {{ $transfer->preparedBy?->name ?? '—' }}
                    @if($transfer->driver_name)
                        &nbsp;|&nbsp; <i class="fas fa-truck mr-1"></i> Chauffeur : {{ $transfer->driver_name }}
                    @endif
                </p>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <a href="{{ route('pos-transfer.print', $transfer->id) }}" target="_blank"
                   class="flex items-center gap-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 px-3 py-2 rounded-xl text-sm transition-all">
                    <i class="fas fa-print"></i> Bon
                </a>
                @if(in_array($transfer->status, ['pending', 'in_transit']))
                <form action="{{ route('pos-transfer.validate', $transfer->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl text-sm font-bold transition-all">
                        <i class="fas fa-check-double"></i> Valider réception
                    </button>
                </form>
                @else
                    <span class="flex items-center gap-1.5 bg-emerald-600/20 text-emerald-400 border border-emerald-500/40 px-4 py-2 rounded-xl text-sm font-bold">
                        <i class="fas fa-check-circle"></i> Validé
                    </span>
                @endif
            </div>
        </div>

        {{-- Lignes du transfert --}}
        <div class="border-t border-slate-700">
            <table class="w-full text-sm">
                <thead class="bg-slate-700/40">
                    <tr>
                        <th class="px-4 py-2 text-left text-slate-400 font-semibold">Article</th>
                        <th class="px-4 py-2 text-center text-slate-400 font-semibold">Type</th>
                        <th class="px-4 py-2 text-right text-slate-400 font-semibold">Quantité</th>
                        <th class="px-4 py-2 text-right text-slate-400 font-semibold">Emballage</th>
                        <th class="px-4 py-2 text-right text-slate-400 font-semibold">Prix unit.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @foreach($transfer->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-white font-medium">{{ $item->label }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($item->item_type === 'contract')
                                <span class="text-xs bg-blue-600/30 text-blue-300 px-2 py-0.5 rounded-full">Contrat</span>
                            @else
                                <span class="text-xs bg-emerald-600/30 text-emerald-300 px-2 py-0.5 rounded-full">Vente</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-white font-bold">
                            {{ $item->quantity }} {{ $item->unit ?? '' }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-400">
                            {{ $item->packaging?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-emerald-400 font-semibold">
                            {{ $item->item_type === 'extra' ? number_format($item->unit_price, 0).' MRU' : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($transfer->notes)
        <div class="px-5 py-3 bg-slate-700/20 text-slate-400 text-sm italic border-t border-slate-700">
            <i class="fas fa-sticky-note mr-1"></i>{{ $transfer->notes }}
        </div>
        @endif
    </div>
    @empty
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-12 text-center">
        <i class="fas fa-inbox text-5xl text-slate-600 mb-4"></i>
        <p class="text-slate-400 text-lg font-semibold">Aucun transfert en attente</p>
        <p class="text-slate-500 text-sm mt-1">La production n'a pas encore envoyé de transferts pour cette caisse.</p>
    </div>
    @endforelse
</div>

</body>
</html>
