@php use App\Support\Qty; @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $kr->bs_number ?? $kr->reference }} — Bon de sortie</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b; margin: 0; padding: 24px; font-size: 13px; }
        .page { max-width: 780px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #b45309; padding-bottom: 12px; }
        h1 { margin: 0; font-size: 22px; letter-spacing: .02em; }
        .ref { font-size: 15px; font-weight: 700; color: #b45309; }
        .muted { color: #64748b; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 16px 0; }
        .box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .box small { display: block; color: #64748b; text-transform: uppercase; font-size: 10px; letter-spacing: .05em; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f8fafc; font-size: 11px; text-transform: uppercase; color: #475569; }
        td.num { text-align: right; font-variant-numeric: tabular-nums; }
        .sign { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 36px; }
        .sign div { border-top: 1px solid #94a3b8; padding-top: 6px; min-height: 70px; }
        .draft { background: #fef3c7; color: #92400e; padding: 6px 10px; border-radius: 6px; margin-top: 10px; font-weight: 600; }
        @media print { body { padding: 0; } .noprint { display: none; } }
    </style>
</head>
<body>
<div class="page">
    <p class="noprint"><button onclick="window.print()">Imprimer</button></p>
    <header>
        <div>
            <h1>BON DE SORTIE</h1>
            <div class="muted">Complex Royal · Magasin → Cuisine</div>
        </div>
        <div style="text-align:right">
            <div class="ref">{{ $kr->bs_number ?? 'BS en attente' }}</div>
            <div class="muted">Demande {{ $kr->reference }}</div>
            <div class="muted">{{ ($kr->validated_at ?? $kr->prepared_at)?->format('d/m/Y H:i') }}</div>
        </div>
    </header>

    @if($kr->status !== 'validated')
        <p class="draft">Document provisoire : la sortie n'est effective qu'après validation par le cuisinier.</p>
    @endif

    <div class="grid">
        <div class="box"><small>Cuisine</small><strong>{{ $kr->kitchen }}</strong></div>
        <div class="box"><small>Magasin</small><strong>{{ $kr->stock->name ?? '—' }}</strong></div>
        <div class="box"><small>Pour le</small><strong>{{ $kr->needed_on?->format('d/m/Y') ?? '—' }}</strong></div>
        <div class="box"><small>Demandé par</small>{{ $kr->requester->name ?? '—' }}</div>
        <div class="box"><small>Remis par (magasinier)</small>{{ $kr->preparer->name ?? '—' }}</div>
        <div class="box"><small>Reçu par (cuisinier)</small>{{ $kr->validator->name ?? $kr->receiver->name ?? '—' }}</div>
    </div>

    <table>
        <thead><tr><th>Produit</th><th>Unité</th><th style="text-align:right">Quantité demandée</th><th style="text-align:right">Quantité remise</th></tr></thead>
        <tbody>
        @foreach($kr->items as $item)
            @php $u = $item->product->unit->symbol ?? ''; @endphp
            <tr>
                <td>{{ $item->product->name ?? '—' }}</td>
                <td>{{ Qty::displayUnit($u)['label'] }}</td>
                <td class="num">{{ Qty::format($item->requested_quantity, $u) }}</td>
                <td class="num"><strong>{{ Qty::format($item->delivered_quantity ?? 0, $u) }}</strong></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @if($kr->notes)<p class="muted" style="margin-top:10px">Remarque : {{ $kr->notes }}</p>@endif

    <div class="sign">
        <div>Signature du magasinier<br><strong>{{ $kr->preparer->name ?? '' }}</strong></div>
        <div>Signature du cuisinier (réceptionnaire)<br><strong>{{ $kr->validator->name ?? $kr->receiver->name ?? '' }}</strong></div>
    </div>
</div>
</body>
</html>
