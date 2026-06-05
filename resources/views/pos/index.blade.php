@extends('layouts.pos')

@section('content')
<div class="meals-section" style="grid-column:1 / -1;margin-bottom:12px;padding:12px 16px;">
    @if(!empty($activeRegister))
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
            <div style="font-size:13px;color:#1f2937;font-weight:600;">
                <i class="fas fa-cash-register" style="color:#16a34a;margin-right:6px;"></i>
                Caisse ouverte: <strong>{{ $activeRegister->user?->name ?? 'Caissier' }}</strong>
                · Module: <strong>Restaurant</strong>
                · Session #{{ $activeRegister->id }}
                · {{ $activeRegister->shift === 'morning' ? 'Matin' : 'Soir' }}
                · Ouverture {{ $activeRegister->opened_at?->format('H:i') }}
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('pos.orders') }}" style="display:inline-flex;align-items:center;gap:6px;background:#e0f2fe;color:#075985;text-decoration:none;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:700;">
                    <i class="fas fa-list"></i> Commandes
                </a>
                <a href="{{ route('cashier.session', $activeRegister->id) }}" style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#d97706,#b45309);color:#fff;text-decoration:none;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:700;">
                    <i class="fas fa-boxes"></i> Ma session
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#ef4444,#b91c1c);color:#fff;border:none;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </button>
                </form>
            </div>
        </div>
    @else
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
            <div style="font-size:13px;color:#991b1b;font-weight:700;">
                <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
                Aucune caisse restaurant ouverte. Les commandes sont bloquées.
            </div>
            @can('pos.accounting.register.open')
                {{-- <a href="{{ route('pos.accounting') }}" style="text-decoration:none;background:#dc2626;color:white;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:700;">
                    Ouvrir une caisse
                </a> --}}
            @endcan
        </div>
    @endif
</div>

<!-- Meals Section -->
<div class="meals-section">
    <!-- Category Filters -->
    <div>
        <h2 class="text-lg font-bold text-gray-800">CatÃ©gories</h2>
        <div class="category-filters" id="categoryFilters">
            <button class="category-btn active" onclick="filterByCategory('all')">
                <i class="fas fa-th mr-1"></i>Tous les Plats
            </button>
            @foreach($categories as $key => $category)
                <button class="category-btn category-color-{{ $key % 4 }}"
                    onclick="filterByCategory({{ $category->id }})"
                    data-category-id="{{ $category->id }}"
                    data-color-index="{{ $key % 4 }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Products Grid -->
    <div class="meals-grid" id="mealsGrid">
        <!-- Meals will be loaded here via JavaScript -->
    </div>
</div>

<!-- Cart Section -->
<div class="cart-section">
    <div class="cart-title">
        <i class="fas fa-shopping-cart"></i>
        <span>Panier</span>
    </div>

    <!-- Customer Number -->
    <label class="block text-xs font-semibold text-gray-600 mb-2">NumÃ©ro Client (optionnel)</label>
    <input type="text" id="customerNumber" placeholder="Ex: CLIENT-001" class="customer-input">

    <!-- Cart Items -->
    <div id="cartItems" class="cart-items">
        <div class="cart-empty">
            <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 8px; display: block; color: #d1d5db;"></i>
            Le panier est vide
        </div>
    </div>

    <!-- Cart Summary -->
    <div class="cart-summary">
        <div class="summary-row">
            <span>Sous-total:</span>
            <span id="subtotal">0,00 MRU</span>
        </div>
        <div class="summary-row total">
            <span>Total:</span>
            <span id="total">0,00 MRU</span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="cart-actions">
        <button id="checkoutBtn" onclick="checkout()" class="btn-action btn-checkout" disabled>
            <i class="fas fa-check-circle"></i>
            <span>Valider</span>
        </button>
        <button onclick="clearCart()" class="btn-action btn-clear">
            <i class="fas fa-trash"></i>
            <span>Vider</span>
        </button>
        <a href="{{ route('pos.orders') }}" class="btn-action btn-history" style="text-decoration: none;">
            <i class="fas fa-history"></i>
            <span>Historique</span>
        </a>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="success-modal hidden" style="display:none;">
    <div class="modal-content">
        <div class="modal-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 class="modal-title">Commande créée !</h3>
        <p class="modal-message">Votre commande a été enregistrée avec succès</p>

        <div class="modal-details">
            <div class="modal-details-row">
                <span>Commande #</span>
                <span class="modal-details-value" id="successOrderId">-</span>
            </div>
            <div class="modal-details-row">
                <span>Articles</span>
                <span class="modal-details-value" id="successItemCount">-</span>
            </div>
            <div class="modal-details-row">
                <span>Montant total</span>
                <span class="modal-details-value" id="successTotal">0,00 MRU</span>
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:0;">
            <button onclick="closeModal()" class="btn-confirm" style="flex:1;">
                Continuer
            </button>
            <button id="btnKitchenTicket" onclick="printKitchenTicketFromModal()" class="btn-confirm"
                style="flex:1;background:linear-gradient(135deg,#d97706,#b45309);">
                <i class="fas fa-utensils"></i> Bon cuisine
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let cart = [];
let meals = [];
const categoryColors = ['#3b82f6', '#10b981', '#a855f7', '#f97316'];
const BASE_URL = '{{ rtrim(url('/'), '/') }}';

