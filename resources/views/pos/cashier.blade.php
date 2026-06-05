@extends('layouts.pos')

@section('title', 'Caisse — Complex Royal')

@section('content')
<style>
    .cashier-content { grid-column: 1 / -1; }

    .cashier-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }

    .cashier-title { font-size: 20px; font-weight: 700; color: #1f2937; }

    .register-badge {
        display: flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600;
        padding: 6px 14px; border-radius: 20px;
    }
    .register-open  { background: #dcfce7; color: #166534; }
    .register-none  { background: #fee2e2; color: #991b1b; }

    .stats-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
        text-align: center;
    }
    .stat-value { font-size: 22px; font-weight: 700; }
    .stat-label { font-size: 11px; color: #6b7280; margin-top: 2px; }
    .stat-pending { color: #92400e; }
    .stat-sent    { color: #1e40af; }
    .stat-paid    { color: #166534; }

    .tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 14px;
        background: #f3f4f6;
        padding: 4px;
        border-radius: 10px;
        width: fit-content;
    }
    .tab-btn {
        padding: 7px 18px;
        border-radius: 7px;
        border: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        color: #6b7280;
        background: transparent;
        transition: all .2s;
        position: relative;
    }
    .tab-btn.active { background: white; color: #667eea; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
    .tab-badge {
        position: absolute; top: 3px; right: 3px;
        background: #ef4444; color: white;
        font-size: 9px; font-weight: 700;
        width: 15px; height: 15px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }

    .orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 14px;
    }

    .order-card {
        background: white;
        border-radius: 14px;
        border: 2px solid #f3f4f6;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.07);
        display: flex;
        flex-direction: column;
        transition: all .25s;
    }
    .order-card:hover { box-shadow: 0 6px 18px rgba(102,126,234,.15); border-color: #c7d2fe; }
    .order-card.status-sent    { border-color: #93c5fd; }
    .order-card.status-pending { border-color: #fcd34d; }
    .order-card.status-paid    { border-color: #86efac; opacity: .75; }

    .order-card-header {
        padding: 12px 14px 10px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .order-number { font-size: 17px; font-weight: 700; color: #1f2937; }
    .order-time   { font-size: 11px; color: #9ca3af; margin-top: 2px; }

    .status-pill {
        font-size: 10px; font-weight: 700;
        padding: 3px 9px; border-radius: 20px;
        white-space: nowrap;
    }
    .pill-pending  { background: #fef3c7; color: #92400e; }
    .pill-sent     { background: #dbeafe; color: #1e40af; }
    .pill-paid     { background: #dcfce7; color: #166534; }
    .pill-cancelled{ background: #fee2e2; color: #991b1b; }

    .order-items {
        padding: 0 14px 10px;
        font-size: 12px;
        color: #4b5563;
        flex: 1;
        border-top: 1px solid #f3f4f6;
        padding-top: 10px;
    }
    .order-item-row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
        border-bottom: 1px dashed #f3f4f6;
    }
    .order-item-row:last-child { border-bottom: none; }

    .order-card-footer {
        padding: 10px 14px;
        background: #f9fafb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .order-total { font-size: 16px; font-weight: 700; color: #667eea; }

    .btn-pay {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white; border: none;
        padding: 8px 16px; border-radius: 8px;
        font-size: 12px; font-weight: 700;
        cursor: pointer; transition: all .2s;
        display: flex; align-items: center; gap: 5px;
    }
    .btn-pay:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,.3); }
    .btn-paid-label {
        color: #16a34a; font-size: 12px; font-weight: 600;
        display: flex; align-items: center; gap: 4px;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    .empty-state i { font-size: 48px; display: block; margin-bottom: 12px; }

    /* ── Modal paiement ── */
    .pay-modal-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,.55);
        display: flex; align-items: center; justify-content: center;
        z-index: 100; padding: 16px;
    }
    .pay-modal {
        background: white; border-radius: 18px;
        width: 100%; max-width: 420px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.25);
    }
    .pay-modal-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 18px 20px;
        color: white;
        display: flex; justify-content: space-between; align-items: center;
    }
    .pay-modal-title { font-size: 17px; font-weight: 700; }
    .btn-close-modal {
        background: rgba(255,255,255,.2); border: none;
        color: white; width: 28px; height: 28px;
        border-radius: 50%; cursor: pointer; font-size: 14px;
        display: flex; align-items: center; justify-content: center;
        transition: background .2s;
    }
    .btn-close-modal:hover { background: rgba(255,255,255,.35); }

    .pay-modal-body { padding: 18px 20px; }

    .modal-order-summary {
        background: #f9fafb; border-radius: 10px; padding: 12px 14px;
        margin-bottom: 16px; font-size: 13px;
    }
    .modal-order-row {
        display: flex; justify-content: space-between;
        padding: 4px 0; color: #374151;
    }
    .modal-order-row.total-row {
        border-top: 1px solid #e5e7eb; margin-top: 6px; padding-top: 8px;
        font-weight: 700; color: #667eea; font-size: 16px;
    }

    .payment-types {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 8px;
        margin-bottom: 16px;
    }
    .payment-type-btn {
        border: 2px solid #e5e7eb; background: white;
        border-radius: 10px; padding: 10px 8px;
        cursor: pointer; text-align: center;
        font-size: 11px; font-weight: 600; color: #374151;
        transition: all .2s;
    }
    .payment-type-btn i { display: block; font-size: 20px; margin-bottom: 5px; color: #9ca3af; }
    .payment-type-btn.selected {
        border-color: #667eea; background: #eef2ff; color: #667eea;
    }
    .payment-type-btn.selected i { color: #667eea; }

    .cash-input-group { margin-bottom: 16px; }
    .cash-input-group label { font-size: 12px; font-weight: 600; color: #374151; display: block; margin-bottom: 6px; }
    .cash-input {
        width: 100%; padding: 10px 12px;
        border: 2px solid #e5e7eb; border-radius: 8px;
        font-size: 15px; font-weight: 600;
        transition: border-color .2s;
    }
    .cash-input:focus { outline: none; border-color: #667eea; }
    .change-display {
        background: #f0fdf4; border: 1px solid #86efac;
        border-radius: 8px; padding: 10px 14px;
        font-size: 14px; font-weight: 700; color: #166534;
        text-align: center; margin-bottom: 16px;
        display: none;
    }

    .btn-confirm-pay {
        width: 100%;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white; border: none;
        padding: 13px; border-radius: 10px;
        font-size: 15px; font-weight: 700;
        cursor: pointer; transition: all .2s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-confirm-pay:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,.35); }
    .btn-confirm-pay:disabled { opacity: .6; cursor: not-allowed; transform: none; box-shadow: none; }

    /* ── Alerte nouvelle commande ───────────────────── */
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(60px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .new-order-alert {
        position: fixed; top: 80px; right: 24px; z-index: 300;
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        color: white; padding: 14px 18px;
        border-radius: 14px; max-width: 300px; cursor: pointer;
        box-shadow: 0 8px 32px rgba(59,130,246,.45);
        animation: slideInRight .35s ease;
        border-left: 4px solid #93c5fd;
    }
    .new-order-alert .alert-title {
        font-size: 14px; font-weight: 700; margin-bottom: 4px;
        display: flex; align-items: center; gap: 6px;
    }
    .new-order-alert .alert-body { font-size: 12px; opacity: .88; line-height: 1.4; }
</style>

<div class="cashier-content">
    <!-- En-tête page -->
    <div class="cashier-header">
        <div>
            <div class="cashier-title"><i class="fas fa-cash-register" style="color:#667eea;margin-right:8px;"></i>Espace Caissier</div>
            <div style="font-size:12px;color:#6b7280;margin-top:3px;">Mise à jour automatique toutes les 5 s</div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            @if($cashRegister)
                <div class="register-badge register-open">
                    <i class="fas fa-circle" style="font-size:8px;"></i>
                    Caisse ouverte · Ouverture {{ $cashRegister->opened_at->format('H:i') }}
                </div>
            @else
                <div class="register-badge register-none">
                    <i class="fas fa-exclamation-circle"></i> Aucune caisse ouverte
                </div>
            @endif
            @can('pos.orders.view')
            <a href="{{ route('pos.orders') }}" class="btn-pay" style="background:linear-gradient(135deg,#0ea5e9,#0369a1);text-decoration:none;">
                <i class="fas fa-list"></i> Commandes
            </a>
            @endcan
            @if($cashRegister)
            <a href="{{ route('cashier.session', $cashRegister->id) }}" class="btn-pay" style="background:linear-gradient(135deg,#d97706,#b45309);text-decoration:none;">
                <i class="fas fa-boxes"></i> Ma session
            </a>
            @endif
            <a href="{{ route('modules.pos') }}" class="btn-pay" style="background:linear-gradient(135deg,#667eea,#764ba2);text-decoration:none;">
                <i class="fas fa-utensils"></i> Prise de commande
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn-pay" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </button>
            </form>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="stats-bar" id="statsBar">
        <div class="stat-card"><div class="stat-value stat-sent" id="statSent">—</div><div class="stat-label">Prêts à encaisser</div></div>
        <div class="stat-card"><div class="stat-value stat-pending" id="statPending">—</div><div class="stat-label">En préparation</div></div>
        <div class="stat-card"><div class="stat-value stat-paid" id="statPaid">—</div><div class="stat-label">Encaissés aujourd'hui</div></div>
        <div class="stat-card"><div class="stat-value" style="color:#374151;" id="statRevenue">—</div><div class="stat-label">CA aujourd'hui (MRU)</div></div>
    </div>

    <!-- Onglets -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('sent')" id="tab-sent">
            <i class="fas fa-bell mr-1"></i>À encaisser
            <span class="tab-badge" id="badge-sent" style="display:none;"></span>
        </button>
        <button class="tab-btn" onclick="switchTab('pending')" id="tab-pending">
            <i class="fas fa-clock mr-1"></i>En préparation
        </button>
        <button class="tab-btn" onclick="switchTab('paid')" id="tab-paid">
            <i class="fas fa-check-circle mr-1"></i>Encaissées
        </button>
    </div>

    <!-- Grille des commandes -->
    <div class="orders-grid" id="ordersGrid">
        <div class="empty-state"><i class="fas fa-spinner fa-spin"></i>Chargement…</div>
    </div>
</div>

<!-- ── Modal paiement ── -->
<div class="pay-modal-overlay" id="payModal" style="display:none;">
    <div class="pay-modal">
        <div class="pay-modal-header">
            <div class="pay-modal-title"><i class="fas fa-cash-register mr-2"></i>Encaisser la commande</div>
            <button class="btn-close-modal" onclick="closePayModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="pay-modal-body">
            <!-- Récapitulatif -->
            <div class="modal-order-summary" id="modalSummary">
                <div style="font-weight:700;font-size:13px;color:#1f2937;margin-bottom:8px;" id="modalOrderTitle"></div>
                <div id="modalItemsList"></div>
                <div class="modal-order-row total-row">
                    <span>Total à payer</span>
                    <span id="modalTotal"></span>
                </div>
            </div>

            <!-- Modes de paiement -->
            <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:8px;">Mode de paiement</div>
            <div class="payment-types" id="paymentTypesGrid">
                @foreach($paymentTypes as $pt)
                    <button class="payment-type-btn" onclick="selectPaymentType({{ $pt->id }}, this)"
                            data-id="{{ $pt->id }}">
                        <i class="{{ match(true) {
                            str_contains(strtolower($pt->name), 'espèce') || str_contains(strtolower($pt->name), 'espece') => 'fas fa-money-bill-wave',
                            str_contains(strtolower($pt->name), 'carte')  => 'fas fa-credit-card',
                            str_contains(strtolower($pt->name), 'mobile') => 'fas fa-mobile-alt',
                            str_contains(strtolower($pt->name), 'virement') => 'fas fa-university',
                            str_contains(strtolower($pt->name), 'chèque') || str_contains(strtolower($pt->name), 'cheque') => 'fas fa-file-invoice',
                            default => 'fas fa-coins'
                        } }}"></i>
                        {{ $pt->name }}
                    </button>
                @endforeach
            </div>

            <!-- Montant reçu (pour espèces) -->
            <div class="cash-input-group" id="cashInputGroup" style="display:none;">
                <label><i class="fas fa-hand-holding-usd mr-1"></i>Montant reçu (MRU)</label>
                <input type="number" id="cashReceived" class="cash-input" placeholder="0.00" min="0" step="0.01"
                       oninput="computeChange()">
            </div>
            <div class="change-display" id="changeDisplay">
                <i class="fas fa-coins mr-1"></i>Monnaie à rendre : <span id="changeAmount"></span> MRU
            </div>

            <button class="btn-confirm-pay" id="confirmPayBtn" onclick="confirmPayment()" disabled>
                <i class="fas fa-check-circle"></i> Confirmer l'encaissement
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const BASE_URL = '{{ rtrim(url('/'), '/') }}';
let allOrders     = [];
let knownOrderIds = new Set();
let isFirstLoad   = true;
let currentTab   = 'sent';
let currentOrder = null;
let selectedPayTypeId = null;
let pollingTimer = null;

const POLL_INTERVAL = 5000;

// ── Son de notification (Web Audio API — aucun fichier requis) ──
function playOrderSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        // Bip #1 — aigu court
        const o1 = ctx.createOscillator(), g1 = ctx.createGain();
        o1.connect(g1); g1.connect(ctx.destination);
        o1.type = 'sine'; o1.frequency.value = 1400;
        g1.gain.setValueAtTime(0.5, ctx.currentTime);
        g1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.18);
        o1.start(ctx.currentTime); o1.stop(ctx.currentTime + 0.18);
        // Bip #2 — médium décalé
        const o2 = ctx.createOscillator(), g2 = ctx.createGain();
        o2.connect(g2); g2.connect(ctx.destination);
        o2.type = 'sine'; o2.frequency.value = 1050;
        g2.gain.setValueAtTime(0.4, ctx.currentTime + 0.22);
        g2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.48);
        o2.start(ctx.currentTime + 0.22); o2.stop(ctx.currentTime + 0.48);
    } catch(e) { /* AudioContext non supporté */ }
}

// ── Alerte visuelle nouvelle commande ──────────────────────────
function showNewOrderAlert(order) {
    const label = order.customer_number ? 'Table ' + order.customer_number : 'Commande #' + order.id;
    const div   = document.createElement('div');
    div.className = 'new-order-alert';
    div.innerHTML = `
        <div class="alert-title"><i class="fas fa-bell"></i> Nouvelle commande !</div>
        <div class="alert-body">
            <strong>${label}</strong><br>
            ${order.items_summary || ''}
        </div>`;
    div.onclick = () => { switchTab('pending'); div.remove(); };
    document.body.appendChild(div);
    setTimeout(() => div && div.remove(), 7000);
}

// ── Chargement + détection nouvelles commandes ─────────────────
async function loadOrders() {
    try {
        const res = await fetch('{{ route("pos.pending-orders") }}');
        const freshOrders = await res.json();

        if (!isFirstLoad) {
            const newOrders = freshOrders.filter(o =>
                o.status !== 'paid' && !knownOrderIds.has(o.id)
            );
            if (newOrders.length > 0) {
                playOrderSound();
                newOrders.forEach(o => showNewOrderAlert(o));
                // Basculer sur l'onglet "En préparation" si on n'est pas sur "Prêts"
                if (currentTab === 'paid') switchTab('pending');
            }
        }

        knownOrderIds = new Set(freshOrders.map(o => o.id));
        isFirstLoad   = false;

        allOrders = freshOrders;
        updateStats();
        renderOrders();
        schedulePoll();
    } catch (e) {
        console.error('Erreur polling:', e);
        schedulePoll();
    }
}

function schedulePoll() {
    clearTimeout(pollingTimer);
    pollingTimer = setTimeout(loadOrders, POLL_INTERVAL);
}

// ── Stats ────────────────────────────────────────────────────────
function updateStats() {
    const sent    = allOrders.filter(o => o.status === 'sent').length;
    const pending = allOrders.filter(o => o.status === 'pending').length;
    const paid    = allOrders.filter(o => o.status === 'paid').length;
    const revenue = allOrders.filter(o => o.status === 'paid')
                             .reduce((s, o) => s + parseFloat(o.total_amount), 0);

    document.getElementById('statSent').textContent    = sent;
    document.getElementById('statPending').textContent = pending;
    document.getElementById('statPaid').textContent    = paid;
    document.getElementById('statRevenue').textContent = revenue.toLocaleString('fr-FR', {minimumFractionDigits: 0});

    const badge = document.getElementById('badge-sent');
    if (sent > 0) {
        badge.textContent = sent;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

// ── Onglets ──────────────────────────────────────────────────────
function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    renderOrders();
}

// ── Rendu des cartes ─────────────────────────────────────────────
function renderOrders() {
    const grid = document.getElementById('ordersGrid');
    const filtered = allOrders.filter(o => o.status === currentTab);

    if (filtered.length === 0) {
        const msgs = {
            sent:    '<i class="far fa-bell-slash"></i>Aucune commande prête à encaisser',
            pending: '<i class="fas fa-hourglass-half"></i>Aucune commande en préparation',
            paid:    '<i class="fas fa-check-circle"></i>Aucune commande encaissée aujourd\'hui',
        };
        grid.innerHTML = `<div class="empty-state">${msgs[currentTab]}</div>`;
        return;
    }

    grid.innerHTML = filtered.map(order => {
        const pillClass = { sent: 'pill-sent', pending: 'pill-pending', paid: 'pill-paid' }[order.status] || '';
        const pillLabel = { sent: 'Prêt', pending: 'En préparation', paid: 'Encaissé' }[order.status] || order.status;

        const cashInParam = order.cash_in > 0 ? `?received=${order.cash_in}` : '';
        const footerAction = order.status === 'paid'
            ? `<span class="btn-paid-label"><i class="fas fa-check-circle"></i>${order.paid_at ?? ''}</span>
               <button class="btn-pay" style="background:linear-gradient(135deg,#0891b2,#0e7490);margin-left:6px;padding:7px 12px;font-size:11px;"
                   onclick="window.open('{{ url('pos/orders') }}/' + ${order.id} + '/receipt' + '${cashInParam}', '_blank', 'width=380,height=600,scrollbars=yes')">
                   <i class="fas fa-receipt"></i> Ticket
               </button>`
            : `<button class="btn-pay" onclick="openPayModal(${order.id})">
                 <i class="fas fa-cash-register"></i> Encaisser
               </button>
               <button class="btn-pay" style="background:linear-gradient(135deg,#d97706,#b45309);margin-left:6px;padding:7px 12px;font-size:11px;"
                   onclick="window.open('{{ url('pos/orders') }}/' + ${order.id} + '/kitchen-ticket', '_blank', 'width=340,height=520,scrollbars=yes')">
                   <i class="fas fa-utensils"></i> Cuisine
               </button>`;

        return `
        <div class="order-card status-${order.status}">
            <div class="order-card-header">
                <div>
                    <div class="order-number">${order.customer_number ? 'Table ' + order.customer_number : 'Commande #' + order.id}</div>
                    <div class="order-time"><i class="fas fa-clock" style="margin-right:3px;"></i>${order.created_at}</div>
                </div>
                <div>
                    <span class="status-pill ${pillClass}">${pillLabel}</span>
                    ${order.is_prepared ? '<div style="font-size:10px;color:#16a34a;margin-top:4px;"><i class="fas fa-check mr-1"></i>Prêt en cuisine</div>' : ''}
                </div>
            </div>
            <div class="order-items">
                ${order.items_summary
                    ? order.items_summary.split(', ').map(item =>
                        `<div class="order-item-row"><span>${item}</span></div>`).join('')
                    : '<span style="color:#9ca3af;font-style:italic;">Aucun article</span>'}
            </div>
            <div class="order-card-footer">
                <div class="order-total">${parseFloat(order.total_amount).toLocaleString('fr-FR', {minimumFractionDigits: 2})} MRU</div>
                ${footerAction}
            </div>
        </div>`;
    }).join('');
}

// ── Modal paiement ───────────────────────────────────────────────
function openPayModal(orderId) {
    currentOrder = allOrders.find(o => o.id === orderId);
    if (!currentOrder) return;

    selectedPayTypeId = null;
    document.querySelectorAll('.payment-type-btn').forEach(b => b.classList.remove('selected'));
    document.getElementById('cashInputGroup').style.display  = 'none';
    document.getElementById('changeDisplay').style.display   = 'none';
    document.getElementById('cashReceived').value = '';
    document.getElementById('confirmPayBtn').disabled = true;

    document.getElementById('modalOrderTitle').textContent =
        (currentOrder.customer_number ? 'Table ' + currentOrder.customer_number : 'Commande #' + currentOrder.id)
        + ' · ' + currentOrder.items_count + ' article(s)';

    document.getElementById('modalItemsList').innerHTML = currentOrder.items_summary
        ? currentOrder.items_summary.split(', ').map(i =>
            `<div class="modal-order-row"><span>${i}</span></div>`).join('')
        : '';

    document.getElementById('modalTotal').textContent =
        parseFloat(currentOrder.total_amount).toLocaleString('fr-FR', {minimumFractionDigits: 2}) + ' MRU';

    document.getElementById('payModal').style.display = 'flex';
}

function closePayModal() {
    document.getElementById('payModal').style.display = 'none';
    currentOrder = null;
}

function selectPaymentType(typeId, btn) {
    selectedPayTypeId = typeId;
    document.querySelectorAll('.payment-type-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');

    const isCash = btn.textContent.toLowerCase().includes('espèce')
                || btn.textContent.toLowerCase().includes('espece');
    document.getElementById('cashInputGroup').style.display = isCash ? 'block' : 'none';
    document.getElementById('changeDisplay').style.display  = 'none';

    if (!isCash) {
        document.getElementById('confirmPayBtn').disabled = false;
    } else {
        document.getElementById('cashReceived').value = '';
        document.getElementById('confirmPayBtn').disabled = true;
    }
}

function computeChange() {
    if (!currentOrder) return;
    const received = parseFloat(document.getElementById('cashReceived').value) || 0;
    const total    = parseFloat(currentOrder.total_amount);
    const change   = received - total;

    const changeDiv = document.getElementById('changeDisplay');
    const confirmBtn = document.getElementById('confirmPayBtn');

    if (received >= total) {
        document.getElementById('changeAmount').textContent =
            change.toLocaleString('fr-FR', {minimumFractionDigits: 2});
        changeDiv.style.display  = 'block';
        confirmBtn.disabled = false;
    } else {
        changeDiv.style.display  = 'none';
        confirmBtn.disabled = true;
    }
}

async function confirmPayment() {
    if (!currentOrder || !selectedPayTypeId) return;

    const btn = document.getElementById('confirmPayBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement…';

    try {
        const res = await fetch(`${BASE_URL}/pos/orders/${currentOrder.id}/payment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                payment_type_id: selectedPayTypeId,
                amount: parseFloat(document.getElementById('cashReceived').value) || currentOrder.total_amount,
            }),
        });

        const data = await res.json();

        if (data.success) {
            // Monnaie reçue (pour la monnaie rendue sur le ticket)
            const cashIn = parseFloat(document.getElementById('cashReceived').value) || 0;

            closePayModal();
            // Mise à jour locale immédiate sans attendre le polling
            const idx = allOrders.findIndex(o => o.id === data.order_id);
            if (idx !== -1) {
                allOrders[idx].status   = 'paid';
                allOrders[idx].paid_at  = new Date().toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
                allOrders[idx].cash_in  = cashIn;
            }
            updateStats();
            renderOrders();
            showToast(`✅ Commande #${data.order_id} encaissée — ${parseFloat(data.total).toLocaleString('fr-FR', {minimumFractionDigits:2})} MRU`);

            // Ouvrir le ticket dans une nouvelle fenêtre
            const receiptUrl = `{{ url('pos/orders') }}/${data.order_id}/receipt`
                + (cashIn > 0 ? `?received=${cashIn}` : '');
            window.open(receiptUrl, '_blank', 'width=380,height=600,scrollbars=yes');
        } else {
            alert('Erreur : ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmer l\'encaissement';
        }
    } catch (e) {
        console.error(e);
        alert('Erreur de connexion.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmer l\'encaissement';
    }
}

// ── Toast notification ───────────────────────────────────────────
function showToast(msg) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position:fixed; bottom:24px; right:24px; z-index:200;
        background:#1f2937; color:white; padding:12px 20px;
        border-radius:10px; font-size:13px; font-weight:600;
        box-shadow:0 8px 24px rgba(0,0,0,.25);
        animation: slideInUp .3s ease;
    `;
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// ── Fermer modal en cliquant sur l'overlay ───────────────────────
document.getElementById('payModal').addEventListener('click', function(e) {
    if (e.target === this) { this.style.display = 'none'; currentOrder = null; }
});

// ── Init ─────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', loadOrders);
</script>
@endsection
