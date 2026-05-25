<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #{{ $order->reference }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A4; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 0; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 2px solid #1e3a5f; padding-bottom: 12px; }
        .company-name { font-size: 20px; font-weight: bold; color: #1e3a5f; }
        .company-info { font-size: 11px; color: #4b5563; margin-top: 4px; line-height: 1.5; }
        .doc-title { text-align: right; }
        .doc-title h2 { font-size: 18px; color: #1e3a5f; margin: 0 0 6px; }
        .doc-title .doc-ref { font-size: 13px; font-weight: bold; }
        .doc-title .doc-date { font-size: 11px; color: #6b7280; }

        .parties { display: flex; gap: 24px; margin-bottom: 20px; }
        .party-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 14px; }
        .party-box h4 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; }
        .party-box p { margin: 0; font-size: 12px; line-height: 1.6; }

        h3 { font-size: 13px; color: #1e3a5f; margin: 20px 0 8px; border-left: 3px solid #1e3a5f; padding-left: 8px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead th { background: #1e3a5f; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
        thead th.right { text-align: right; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody tr:nth-child(even) td { background: #f9fafb; }

        .totals { display: flex; justify-content: flex-end; margin: 10px 0 24px; }
        .totals-box { width: 280px; border: 1px solid #e5e7eb; border-radius: 4px; overflow: hidden; }
        .totals-box .row { display: flex; justify-content: space-between; padding: 6px 12px; font-size: 12px; border-bottom: 1px solid #f3f4f6; }
        .totals-box .row.total { border-bottom: none; background: #1e3a5f; color: #fff; font-weight: bold; font-size: 13px; }
        .totals-box .row.paid { background: #f0fdf4; color: #15803d; }
        .totals-box .row.remaining { background: #fef2f2; color: #b91c1c; }

        .badge-paid { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 10px; background: #dcfce7; color: #15803d; font-weight: bold; }
        .badge-partial { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 10px; background: #fef9c3; color: #854d0e; font-weight: bold; }
        .badge-unpaid { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 10px; background: #fee2e2; color: #b91c1c; font-weight: bold; }

        .footer { border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 10px; color: #9ca3af; text-align: center; margin-top: 24px; }
        .signatures { display: flex; gap: 40px; margin-top: 28px; }
        .sig { flex: 1; border-top: 1px solid #d1d5db; padding-top: 6px; text-align: center; font-size: 11px; color: #4b5563; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:right; margin-bottom:12px;">
        <button onclick="window.print()" style="padding:6px 16px; background:#1e3a5f; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px;">Imprimer</button>
        <button onclick="window.close()" style="padding:6px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:4px; cursor:pointer; font-size:12px; margin-left:6px;">Fermer</button>
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
            <h2>FACTURE</h2>
            <div class="doc-ref">Réf: {{ $order->reference }}</div>
            <div class="doc-date">Date: {{ $order->created_at->format('d/m/Y') }}</div>
            <div style="margin-top:4px;">
                @if($order->payment_status === 'paid')
                    <span class="badge-paid">Payé</span>
                @elseif($order->payment_status === 'partial')
                    <span class="badge-partial">Partiel</span>
                @else
                    <span class="badge-unpaid">Non payé</span>
                @endif
            </div>
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

    <!-- Items commandés -->
    <h3>Articles commandés</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produit</th>
                <th>Conditionnement</th>
                <th class="right">Qté commandée</th>
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
                <td class="right">{{ number_format($item->quantity, 2, ',', ' ') }} {{ $item->product->unit->abbreviation ?? '' }}</td>
                <td class="right">{{ number_format($item->price, 2, ',', ' ') }}</td>
                <td class="right">{{ number_format($item->total, 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Livraisons -->
    @if($order->goodsReceipts->count())
    <h3>Livraisons reçues</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Produit</th>
                <th class="right">Quantité reçue</th>
            </tr>
        </thead>
        <tbody>
            @php $receiptRow = 1; @endphp
            @foreach($order->goodsReceipts as $receipt)
                @foreach($receipt->items as $item)
                <tr>
                    <td>{{ $receiptRow++ }}</td>
                    <td>{{ $receipt->received_at ? \Carbon\Carbon::parse($receipt->received_at)->format('d/m/Y') : $receipt->created_at->format('d/m/Y') }}</td>
                    <td>{{ $item->product->name ?? '-' }}</td>
                    <td class="right">{{ number_format($item->quantity_received ?? $item->quantity, 2, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Paiements -->
    @if($order->supplierPayments->count())
    <h3>Paiements effectués</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Mode de paiement</th>
                <th class="right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->supplierPayments as $i => $payment)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y') : $payment->created_at->format('d/m/Y') }}</td>
                <td>{{ $payment->paymentType->name ?? '-' }}</td>
                <td class="right">{{ number_format($payment->amount, 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Totals -->
    <div class="totals">
        <div class="totals-box">
            <div class="row total"><span>Montant total</span><span>{{ number_format($order->total_amount, 2, ',', ' ') }}</span></div>
            <div class="row paid"><span>Total payé</span><span>{{ number_format($order->paid_amount, 2, ',', ' ') }}</span></div>
            <div class="row remaining"><span>Reste à payer</span><span>{{ number_format($order->remaining_amount, 2, ',', ' ') }}</span></div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig">Signature Acheteur</div>
        <div class="sig">Cachet &amp; Signature Fournisseur</div>
    </div>

    <div class="footer">Document généré le {{ now()->format('d/m/Y à H:i') }} · {{ $company['name'] ?? '' }}</div>

    <script>window.onload = function () { window.print(); };</script>
</body>
</html>
