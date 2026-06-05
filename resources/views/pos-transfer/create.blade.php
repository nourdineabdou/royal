<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nouveau Transfert POS — Complex Royal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">

{{-- ── Header ── --}}
<header class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center gap-4">
    <a href="{{ route('catering.dashboard') }}"
       class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div class="w-10 h-10 rounded-xl bg-amber-600 flex items-center justify-center">
        <i class="fas fa-truck text-white"></i>
    </div>
    <div>
        <h1 class="font-bold text-lg leading-tight">Nouveau transfert de production</h1>
        <p class="text-slate-400 text-xs">Créer un bon de transfert vers un point de vente catering</p>
    </div>
</header>

{{-- ── Alertes ── --}}
@if(session('error'))
    <div class="mx-6 mt-4 p-4 bg-red-500/20 border border-red-500/40 rounded-xl text-red-300 text-sm">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="mx-6 mt-4 p-4 bg-red-500/20 border border-red-500/40 rounded-xl text-red-300 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

{{-- Alerte : aucun terminal configuré --}}
@if($terminals->isEmpty())
    <div class="mx-6 mt-4 p-4 bg-amber-500/20 border border-amber-500/40 rounded-xl text-amber-200 text-sm flex items-start gap-3">
        <i class="fas fa-info-circle mt-0.5 text-amber-400"></i>
        <div>
            <strong class="block mb-1">Aucun terminal POS configuré</strong>
            L'administrateur doit d'abord créer des terminaux dans
            <a href="{{ route('settings.pos-terminals.index') }}" class="text-amber-300 underline">Paramètres → Terminaux POS</a>.
        </div>
    </div>
@endif