// Charger les plats
async function loadMeals() {
    try {
        const response = await fetch(`${BASE_URL}/pos/meals`);
        meals = await response.json();
        displayMeals('all');
    } catch (error) {
        console.error('Erreur lors du chargement des plats:', error);
    }
}

// Afficher les plats
function filterByCategory(categoryId) {
    displayMeals(categoryId);

    // Mettre Ã  jour les couleurs des boutons
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    if (categoryId === 'all') {
        document.querySelector('button[onclick="filterByCategory(\'all\')"]').classList.add('active');
    } else {
        const activeBtn = document.querySelector(`[data-category-id="${categoryId}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }
}

function displayMeals(categoryId) {
    const grid = document.getElementById('mealsGrid');

    let filteredMeals = meals;
    if (categoryId !== 'all') {
        filteredMeals = meals.filter(meal => meal.category_id == categoryId);
    }

    grid.innerHTML = filteredMeals.map(meal => {
        const categoryColorIndex = (meal.category_id - 1) % 4;
        const colorClass = ['#3b82f6', '#10b981', '#a855f7', '#f97316'][categoryColorIndex];

        return `
            <div class="meal-card">
                <div class="meal-image">
                    ${meal.image_url ? `<img src="${meal.image_url}" alt="${meal.name}">` : `<i class="fas fa-utensils"></i>`}
                    <div class="meal-category-badge" style="background:${colorClass}">
                        ${meal.category?.name || 'N/A'}
                    </div>
                </div>
                <div class="meal-info">
                    <div class="meal-name">${meal.name}</div>
                    <div class="meal-price">${meal.price.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} MRU</div>
                    <button onclick="addToCart(${meal.id})" class="meal-add-btn">
                        <i class="fas fa-plus"></i>Ajouter
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Ajouter au panier
function addToCart(mealId) {
    const meal = meals.find(m => m.id == mealId);
    if (!meal) {
        console.error('Plat introuvable id=', mealId, 'meals=', meals.length);
        return;
    }
    const cartItem = cart.find(item => item.id == mealId);
    if (cartItem) {
        cartItem.quantity++;
    } else {
        cart.push({ ...meal, quantity: 1 });
    }
    updateCart();
}

// Retirer du panier
function removeFromCart(mealId) {
    cart = cart.filter(item => item.id !== mealId);
    updateCart();
}

// Diminuer quantitÃ©
function decreaseQuantity(mealId) {
    const item = cart.find(m => m.id === mealId);
    if (item) {
        if (item.quantity > 1) {
            item.quantity--;
        } else {
            removeFromCart(mealId);
        }
    }
    updateCart();
}

// Augmenter quantitÃ©
function increaseQuantity(mealId) {
    const item = cart.find(m => m.id === mealId);
    if (item) {
        item.quantity++;
    }
    updateCart();
}

// Mettre Ã  jour le panier
function updateCart() {
    const cartItemsDiv = document.getElementById('cartItems');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const totalAmount = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    if (cart.length === 0) {
        cartItemsDiv.innerHTML = `
            <div class="cart-empty">
                <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 8px; display: block; color: #d1d5db;"></i>
                Le panier est vide
            </div>
        `;
        checkoutBtn.disabled = true;
        document.getElementById('total').textContent = '0,00 â‚¬';
        document.getElementById('subtotal').textContent = '0,00 â‚¬';
        return;
    }

    checkoutBtn.disabled = false;

    cartItemsDiv.innerHTML = cart.map(item => `
        <div class="cart-item">
            <div style="flex: 1;">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-qty">
                    <button onclick="decreaseQuantity(${item.id})" class="qty-btn">âˆ’</button>
                    <span class="qty-display">${item.quantity}</span>
                    <button onclick="increaseQuantity(${item.id})" class="qty-btn">+</button>
                </div>
            </div>
            <div style="text-align: right;">
                <div class="cart-item-price">${(item.price * item.quantity).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} MRU</div>
                <button onclick="removeFromCart(${item.id})" class="cart-item-remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');

    const formattedTotal = totalAmount.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('subtotal').textContent = formattedTotal + ' MRU';
    document.getElementById('total').textContent = formattedTotal + ' MRU';
}

// Vider le panier
function clearCart() {
    if (confirm('ÃŠtes-vous sÃ»r de vouloir vider le panier?')) {
        cart = [];
        updateCart();
    }
}

// Valider la commande
async function checkout() {
    if (cart.length === 0) {
        alert('Le panier est vide!');
        return;
    }

    const totalAmount = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const checkoutBtn = document.getElementById('checkoutBtn');
    checkoutBtn.disabled = true;
    checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Traitement...</span>';

    try {
        const response = await fetch(`${BASE_URL}/pos/create-order`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                items: cart.map(item => ({
                    meal_id:  item.id,
                    quantity: item.quantity,
                    price:    item.price,
                })),
                customer_number: document.getElementById('customerNumber').value,
                total_amount: totalAmount
            })
        });

        const data = await response.json();

        if (data.success) {
            const finalTotal = data.total ?? totalAmount;
            document.getElementById('successOrderId').textContent   = '#' + data.order_id;
            document.getElementById('successItemCount').textContent = cart.length;
            document.getElementById('successTotal').textContent     = finalTotal.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' MRU';
            document.getElementById('successModal').classList.remove('hidden');
            document.getElementById('btnKitchenTicket').dataset.orderId = data.order_id;
            playOrderSound();

            cart = [];
            document.getElementById('customerNumber').value = '';
            updateCart();
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message || 'Erreur inconnue');
            alert('Erreur : ' + msg);
        }
    } catch (error) {
        console.error('Erreur checkout:', error);
        alert('Erreur de connexion. Veuillez réessayer.');
    } finally {
        checkoutBtn.disabled = (cart.length === 0);
        checkoutBtn.innerHTML = '<i class="fas fa-check-circle"></i> <span>Valider</span>';
    }
}

function closeModal() {
    document.getElementById('successModal').classList.add('hidden');
}

function printKitchenTicketFromModal() {
    const orderId = document.getElementById('btnKitchenTicket').dataset.orderId;
    if (!orderId) return;
    window.open(`{{ url('pos/orders') }}/${orderId}/kitchen-ticket`, '_blank', 'width=340,height=520,scrollbars=yes');
}

// ── Son de notification (Web Audio API) ──────────────────────────
function playOrderSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const o1 = ctx.createOscillator(), g1 = ctx.createGain();
        o1.connect(g1); g1.connect(ctx.destination);
        o1.type = 'sine'; o1.frequency.value = 1400;
        g1.gain.setValueAtTime(0.5, ctx.currentTime);
        g1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.18);
        o1.start(ctx.currentTime); o1.stop(ctx.currentTime + 0.18);
        const o2 = ctx.createOscillator(), g2 = ctx.createGain();
        o2.connect(g2); g2.connect(ctx.destination);
        o2.type = 'sine'; o2.frequency.value = 1050;
        g2.gain.setValueAtTime(0.4, ctx.currentTime + 0.22);
        g2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.48);
        o2.start(ctx.currentTime + 0.22); o2.stop(ctx.currentTime + 0.48);
    } catch(e) {}
}

// Initialiser
document.addEventListener('DOMContentLoaded', function() {
    loadMeals();
});
</script>
@endsection

