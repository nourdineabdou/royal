@extends('layouts.accounting')

@section('title', 'Comptabilité Caisse — Complex Royal')

@section('accounting_content')
<style>
    .acc-content { grid-column: 1 / -1; }

    .acc-header {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
    }
    .acc-title { font-size: 20px; font-weight: 700; color: #1f2937; }

    /* Stats */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 12px; margin-bottom: 24px;
    }
    .stat-card {
        background: white; border-radius: 12px; padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08); text-align: center;
    }
    .stat-value { font-size: 24px; font-weight: 700; }
    .stat-label { font-size: 11px; color: #6b7280; margin-top: 2px; }

    /* Table */
    .acc-table-wrap {
        background: white; border-radius: 16px; overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }
    .acc-table-header {
        padding: 16px 20px;
        background: linear-gradient(135deg,#6366f1,#4f46e5);
        color: white;
        display: flex; align-items: center; justify-content: space-between;
    }
    .acc-table-title { font-size: 15px; font-weight: 700; }
    table.acc-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    table.acc-table thead th {
        background: #f8fafc; padding: 11px 16px;
        text-align: left; font-weight: 600; color: #374151;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    table.acc-table tbody tr:hover { background: #f9fafb; }
    table.acc-table tbody td {
        padding: 12px 16px; border-bottom: 1px solid #f3f4f6;
        color: #374151; vertical-align: middle;
    }
    table.acc-table tbody tr:last-child td { border-bottom: none; }

    /* Status pills */
    .pill {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 700;
        padding: 3px 10px; border-radius: 20px;
        white-space: nowrap;
    }
    .pill-open      { background:#fef3c7; color:#92400e; }
    .pill-closed    { background:#dbeafe; color:#1e40af; }
    .pill-validated { background:#dcfce7; color:#166534; }
    .pill-flagged   { background:#fee2e2; color:#991b1b; }

    /* Buttons */
    .btn-view {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;
        border: none; cursor: pointer; text-decoration: none; transition: all .2s;
    }
    .btn-indigo { background:#eef2ff; color:#4f46e5; }
    .btn-indigo:hover { background:#e0e7ff; }
    .btn-green  { background:#dcfce7; color:#166534; }
    .btn-green:hover { background:#bbf7d0; }
    .btn-primary {
        background: linear-gradient(135deg,#6366f1,#4f46e5);
        color:white; border:none; padding:9px 18px; border-radius:10px;
        font-size:13px; font-weight:600; cursor:pointer;
        display:inline-flex; align-items:center; gap:6px;
        transition: all .2s;
    }
    .btn-primary:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(99,102,241,.3); }

    /* Open modal */
    .modal-overlay {
        position:fixed; inset:0; background:rgba(0,0,0,.5);
        display:flex; align-items:center; justify-content:center;
        z-index:100; padding:16px;
    }
    .modal-overlay.hidden { display:none !important; }
    .modal-box {
        background:white; border-radius:16px; width:100%; max-width:400px;
        overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.25);
    }
    .modal-header {
        background:linear-gradient(135deg,#6366f1,#4f46e5);
        padding:16px 20px; color:white;
        display:flex; justify-content:space-between; align-items:center;
    }
    .modal-body { padding:20px; }
    .form-group { margin-bottom:14px; }
    .form-group label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px; }
    .form-control {
        width:100%; padding:9px 12px; border:2px solid #e5e7eb; border-radius:8px;
        font-size:14px; transition:border-color .2s;
    }
    .form-control:focus { outline:none; border-color:#6366f1; }
    .btn-close-modal {
        background:rgba(255,255,255,.2); border:none; color:white;
        width:28px; height:28px; border-radius:50%; cursor:pointer;
        display:flex; align-items:center; justify-content:center; font-size:14px;
    }

    .empty-row td { text-align:center; color:#9ca3af; padding:40px!important; font-style:italic; }

    .tx-wrap {
        margin-top: 22px;
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }
    .tx-head {
        padding: 14px 18px;
        background: linear-gradient(135deg,#1f2937,#111827);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    table.tx-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    table.tx-table thead th {
        background: #f8fafc;
        padding: 10px 14px;
        text-align: left;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }
    table.tx-table tbody td {
        padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6;
    }
    table.tx-table tbody tr:last-child td { border-bottom: none; }
    .tx-type {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 20px;
    }
    .tx-sale { background: #dcfce7; color: #166534; }
    .tx-purchase { background: #fee2e2; color: #991b1b; }
    .tx-salary { background: #dbeafe; color: #1e40af; }
    .tx-expense { background: #fef3c7; color: #92400e; }
</style>

<div class="acc-content">
    @php
        $filterModule = $filters['module'] ?? '';
        $filterCashier = $filters['cashier_user_id'] ?? '';
        $filterFrom = $filters['from_date'] ?? '';
        $filterTo = $filters['to_date'] ?? '';
        $queryString = http_build_query(array_filter([
            'module' => $filterModule,
            'cashier_user_id' => $filterCashier,
            'from_date' => $filterFrom,
            'to_date' => $filterTo,
        ]));
    @endphp

    <!-- En-tête -->
    <div class="acc-header">
        <div>
            <div class="acc-title"><i class="fas fa-calculator" style="color:#6366f1;margin-right:8px;"></i>Comptabilité des Caisses</div>
            <div style="font-size:12px;color:#6b7280;margin-top:3px;">Suivi des sessions d'encaissement et validation comptable</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('accounting.transactions') }}" class="btn-primary" style="background:linear-gradient(135deg,#059669,#047857);text-decoration:none;">
                <i class="fas fa-list"></i> Toutes les transactions
            </a>
            @can('pos.accounting.register.open')
            <button class="btn-primary" onclick="openModal('openRegisterModal')">
                <i class="fas fa-plus-circle"></i> Ouvrir une caisse
            </button>
            @endcan
            @can('pos.accounting.traces.export')
            <!-- Ancien lien supprimé : <a href="{{ route('pos.accounting-traces') }}{{ $queryString ? '?' . $queryString : '' }}" class="btn-primary" style="text-decoration:none;background:linear-gradient(135deg,#374151,#1f2937);"> -->
                <i class="fas fa-file-invoice-dollar"></i> Traces globales
            </a>
            @endcan
            @can('pos.accounting.sessions.export')
            <!-- Ancien lien supprimé : <a href="{{ route('pos.accounting-export-sessions-csv') }}{{ $queryString ? '?' . $queryString : '' }}" class="btn-primary" style="text-decoration:none;background:linear-gradient(135deg,#059669,#047857);"> -->
                <i class="fas fa-file-csv"></i> Sessions Excel
            </a>
            <!-- Ancien lien supprimé : <a href="{{ route('pos.accounting-export-sessions-pdf') }}{{ $queryString ? '?' . $queryString : '' }}" class="btn-primary" target="_blank" style="text-decoration:none;background:linear-gradient(135deg,#7c3aed,#6d28d9);"> -->
                <i class="fas fa-file-pdf"></i> Sessions PDF
            </a>
            @endcan
            @can('pos.accounting.traces.export')
            <!-- Ancien lien supprimé : <a href="{{ route('pos.accounting-export-traces-csv') }}{{ $queryString ? '?' . $queryString : '' }}" class="btn-primary" style="text-decoration:none;background:linear-gradient(135deg,#2563eb,#1d4ed8);"> -->
                <i class="fas fa-file-csv"></i> Traces Excel
            </a>
            <!-- Ancien lien supprimé : <a href="{{ route('pos.accounting-export-traces-pdf') }}{{ $queryString ? '?' . $queryString : '' }}" class="btn-primary" target="_blank" style="text-decoration:none;background:linear-gradient(135deg,#dc2626,#b91c1c);"> -->
                <i class="fas fa-file-pdf"></i> Traces PDF
            </a>
            @endcan
        </div>
    </div>

    <!-- Ancien formulaire supprimé : <form method="GET" action="{{ route('pos.accounting') }}" class="acc-table-wrap" style="margin-bottom:16px;padding:14px 16px;"> -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:10px;align-items:end;">
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;">Module</label>
                <select name="module" class="form-control">
                    <option value="">Tous modules</option>
                    <option value="restaurant" {{ $filterModule === 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                    <option value="catering" {{ $filterModule === 'catering' ? 'selected' : '' }}>Catering</option>
                    <option value="events" {{ $filterModule === 'events' ? 'selected' : '' }}>Événements</option>
                    <option value="residence" {{ $filterModule === 'residence' ? 'selected' : '' }}>Résidence</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;">Caissier</label>
                <select name="cashier_user_id" class="form-control">
                    <option value="">Tous caissiers</option>
                    @foreach(($cashiers ?? collect()) as $cashier)
                        <option value="{{ $cashier->id }}" {{ (string)$filterCashier === (string)$cashier->id ? 'selected' : '' }}>{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;">Du</label>
                <input type="date" name="from_date" value="{{ $filterFrom }}" class="form-control">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;">Au</label>
                <input type="date" name="to_date" value="{{ $filterTo }}" class="form-control">
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-primary" style="padding:9px 12px;">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
                <!-- Ancien lien supprimé : <a href="{{ route('pos.accounting') }}" class="btn-primary" style="text-decoration:none;background:#6b7280;padding:9px 12px;"> -->
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Statistiques -->
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-value" style="color:#374151;">{{ $stats->total ?? 0 }}</div>
            <div class="stat-label">Total sessions</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#d97706;">{{ $stats->open ?? 0 }}</div>
            <div class="stat-label">Ouvertes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#2563eb;">{{ $stats->closed ?? 0 }}</div>
            <div class="stat-label">À valider</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#16a34a;">{{ $stats->validated ?? 0 }}</div>
            <div class="stat-label">Validées</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#dc2626;">{{ $stats->flagged ?? 0 }}</div>
            <div class="stat-label">Problèmes</div>
        </div>
    </div>

    <!-- Tableau sessions -->
    <div class="acc-table-wrap">
        <div class="acc-table-header">
            <div class="acc-table-title"><i class="fas fa-history mr-2"></i>Historique des sessions</div>
            <div style="font-size:12px;opacity:.8;">{{ $registers->total() }} sessions au total</div>
        </div>
        <div style="overflow-x:auto;">
            <table class="acc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Caissier</th>
                        <th>Module</th>
                        <th>Poste</th>
                        <th>Ouverture</th>
                        <th>Fermeture</th>
                        <th>Fond ouv.</th>
                        <th>Encaissé</th>
                        <th>Solde réel</th>
                        <th>Écart</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registers as $reg)
                    @php
                        $systemTotal = (float)($reg->payments_sum_amount ?? 0);
                        $expectedCash = (float)$reg->opening_balance;
                        $realClosing = $reg->closing_balance !== null ? (float)$reg->closing_balance : null;
                        $ecart = $realClosing !== null ? $realClosing - $expectedCash : null;
                    @endphp
                    <tr>
                        <td><strong>#{{ $reg->id }}</strong></td>
                        <td>
                            <div style="font-weight:600;">{{ $reg->user?->name ?? 'Inconnu' }}</div>
                        </td>
                        <td>
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#eef2ff;color:#4f46e5;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;">
                                <i class="fas fa-layer-group" style="font-size:9px;"></i>
                                {{ ucfirst($reg->module ?? 'restaurant') }}
                            </span>
                        </td>
                        <td>
                            @if($reg->shift === 'morning')
                                <span style="color:#d97706;"><i class="fas fa-sun mr-1"></i>Matin</span>
                            @else
                                <span style="color:#7c3aed;"><i class="fas fa-moon mr-1"></i>Soir</span>
                            @endif
                        </td>
                        <td>{{ $reg->opened_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $reg->closed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ number_format($reg->opening_balance, 2, ',', ' ') }} <small>MRU</small></td>
                        <td><strong>{{ number_format($systemTotal, 2, ',', ' ') }}</strong> <small>MRU</small></td>
                        <td>{{ $realClosing !== null ? number_format($realClosing, 2, ',', ' ') . ' MRU' : '—' }}</td>
                        <td>
                            @if($ecart !== null)
                                @if($ecart > 0.005)
                                    <span style="color:#16a34a;font-weight:700;">+{{ number_format($ecart, 2, ',', ' ') }} MRU</span>
                                @elseif($ecart < -0.005)
                                    <span style="color:#dc2626;font-weight:700;">{{ number_format($ecart, 2, ',', ' ') }} MRU</span>
                                @else
                                    <span style="color:#16a34a;">✓ Équilibré</span>
                                @endif
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $status = $reg->status ?? 'open';
                                $pillMap = ['open'=>'pill-open','closed'=>'pill-closed','validated'=>'pill-validated','flagged'=>'pill-flagged'];
                                $labelMap = ['open'=>'Ouverte','closed'=>'Fermée','validated'=>'Validée','flagged'=>'Problème'];
                                $iconMap  = ['open'=>'fa-circle','closed'=>'fa-lock','validated'=>'fa-check-circle','flagged'=>'fa-exclamation-circle'];
                            @endphp
                            <span class="pill {{ $pillMap[$status] ?? 'pill-closed' }}">
                                <i class="fas {{ $iconMap[$status] ?? 'fa-circle' }}" style="font-size:8px;"></i>
                                {{ $labelMap[$status] ?? $status }}
                            </span>
                        </td>
                        <td>
                            <!-- Ancien lien supprimé : <a href="{{ route('pos.accounting-detail', $reg->id) }}" class="btn-view btn-indigo"> -->
                                <i class="fas fa-eye"></i> Voir
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="12"><i class="fas fa-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>Aucune session de caisse enregistrée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($registers->hasPages())
        <div style="padding:14px 18px;border-top:1px solid #f3f4f6;">
            {{ $registers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- ── Modal Ouvrir une caisse ── -->
<div class="modal-overlay" id="openRegisterModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <div style="font-size:16px;font-weight:700;"><i class="fas fa-cash-register mr-2"></i>Ouvrir une caisse</div>
            <button class="btn-close-modal" onclick="closeModal('openRegisterModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label><i class="fas fa-sun mr-1"></i>Poste</label>
                <select id="openShift" class="form-control">
                    <option value="morning">Matin</option>
                    <option value="evening">Soir</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-layer-group mr-1"></i>Module</label>
                <select id="openModule" class="form-control">
                    <option value="restaurant">Restaurant</option>
                    <option value="catering">Catering</option>
                    <option value="events">Événements</option>
                    <option value="residence">Résidence</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-user mr-1"></i>Caissier assigné</label>
                <select id="openCashierUser" class="form-control">
                    @foreach(($cashiers ?? collect()) as $cashier)
                        <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-money-bill-wave mr-1"></i>Fond d'ouverture (MRU)</label>
                <input type="number" id="openBalance" class="form-control" placeholder="0.00" min="0" step="0.01" value="0">
            </div>
            @if(($cashiers ?? collect())->isEmpty())
                <div style="background:#fee2e2;color:#991b1b;border-radius:8px;padding:10px 12px;font-size:12px;font-weight:600;margin-bottom:10px;">
                    Aucun utilisateur avec le rôle caissier. Créez un caissier d'abord.
                </div>
            @endif
            <button class="btn-primary" style="width:100%;justify-content:center;" onclick="submitOpenRegister()" {{ ($cashiers ?? collect())->isEmpty() ? 'disabled' : '' }}>
                <i class="fas fa-check-circle"></i> Ouvrir la caisse
            </button>
        </div>
    </div>
</div>

<div class="tx-wrap">
    <div class="tx-head">
        <div style="font-size:14px;font-weight:700;">
            <i class="fas fa-file-invoice-dollar mr-2"></i>Traces globales comptables — Aujourd'hui
        </div>
        <div style="font-size:12px;opacity:.8;">Transactions · Ventes · Mouvements de stock du jour</div>
    </div>

    {{-- Résumé du jour --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;padding:16px 18px;border-bottom:1px solid #f3f4f6;">
        <div style="text-align:center;padding:10px;background:#f8fafc;border-radius:10px;">
            <div style="font-size:22px;font-weight:700;color:#374151;">{{ ($recentTransactions ?? collect())->count() }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;"><i class="fas fa-file-invoice-dollar" style="color:#374151;"></i> Transactions</div>
        </div>
        <div style="text-align:center;padding:10px;background:#eef2ff;border-radius:10px;">
            <div style="font-size:22px;font-weight:700;color:#4f46e5;">{{ ($recentPaidOrders ?? collect())->count() }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;"><i class="fas fa-receipt" style="color:#4f46e5;"></i> Ventes</div>
        </div>
        <div style="text-align:center;padding:10px;background:#ecfdf5;border-radius:10px;">
            <div style="font-size:22px;font-weight:700;color:#059669;">{{ ($recentStockMovements ?? collect())->count() }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;"><i class="fas fa-boxes-stacked" style="color:#059669;"></i> Mvts stock</div>
        </div>
        <div style="text-align:center;padding:10px;background:#fef3c7;border-radius:10px;">
            <div style="font-size:22px;font-weight:700;color:#d97706;">
                {{ number_format(($recentPaidOrders ?? collect())->sum('total_amount'), 0, ',', ' ') }}
            </div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;">Total encaissé (MRU)</div>
        </div>
    </div>

    {{-- Dernières ventes du jour --}}
    @if(($recentPaidOrders ?? collect())->isNotEmpty())
    <div style="overflow-x:auto;border-bottom:1px solid #f3f4f6;">
        <div style="padding:10px 18px;font-size:12px;font-weight:700;color:#6b7280;background:#fafafa;">
            <i class="fas fa-receipt mr-1"></i>Dernières ventes du jour
        </div>
        <table class="tx-table">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Module</th>
                    <th>Caissier</th>
                    <th>Articles</th>
                    <th>Paiement</th>
                    <th style="text-align:right;">Montant</th>
                    <th>Heure</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($recentPaidOrders ?? collect()) as $order)
                <tr>
                    <td>
                        <a href="{{ route('pos.order-detail', $order->id) }}" style="text-decoration:none;color:#4f46e5;font-weight:700;">#{{ $order->id }}</a>
                        <div style="font-size:11px;color:#9ca3af;">{{ $order->customer_number ? 'Table '.$order->customer_number : 'Client direct' }}</div>
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:4px;background:#eef2ff;color:#4f46e5;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;">
                            {{ ucfirst($order->cashRegister?->module ?? 'restaurant') }}
                        </span>
                    </td>
                    <td style="font-size:12px;color:#6b7280;">{{ $order->cashRegister?->user?->name ?? '—' }}</td>
                    <td style="max-width:250px;font-size:12px;">
                        <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $order->items->map(fn($i)=>($i->meal->name??'Article').' ×'.$i->quantity)->join(', ') }}">
                            {{ $order->items->map(fn($i)=>($i->meal->name??'Article').' ×'.$i->quantity)->join(', ') ?: '—' }}
                        </div>
                    </td>
                    <td style="font-size:12px;color:#6b7280;">{{ $order->payment?->paymentType?->name ?? '—' }}</td>
                    <td style="text-align:right;font-weight:700;white-space:nowrap;">{{ number_format($order->total_amount, 2, ',', ' ') }} MRU</td>
                    <td style="font-size:11px;color:#6b7280;">{{ $order->paid_at?->format('H:i') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Derniers mouvements de stock du jour --}}
    @if(($recentStockMovements ?? collect())->isNotEmpty())
    <div style="overflow-x:auto;border-bottom:1px solid #f3f4f6;">
        <div style="padding:10px 18px;font-size:12px;font-weight:700;color:#6b7280;background:#fafafa;">
            <i class="fas fa-boxes-stacked mr-1" style="color:#059669;"></i>Mouvements de stock du jour
        </div>
        <table class="tx-table">
            <thead>
                <tr>
                    <th>Heure</th>
                    <th>Stock</th>
                    <th>Produit</th>
                    <th>Module</th>
                    <th>Type</th>
                    <th style="text-align:right;">Qté</th>
                    <th>Opérateur</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($recentStockMovements ?? collect()) as $mv)
                @php
                    $oc = [
                        'restaurant' => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                        'catering'   => ['bg'=>'#f3e8ff','color'=>'#6b21a8'],
                        'events'     => ['bg'=>'#fff7ed','color'=>'#c2410c'],
                    ][$mv->origin_module] ?? ['bg'=>'#f1f5f9','color'=>'#475569'];
                @endphp
                <tr>
                    <td style="font-size:11px;color:#6b7280;">{{ $mv->created_at->format('H:i') }}</td>
                    <td style="font-size:12px;font-weight:600;color:#059669;">{{ $mv->stock?->name ?? '—' }}</td>
                    <td style="font-size:12px;">{{ $mv->product?->name ?? '—' }} <small style="color:#9ca3af;">{{ $mv->product?->unit?->name }}</small></td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:3px;background:{{ $oc['bg'] }};color:{{ $oc['color'] }};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">
                            {{ $mv->origin_module_label }}
                        </span>
                    </td>
                    <td>
                        @if($mv->type === 'out')
                            <span style="display:inline-flex;align-items:center;background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">Sortie</span>
                        @elseif($mv->type === 'in')
                            <span style="display:inline-flex;align-items:center;background:#dcfce7;color:#166534;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">Entrée</span>
                        @else
                            <span style="display:inline-flex;align-items:center;background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">Transfert</span>
                        @endif
                    </td>
                    <td style="text-align:right;font-weight:700;color:{{ $mv->type === 'out' ? '#dc2626' : '#16a34a' }};">
                        {{ $mv->type === 'out' ? '-' : '+' }}{{ number_format($mv->quantity, 2, ',', ' ') }}
                    </td>
                    <td style="font-size:11px;color:#6b7280;">{{ $mv->user?->name ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div style="padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <span style="font-size:12px;color:#9ca3af;font-style:italic;">
            Affichage limité aux 50 dernières opérations du jour
        </span>
        <!-- Ancien lien supprimé : <a href="{{ route('pos.accounting-traces') }}{{ $queryString ? '?'.$queryString : '' }}" -->
           style="display:inline-flex;align-items:center;gap:6px;color:#4f46e5;font-size:12px;font-weight:700;text-decoration:none;">
            <i class="fas fa-external-link-alt"></i>
            Voir toutes les traces (filtres avancés + export Excel)
        </a>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});

async function submitOpenRegister() {
    const shift   = document.getElementById('openShift').value;
    const module  = document.getElementById('openModule').value;
    const cashierUserId = parseInt(document.getElementById('openCashierUser').value, 10);
    const balance = parseFloat(document.getElementById('openBalance').value) || 0;

    try {
        // Ancienne action supprimée : const res = await fetch('{{ route("pos.accounting-open") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ shift, module, cashier_user_id: cashierUserId, opening_balance: balance }),
        });
        const data = await res.json();

        if (data.success) {
            window.location.href = '{{ url("/pos/accounting") }}/' + data.register_id;
        } else {
            alert(data.message || 'Erreur');
        }
    } catch (e) {
        alert('Erreur de connexion');
    }
}
</script>
@endsection
