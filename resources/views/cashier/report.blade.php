<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de clôture — {{ $register->label ?? 'Session' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-card { box-shadow: none !important; border: 1px solid #e5e7eb !important; }
            .print-break { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-6 text-slate-900">

@php
    $isCatering = $register->isCateringPos();
    $signatureDate = now()->format('d/m/Y H:i');
@endphp

<div class="max-w-5xl mx-auto space-y-4">

    <div class="no-print flex items-center justify-between gap-4 mb-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Rapport de clôture</h1>
            <p class="text-slate-500 text-sm">À imprimer, signer, puis remettre au comptable avec la caisse</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl font-semibold transition-all">
                <i class="fas fa-print"></i> Imprimer
            </button>
            @if($isCatering)
                <a href="{{ route('cashier.ticket-codes.export', $register->id) }}" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-xl font-semibold transition-all">
                    <i class="fas fa-file-csv"></i> Export tickets CSV
                </a>
            @endif
            <a href="{{ route('cashier.open') }}" class="flex items-center gap-2 bg-slate-600 hover:bg-slate-500 text-white px-5 py-2.5 rounded-xl font-semibold transition-all">
                <i class="fas fa-home"></i> Accueil
            </a>
        </div>
    </div>

    <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-6 print-break">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Caissier</p>
                <p class="font-bold text-slate-800 mt-0.5">{{ $register->user->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Point de vente</p>
                <p class="font-bold text-slate-800 mt-0.5">{{ $register->label ?? 'Session #' . $register->id }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Ouverture</p>
                <p class="font-bold text-slate-800 mt-0.5">{{ $register->opened_at?->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Fermeture</p>
                <p class="font-bold text-slate-800 mt-0.5">{{ $register->closed_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="text-xs text-slate-500 uppercase tracking-wider">Module</p>
                <p class="font-semibold">{{ ucfirst($register->module) }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="text-xs text-slate-500 uppercase tracking-wider">Shift</p>
                <p class="font-semibold">{{ $register->shift === 'morning' ? 'Matin' : 'Soir' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="text-xs text-slate-500 uppercase tracking-wider">Statut comptable</p>
                <p class="font-semibold {{ $register->status === 'validated' ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $register->status === 'validated' ? 'Validé' : 'En attente' }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wider">Solde ouverture</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($register->opening_balance, 0, ',', ' ') }} MRU</p>
        </div>
        <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wider">Total encaissé</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($totalSales, 0, ',', ' ') }} MRU</p>
        </div>
        <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wider">Espèces comptées</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($register->closing_balance ?? 0, 0, ',', ' ') }} MRU</p>
        </div>
        <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wider">Écart</p>
            <p class="text-2xl font-bold {{ $variance > 0 ? 'text-orange-600' : ($variance < 0 ? 'text-red-600' : 'text-slate-500') }} mt-1">
                {{ ($variance >= 0 ? '+' : '') . number_format($variance, 0, ',', ' ') }} MRU
            </p>
        </div>
    </div>

    <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-6 print-break">
        <h2 class="font-bold text-slate-800 text-lg mb-4 flex items-center gap-2">
            <i class="fas fa-money-bill-wave text-emerald-500"></i> Répartition des paiements
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @forelse($paymentBreakdown as $type => $amount)
                <div class="flex items-center justify-between bg-slate-50 rounded-xl px-4 py-3">
                    <span class="font-medium text-slate-700">{{ $type }}</span>
                    <span class="font-bold text-slate-900">{{ number_format($amount, 0, ',', ' ') }} MRU</span>
                </div>
            @empty
                <p class="text-slate-500 text-sm">Aucun paiement enregistré.</p>
            @endforelse
        </div>
        @if($register->accounting_note)
            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Note de clôture</p>
                <p class="text-slate-700 text-sm">{{ $register->accounting_note }}</p>
            </div>
        @endif
    </div>

    @if(!$isCatering)
    <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-6 print-break">
        <h2 class="font-bold text-slate-800 text-lg mb-4 flex items-center gap-2">
            <i class="fas fa-receipt text-indigo-500"></i> Ventes restaurant</h2>
        @if($restaurantOrders->isEmpty())
            <p class="text-slate-500 text-sm">Aucune vente restaurant sur cette session.</p>
        @else
            <div class="space-y-4">
                @foreach($restaurantOrders as $order)
                    <div class="border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="font-bold text-slate-800">Commande #{{ $order->id }} @if($order->customer_number) - {{ $order->customer_number }} @endif</p>
                                <p class="text-xs text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-indigo-600">{{ number_format($order->total_amount, 0, ',', ' ') }} MRU</p>
                                <p class="text-xs text-slate-500">{{ $order->payment?->paymentType?->name ?? 'Paiement' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between bg-slate-50 rounded-lg px-3 py-2">
                                    <span>{{ $item->meal?->name ?? $item->product?->name ?? $item->label ?? 'Article' }} × {{ $item->quantity }}</span>
                                    <span class="font-semibold text-slate-700">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} MRU</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    @if($isCatering)
    <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-6 print-break">
        <h2 class="font-bold text-slate-800 text-lg mb-4 flex items-center gap-2">
            <i class="fas fa-utensils text-amber-500"></i> Plats contrat consommés</h2>
        @if($contractMealsConsumed->isEmpty())
            <p class="text-slate-500 text-sm">Aucun plat contrat sur cette session.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left py-2 text-slate-500 font-semibold">Plat</th>
                    <th class="text-right py-2 text-slate-500 font-semibold">Reçu</th>
                    <th class="text-right py-2 text-slate-500 font-semibold">Servi</th>
                    <th class="text-right py-2 text-slate-500 font-semibold">Restant</th>
                    <th class="text-left py-2 text-slate-500 font-semibold">Codes ticket saisis</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($contractMealsConsumed as $item)
                    @php
                        $ticketLogs = $contractTicketLogsByItem->get($item->id, collect());
                        $ticketCodes = $ticketLogs->pluck('ticket_code')->filter()->values();
                        $withoutTicketCount = $ticketLogs->filter(fn($log) => empty($log->ticket_code))->count();
                    @endphp
                    <tr>
                        <td class="py-2 font-medium text-slate-800">{{ $item->label }}</td>
                        <td class="py-2 text-right text-slate-600">{{ number_format($item->quantity_received, 0, ',', ' ') }}</td>
                        <td class="py-2 text-right text-blue-600 font-bold">{{ number_format($item->quantity_served, 0, ',', ' ') }}</td>
                        <td class="py-2 text-right {{ $item->available_qty > 0 ? 'text-orange-500 font-bold' : 'text-slate-400' }}">{{ number_format($item->available_qty, 0, ',', ' ') }}</td>
                        <td class="py-2 text-xs">
                            @if($ticketCodes->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($ticketCodes as $code)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">{{ $code }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400">Aucun code</span>
                            @endif
                            @if($withoutTicketCount > 0)
                                <div class="text-slate-400 mt-1">Sans code: {{ $withoutTicketCount }}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-6 print-break">
        <h2 class="font-bold text-slate-800 text-lg mb-4 flex items-center gap-2">
            <i class="fas fa-shopping-bag text-emerald-500"></i> Produits libres vendus</h2>
        @if($extraSold->isEmpty())
            <p class="text-slate-500 text-sm">Aucun produit libre vendu sur cette session.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left py-2 text-slate-500 font-semibold">Produit</th>
                    <th class="text-right py-2 text-slate-500 font-semibold">Reçu</th>
                    <th class="text-right py-2 text-slate-500 font-semibold">Vendu</th>
                    <th class="text-right py-2 text-slate-500 font-semibold">Restant</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($extraSold as $item)
                    <tr>
                        <td class="py-2 font-medium text-slate-800">{{ $item->label }}</td>
                        <td class="py-2 text-right text-slate-600">{{ number_format($item->quantity_received, 0, ',', ' ') }}</td>
                        <td class="py-2 text-right text-emerald-600 font-bold">{{ number_format($item->quantity_sold, 0, ',', ' ') }}</td>
                        <td class="py-2 text-right {{ $item->available_qty > 0 ? 'text-orange-500' : 'text-slate-400' }}">{{ number_format($item->available_qty, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="border-t-2 border-slate-300">
                <tr>
                    <td colspan="3" class="py-2 font-bold text-slate-700">Total produits libres encaissés</td>
                    <td class="py-2 text-right font-bold text-emerald-600 text-lg">{{ number_format($register->payments()->sum('amount'), 0, ',', ' ') }} MRU</td>
                </tr>
                </tfoot>
            </table>
        @endif
    </div>

    <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-6 print-break">
        <h2 class="font-bold text-slate-800 text-lg mb-4 flex items-center gap-2">
            <i class="fas fa-boxes text-slate-600"></i> Stock restant au terminal</h2>
        @if($terminalStockItems->isEmpty())
            <p class="text-slate-500 text-sm">Aucun stock terminal.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left py-2 text-slate-500 font-semibold">Article</th>
                    <th class="text-left py-2 text-slate-500 font-semibold">Type</th>
                    <th class="text-right py-2 text-slate-500 font-semibold">Reçu</th>
                    <th class="text-right py-2 text-slate-500 font-semibold">Servi</th>
                    <th class="text-right py-2 text-slate-500 font-semibold">Vendu</th>
                    <th class="text-right py-2 text-slate-500 font-semibold">Restant</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($terminalStockItems as $item)
                    <tr>
                        <td class="py-2 font-medium text-slate-800">{{ $item->label }}</td>
                        <td class="py-2 text-slate-500">{{ $item->item_type === 'contract' ? 'Contrat' : 'Extra' }}</td>
                        <td class="py-2 text-right text-slate-600">{{ number_format($item->quantity_received, 0, ',', ' ') }}</td>
                        <td class="py-2 text-right text-blue-600">{{ number_format($item->quantity_served, 0, ',', ' ') }}</td>
                        <td class="py-2 text-right text-emerald-600">{{ number_format($item->quantity_sold, 0, ',', ' ') }}</td>
                        <td class="py-2 text-right font-bold {{ $item->available_qty > 0 ? 'text-orange-500' : 'text-slate-400' }}">{{ number_format($item->available_qty, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
    @endif

    <div class="bg-white print-card rounded-2xl shadow-sm border border-slate-200 p-6 print-break">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="font-bold text-slate-800 text-lg mb-3 flex items-center gap-2">
                    <i class="fas fa-pen-fancy text-slate-600"></i> Signature caissier</h2>
                <div class="h-28 border-2 border-dashed border-slate-300 rounded-xl flex items-end p-4">
                    <div class="w-full">
                        <p class="text-xs text-slate-500">Nom: <strong>{{ $register->user->name ?? '—' }}</strong></p>
                        <p class="text-xs text-slate-500">Date: <strong>{{ $signatureDate }}</strong></p>
                        <div class="mt-8 border-t border-slate-300 pt-2 text-center text-slate-400 text-xs">Signature caissier</div>
                    </div>
                </div>
            </div>
            <div>
                <h2 class="font-bold text-slate-800 text-lg mb-3 flex items-center gap-2">
                    <i class="fas fa-user-check text-slate-600"></i> Réception comptable</h2>
                <div class="h-28 border-2 border-dashed border-slate-300 rounded-xl flex items-end p-4">
                    <div class="w-full">
                        <p class="text-xs text-slate-500">Montant remis: <strong>{{ number_format($register->closing_balance ?? 0, 0, ',', ' ') }} MRU</strong></p>
                        <p class="text-xs text-slate-500">Statut: <strong>{{ $register->status === 'validated' ? 'Validée' : 'À valider' }}</strong></p>
                        <div class="mt-8 border-t border-slate-300 pt-2 text-center text-slate-400 text-xs">Signature comptable</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
