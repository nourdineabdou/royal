@extends('layouts.pos')

@section('content')
<style>
    .orders-content {
        grid-column: 1 / -1;
    }

    .filter-section {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 20px;
    }

    @media (min-width: 768px) {
        .filter-section {
            grid-template-columns: 200px 1fr auto;
            align-items: flex-end;
        }
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-sent { background: #dbeafe; color: #1e40af; }
    .status-paid { background: #dcfce7; color: #166534; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    .orders-table {
        width: 100%;
        background: white;
        border-radius: 12px;
        overflow: auto;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .orders-table tbody tr:hover {
        background: #f9fafb;
    }

    .orders-table td, .orders-table th {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
    }

    .orders-table th {
        background: #f9fafb;
        font-weight: 600;
        color: #1f2937;
        text-align: left;
    }

    .order-id {
        font-weight: 700;
        color: #667eea;
    }

    .action-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .action-link:hover {
        background: #f3f4f6;
        color: #764ba2;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    .btn-new-order {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px 16px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s;
    }

    .btn-new-order:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
    }

    .pagination {
        margin-top: 20px;
        text-align: center;
    }

    .pagination button, .pagination a {
        padding: 8px 12px;
        margin: 0 4px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: white;
        color: #667eea;
        text-decoration: none;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .pagination button:hover, .pagination a:hover {
        background: #f3f4f6;
    }

    .pagination .page-item.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    @media (max-width: 768px) {
        .orders-table {
            font-size: 12px;
        }

        .orders-table td, .orders-table th {
            padding: 8px;
        }

        .order-id {
            font-size: 12px;
        }
    }
</style>

<div class="orders-content">
    <!-- Filters -->
    <div class="filter-section">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Filtrer par statut</label>
            <select class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-13px focus:border-indigo-600 focus:outline-none" onchange="filterByStatus(this.value)">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="sent">Envoyée</option>
                <option value="paid">Payée</option>
                <option value="cancelled">Annulée</option>
            </select>
        </div>
        <div></div>
        @can('pos.orders.create')
        <a href="{{ route('modules.pos') }}" class="btn-new-order">
            <i class="fas fa-plus-circle"></i>
            <span>Nouvelle Commande</span>
        </a>
        @endcan
    </div>

    <!-- Orders Table -->
    <div class="orders-table">
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th style="width: 120px;">Client</th>
                    <th style="width: 80px;">Articles</th>
                    <th style="text-align: right; width: 100px;">Total</th>
                    <th style="width: 120px;">Statut</th>
                    <th style="width: 140px;">Date</th>
                    <th style="text-align: center; width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="order-id">#{{ $order->id }}</td>
                        <td>{{ $order->customer_number ?? '-' }}</td>
                        <td>{{ $order->items->count() }}</td>
                        <td style="text-align: right; font-weight: 600;">{{ number_format($order->total_amount, 2, ',', '') }} MRU</td>
                        <td>
                            <span class="status-badge status-{{ $order->status }}">
                                @if($order->status === 'pending') En attente
                                @elseif($order->status === 'sent') Envoyée
                                @elseif($order->status === 'paid') Payée
                                @else Annulée
                                @endif
                            </span>
                        </td>
                        <td style="color: #6b7280;">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('pos.order-detail', $order->id) }}" class="action-link" title="Voir la commande">
                                <i class="fas fa-eye"></i>Voir
                            </a>

                            @if($order->status === 'pending' || $order->status === 'sent')
                                <form action="{{ route('pos.mark-paid', $order->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="action-link" style="color: #166534;" title="Valider/Payer">
                                        <i class="fas fa-check"></i>Valider
                                    </button>
                                </form>
                                <form action="{{ route('pos.cancel', $order->id) }}" method="POST" style="display:inline-block; margin-left: 4px;">
                                    @csrf
                                    <button type="submit" class="action-link" style="color: #991b1b;" title="Annuler la commande" onclick="return confirm('Annuler cette commande ?');">
                                        <i class="fas fa-times"></i>Annuler
                                    </button>
                                </form>
                            @endif

                            @if($order->status === 'paid')
                                <a href="{{ route('pos.order-receipt', $order->id) }}" target="_blank" class="action-link" style="color: #1e40af;" title="Imprimer le ticket">
                                    <i class="fas fa-print"></i>Ticket
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <p>Aucune commande trouvée</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="pagination">
            {{ $orders->links() }}
        </div>
    @endif
</div>

<script>
function filterByStatus(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location = url.toString();
}
</script>
@endsection