{{-- ── Formulaire ── --}}
<form id="transferForm" method="POST" action="{{ route('pos-transfer.store') }}"
      class="max-w-4xl mx-auto px-6 py-6 space-y-6">
    @csrf

    {{-- Section 1 : Informations générales ──────────────────────────────── --}}
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 space-y-4">
        <h2 class="font-semibold text-slate-200 flex items-center gap-2">
            <i class="fas fa-file-alt text-amber-400"></i> Informations générales
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Client --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Client catering *</label>
                <select id="clientSelect" name="client_id" required
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Choisir un client --</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}"
                            {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }} @if($client->company) — {{ $client->company }} @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Contrat --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Contrat catering</label>
                <select id="contractSelect" name="catering_contract_id"
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Sélectionner d'abord un client --</option>
                </select>
            </div>

            {{-- Stock de départ --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Stock de départ (production) *</label>
                <select id="fromStockSelect" name="from_stock_id" required
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Choisir le stock catering --</option>
                    @foreach($allStocks as $stock)
                        <option value="{{ $stock->id }}" {{ old('from_stock_id') == $stock->id ? 'selected' : '' }}>
                            {{ $stock->name }} ({{ $stock->module_label ?? 'Catering' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Le transfert catering utilise uniquement le stock dédié Catering.</p>
            </div>

            {{-- Terminal POS de destination --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">
                    Terminal POS destination *
                </label>
                <select id="terminalSelect" name="pos_terminal_id" required
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Choisir un terminal POS --</option>
                    @foreach($terminals as $terminal)
                        <option value="{{ $terminal->id }}"
                            data-client="{{ $terminal->client_id }}"
                            data-morning="{{ $terminal->cashierMorning?->name ?? '' }}"
                            data-evening="{{ $terminal->cashierEvening?->name ?? '' }}"
                            data-active="{{ $terminal->activeSession ? '1' : '0' }}"
                            {{ old('pos_terminal_id') == $terminal->id ? 'selected' : '' }}>
                            {{ $terminal->label }}
                            (Distant catering)
                            @if($terminal->client) — {{ $terminal->client->name }} @endif
                            @if($terminal->activeSession) 🟢 Session ouverte @else ⚪ En attente @endif
                        </option>
                    @endforeach
                </select>
                {{-- Affichage des caissiers liés au terminal sélectionné --}}
                <div id="terminalCashiers" class="hidden mt-2 p-3 bg-amber-900/20 border border-amber-500/20 rounded-xl text-xs">
                    <p class="text-amber-300 font-semibold mb-1"><i class="fas fa-user-tie mr-1"></i>Caissiers liés</p>
                    <div id="cashierMorning" class="flex items-center gap-2 text-slate-300">
                        <i class="fas fa-sun text-yellow-400 w-4"></i>
                        <span id="cashierMorningName">—</span>
                    </div>
                    <div id="cashierEvening" class="flex items-center gap-2 text-slate-300 mt-1">
                        <i class="fas fa-moon text-indigo-400 w-4"></i>
                        <span id="cashierEveningName">—</span>
                    </div>
                </div>
            </div>

            {{-- Date du transfert --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Date de livraison *</label>
                <input type="date" id="transferDate" name="transfer_date" required
                    value="{{ old('transfer_date', date('Y-m-d')) }}"
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            {{-- Chauffeur --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Nom du chauffeur</label>
                <input type="text" name="driver_name" value="{{ old('driver_name') }}"
                    placeholder="Ex: Mohammed Ahmed"
                    class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1">Notes / Instructions</label>
            <textarea name="notes" rows="2" placeholder="Instructions au chauffeur, remarques..."
                class="w-full bg-slate-700 border border-slate-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none">{{ old('notes') }}</textarea>
        </div>
    </div>

    {{-- Suggestion menu du jour ── (apparaît après sélection du client) --}}
    <div id="menuSuggestion" class="hidden bg-emerald-900/30 border border-emerald-500/30 rounded-2xl p-5">
        <h3 class="font-semibold text-emerald-300 flex items-center gap-2 mb-3">
            <i class="fas fa-utensils"></i> Menu programmé pour aujourd'hui
        </h3>
        <div id="menuSuggestionContent" class="space-y-2 text-sm text-slate-300"></div>
        <button type="button" id="addMenuItemsBtn"
            class="mt-4 flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-plus-circle"></i> Ajouter ces plats au transfert
        </button>
    </div>

    {{-- Section 2 : Articles du transfert ───────────────────────────────── --}}
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-200 flex items-center gap-2">
                <i class="fas fa-list text-amber-400"></i> Articles à transférer
            </h2>
            <button type="button" id="addRowBtn"
                class="flex items-center gap-2 px-3 py-1.5 bg-amber-600 hover:bg-amber-500 rounded-xl text-sm font-semibold transition">
                <i class="fas fa-plus"></i> Ajouter une ligne
            </button>
        </div>

        <div id="itemsTable" class="space-y-3">
            {{-- Les lignes sont insérées dynamiquement --}}
        </div>

        <p id="noItemsMsg" class="text-center text-slate-500 text-sm py-6">
            <i class="fas fa-box-open block text-2xl mb-1 opacity-40"></i>
            Aucun article. Cliquez sur « Ajouter une ligne » ou utilisez le menu suggéré.
        </p>
    </div>

    {{-- Submit ── --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('catering.dashboard') }}"
           class="px-5 py-2.5 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm font-medium transition">
            Annuler
        </a>
        <button type="submit"
            class="flex items-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-500 rounded-xl font-semibold text-sm transition shadow-lg shadow-amber-900/40">
            <i class="fas fa-paper-plane"></i> Créer le transfert
        </button>
    </div>
</form>

{{-- ── Template ligne article ── --}}
<template id="rowTpl">
    <div class="item-row grid grid-cols-12 gap-2 items-start bg-slate-750 border border-slate-600/60 rounded-xl p-3">

        {{-- Type --}}
        <div class="col-span-2">
            <label class="text-[10px] text-slate-400 mb-0.5 block">Type</label>
            <select name="items[__IDX__][item_type]"
                class="w-full bg-slate-700 border border-slate-600 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500">
                <option value="contract">Contrat</option>
                <option value="extra">Extra</option>
            </select>
        </div>

        {{-- Label --}}
        <div class="col-span-3">
            <label class="text-[10px] text-slate-400 mb-0.5 block">Désignation *</label>
            <input type="text" name="items[__IDX__][label]" required placeholder="Ex: Déjeuner"
                class="w-full bg-slate-700 border border-slate-600 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500">
        </div>

        {{-- Source --}}
        <div class="col-span-2">
            <label class="text-[10px] text-slate-400 mb-0.5 block">Source</label>
            <select name="items[__IDX__][source_kind]"
                class="source-kind w-full bg-slate-700 border border-slate-600 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500">
                <option value="meal">Plat</option>
                <option value="product">Produit</option>
            </select>
        </div>

        {{-- Plat --}}
        <div class="col-span-2 meal-select-wrap">
            <label class="text-[10px] text-slate-400 mb-0.5 block">Plat</label>
            <select name="items[__IDX__][meal_id]"
                class="meal-select w-full bg-slate-700 border border-slate-600 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500">
                <option value="">-- Plat --</option>
            </select>
        </div>

        {{-- Produit --}}
        <div class="col-span-2 product-select-wrap hidden">
            <label class="text-[10px] text-slate-400 mb-0.5 block">Produit</label>
            <select name="items[__IDX__][product_id]"
                class="product-select w-full bg-slate-700 border border-slate-600 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500">
                <option value="">-- Produit --</option>
            </select>
        </div>

        {{-- Emballage --}}
        <div class="col-span-2 packaging-select-wrap hidden">
            <label class="text-[10px] text-slate-400 mb-0.5 block">Emballage</label>
            <select name="items[__IDX__][packaging_id]"
                class="packaging-select w-full bg-slate-700 border border-slate-600 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500">
                <option value="">Unité de base</option>
            </select>
        </div>

        {{-- Qté --}}
        <div class="col-span-1">
            <label class="text-[10px] text-slate-400 mb-0.5 block">Quantité *</label>
            <input type="number" name="items[__IDX__][quantity]" min="0.001" step="0.001" required
                class="w-full bg-slate-700 border border-slate-600 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500">
        </div>

        {{-- Unité --}}
        <div class="col-span-1 hidden md:block">
            <label class="text-[10px] text-slate-400 mb-0.5 block">Unité</label>
            <input type="text" name="items[__IDX__][unit]" placeholder="portion"
                class="w-full bg-slate-700 border border-slate-600 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500">
        </div>

        {{-- Prix unitaire --}}
        <div class="col-span-1">
            <label class="text-[10px] text-slate-400 mb-0.5 block">P.U. (FCFA)</label>
            <input type="number" name="items[__IDX__][unit_price]" min="0" step="1" placeholder="0"
                class="w-full bg-slate-700 border border-slate-600 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500">
        </div>

        {{-- Supprimer --}}
        <div class="col-span-1 flex items-end justify-center pb-0.5">
            <button type="button" onclick="this.closest('.item-row').remove(); refreshNoItemsMsg()"
                class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-400 transition">
                <i class="fas fa-trash-alt text-xs"></i>
            </button>
        </div>

        {{-- Champs front-only --}}
        <input type="hidden" name="items[__IDX__][row_meta]" value="1">
    </div>
</template>

<script>
let rowIndex = 0;
let suggestedMeals = [];

const products = @json($products->map(function($p){
    return [
        'id' => $p->id,
        'name' => $p->name,
        'packagings' => $p->productPackagings->map(function($pp){
            return [
                'packaging_id' => $pp->packaging_id,
                'packaging_name' => $pp->packaging?->name,
                'quantity' => (float) $pp->quantity,
            ];
        })->values(),
    ];
})->values());
const meals = @json($meals->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values());

document.getElementById('addRowBtn').addEventListener('click', () => addRow());
document.getElementById('clientSelect').addEventListener('change', () => loadClientContractsAndMeals());
document.getElementById('contractSelect').addEventListener('change', () => loadProgrammedMealsOnly());
document.getElementById('transferDate').addEventListener('change', () => loadClientContractsAndMeals(true));
document.getElementById('terminalSelect').addEventListener('change', handleTerminalInfo);

function mealOptionsHtml() {
    return '<option value="">-- Plat --</option>' + meals.map(m => `<option value="${m.id}">${escapeHtml(m.name)}</option>`).join('');
}

function productOptionsHtml() {
    return '<option value="">-- Produit --</option>' + products.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('');
}

function addRow(data = {}) {
    const tpl = document.getElementById('rowTpl').content.cloneNode(true);
    const row = tpl.querySelector('.item-row');
    const idx = rowIndex++;

    row.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace('__IDX__', idx);
    });

    const mealSelect = row.querySelector('.meal-select');
    const productSelect = row.querySelector('.product-select');
    mealSelect.innerHTML = mealOptionsHtml();
    productSelect.innerHTML = productOptionsHtml();

    if (data.item_type) row.querySelector(`[name="items[${idx}][item_type]"]`).value = data.item_type;
    if (data.label) row.querySelector(`[name="items[${idx}][label]"]`).value = data.label;
    if (data.quantity) row.querySelector(`[name="items[${idx}][quantity]"]`).value = data.quantity;
    if (data.unit) row.querySelector(`[name="items[${idx}][unit]"]`).value = data.unit;
    if (data.unit_price !== undefined) row.querySelector(`[name="items[${idx}][unit_price]"]`).value = data.unit_price;

    if (data.product_id) {
        row.querySelector('.source-kind').value = 'product';
        productSelect.value = data.product_id;
    }
    if (data.meal_id) {
        row.querySelector('.source-kind').value = 'meal';
        mealSelect.value = data.meal_id;
    }

    bindRowEvents(row);
    document.getElementById('itemsTable').appendChild(row);

    updateRowTypeUI(row);
    if (data.packaging_id) {
        const packagingSelect = row.querySelector('.packaging-select');
        packagingSelect.value = String(data.packaging_id);
    }

    refreshNoItemsMsg();
}

function bindRowEvents(row) {
    row.querySelector('.source-kind').addEventListener('change', () => updateRowTypeUI(row));
    row.querySelector('.product-select').addEventListener('change', () => refreshPackagingOptions(row));
    row.querySelector('select[name*="[item_type]"]').addEventListener('change', () => {
        const type = row.querySelector('select[name*="[item_type]"]').value;
        if (type === 'contract') {
            row.querySelector('.source-kind').value = 'meal';
            const pu = row.querySelector('input[name*="[unit_price]"]');
            pu.value = 0;
        }
        updateRowTypeUI(row);
    });
}

function updateRowTypeUI(row) {
    const itemType = row.querySelector('select[name*="[item_type]"]').value;
    const sourceKindSelect = row.querySelector('.source-kind');

    if (itemType === 'contract') {
        sourceKindSelect.value = 'meal';
        sourceKindSelect.disabled = true;
    } else {
        sourceKindSelect.disabled = false;
    }

    const sourceKind = sourceKindSelect.value;
    const mealWrap = row.querySelector('.meal-select-wrap');
    const productWrap = row.querySelector('.product-select-wrap');
    const packagingWrap = row.querySelector('.packaging-select-wrap');

    if (sourceKind === 'meal') {
        mealWrap.classList.remove('hidden');
        productWrap.classList.add('hidden');
        packagingWrap.classList.add('hidden');
        row.querySelector('.product-select').value = '';
        row.querySelector('.packaging-select').innerHTML = '<option value="">Unité de base</option>';
    } else {
        mealWrap.classList.add('hidden');
        productWrap.classList.remove('hidden');
        packagingWrap.classList.remove('hidden');
        row.querySelector('.meal-select').value = '';
        refreshPackagingOptions(row);
    }
}

function refreshPackagingOptions(row) {
    const productId = Number(row.querySelector('.product-select').value || 0);
    const packagingSelect = row.querySelector('.packaging-select');
    packagingSelect.innerHTML = '<option value="">Unité de base</option>';

    if (!productId) return;

    const product = products.find(p => Number(p.id) === productId);
    if (!product || !Array.isArray(product.packagings)) return;

    product.packagings.forEach(pp => {
        const opt = document.createElement('option');
        opt.value = pp.packaging_id;
        opt.textContent = `${pp.packaging_name || 'Emballage'} (x${pp.quantity})`;
        packagingSelect.appendChild(opt);
    });
}

function refreshNoItemsMsg() {
    const rows = document.querySelectorAll('.item-row').length;
    document.getElementById('noItemsMsg').style.display = rows === 0 ? '' : 'none';
}

function handleTerminalInfo() {
    const select = document.getElementById('terminalSelect');
    const opt = select.options[select.selectedIndex];
    const panel = document.getElementById('terminalCashiers');
    if (!select.value) {
        panel.classList.add('hidden');
        return;
    }
    document.getElementById('cashierMorningName').textContent = opt.dataset.morning || '(Non assigné)';
    document.getElementById('cashierEveningName').textContent = opt.dataset.evening || '(Non assigné)';
    panel.classList.remove('hidden');
}

function filterTerminalsByClient(clientId) {
    const select = document.getElementById('terminalSelect');
    Array.from(select.options).forEach((opt, index) => {
        if (index === 0) return;
        const match = !clientId || String(opt.dataset.client || '') === String(clientId);
        opt.hidden = !match;
    });

    if (select.value) {
        const selected = select.options[select.selectedIndex];
        if (selected && selected.hidden) {
            select.value = '';
            document.getElementById('terminalCashiers').classList.add('hidden');
        }
    }
}

function renderProgrammedMeals(programmedMeals) {
    const container = document.getElementById('menuSuggestionContent');
    if (!programmedMeals || programmedMeals.length === 0) {
        suggestedMeals = [];
        document.getElementById('menuSuggestion').classList.add('hidden');
        return;
    }

    suggestedMeals = programmedMeals;
    container.innerHTML = programmedMeals.map(group => {
        const type = group.type || '';
        const badge = type === 'breakfast'
            ? 'bg-yellow-500/20 text-yellow-300'
            : (type === 'lunch' ? 'bg-green-500/20 text-green-300' : 'bg-indigo-500/20 text-indigo-300');

        return `<div class="flex items-start gap-2 py-1">
            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium ${badge}">${escapeHtml(group.type_label || type)}</span>
            <span class="text-slate-200">${(group.items || []).map(i => escapeHtml(i.name)).join(', ') || '—'}</span>
            <span class="text-slate-500 text-xs">(${group.guest_count || '?'} couverts)</span>
        </div>`;
    }).join('');

    document.getElementById('menuSuggestion').classList.remove('hidden');
}

function loadClientContractsAndMeals(keepContract = false) {
    const clientId = document.getElementById('clientSelect').value;
    const date = document.getElementById('transferDate').value;
    const contractSel = document.getElementById('contractSelect');
    const currentContract = contractSel.value;

    filterTerminalsByClient(clientId);

    if (!clientId) {
        contractSel.innerHTML = '<option value="">-- Sélectionner d\'abord un client --</option>';
        renderProgrammedMeals([]);
        return;
    }

    contractSel.innerHTML = '<option value="">Chargement...</option>';

    const params = new URLSearchParams({ client_id: clientId, transfer_date: date || '' });
    if (keepContract && currentContract) {
        params.append('contract_id', currentContract);
    }

    fetch(`{{ route('pos-transfer.api.contracts') }}?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            contractSel.innerHTML = '<option value="">-- Choisir un contrat --</option>';
            (data.contracts || []).forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = `${c.label} (${c.guest_count} couverts)`;
                contractSel.appendChild(opt);
            });

            if (keepContract && currentContract) {
                contractSel.value = currentContract;
            }

            renderProgrammedMeals(data.programmed_meals || []);
        })
        .catch(() => {
            contractSel.innerHTML = '<option value="">-- Erreur de chargement --</option>';
            renderProgrammedMeals([]);
        });
}

function loadProgrammedMealsOnly() {
    const clientId = document.getElementById('clientSelect').value;
    const contractId = document.getElementById('contractSelect').value;
    const date = document.getElementById('transferDate').value;

    if (!clientId) {
        renderProgrammedMeals([]);
        return;
    }

    const params = new URLSearchParams({ client_id: clientId, date: date || '' });
    if (contractId) params.append('contract_id', contractId);

    fetch(`{{ route('pos-transfer.api.meals') }}?${params.toString()}`)
        .then(r => r.json())
        .then(data => renderProgrammedMeals(data.programmed_meals || []))
        .catch(() => renderProgrammedMeals([]));
}

document.getElementById('addMenuItemsBtn').addEventListener('click', () => {
    suggestedMeals.forEach(group => {
        (group.items || []).forEach(item => {
            addRow({
                item_type: 'contract',
                label: `${group.type_label || 'Repas'} — ${item.name}`,
                quantity: group.guest_count || 1,
                unit: 'portion',
                unit_price: group.unit_price || 0,
                meal_id: item.id,
            });
        });
    });

    document.getElementById('menuSuggestion').classList.add('hidden');
});

function escapeHtml(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.getElementById('transferForm').addEventListener('submit', function (e) {
    const rows = document.querySelectorAll('.item-row');
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const type = row.querySelector('select[name*="[item_type]"]')?.value;
        const source = row.querySelector('.source-kind')?.value;
        const mealId = row.querySelector('.meal-select')?.value;
        const productId = row.querySelector('.product-select')?.value;

        if (type === 'contract' && (!mealId || mealId === '')) {
            e.preventDefault();
            alert('Ligne ' + (i + 1) + ': un article contrat doit être lié à un plat programmé.');
            return false;
        }

        if (type === 'extra') {
            if (source === 'meal' && (!mealId || mealId === '')) {
                e.preventDefault();
                alert('Ligne ' + (i + 1) + ': sélectionnez un plat pour cet extra.');
                return false;
            }
            if (source === 'product' && (!productId || productId === '')) {
                e.preventDefault();
                alert('Ligne ' + (i + 1) + ': sélectionnez un produit pour cet extra.');
                return false;
            }
        }
    }
});

refreshNoItemsMsg();

// Précharge contrat/menus si un client est déjà sélectionné
if (document.getElementById('clientSelect').value) {
    loadClientContractsAndMeals(true);
}

if (document.getElementById('terminalSelect').value) {
    handleTerminalInfo();
}

if (@json($allStocks->count()) === 1) {
    const fromStock = document.getElementById('fromStockSelect');
    if (fromStock && !fromStock.value) {
        fromStock.value = fromStock.options[1]?.value || '';
    }
}
</script>
</body>
</html>
