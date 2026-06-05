<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de transfert {{ $transfer->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; background: white; }
        .page { max-width: 800px; margin: 0 auto; padding: 30px; }

        /* En-tête */
        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #1e293b; margin-bottom: 20px; }
        .company-logo { font-size: 22px; font-weight: 900; color: #1e293b; letter-spacing: -0.5px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 18px; font-weight: 900; color: #1e293b; text-transform: uppercase; }
        .doc-title .ref { font-size: 14px; color: #6366f1; font-weight: 700; margin-top: 4px; }
        .doc-title .date { font-size: 11px; color: #64748b; margin-top: 2px; }

        /* Parties */
        .parties-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        .party-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .party-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; font-weight: 700; margin-bottom: 5px; }
        .party-name { font-size: 13px; font-weight: 700; color: #1e293b; }
        .party-sub { font-size: 10px; color: #64748b; margin-top: 2px; }

        /* Table articles */
        .table-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #475569; margin-bottom: 8px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table thead th { background: #1e293b; color: white; padding: 8px 10px; text-align: left; font-size: 11px; font-weight: 700; }
        table thead th:last-child { text-align: right; }
        table tbody tr:nth-child(even) { background: #f8fafc; }
        table tbody td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; vertical-align: middle; }
        .badge-contract { background: #dbeafe; color: #1d4ed8; padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .badge-extra { background: #d1fae5; color: #065f46; padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 700; }

        /* Notes */
        .notes-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 12px; margin-bottom: 20px; font-size: 11px; color: #92400e; }

        /* Zone signatures */
        .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 24px; padding-top: 16px; border-top: 2px solid #e2e8f0; }
        .sig-box { text-align: center; }
        .sig-label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #475569; margin-bottom: 6px; }
        .sig-role { font-size: 11px; color: #1e293b; font-weight: 600; margin-bottom: 60px; }
        .sig-line { border-bottom: 1px solid #1e293b; margin-bottom: 4px; }
        .sig-name { font-size: 10px; color: #94a3b8; }

        /* Watermark statut */
        .status-stamp { position: fixed; top: 40%; left: 50%; transform: translate(-50%,-50%) rotate(-30deg); font-size: 72px; font-weight: 900; opacity: 0.06; color: #16a34a; pointer-events: none; }

        /* Print */
        @media print {
            .no-print { display: none; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="page">

    @if($transfer->status === 'validated')
        <div class="status-stamp">VALIDÉ</div>
    @endif

    {{-- En-tête --}}
    <div class="doc-header">
        <div>
            <div class="company-logo">Complex Royal</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;">Production → Point de vente catering</div>
        </div>
        <div class="doc-title">
            <h1>Bon de transfert</h1>
            <div class="ref">{{ $transfer->reference }}</div>
            <div class="date">{{ $transfer->transfer_date->format('d/m/Y') }}</div>
        </div>
    </div>

    {{-- Parties --}}
    <div class="parties-grid">
        <div class="party-box">
            <div class="party-label">De (Production)</div>
            <div class="party-name">{{ $transfer->fromStock->name ?? '—' }}</div>
            <div class="party-sub">Préparé par : {{ $transfer->preparedBy?->name ?? '—' }}</div>
        </div>
        <div class="party-box">
            <div class="party-label">Client</div>
            <div class="party-name">{{ $transfer->client->name }}</div>
            @if($transfer->cateringContract)
                <div class="party-sub">Contrat du {{ $transfer->cateringContract->start_date->format('d/m/Y') }}</div>
            @endif
        </div>
        <div class="party-box">
            <div class="party-label">Destination (Terminal POS)</div>
            @if($transfer->posTerminal)
                <div class="party-name">{{ $transfer->posTerminal->label }}</div>
                <div class="party-sub">{{ $transfer->posTerminal->typeLabel }}</div>
                @if($transfer->driver_name)
                    <div class="party-sub" style="margin-top:4px;">Chauffeur : {{ $transfer->driver_name }}</div>
                @endif
            @elseif($transfer->cashRegister)
                <div class="party-name">{{ $transfer->cashRegister->label ?? 'Caisse #'.$transfer->cashRegister->id }}</div>
                <div class="party-sub">{{ $transfer->cashRegister->user?->name ?? '—' }}</div>
                @if($transfer->driver_name)
                    <div class="party-sub" style="margin-top:4px;">Chauffeur : {{ $transfer->driver_name }}</div>
                @endif
            @else
                <div class="party-name">En attente d'assignation</div>
                @if($transfer->driver_name)
                    <div class="party-sub">Chauffeur : {{ $transfer->driver_name }}</div>
                @endif
            @endif
        </div>
    </div>

    {{-- Articles contrat --}}
    @php $contractItems = $transfer->items->where('item_type', 'contract'); @endphp
    @if($contractItems->count())
    <div class="table-title">Plats du contrat (inclus — non facturés)</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Désignation</th>
                <th>Type</th>
                <th>Emballage</th>
                <th>Quantité</th>
                <th>Unité</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contractItems as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td style="font-weight:600;">{{ $item->label }}</td>
                <td><span class="badge-contract">Contrat</span></td>
                <td>{{ $item->packaging?->name ?? '—' }}</td>
                <td style="font-weight:700;">{{ $item->quantity }}</td>
                <td>{{ $item->unit ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Articles extras --}}
    @php $extraItems = $transfer->items->where('item_type', 'extra'); @endphp
    @if($extraItems->count())
    <div class="table-title">Produits hors contrat (à vendre)</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Désignation</th>
                <th>Emballage</th>
                <th>Quantité</th>
                <th>Unité</th>
                <th>Prix unit.</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($extraItems as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td style="font-weight:600;">{{ $item->label }} <span class="badge-extra">Vente</span></td>
                <td>{{ $item->packaging?->name ?? '—' }}</td>
                <td style="font-weight:700;">{{ $item->quantity }}</td>
                <td>{{ $item->unit ?? '—' }}</td>
                <td>{{ number_format($item->unit_price, 0) }}</td>
                <td style="text-align:right;font-weight:700;">{{ number_format($item->quantity * $item->unit_price, 0) }} MRU</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f0fdf4;">
                <td colspan="6" style="font-weight:700;padding:8px 10px;text-align:right;">Total estimé extras :</td>
                <td style="font-weight:900;font-size:14px;padding:8px 10px;text-align:right;color:#16a34a;">
                    {{ number_format($extraItems->sum(fn($i) => $i->quantity * $i->unit_price), 0, ',', ' ') }} MRU
                </td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- Notes --}}
    @if($transfer->notes)
    <div class="notes-box"><strong>Notes :</strong> {{ $transfer->notes }}</div>
    @endif

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig-box">
            <div class="sig-label">Chef de production</div>
            <div class="sig-role">{{ $transfer->preparedBy?->name ?? '____________________' }}</div>
            <div class="sig-line"></div>
            <div class="sig-name">Signature &amp; date</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Chauffeur / Livreur</div>
            <div class="sig-role">{{ $transfer->driver_name ?? '____________________' }}</div>
            <div class="sig-line"></div>
            <div class="sig-name">Signature &amp; date</div>
        </div>
        @if($transfer->posTerminal)
            {{-- Caissier matin --}}
            <div class="sig-box">
                <div class="sig-label">Caissier Matin (réception)</div>
                <div class="sig-role">{{ $transfer->posTerminal->cashierMorning?->name ?? '____________________' }}</div>
                <div class="sig-line"></div>
                <div class="sig-name">Signature &amp; date de validation</div>
            </div>
        @else
            <div class="sig-box">
                <div class="sig-label">Caissier (réception)</div>
                <div class="sig-role">{{ $transfer->cashRegister->user?->name ?? '____________________' }}</div>
                <div class="sig-line"></div>
                <div class="sig-name">Signature &amp; date de validation</div>
            </div>
        @endif
    </div>

    {{-- 2e ligne de signatures si caissier soir existe --}}
    @if($transfer->posTerminal && $transfer->posTerminal->cashierEvening)
    <div class="signatures" style="margin-top:16px;border-top:1px dashed #e2e8f0;padding-top:16px;">
        <div class="sig-box" style="grid-column:3">
            <div class="sig-label">Caissier Soir (information)</div>
            <div class="sig-role">{{ $transfer->posTerminal->cashierEvening->name }}</div>
            <div class="sig-line"></div>
            <div class="sig-name">Signature &amp; date</div>
        </div>
    </div>
    @endif

    {{-- Pied de page --}}
    <div style="text-align:center;font-size:10px;color:#94a3b8;margin-top:24px;padding-top:12px;border-top:1px solid #e2e8f0;">
        Imprimé le {{ now()->format('d/m/Y à H:i') }} — Complex Royal © {{ date('Y') }}
    </div>
</div>

<script>window.onload = () => window.print();</script>
</body>
</html>
