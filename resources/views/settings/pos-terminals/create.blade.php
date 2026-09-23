<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($posTerminal) ? 'Modifier' : 'Nouveau' }} terminal POS — Complex Royal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px; border: 1px solid #d1d5db; border-radius: 0.5rem;
            display: flex; align-items: center; padding: 0 0.5rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal; padding-left: 0.25rem; color: #111827;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,0.3);
        }
    </style>
    <script>
        (function ($) {
            function initSelect2(scope) {
                $(scope || document).find('select:not(.select2-hidden-accessible)').each(function () {
                    $(this).select2({ width: 'resolve' });
                });
            }
            $(function () { initSelect2(); });
            if (window.MutationObserver) {
                new MutationObserver(function (mutations) {
                    mutations.forEach(function (m) {
                        m.addedNodes.forEach(function (node) {
                            if (node.nodeType !== 1) return;
                            if (node.matches && node.matches('select')) initSelect2(node.parentNode || document);
                            else if (node.querySelectorAll) initSelect2(node);
                        });
                    });
                }).observe(document.body, { childList: true, subtree: true });
            }
        })(jQuery);
    </script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">

<header class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center gap-4">
    <a href="{{ route('settings.pos-terminals.index') }}"
       class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div class="w-10 h-10 rounded-xl bg-violet-600 flex items-center justify-center">
        <i class="fas fa-store text-white"></i>
    </div>
    <div>
        <h1 class="font-bold text-lg leading-tight">
            {{ isset($posTerminal) ? 'Modifier le terminal : '.$posTerminal->label : 'Nouveau terminal POS' }}
        </h1>
        <p class="text-slate-400 text-xs">Configuration du point de vente et affectation des caissiers</p>
    </div>
</header>

