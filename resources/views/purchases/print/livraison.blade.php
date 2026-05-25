<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de Livraison — {{ $order->reference }}</title>
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

        .info-grid { display: flex; gap: 24px; margin-bottom: 20px; }
        .info-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 14px; }
        .info-box h4 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; }
        .info-box .val { font-size: 13px; font-weight: bold; color: #1e3a5f; }
        .info-box .sub { font-size: 11px; color: #6b7280; margin-top: 2px; }

        h3 { font-size: 13px; color: #1e3a5f; margin: 20px 0 8px; border-left: 3px solid #1e3a5f; padding-left: 8px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #1e3a5f; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
        thead th.right { text-align: right; }
        tbody td { padding: 7px 8px; border-bottom: 1px solid #f3f4f6; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody tr:nth-child(even) td { background: #f9fafb; }

        .note-box { border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 14px; font-size: 11px; color: #4b5563; min-height: 50px; margin-bottom: 16px; }

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
            <h2>BON DE LIVRAISON</h2>
            <div class="doc-ref">Commande: {{ $order->reference }}</div>
            <div class="doc-date">
                Date livraison: {{ $receipt->received_at
                    ? \Carbon\Carbon::parse($receipt->received_at)->format('d/m/Y')
                    : $receipt->created_at->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <!-- Info boxes -->
    <div class="info-grid">
        <div class="info-box">
            <h4>Fournisseur</h4>
            <div class="val">{{ $order->supplier->name }}</div>
            @if($order->supplier->phone)<div class="sub">Tél: {{ $order->supplier->phone }}</div>@endif
        </div>
        <div class="info-box">
            <h4>Stock destinataire</h4>
            <div class="val">{{ $receipt->stock->name ?? '-' }}</div>
        </div>
        <div class="info-box">
            <h4>Date de réception</h4>
            <div class="val">
                {{ $receipt->received_at
                    ? \Carbon\Carbon::parse($receipt->received_at)->format('d/m/Y')
                    : $receipt->created_at->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <!-- Articles livrés -->
    <h3>Articles reçus</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produit</th>
                <th>Unité</th>
                <th class="right">Qté commandée</th>
                <th class="right">Qté reçue</th>
                <th>Observations</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $i => $item)
            @php
                $ordered = $order->items->firstWhere('product_id', $item->product_id);
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td>{{ $item->product->unit->abbreviation ?? '-' }}</td>
                <td class="right">{{ $ordered ? number_format($ordered->quantity, 2, ',', ' ') : '-' }}</td>
                <td class="right">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                <td>&nbsp;</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Note section -->
    <p style="font-size:11px; color:#6b7280; margin-bottom:4px;"><strong>Observations / Remarques :</strong></p>
    <div class="note-box">&nbsp;</div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig">Signature Réceptionneur</div>
        <div class="sig">Cachet &amp; Signature Livreur</div>
    </div>

    <div class="footer">Document généré le {{ now()->format('d/m/Y à H:i') }} · {{ $company['name'] ?? '' }}</div>

    <script>window.onload = function () { window.print(); };</script>
</body>
</html>
