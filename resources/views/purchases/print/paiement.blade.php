<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de Paiement — {{ $order->reference }}</title>
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

        .receipt-card { border: 2px solid #1e3a5f; border-radius: 6px; padding: 20px 24px; max-width: 520px; margin: 0 auto 24px; }
        .receipt-card .amount { font-size: 32px; font-weight: bold; color: #1e3a5f; text-align: center; margin: 12px 0; }
        .receipt-card .amount-label { text-align: center; font-size: 11px; color: #6b7280; margin-bottom: 16px; }

        .detail-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 12px; }
        .detail-row:last-child { border-bottom: none; }
        .detail-row .label { color: #6b7280; }
        .detail-row .value { font-weight: 500; }

        h3 { font-size: 13px; color: #1e3a5f; margin: 24px 0 8px; border-left: 3px solid #1e3a5f; padding-left: 8px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead th { background: #1e3a5f; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
        thead th.right { text-align: right; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody tr:nth-child(even) td { background: #f9fafb; }

        .totals-box { width: 280px; margin-left: auto; border: 1px solid #e5e7eb; border-radius: 4px; overflow: hidden; }
        .totals-box .row { display: flex; justify-content: space-between; padding: 6px 12px; font-size: 12px; border-bottom: 1px solid #f3f4f6; }
        .totals-box .row:last-child { border-bottom: none; background: #1e3a5f; color: #fff; font-weight: bold; }

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
            <h2>REÇU DE PAIEMENT</h2>
            <div class="doc-ref">Commande: {{ $order->reference }}</div>
            <div class="doc-date">
                Date: {{ $payment->paid_at
                    ? \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y')
                    : $payment->created_at->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <!-- Receipt main card -->
    <div class="receipt-card">
        <div class="amount">{{ number_format($payment->amount, 2, ',', ' ') }} MRU</div>
        <div class="amount-label">Montant payé</div>

        <div class="detail-row">
            <span class="label">Fournisseur</span>
            <span class="value">{{ $order->supplier->name }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Référence commande</span>
            <span class="value">{{ $order->reference }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Mode de paiement</span>
            <span class="value">{{ $payment->paymentType->name ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Date de paiement</span>
            <span class="value">
                {{ $payment->paid_at
                    ? \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y')
                    : $payment->created_at->format('d/m/Y') }}
            </span>
        </div>
    </div>

    <!-- Récapitulatif de la commande -->
    <h3>Récapitulatif des paiements de la commande</h3>
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
            @foreach($order->supplierPayments as $i => $p)
            <tr @if($p->id === $payment->id) style="background:#fffbeb; font-weight:bold;" @endif>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->paid_at ? \Carbon\Carbon::parse($p->paid_at)->format('d/m/Y') : $p->created_at->format('d/m/Y') }}</td>
                <td>{{ $p->paymentType->name ?? '-' }}</td>
                <td class="right">{{ number_format($p->amount, 2, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary totals -->
    <div class="totals-box" style="margin-bottom:24px;">
        <div class="row"><span>Total commande</span><span>{{ number_format($order->total_amount, 2, ',', ' ') }}</span></div>
        <div class="row"><span>Total payé</span><span>{{ number_format($order->paid_amount, 2, ',', ' ') }}</span></div>
        <div class="row"><span>Reste à payer</span><span>{{ number_format($order->remaining_amount, 2, ',', ' ') }}</span></div>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig">Signature Payeur</div>
        <div class="sig">Cachet &amp; Signature Fournisseur</div>
    </div>

    <div class="footer">Document généré le {{ now()->format('d/m/Y à H:i') }} · {{ $company['name'] ?? '' }}</div>

    <script>window.onload = function () { window.print(); };</script>
</body>
</html>
