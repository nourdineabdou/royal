<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de Commande #{{ $order->reference }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A4; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 0; }

        /* Company header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 2px solid #1e3a5f; padding-bottom: 12px; }
        .company-name { font-size: 20px; font-weight: bold; color: #1e3a5f; }
        .company-info { font-size: 11px; color: #4b5563; margin-top: 4px; line-height: 1.5; }
        .doc-title { text-align: right; }
        .doc-title h2 { font-size: 18px; color: #1e3a5f; margin: 0 0 6px; }
        .doc-title .doc-ref { font-size: 13px; font-weight: bold; }
        .doc-title .doc-date { font-size: 11px; color: #6b7280; }

        /* Parties */
        .parties { display: flex; gap: 24px; margin-bottom: 20px; }
        .party-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 14px; }
        .party-box h4 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; }
        .party-box p { margin: 0; font-size: 12px; line-height: 1.6; }
        .party-box strong { font-size: 13px; }

        /* Items table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #1e3a5f; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
        thead th.right { text-align: right; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody tr:nth-child(even) td { background: #f9fafb; }

        /* Totals */
        .totals { display: flex; justify-content: flex-end; margin-bottom: 24px; }
        .totals-box { width: 260px; border: 1px solid #e5e7eb; border-radius: 4px; overflow: hidden; }
        .totals-box .row { display: flex; justify-content: space-between; padding: 6px 12px; font-size: 12px; border-bottom: 1px solid #f3f4f6; }
        .totals-box .row:last-child { border-bottom: none; background: #1e3a5f; color: #fff; font-weight: bold; font-size: 13px; }

        /* Status badge */
        .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: bold; background: #e0f2fe; color: #0369a1; }

        /* Footer */
        .footer { border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 10px; color: #9ca3af; text-align: center; }
        .signatures { display: flex; gap: 40px; margin-top: 28px; }
        .sig { flex: 1; border-top: 1px solid #d1d5db; padding-top: 6px; text-align: center; font-size: 11px; color: #4b5563; }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- Print button -->
    <div class="no-print" style="text-align:right; margin-bottom:12px;">
        <button onclick="window.print()" style="padding:6px 16px; background:#1e3a5f; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
            Imprimer
        </button>
        <button onclick="window.close()" style="padding:6px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:4px; cursor:pointer; font-size:12px; margin-left:6px;">
            Fermer
        </button>
    </div>

    <!-- Header -->
    <div class="header">
        <div style="display:flex; align-items:center; gap:14px;">
            @if(!empty($company['logo']))
            <img src="{{ asset($company['logo']) }}" alt="{{ $company['name'] }}" style="height:70px; width:auto; object-fit:contain;">
            @endif
            <div>
                <div class="company-name">{{ $company['name'] ?? 'Entreprise' }}</div>
                <div class="company-info">
                    @if(!empty($company['address'])){{ $company['address'] }}@endif
                    @if(!empty($company['phone']))<br>Tél: {{ $company['phone'] }}@endif
                </div>
            </div>
        </div>
        <div class="doc-title">
            <h2>BON DE COMMANDE</h2>
            <div class="doc-ref">Réf: {{ $order->reference }}</div>
            <div class="doc-date">Date: {{ $order->created_at->format('d/m/Y') }}</div>
            <div style="margin-top:4px;"><span class="badge">{{ ucfirst($order->status) }}</span></div>
        </div>
    </div>

    <!-- Parties -->
    <div class="parties">
        <div class="party-box">
            <h4>Acheteur</h4>
            <p><strong>{{ $company['name'] ?? 'Entreprise' }}</strong></p>
            @if(!empty($company['address']))<p>{{ $company['address'] }}</p>@endif
            @if(!empty($company['phone']))<p>Tél: {{ $company['phone'] }}</p>@endif
        </div>
        <div class="party-box">
            <h4>Fournisseur</h4>
            <p><strong>{{ $order->supplier->name }}</strong></p>
            @if($order->supplier->phone)<p>Tél: {{ $order->supplier->phone }}</p>@endif
            @if($order->supplier->email)<p>{{ $order->supplier->email }}</p>@endif
            @if($order->supplier->address)<p>{{ $order->supplier->address }}</p>@endif
        </div>
    </div>

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produit</th>
                <th>Conditionnement</th>
                <th class="right">Quantité</th>
                <th class="right">Prix unitaire</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td>{{ $item->packaging->name ?? '-' }}</td>
                <td class="right">{{ number_format($item->quantity, 2, ',', ' ') }} {{ $item->product->unit->symbol ?? '' }}</td>
                <td class="right">{{ number_format($item->price, 2, ',', ' ') }}</td>
                <td class="right">{{ number_format($item->total, 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <div class="totals-box">
            <div class="row"><span>Total HT</span><span>{{ number_format($order->total_amount, 2, ',', ' ') }}</span></div>
            <div class="row"><span>Payé</span><span>{{ number_format($order->paid_amount, 2, ',', ' ') }}</span></div>
            <div class="row"><span>Reste à payer</span><span>{{ number_format($order->remaining_amount, 2, ',', ' ') }}</span></div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig">Signature Acheteur</div>
        <div class="sig">Cachet &amp; Signature Fournisseur</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} · {{ $company['name'] ?? '' }}
    </div>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
