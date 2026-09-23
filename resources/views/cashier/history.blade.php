<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes sessions — Complex Royal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">

<header class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center">
            <i class="fas fa-clock-rotate-left text-white"></i>
        </div>
        <h1 class="font-bold text-lg leading-tight">Mes sessions de caisse</h1>
    </div>
    <a href="{{ route('cashier.open') }}"
       class="flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-slate-200 px-4 py-2 rounded-xl text-sm font-semibold transition-all">
        <i class="fas fa-home"></i> Accueil
    </a>
</header>

<div class="max-w-5xl mx-auto p-6 space-y-4">

    @if(session('success'))
        <div class="p-4 bg-emerald-500/20 border border-emerald-500/40 rounded-2xl text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($registers->isEmpty())
        <div class="bg-slate-800 border border-slate-700 rounded-3xl p-8 text-center text-slate-400">
            <i class="fas fa-inbox text-3xl mb-3"></i>
            <p>Aucune session pour le moment.</p>
        </div>
    @else
        <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-700/50 text-slate-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Caisse</th>
                        <th class="px-4 py-3 text-left">Ouverte le</th>
                        <th class="px-4 py-3 text-left">Fermée le</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-right">Fonds initial</th>
                        <th class="px-4 py-3 text-right">Solde clôture</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @foreach($registers as $register)
                        @php
                            $statusColor = match($register->status) {
                                'open' => 'emerald',
                                'closed' => 'amber',
                                'validated' => 'indigo',
                                default => 'slate',
                            };
                            $statusLabel = match($register->status) {
                                'open' => 'Ouverte',
                                'closed' => 'Fermée',
                                'validated' => 'Validée',
                                default => $register->status,
                            };
                        @endphp
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-100">{{ $register->label ?? ($register->isCateringPos() ? 'Point de vente catering' : 'Caisse ordinaire') }}</p>
                                <p class="text-xs text-slate-500">Shift {{ $register->shift === 'morning' ? 'Matin' : 'Soir' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $register->opened_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $register->closed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full bg-{{ $statusColor }}-500/20 text-{{ $statusColor }}-300 font-medium">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-slate-300">{{ number_format($register->opening_balance, 2) }} MRU</td>
                            <td class="px-4 py-3 text-right text-slate-300">{{ $register->closing_balance !== null ? number_format($register->closing_balance, 2) . ' MRU' : '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($register->status === 'open')
                                    <a href="{{ route('cashier.session', $register->id) }}"
                                       class="text-indigo-400 hover:text-indigo-300 font-medium">
                                        <i class="fas fa-arrow-right"></i> Voir
                                    </a>
                                @else
                                    <a href="{{ route('cashier.report', $register->id) }}"
                                       class="text-emerald-400 hover:text-emerald-300 font-medium">
                                        <i class="fas fa-print"></i> Rapport
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-center">
            {{ $registers->links() }}
        </div>
    @endif

</div>
</body>
</html>
