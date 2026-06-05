<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Retour marchandises — Complex Royal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">

<header class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center gap-4">
    <a href="{{ route('cashier.session', $register->id) }}"
       class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div class="w-10 h-10 rounded-xl bg-rose-600 flex items-center justify-center">
        <i class="fas fa-undo text-white"></i>
    </div>
    <div>
        <h1 class="font-bold text-lg">Retour de marchandises en production</h1>
        <p class="text-slate-400 text-xs">Session {{ $register->label ?? 'Caisse' }} · {{ $register->client?->name }}</p>
    </div>
</header>

@if($errors->any())
    <div class="mx-6 mt-4 p-4 bg-red-500/20 border border-red-500/40 rounded-2xl text-red-300 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('pos-transfer.return.store', $register->id) }}"
      class="max-w-2xl mx-auto px-6 py-6 space-y-5">
    @csrf

    @if($stockItems->isEmpty())
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-10 text-center">
            <i class="fas fa-box-open text-4xl text-slate-600 mb-3"></i>
            <p class="text-slate-400">Aucun article disponible pour retour.</p>
            <p class="text-slate-500 text-sm mt-1">Tout le stock est déjà distribué ou retourné.</p>
        </div>
    @else
        <div class="bg-slate-800 border border-rose-500/20 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 bg-rose-600/10 border-b border-rose-500/20 flex items-center gap-3">
                <i class="fas fa-boxes text-rose-300"></i>
                <span class="font-semibold text-rose-300">Sélectionner les articles à retourner</span>
            </div>

            <div class="divide-y divide-slate-700">
                @foreach($stockItems as $idx => $si)
                <div class="px-5 py-4 flex items-center gap-4">
                    <input type="hidden" name="items[{{ $idx }}][stock_item_id]" value="{{ $si->id }}">

                    <div class="flex-1">
                        <p class="font-semibold text-white">{{ $si->label }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            <span class="{{ $si->is_contract ? 'text-blue-300' : 'text-emerald-300' }}">
                                {{ $si->is_contract ? 'Contrat' : 'Extra' }}
                            </span>
                            · Disponible: <strong class="text-white">{{ $si->available_qty }}</strong>
                            · Reçu: {{ $si->quantity_received }}
                            · Distribué: {{ $si->quantity_served }}
                        </p>
                    </div>

                    <div class="w-28">
                        <label class="block text-xs text-slate-400 mb-1">Qté à retourner</label>
                        <input type="number" name="items[{{ $idx }}][quantity]"
                               min="0" step="1" value="0"
                               max="{{ $si->available_qty }}"
                               class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2 text-center text-lg font-bold text-white focus:ring-2 focus:ring-rose-500 outline-none">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">Notes <span class="font-normal text-slate-500">(optionnel)</span></label>
            <textarea name="notes" rows="2"
                      placeholder="Raison du retour, état des marchandises..."
                      class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-sm text-white resize-none focus:ring-2 focus:ring-rose-500 outline-none"></textarea>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('cashier.session', $register->id) }}"
               class="px-5 py-2.5 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm font-medium transition">
                Annuler
            </a>
            <button type="submit"
                    class="flex items-center gap-2 px-6 py-2.5 bg-rose-600 hover:bg-rose-500 rounded-xl font-semibold text-sm transition shadow-lg shadow-rose-900/30">
                <i class="fas fa-paper-plane"></i> Enregistrer le retour
            </button>
        </div>
    @endif
</form>

</body>
</html>
