<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande d'Achat {{ $purchaseRequest->reference }}</title>
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
        .party-box strong { font-size: 13px; }
        .fill-line { display: inline-block; border-bottom: 1px solid #9ca3af; min-width: 180px; height: 14px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #1e3a5f; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
        thead th.right { text-align: right; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody tr:nth-child(even) td { background: #f9fafb; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: bold; background: #fef3c7; color: #92400e; }

        .footer { border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 10px; color: #9ca3af; text-align: center; }
        .signatures { display: flex; gap: 40px; margin-top: 28px; }
        .sig { flex: 1; border-top: 1px solid #d1d5db; padding-top: 6px; text-align: center; font-size: 11px; color: #4b5563; }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:right; margin-bottom:12px;">
        <button onclick="window.print()" style="padding:6px 16px; background:#1e3a5f; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
            Imprimer
        </button>
        <button onclick="window.close()" style="padding:6px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:4px; cursor:pointer; font-size:12px; margin-left:6px;">
            Fermer
        </button>
    </div>

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
            <h2>DEMANDE D'ACHAT</h2>
            <div class="doc-ref">Réf: {{ $purchaseRequest->reference }}</div>
            <div class="doc-date">Date: {{ $purchaseRequest->created_at->format('d/m/Y') }}</div>
            <div style="margin-top:4px;"><span class="badge">À consulter fournisseur</span></div>
        </div>
    </div>

    <div class="parties">
        <div class="party-box">
            <h4>Acheteur</h4>
            <p><strong>{{ $company['name'] ?? 'Entreprise' }}</strong></p>
            @if(!empty($company['address']))<p>{{ $company['address'] }}</p>@endif
            @if(!empty($company['phone']))<p>Tél: {{ $company['phone'] }}</p>@endif
            <p>Demandé par : {{ $purchaseRequest->requestedBy->name ?? '—' }}</p>
        </div>
        <div class="party-box">
            <h4>Fournisseur consulté</h4>
            <p>Nom : <span class="fill-line"></span></p>
            <p style="margin-top:8px;">Contact : <span class="fill-line"></span></p>
            <p style="margin-top:8px;">Date de remise du devis : <span class="fill-line" style="min-width:120px;"></span></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produit</th>
                <th class="right">Quantité demandée</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseRequest->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td class="right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }} {{ $item->product->unit->symbol ?? '' }}</td>
                <td>{{ $item->notes ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($purchaseRequest->notes)
    <p style="font-size:11px; color:#4b5563;"><strong>Notes générales :</strong> {{ $purchaseRequest->notes }}</p>
    @endif

    <p style="font-size:11px; color:#6b7280; margin-top:16px;">
        Ce document exprime un besoin d'achat — il n'engage ni fournisseur ni prix. Merci de retourner votre devis pour les quantités ci-dessus.
    </p>

    <div class="signatures">
        <div class="sig">Signature Acheteur</div>
        <div class="sig">Cachet &amp; Signature Fournisseur</div>
    </div>

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} · {{ $company['name'] ?? '' }}
    </div>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
