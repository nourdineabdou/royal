@extends('layouts.pos')

@section('content')
<style>
    .detail-content {
        grid-column: 1 / -1;
    }

    .section-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        color: #667eea;
        font-size: 18px;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }

    .order-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
    }

    .order-date {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }

    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-sent { background: #dbeafe; color: #1e40af; }
    .status-paid { background: #dcfce7; color: #166534; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    .order-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .info-box {
        background: #f9fafb;
        padding: 12px;
        border-radius: 8px;
    }

    .info-label {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    .items-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
    }

    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
    }

    .item-name {
        font-weight: 600;
        color: #1f2937;
    }

    .category-tag {
        display: inline-block;
        background: #f3f4f6;
        color: #667eea;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        margin-top: 4px;
    }

    .item-amount {
        text-align: right;
        font-weight: 600;
        color: #667eea;
    }

    .deduction-section {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-left: 4px solid #3b82f6;
    }

    .deduction-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 6px;
        margin-bottom: 8px;
        font-size: 13px;
    }

    .deduction-item:last-child {
        margin-bottom: 0;
    }

    .deduction-product {
        color: #1f2937;
        font-weight: 500;
    }

    .deduction-quantity {
        color: #1e40af;
        font-weight: 600;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .btn-action {
        padding: 12px 16px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: white;
        text-decoration: none;
    }

    .btn-paid {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .btn-paid:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
    }

    .btn-cancel {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .btn-cancel:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
    }

    .btn-back {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }

    .btn-back:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(107, 114, 128, 0.3);
    }

    .btn-disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .summary-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
    }

    .summary-label {
        font-size: 12px;
        opacity: 0.9;
        margin-bottom: 4px;
    }

    .summary-value {
        font-size: 24px;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .order-header {
            flex-direction: column;
        }

        .items-table {
            font-size: 12px;
        }

        .items-table th, .items-table td {
            padding: 8px;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="detail-content">
    <!-- Order Header -->
    <div class="section-card">
        <div class="order-header">
            <div>
                <div class="order-title">Commande #{{ $order->id }}</div>
                <div class="order-date">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
            </div>
            <span class="status-badge status-{{ $order->status }}">
                @if($order->status === 'pending') En attente
                @elseif($order->status === 'sent') Envoyée
                @elseif($order->status === 'paid') Payée
                @else Annulée
                @endif
            </span>
        </div>

        <!-- Order Info -->
        <div class="order-info-grid">
            <div class="info-box">
                <div class="info-label">Serveur</div>
                <div class="info-value">{{ $order->server->name ?? '-' }}</div>
            </div>
            <div class="info-box">
                <div class="info-label">Caissier</div>
                <div class="info-value">{{ $order->cashier->name ?? '-' }}</div>
            </div>
            <div class="info-box">
                <div class="info-label">Client</div>
                <div class="info-value">{{ $order->customer_number ?? '-' }}</div>
            </div>
            <div class="summary-box">
                <div class="summary-label">Total</div>
                <div class="summary-value">{{ number_format($order->total_amount, 2, ',', '') }} MRU</div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="section-card">
        <h3 class="section-title">
            <i class="fas fa-shopping-bag"></i>
            <span>Articles Commandés</span>
        </h3>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th style="width: 60px; text-align: center;">Qty</th>
                    <th style="width: 80px; text-align: right;">P.U.</th>
                    <th style="width: 80px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->meal->name }}</div>
                            <div class="category-tag">{{ $item->meal->category->name }}</div>
                        </td>
                        <td style="text-align: center; font-weight: 600;">{{ $item->quantity }}</td>
                        <td class="item-amount">{{ number_format($item->price, 2, ',', '') }} MRU</td>
                        <td class="item-amount">{{ number_format($item->price * $item->quantity, 2, ',', '') }} MRU</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Stock Deduction Summary -->
    <div class="section-card deduction-section">
        <h3 class="section-title" style="color: #1e40af;">
            <i class="fas fa-box" style="color: #3b82f6;"></i>
            <span>Déduction de Stock</span>
        </h3>

        <p style="font-size: 12px; color: #1e40af; margin-bottom: 12px;">
            Le stock a été automatiquement ajusté en fonction des recettes des plats commandés.
        </p>

        @php
            $stockDeductions = [];
            foreach($order->items as $item) {
                if($item->meal->recipe) {
                    foreach($item->meal->recipe->items as $recipeItem) {
                        $key = $recipeItem->product->name;
                        if(!isset($stockDeductions[$key])) {
                            $stockDeductions[$key] = 0;
                        }
                        $stockDeductions[$key] += $recipeItem->quantity * $item->quantity;
                    }
                }
            }
        @endphp

        @if(count($stockDeductions) > 0)
            <div>
                @foreach($stockDeductions as $product => $quantity)
                    <div class="deduction-item">
                        <span class="deduction-product">{{ $product }}</span>
                        <span class="deduction-quantity">−{{ number_format($quantity, 2, ',', '') }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p style="font-size: 12px; color: #1e40af; font-style: italic;">
                Aucune recette n'a été trouvée pour ces plats.
            </p>
        @endif
    </div>

    <!-- Actions -->
    <div class="section-card">
        <h3 class="section-title">
            <i class="fas fa-cog"></i>
            <span>Actions</span>
        </h3>

        <div class="actions-grid">
            @if($order->status !== 'paid' && $order->status !== 'cancelled')
                <form method="POST" action="{{ route('pos.mark-paid', $order->id) }}" style="width: 100%;">
                    @csrf
                    <button type="submit" class="btn-action btn-paid" style="width: 100%;">
                        <i class="fas fa-credit-card"></i>
                        Marquer Payée
                    </button>
                </form>
            @else
                <button class="btn-action btn-disabled" style="width: 100%; opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-credit-card"></i>
                    Marquer Payée
                </button>
            @endif

            @if($order->status !== 'paid' && $order->status !== 'cancelled')
                <form method="POST" action="{{ route('pos.cancel', $order->id) }}" onsubmit="return confirm('Êtes-vous sûr? Le stock sera restauré.');" style="width: 100%;">
                    @csrf
                    <button type="submit" class="btn-action btn-cancel" style="width: 100%;">
                        <i class="fas fa-times-circle"></i>
                        Annuler
                    </button>
                </form>
            @else
                <button class="btn-action btn-disabled" style="width: 100%; opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-times-circle"></i>
                    Annuler
                </button>
            @endif

            <a href="{{ route('pos.orders') }}" class="btn-action btn-back" style="width: 100%;">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
        </div>
    </div>
</div>
@endsection