@if($errors->any())
    <div class="mx-6 mt-4 p-4 bg-red-500/20 border border-red-500/40 rounded-xl text-red-300 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ isset($posTerminal) ? route('settings.pos-terminals.update', $posTerminal) : route('settings.pos-terminals.store') }}"
      class="max-w-2xl mx-auto px-6 py-6 space-y-6">
    @csrf
    @if(isset($posTerminal)) @method('PUT') @endif

    {{-- ── Section 1 : Identité ─────────────────────────────────────── --}}
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 space-y-4">
        <h2 class="font-semibold text-slate-200 flex items-center gap-2">
            <i class="fas fa-id-card text-violet-400"></i> Identité du terminal
        </h2>

        {{-- Label --}}
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1">Nom du terminal *</label>
            <input type="text" name="label" required
                value="{{ old('label', $posTerminal->label ?? '') }}"
                placeholder="Ex: POS Catering Abdallah — Matin"
                class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            {{-- Type --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Type *</label>
                <select name="type" id="typeSelect" required
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    <option value="ordinary"    {{ old('type', $posTerminal->type ?? '') === 'ordinary'    ? 'selected' : '' }}>Point de vente ordinaire</option>
                    <option value="catering_pos" {{ old('type', $posTerminal->type ?? '') === 'catering_pos' ? 'selected' : '' }}>Point de vente distant (lie au stock/client)</option>
                </select>
                <p class="text-xs text-slate-500 mt-1">
                    Ordinaire: caisse classique. Distant: terminal rattache a un client et a un stock (catering).
                </p>
            </div>

            {{-- Module --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Module *</label>
                <select name="module" required
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    <option value="restaurant"  {{ old('module', $posTerminal->module ?? '') === 'restaurant'  ? 'selected' : '' }}>Restaurant</option>
                    <option value="catering"    {{ old('module', $posTerminal->module ?? '') === 'catering'    ? 'selected' : '' }}>Catering</option>
                    <option value="events"      {{ old('module', $posTerminal->module ?? '') === 'events'      ? 'selected' : '' }}>Événements</option>
                    <option value="residence"   {{ old('module', $posTerminal->module ?? '') === 'residence'   ? 'selected' : '' }}>Résidence</option>
                </select>
            </div>
        </div>

        {{-- Actif / Notes --}}
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $posTerminal->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded accent-violet-500">
                <span class="text-sm text-slate-300">Terminal actif</span>
            </label>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1">Notes internes</label>
            <textarea name="notes" rows="2" placeholder="Emplacement, remarques..."
                class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 resize-none">{{ old('notes', $posTerminal->notes ?? '') }}</textarea>
        </div>
    </div>

    {{-- ── Section 2 : Rattachement catering ───────────────────────────── --}}
    <div id="cateringSection"
         class="{{ old('type', $posTerminal->type ?? '') !== 'catering_pos' ? 'hidden' : '' }}
                bg-amber-900/20 border border-amber-600/30 rounded-2xl p-6 space-y-4">
        <h2 class="font-semibold text-amber-300 flex items-center gap-2">
            <i class="fas fa-truck text-amber-400"></i> Paramètres catering
        </h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Client catering *</label>
                <select name="client_id"
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">— Choisir un client —</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}"
                            {{ old('client_id', $posTerminal->client_id ?? '') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Stock rattaché *</label>
                <select name="stock_id"
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">— Choisir un stock —</option>
                    @foreach($stocks as $stock)
                        <option value="{{ $stock->id }}"
                            {{ old('stock_id', $posTerminal->stock_id ?? '') == $stock->id ? 'selected' : '' }}>
                            {{ $stock->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ── Section 3 : Affectation des caissiers ────────────────────────── --}}
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 space-y-4">
        <h2 class="font-semibold text-slate-200 flex items-center gap-2">
            <i class="fas fa-users text-indigo-400"></i> Affectation des caissiers
        </h2>

        <div class="grid grid-cols-2 gap-4">
            {{-- Matin --}}
            <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                        <i class="fas fa-sun text-yellow-400 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-yellow-300">Shift Matin</span>
                </div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Caissier assigné</label>
                <select name="cashier_morning_id"
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <option value="">— Non assigné —</option>
                    @foreach($cashiers as $cashier)
                        <option value="{{ $cashier->id }}"
                            {{ old('cashier_morning_id', $posTerminal->cashier_morning_id ?? '') == $cashier->id ? 'selected' : '' }}>
                            {{ $cashier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Soir --}}
            <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                        <i class="fas fa-moon text-indigo-400 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-indigo-300">Shift Soir</span>
                </div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Caissier assigné</label>
                <select name="cashier_evening_id"
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Non assigné —</option>
                    @foreach($cashiers as $cashier)
                        <option value="{{ $cashier->id }}"
                            {{ old('cashier_evening_id', $posTerminal->cashier_evening_id ?? '') == $cashier->id ? 'selected' : '' }}>
                            {{ $cashier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <p class="text-xs text-slate-500">
            <i class="fas fa-info-circle mr-1"></i>
            Seuls les utilisateurs avec le rôle <strong>caissier</strong> sont listés.
            Un caissier non assigné verra un message "contactez votre admin" à la connexion.
        </p>
    </div>

    {{-- Submit ── --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('settings.pos-terminals.index') }}"
           class="px-5 py-2.5 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm font-medium transition">
            Annuler
        </a>
        <button type="submit"
            class="flex items-center gap-2 px-6 py-2.5 bg-violet-600 hover:bg-violet-500 rounded-xl font-semibold text-sm transition shadow-lg">
            <i class="fas fa-save"></i>
            {{ isset($posTerminal) ? 'Enregistrer les modifications' : 'Créer le terminal' }}
        </button>
    </div>
</form>

<script>
document.getElementById('typeSelect').addEventListener('change', function () {
    const sec = document.getElementById('cateringSection');
    sec.classList.toggle('hidden', this.value !== 'catering_pos');
});
</script>
</body>
</html>
