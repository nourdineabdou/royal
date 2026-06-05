@extends('layouts.pos')

@section('title', 'POS Catering - Vente libre')

@section('content')
@if($activeRegister)
<div style="background:linear-gradient(135deg,#d97706,#b45309);color:#fff;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;font-size:13px;grid-column:1 / -1;">
    <div style="display:flex;align-items:center;gap:10px;">
        <i class="fas fa-truck" style="font-size:18px;opacity:.85;"></i>
        <span>
            <strong>Caisse catering ouverte :</strong> {{ $activeRegister->user?->name ?? 'Caissier' }}
            · Session #{{ $activeRegister->id }}
            · {{ $activeRegister->shift === 'morning' ? 'Matin' : 'Soir' }}
            · Ouverture {{ $activeRegister->opened_at?->format('H:i') }}
        </span>
    </div>
    <a href="{{ route('cashier.session', $activeRegister->id) }}" style="background:rgba(255,255,255,.2);color:#fff;padding:6px 14px;border-radius:8px;text-decoration:none;font-size:12px;">
        <i class="fas fa-boxes"></i> Gestion stock catering
    </a>
</div>
@else
<div style="background:#ef4444;color:#fff;padding:10px 20px;font-size:13px;grid-column:1 / -1;">
    <i class="fas fa-exclamation-triangle"></i>
    Aucune caisse catering ouverte. Contactez la comptabilite.
</div>
@endif

