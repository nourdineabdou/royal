<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Point de Vente') - Complex Royal</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .pos-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 8px;
        }

        .pos-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 0 0 20px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 20px;
            margin-bottom: 20px;
        }

        .pos-header-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .pos-header-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .pos-header-text h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .pos-header-text p {
            font-size: 13px;
            color: #6b7280;
            margin: 4px 0 0 0;
        }

        .pos-main {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            padding-bottom: 20px;
        }

        /* RESPONSIVE DESIGN */
        @media (min-width: 768px) {
            .pos-main {
                grid-template-columns: 1fr 380px;
            }

            .pos-container {
                padding: 16px;
            }

            .pos-header {
                margin-bottom: 24px;
            }
        }

        @media (min-width: 1024px) {
            .pos-main {
                grid-template-columns: 1fr 420px;
            }

            .pos-container {
                padding: 24px;
            }
        }

        .meals-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .cart-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .meals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        @media (min-width: 640px) {
            .meals-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 16px;
            }
        }

        @media (min-width: 1024px) {
            .meals-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 16px;
            }
        }

        .meal-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #f3f4f6;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .meal-card:hover {
            transform: translateY(-8px);
            border-color: #667eea;
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.2);
        }

        .meal-image {
            width: 100%;
            height: 120px;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #d1d5db;
            position: relative;
            overflow: hidden;
        }

        .meal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .meal-category-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 6px;
            color: white;
            font-weight: 600;
        }

        .meal-info {
            padding: 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .meal-name {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
            line-height: 1.4;
            flex: 1;
        }

        .meal-price {
            font-size: 16px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }

        .meal-add-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 6px 8px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .meal-add-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding-bottom: 4px;
        }

        .category-btn {
            padding: 6px 12px;
            border-radius: 20px;
            border: 2px solid transparent;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s;
            color: white;
            flex-shrink: 0;
        }

        @media (min-width: 640px) {
            .category-btn {
                padding: 7px 14px;
                font-size: 13px;
            }
        }

        .category-btn.active {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .category-color-0 { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .category-color-1 { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .category-color-2 { background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%); }
        .category-color-3 { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); }

        .cart-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-title i {
            color: #667eea;
            font-size: 20px;
        }

        .customer-input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .customer-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .cart-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 12px;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 12px;
        }

        .cart-item {
            background: #f9fafb;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cart-item-name {
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .cart-item-qty {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .qty-btn {
            background: #e5e7eb;
            border: none;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background: #d1d5db;
        }

        .qty-display {
            font-weight: 600;
            color: #1f2937;
            min-width: 20px;
            text-align: center;
            font-size: 12px;
        }

        .cart-item-price {
            font-weight: 700;
            color: #667eea;
            font-size: 13px;
            text-align: right;
        }

        .cart-item-remove {
            background: #fee2e2;
            border: none;
            color: #dc2626;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 10px;
            transition: all 0.2s;
        }

        .cart-item-remove:hover {
            background: #fecaca;
        }

        .cart-empty {
            text-align: center;
            color: #9ca3af;
            padding: 24px 0;
            font-size: 12px;
        }

        .cart-summary {
            background: #f9fafb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 13px;
            color: #6b7280;
        }

        .summary-row.total {
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
            border-top: 2px solid #e5e7eb;
            padding-top: 8px;
            margin-top: 8px;
            margin-bottom: 0;
        }

        .cart-actions {
            display: grid;
            gap: 8px;
        }

        .btn-action {
            padding: 12px;
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
        }

        .btn-checkout {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .btn-checkout:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-checkout:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-clear {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .btn-clear:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        .btn-history {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .btn-history:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }

        .success-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            padding: 16px;
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-icon {
            font-size: 48px;
            color: #10b981;
            margin-bottom: 16px;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .modal-message {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .modal-details {
            background: #f9fafb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
            text-align: left;
            font-size: 13px;
        }

        .modal-details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            color: #374151;
        }

        .modal-details-row:last-child {
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            margin-top: 8px;
        }

        .modal-details-value {
            font-weight: 600;
            color: #667eea;
        }

        .btn-confirm {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        /* Scrollbar stylisé */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="pos-container">
        <!-- Header -->
        <div class="pos-header">
            <div class="pos-header-title" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;align-items:center;gap:15px;">
                    <div class="pos-header-logo">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="pos-header-text">
                        <h1>Complex Royal</h1>
                        <p>Point de Vente - Système de gestion des ventes</p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    @unless(request()->routeIs('pos.cashier'))
                    <a href="{{ route('pos.cashier') }}"
                       style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#10b981,#059669);color:white;text-decoration:none;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;box-shadow:0 2px 8px rgba(16,185,129,.25);">
                        <i class="fas fa-cash-register"></i> Caisse
                    </a>
                    @endunless
                    @if(request()->routeIs('pos.accounting*', 'pos.orders', 'pos.order-detail'))
                    {{-- @can('pos.accounting.profitability')
                    <a href="{{ route('pos.accounting-profitability') }}"
                       style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#059669,#047857);color:white;text-decoration:none;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;box-shadow:0 2px 8px rgba(5,150,105,.25);">
                        <i class="fas fa-chart-line"></i> Rentabilité
                    </a>
                    @endcan --}}
                    <a href="{{ route('pos.orders') }}"
                       style="display:inline-flex;align-items:center;gap:6px;background:#f3f4f6;color:#374151;text-decoration:none;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;">
                        <i class="fas fa-list-alt"></i> Commandes
                    </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit"
                                style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#ef4444,#b91c1c);color:white;border:none;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="pos-main">
            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>
</html>
