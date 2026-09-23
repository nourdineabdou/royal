@section('title', 'Traces Comptables Globales — Complex Royal')
<style>
    .tr-content { grid-column: 1 / -1; }
    .tr-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
    .tr-title { font-size: 20px; font-weight: 700; color: #1f2937; }
    .tr-subtitle { font-size: 12px; color: #6b7280; margin-top: 3px; }
    .filter-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 20px; }
    .filter-head { background: linear-gradient(135deg,#1f2937,#111827); color: white; padding: 12px 18px; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; }
    .filter-body { padding: 16px 18px; }
    .form-control { width: 100%; padding: 9px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 13px; transition: border-color .2s; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #6366f1; }
    .form-label { display: block; font-size: 11px; font-weight: 700; color: #374151; margin-bottom: 5px; }
    .btn-primary { background: linear-gradient(135deg,#6366f1,#4f46e5); color: white; border: none; padding: 9px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all .2s; }
    .btn-primary:hover { transform: translateY(-1px); filter: brightness(1.05); }
    .section-wrap { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 24px; }
    .section-head { padding: 14px 18px; color: white; display: flex; justify-content: space-between; align-items: center; }
    .section-head-title { font-size: 14px; font-weight: 700; }
    .section-head-sub { font-size: 12px; opacity: .8; }
    table.tr-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    table.tr-table thead th { background: #f8fafc; padding: 10px 14px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
    table.tr-table tbody tr:hover { background: #f9fafb; }
    table.tr-table tbody td { padding: 11px 14px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: middle; }
    table.tr-table tbody tr:last-child td { border-bottom: none; }
    .pill { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
    .tabs-bar { display: flex; gap: 6px; flex-wrap: wrap; padding: 14px 18px; border-bottom: 1px solid #f3f4f6; }
    .tab-btn { padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 700; border: 2px solid transparent; cursor: pointer; transition: all .2s; }
    .tab-btn.active { border-color: currentColor; }
    .tab-btn-tx    { color: #374151; background: #f3f4f6; }
    .tab-btn-tx.active { background: #1f2937; color: white; }
    .tab-btn-orders { color: #4f46e5; background: #eef2ff; }
    .tab-btn-orders.active { background: #4f46e5; color: white; }
    .tab-btn-stock  { color: #059669; background: #ecfdf5; }
    .tab-btn-stock.active  { background: #059669; color: white; }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .pager { padding: 12px 18px; border-top: 1px solid #f3f4f6; display: flex; gap: 8px; align-items: center; }
</style>
<div class="tr-content">
    @php
        $fm = $filters['module'] ?? '';
        $ftype = $filters['type'] ?? '';
        $fcashier = $filters['cashier_user_id'] ?? '';
        $ffrom = $filters['from_date'] ?? '';
        $fto = $filters['to_date'] ?? '';
        $qs = http_build_query(array_filter([
            'module' => $fm, 'type' => $ftype,
            'cashier_user_id' => $fcashier,
            'from_date' => $ffrom, 'to_date' => $fto,
        ]));
    @endphp
    <!-- Header -->
    <div class="tr-header">
        <div>
            <div class="tr-title">
                <i class="fas fa-file-invoice-dollar" style="color:#6366f1;margin-right:8px;"></i>
                Traces Comptables Globales
            </div>
            <div class="tr-subtitle">Transactions · Ventes détaillées · Mouvements de stock par module</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <a href="{{ route('modules.accounting') }}" class="btn-primary" style="background:linear-gradient(135deg,#6b7280,#4b5563);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Retour Comptabilité
            </a>
            <a href="{{ route('accounting.traces-export-csv') }}{{ $qs ? '?'.$qs : '' }}"
               class="btn-primary" style="background:linear-gradient(135deg,#059669,#047857);text-decoration:none;">
                <i class="fas fa-file-excel"></i> Exporter Excel (CSV)
            </a>
        </div>
    </div>
    <!-- Filtres -->
    <div class="filter-card">
        <div class="filter-head"><i class="fas fa-filter"></i> Filtres</div>
        <form method="GET" action="{{ route('accounting.traces') }}" class="filter-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;align-items:end;">
                <div>
                    <label class="form-label">Module</label>
                    <select name="module" class="form-control">
                        <option value="">Tous les modules</option>
                        <option value="restaurant" {{ $fm === 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                        <option value="catering"   {{ $fm === 'catering'   ? 'selected' : '' }}>Catering</option>
                        <option value="events"     {{ $fm === 'events'     ? 'selected' : '' }}>Événements</option>
                        <option value="residence"  {{ $fm === 'residence'  ? 'selected' : '' }}>Résidence</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Type transaction</label>
                    <select name="type" class="form-control">
                        <option value="">Tous types</option>
                        <option value="sale"     {{ $ftype === 'sale'     ? 'selected' : '' }}>Vente</option>
                        <option value="purchase" {{ $ftype === 'purchase' ? 'selected' : '' }}>Achat</option>
                        <option value="salary"   {{ $ftype === 'salary'   ? 'selected' : '' }}>Salaire</option>
                        <option value="expense"  {{ $ftype === 'expense'  ? 'selected' : '' }}>Dépense</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Caissier</label>
                    <select name="cashier_user_id" class="form-control">
                        <option value="">Tous caissiers</option>
                        @foreach(($cashiers ?? collect()) as $c)
                            <option value="{{ $c->id }}" {{ (string)$fcashier === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Du</label>
                    <input type="date" name="from_date" value="{{ $ffrom }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Au</label>
                    <input type="date" name="to_date" value="{{ $fto }}" class="form-control">
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn-primary" style="padding:9px 14px;">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="{{ route('accounting.traces') }}" class="btn-primary"
                       style="text-decoration:none;background:#6b7280;padding:9px 14px;">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
    <!-- Résumé rapide -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
        <div style="background:white;border-radius:12px;padding:14px 16px;box-shadow:0 1px 4px rgba(0,0,0,.07);text-align:center;">
            <div style="font-size:26px;font-weight:700;color:#374151;">{{ $transactions->total() }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;">Transactions</div>
        </div>
        <div style="background:white;border-radius:12px;padding:14px 16px;box-shadow:0 1px 4px rgba(0,0,0,.07);text-align:center;">
            <div style="font-size:26px;font-weight:700;color:#4f46e5;">{{ $orders->total() }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;">Ventes</div>
        </div>
        <div style="background:white;border-radius:12px;padding:14px 16px;box-shadow:0 1px 4px rgba(0,0,0,.07);text-align:center;">
            <div style="font-size:26px;font-weight:700;color:#059669;">{{ $stockMovements->total() }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;">Mvts stock</div>
        </div>
        <div style="background:white;border-radius:12px;padding:14px 16px;box-shadow:0 1px 4px rgba(0,0,0,.07);text-align:center;">
            <div style="font-size:26px;font-weight:700;color:#d97706;">
                {{ number_format($orders->getCollection()->sum('total_amount'), 0, ',', ' ') }}
            </div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px;">Total ventes (MRU)</div>
        </div>
    </div>
    <!-- Tabs -->
    <div class="section-wrap">
        <div class="tabs-bar">
            <button class="tab-btn tab-btn-tx active" onclick="switchTab('tx', this)">
                <i class="fas fa-file-invoice-dollar mr-1"></i>Transactions ({{ $transactions->total() }})
            </button>
            <button class="tab-btn tab-btn-orders" onclick="switchTab('orders', this)">
                <i class="fas fa-receipt mr-1"></i>Ventes ({{ $orders->total() }})
            </button>
            <button class="tab-btn tab-btn-stock" onclick="switchTab('stock', this)">
                <i class="fas fa-boxes-stacked mr-1"></i>Stock ({{ $stockMovements->total() }})
            </button>
        </div>
        {{-- === TAB: Transactions === --}}
        <div class="tab-panel active" id="tab-tx">
            <div style="overflow-x:auto;">
                <table class="tr-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Référence</th>
                            <th style="text-align:right;">Montant (MRU)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        @php
                            $txColors = [
                                'sale'     => ['bg'=>'#dcfce7','color'=>'#166534','label'=>'Vente'],
                                'purchase' => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'Achat'],
                                'salary'   => ['bg'=>'#fef3c7','color'=>'#92400e','label'=>'Salaire'],
                                'expense'  => ['bg'=>'#f3e8ff','color'=>'#6b21a8','label'=>'Dépense'],
                            ];
                            $tc = $txColors[$tx->type] ?? ['bg'=>'#f1f5f9','color'=>'#475569','label'=>ucfirst($tx->type)];
                        @endphp
                        <tr>
                            <td><strong>#{{ $tx->id }}</strong></td>
                            <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($tx->date)->format('d/m/Y') }}</td>
                            <td>
                                <span class="pill" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">
                                    {{ $tc['label'] }}
                                </span>
                            </td>
                            <td style="color:#6b7280;">{{ $tx->reference ?? '—' }}</td>
                            <td style="text-align:right;font-weight:700;">{{ number_format((float)$tx->amount, 2, ',', ' ') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center;color:#9ca3af;padding:32px;font-style:italic;">
                                Aucune transaction
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
            <div class="pager">
                {{ $transactions->appends(array_merge($filters, ['order_page' => $orders->currentPage(), 'sm_page' => $stockMovements->currentPage()]))->links() }}
            </div>
            @endif
        </div>
        {{-- === TAB: Ventes === --}}
        <div class="tab-panel" id="tab-orders">
            <div style="overflow-x:auto;">
                <table class="tr-table">
                    <thead>
                        <tr>
                            <th>Commande</th>
                            <th>Module</th>
                            <th>Caissier</th>
                            <th>Articles</th>
                            <th>Paiement</th>
                            <th style="text-align:right;">Montant</th>
                            <th>Date/Heure</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        @php
                            $modColors = [
                                'restaurant' => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                                'catering'   => ['bg'=>'#f3e8ff','color'=>'#6b21a8'],
                                'events'     => ['bg'=>'#fff7ed','color'=>'#c2410c'],
                                'residence'  => ['bg'=>'#dcfce7','color'=>'#166534'],
                            ];
                            $mod = $order->cashRegister?->module ?? 'restaurant';
                            $mc = $modColors[$mod] ?? ['bg'=>'#f1f5f9','color'=>'#475569'];
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('pos.order-detail', $order->id) }}"
                                   style="text-decoration:none;color:#4f46e5;font-weight:700;">#{{ $order->id }}</a>
                                @if($order->customer_number)
                                    <div style="font-size:11px;color:#9ca3af;">Table {{ $order->customer_number }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="pill" style="background:{{ $mc['bg'] }};color:{{ $mc['color'] }};">
                                    {{ ucfirst($mod) }}
                                </span>
                            </td>
                            <td style="color:#6b7280;font-size:12px;">{{ $order->cashRegister?->user?->name ?? '—' }}</td>
                            <td style="max-width:260px;font-size:12px;">
                                <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                     title="{{ $order->items->map(fn($i)=>(($i->meal->name??'Article').' ×'.$i->quantity))->join(', ') }}">
                                    {{ $order->items->map(fn($i)=>(($i->meal->name??'Article').' ×'.$i->quantity))->join(', ') ?: '—' }}
                                </div>
                            </td>
                            <td style="font-size:12px;color:#6b7280;">{{ $order->payment?->paymentType?->name ?? '—' }}</td>
                            <td style="text-align:right;font-weight:700;white-space:nowrap;">
                                {{ number_format((float)$order->total_amount, 2, ',', ' ') }} MRU
                            </td>
                            <td style="white-space:nowrap;font-size:11px;color:#6b7280;">
                                {{ $order->paid_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center;color:#9ca3af;padding:32px;font-style:italic;">
                                Aucune vente
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
            <div class="pager">
                {{ $orders->appends(array_merge($filters, ['tx_page' => $transactions->currentPage(), 'sm_page' => $stockMovements->currentPage()]))->links() }}
            </div>
            @endif
        </div>
        {{-- === TAB: Mouvements Stock === --}}
        <div class="tab-panel" id="tab-stock">
            <div style="overflow-x:auto;">
                <table class="tr-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Stock</th>
                            <th>Produit</th>
                            <th>Module</th>
                            <th>Type</th>
                            <th>Référence</th>
                            <th style="text-align:right;">Quantité</th>
                            <th>Opérateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockMovements as $mv)
                        @php
                            $origColors = [
                                'restaurant' => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                                'catering'   => ['bg'=>'#f3e8ff','color'=>'#6b21a8'],
                                'events'     => ['bg'=>'#fff7ed','color'=>'#c2410c'],
                                'transfer'   => ['bg'=>'#ccfbf1','color'=>'#115e59'],
                                'manual'     => ['bg'=>'#f1f5f9','color'=>'#475569'],
                            ];
                            $oc = $origColors[$mv->origin_module] ?? ['bg'=>'#f1f5f9','color'=>'#475569'];
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;font-size:11px;color:#6b7280;">
                                {{ $mv->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <a href="{{ route('stock.movements', $mv->stock_id) }}"
                                   style="text-decoration:none;color:#059669;font-weight:600;font-size:12px;">
                                    {{ $mv->stock?->name ?? '—' }}
                                </a>
                            </td>
                            <td>
                                <span style="font-weight:600;">{{ $mv->product?->name ?? '—' }}</span>
                                <span style="font-size:11px;color:#9ca3af;margin-left:3px;">{{ $mv->product?->unit?->name }}</span>
                            </td>
                            <td>
                                <span class="pill" style="background:{{ $oc['bg'] }};color:{{ $oc['color'] }};">
                                    {{ $mv->origin_module_label }}
                                </span>
                            </td>
                            <td>
                                @if($mv->type === 'out')
                                    <span class="pill" style="background:#fee2e2;color:#991b1b;">
                                        <i class="fas fa-arrow-up" style="font-size:8px;"></i>Sortie
                                    </span>
                                @elseif($mv->type === 'in')
                                    <span class="pill" style="background:#dcfce7;color:#166534;">
                                        <i class="fas fa-arrow-down" style="font-size:8px;"></i>Entrée
                                    </span>
                                @else
                                    <span class="pill" style="background:#dbeafe;color:#1e40af;">
                                        <i class="fas fa-right-left" style="font-size:8px;"></i>Transfert
                                    </span>
                                @endif
                            </td>
                            <td style="font-size:11px;color:#6b7280;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $mv->notes ?: ($mv->origin_id ? '#'.$mv->origin_id : '—') }}
                            </td>
                            <td style="text-align:right;font-weight:700;color:{{ $mv->type === 'out' ? '#dc2626' : '#16a34a' }};">
                                {{ $mv->type === 'out' ? '-' : '+' }}{{ number_format((float)$mv->quantity, 2, ',', ' ') }}
                            </td>
                            <td style="font-size:11px;color:#6b7280;">{{ $mv->user?->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align:center;color:#9ca3af;padding:32px;font-style:italic;">
                                Aucun mouvement de stock
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($stockMovements->hasPages())
            <div class="pager">
                {{ $stockMovements->appends(array_merge($filters, ['tx_page' => $transactions->currentPage(), 'order_page' => $orders->currentPage()]))->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@section('scripts')
<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
    history.replaceState(null, '', '#tab-' + name);
}
document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;
    if (hash === '#tab-orders') {
        switchTab('orders', document.querySelector('.tab-btn-orders'));
    } else if (hash === '#tab-stock') {
        switchTab('stock', document.querySelector('.tab-btn-stock'));
    }
});
</script>
@endsection