<div class="meals-section" style="padding:20px;overflow-y:auto;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <div style="width:40px;height:40px;background:linear-gradient(135deg,#d97706,#b45309);border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-box-open" style="color:#fff;font-size:16px;"></i>
        </div>
        <div>
            <h2 style="margin:0;font-size:18px;font-weight:700;color:#1e293b;">Produits consommables en stock</h2>
            <p style="margin:0;font-size:12px;color:#64748b;">Vente libre uniquement depuis le stock transfere</p>
        </div>
    </div>

    @if($consumableStock->isEmpty())
        <div style="text-align:center;padding:60px 20px;background:#fff;border-radius:16px;border:2px dashed #e2e8f0;">
            <i class="fas fa-box-open" style="font-size:48px;color:#d97706;opacity:.4;margin-bottom:16px;display:block;"></i>
            <h3 style="color:#94a3b8;font-weight:600;margin:0 0 8px;">Aucun article disponible</h3>
            <p style="color:#cbd5e1;font-size:13px;margin:0 0 16px;">La production doit d'abord transferer des produits consommables.</p>
            @if($activeRegister)
            <a href="{{ route('cashier.session', $activeRegister->id) }}" style="display:inline-block;background:#d97706;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-size:13px;">
                Voir les transferts
            </a>
            @endif
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:16px;">
            @foreach($consumableStock as $si)
                @php
                    $available = (float) $si->available_qty;
                    $received  = max(1, (float) $si->quantity_received);
                    $pct       = round(($available / $received) * 100);
                    $barColor  = $pct > 50 ? '#22c55e' : ($pct > 20 ? '#f59e0b' : '#ef4444');
                    $unit      = $si->product?->unit?->name ?? 'unite';
                    $labelLower = strtolower($si->label ?? '');
                @endphp
                <div class="consumable-card"
                     data-stock-item-id="{{ $si->id }}"
                     data-label="{{ $si->label }}"
                     data-price="{{ (float) ($si->product?->sale_price ?? 0) }}"
                     data-available="{{ $available }}"
                     data-unit="{{ $unit }}"
                     onclick="addToCart(this)"
                     style="background:#fff;border-radius:14px;padding:18px;cursor:pointer;border:2px solid transparent;transition:all .2s;box-shadow:0 1px 4px rgba(0,0,0,.06);"
                     onmouseover="this.style.borderColor='#d97706';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 16px rgba(217,119,6,.15)';"
                     onmouseout="this.style.borderColor='transparent';this.style.transform='';this.style.boxShadow='0 1px 4px rgba(0,0,0,.06)';">

                    <div style="width:48px;height:48px;border-radius:12px;margin-bottom:12px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#fef3c7,#fde68a);">
                        @if(str_contains($labelLower,'coca') || str_contains($labelLower,'can'))
                            <i class="fas fa-wine-bottle" style="color:#d97706;font-size:20px;"></i>
                        @elseif(str_contains($labelLower,'eau'))
                            <i class="fas fa-tint" style="color:#3b82f6;font-size:20px;"></i>
                        @elseif(str_contains($labelLower,'jus'))
                            <i class="fas fa-glass-whiskey" style="color:#f59e0b;font-size:20px;"></i>
                        @elseif(str_contains($labelLower,'dessert') || str_contains($labelLower,'creme') || str_contains($labelLower,'yaourt'))
                            <i class="fas fa-ice-cream" style="color:#8b5cf6;font-size:20px;"></i>
                        @else
                            <i class="fas fa-box" style="color:#d97706;font-size:20px;"></i>
                        @endif
                    </div>

                    <div style="font-weight:700;font-size:14px;color:#1e293b;margin-bottom:4px;">{{ $si->label }}</div>
                    <div style="font-size:18px;font-weight:800;color:#d97706;margin-bottom:8px;">
                        {{ number_format((float) ($si->product?->sale_price ?? 0), 0, ',', ' ') }} MRU
                    </div>

                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px;">
                        <span style="color:#64748b;">Disponible :</span>
                        <span class="stock-text" style="font-weight:700;color:{{ $barColor }};">{{ number_format($available, 0, ',', ' ') }} {{ $unit }}</span>
                    </div>
                    <div style="height:4px;background:#f1f5f9;border-radius:2px;overflow:hidden;">
                        <div class="stock-bar" style="height:100%;width:{{ $pct }}%;background:{{ $barColor }};border-radius:2px;"></div>
                    </div>

                    <div style="margin-top:12px;background:linear-gradient(135deg,#d97706,#b45309);color:#fff;padding:8px;border-radius:8px;text-align:center;font-size:12px;font-weight:600;">
                        <i class="fas fa-plus" style="margin-right:4px;"></i> Ajouter
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="cart-section" style="display:flex;flex-direction:column;">
    <div style="padding:16px 20px;background:linear-gradient(135deg,#d97706,#b45309);display:flex;align-items:center;gap:10px;">
        <i class="fas fa-shopping-cart" style="color:#fff;font-size:18px;"></i>
        <span style="color:#fff;font-weight:700;font-size:16px;">Panier catering</span>
        <span id="cartBadge" style="background:rgba(255,255,255,.25);border-radius:20px;padding:2px 8px;font-size:11px;color:#fff;display:none;"></span>
    </div>

    <div style="padding:12px 16px;border-bottom:1px solid #f8fafc;">
        <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px;">REFERENCE CLIENT / TABLE (optionnel)</label>
        <input type="text" id="customerNumber" placeholder="Ex: TABLE-5, Salle A..." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;">

        <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin:10px 0 4px;">TYPE DE PAIEMENT *</label>
        <select id="paymentTypeId" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;background:#fff;">
            <option value="">-- Choisir un type de paiement --</option>
            @foreach($paymentTypes as $pt)
                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
            @endforeach
        </select>
    </div>

    <div id="cartItems" class="cart-items">
        <div id="emptyCartMsg" style="text-align:center;padding:40px 0;color:#94a3b8;">
            <i class="fas fa-cart-arrow-down" style="font-size:32px;margin-bottom:10px;display:block;opacity:.3;"></i>
            <span style="font-size:13px;">Le panier est vide</span>
        </div>
    </div>

    <div class="cart-summary">
        <div class="summary-row"><span>Sous-total :</span><span id="subtotal">0,00 MRU</span></div>
        <div class="summary-row total"><span>Total :</span><span id="total">0,00 MRU</span></div>
    </div>

    <div class="cart-actions">
        <button id="checkoutBtn" onclick="checkout()" disabled class="btn-action btn-checkout" style="background:linear-gradient(135deg,#d97706,#b45309);flex:2;">
            <i class="fas fa-check-circle"></i> <span>Valider</span>
        </button>
        <button onclick="clearCart()" class="btn-action btn-clear">
            <i class="fas fa-trash"></i> <span>Vider</span>
        </button>
        <a href="{{ route('pos.orders') }}" class="btn-action btn-history" style="text-decoration:none;">
            <i class="fas fa-history"></i> <span>Historique</span>
        </a>
    </div>
</div>

<div id="successModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:20px;padding:40px;text-align:center;max-width:380px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.3);">
        <div style="width:80px;height:80px;background:linear-gradient(135deg,#22c55e,#16a34a);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <i class="fas fa-check" style="color:#fff;font-size:32px;"></i>
        </div>
        <h3 style="margin:0 0 6px;font-size:22px;font-weight:800;color:#1e293b;">Vente enregistree !</h3>
        <p id="successMsg" style="color:#64748b;font-size:14px;margin:0 0 24px;"></p>
        <div style="display:flex;gap:10px;">
            <a id="ticketLink" href="#" target="_blank" style="flex:1;background:#1e293b;color:#fff;padding:12px;border-radius:10px;text-decoration:none;font-size:13px;text-align:center;">
                <i class="fas fa-print"></i> Ticket
            </a>
            <button onclick="closeModal()" style="flex:1;background:linear-gradient(135deg,#d97706,#b45309);color:#fff;border:none;padding:12px;border-radius:10px;font-size:13px;cursor:pointer;font-weight:600;">
                <i class="fas fa-plus"></i> Nouvelle vente
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const BASE_URL = '{{ rtrim(url('/'), '/') }}';
const cart = new Map();

function money(v) {
    return Number(v || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MRU';
}

function addToCart(card) {
    const stockItemId = Number(card.dataset.stockItemId);
    const label = card.dataset.label;
    const price = Number(card.dataset.price || 0);
    const available = Number(card.dataset.available || 0);
    const unit = card.dataset.unit || 'unite';

    if (!stockItemId || available <= 0) return;

    const existing = cart.get(stockItemId);
    if (existing) {
        if (existing.quantity + 1 > available) {
            alert('Stock insuffisant pour ' + label);
            return;
        }
        existing.quantity += 1;
        cart.set(stockItemId, existing);
    } else {
        cart.set(stockItemId, { stock_item_id: stockItemId, label, price, quantity: 1, available, unit });
    }

    renderCart();
}

function decreaseItem(stockItemId) {
    const item = cart.get(stockItemId);
    if (!item) return;

    if (item.quantity <= 1) cart.delete(stockItemId);
    else {
        item.quantity -= 1;
        cart.set(stockItemId, item);
    }

    renderCart();
}

function increaseItem(stockItemId) {
    const item = cart.get(stockItemId);
    if (!item) return;

    if (item.quantity + 1 > item.available) {
        alert('Stock insuffisant pour ' + item.label);
        return;
    }

    item.quantity += 1;
    cart.set(stockItemId, item);
    renderCart();
}

function removeItem(stockItemId) {
    cart.delete(stockItemId);
    renderCart();
}

function clearCart() {
    if (!cart.size) return;
    if (!confirm('Vider le panier ?')) return;
    cart.clear();
    renderCart();
}

function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const badge = document.getElementById('cartBadge');

    const items = Array.from(cart.values());
    const itemsCount = items.reduce((s, i) => s + i.quantity, 0);
    const total = items.reduce((s, i) => s + (i.price * i.quantity), 0);

    if (!items.length) {
        cartItems.innerHTML = `
            <div id="emptyCartMsg" style="text-align:center;padding:40px 0;color:#94a3b8;">
                <i class="fas fa-cart-arrow-down" style="font-size:32px;margin-bottom:10px;display:block;opacity:.3;"></i>
                <span style="font-size:13px;">Le panier est vide</span>
            </div>`;
        checkoutBtn.disabled = true;
        badge.style.display = 'none';
        document.getElementById('subtotal').textContent = money(0);
        document.getElementById('total').textContent = money(0);
        return;
    }

    badge.style.display = 'inline-block';
    badge.textContent = itemsCount;
    checkoutBtn.disabled = false;

    cartItems.innerHTML = items.map(item => `
        <div class="cart-item" style="display:flex;justify-content:space-between;gap:10px;padding:10px 12px;border-bottom:1px solid #f1f5f9;">
            <div style="flex:1;min-width:0;">
                <div class="cart-item-name" style="font-weight:700;font-size:13px;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.label}</div>
                <div class="cart-item-qty" style="display:flex;align-items:center;gap:8px;margin-top:6px;">
                    <button onclick="decreaseItem(${item.stock_item_id})" class="qty-btn" style="width:24px;height:24px;border:none;border-radius:6px;background:#f1f5f9;cursor:pointer;">-</button>
                    <span class="qty-display" style="font-size:12px;font-weight:700;min-width:20px;text-align:center;">${item.quantity}</span>
                    <button onclick="increaseItem(${item.stock_item_id})" class="qty-btn" style="width:24px;height:24px;border:none;border-radius:6px;background:#f1f5f9;cursor:pointer;">+</button>
                    <span style="font-size:11px;color:#94a3b8;margin-left:6px;">stock: ${Math.floor(item.available)} ${item.unit}</span>
                </div>
            </div>
            <div style="text-align:right;">
                <div class="cart-item-price" style="font-size:13px;font-weight:700;color:#d97706;">${money(item.price * item.quantity)}</div>
                <button onclick="removeItem(${item.stock_item_id})" class="cart-item-remove" style="margin-top:6px;border:none;background:transparent;color:#ef4444;cursor:pointer;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`).join('');

    document.getElementById('subtotal').textContent = money(total);
    document.getElementById('total').textContent = money(total);
}

async function checkout() {
    const items = Array.from(cart.values());
    const paymentTypeId = document.getElementById('paymentTypeId').value;
    if (!items.length) {
        alert('Le panier est vide');
        return;
    }
    if (!paymentTypeId) {
        alert('Veuillez sélectionner un type de paiement.');
        return;
    }

    const checkoutBtn = document.getElementById('checkoutBtn');
    checkoutBtn.disabled = true;
    checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Traitement...</span>';

    try {
        const response = await fetch(`${BASE_URL}/pos/catering/create-order`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                customer_number: document.getElementById('customerNumber').value,
                payment_type_id: paymentTypeId,
                items: items.map(i => ({ stock_item_id: i.stock_item_id, quantity: i.quantity })),
            }),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            const msg = data.message || 'Erreur lors de la creation de la commande';
            alert(msg);
            return;
        }

        const soldCount = items.reduce((s, i) => s + i.quantity, 0);
        document.getElementById('successMsg').textContent = `Commande #${data.order_id} - ${soldCount} article(s) - ${money(data.total)}`;
        document.getElementById('ticketLink').href = `${BASE_URL}/pos/orders/${data.order_id}/receipt`;
        document.getElementById('successModal').style.display = 'flex';

        cart.clear();
        document.getElementById('customerNumber').value = '';
        renderCart();

        // Recharge pour afficher les nouvelles quantites disponibles
        window.setTimeout(() => window.location.reload(), 250);
    } catch (e) {
        alert('Erreur de connexion. Veuillez reessayer.');
    } finally {
        checkoutBtn.disabled = !cart.size;
        checkoutBtn.innerHTML = '<i class="fas fa-check-circle"></i> <span>Valider</span>';
    }
}

function closeModal() {
    document.getElementById('successModal').style.display = 'none';
}
</script>
@endsection
