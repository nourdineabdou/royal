<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de retour {{ $posReturn->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; background: white; }
        .page { max-width: 800px; margin: 0 auto; padding: 30px; }

        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #1e293b; margin-bottom: 20px; }
        .company-logo { font-size: 22px; font-weight: 900; color: #1e293b; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 18px; font-weight: 900; color: #be123c; text-transform: uppercase; }
        .doc-title .ref { font-size: 14px; color: #be123c; font-weight: 700; margin-top: 4px; }
        .doc-title .date { font-size: 11px; color: #64748b; margin-top: 2px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .info-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; font-weight: 700; margin-bottom: 4px; }
        .info-value { font-size: 13px; font-weight: 700; color: #1e293b; }
        .info-sub { font-size: 10px; color: #64748b; margin-top: 2px; }

        .table-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #be123c; margin-bottom: 8px; padding-top: 12px; border-top: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #be123c; color: white; padding: 8px 10px; text-align: left; font-size: 11px; font-weight: 700; }
        tbody tr:nth-child(even) { background: #fef2f2; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #fee2e2; font-size: 12px; }
        .badge-contract { background: #dbeafe; color: #1d4ed8; padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .badge-extra { background: #d1fae5; color: #065f46; padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 700; }

        .notes-box { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 10px 12px; margin-bottom: 20px; font-size: 11px; color: #92400e; }

        .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 24px; padding-top: 16px; border-top: 2px solid #e2e8f0; }
        .sig-box { text-align: center; }
        .sig-label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #be123c; margin-bottom: 6px; }
        .sig-role { font-size: 11px; color: #1e293b; font-weight: 600; margin-bottom: 60px; }
        .sig-line { border-bottom: 1px solid #1e293b; margin-bottom: 4px; }
        .sig-name { font-size: 10px; color: #94a3b8; }

        .alert-retour { background: #fff1f2; border: 2px solid #be123c; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; color: #be123c; font-weight: 700; font-size: 12px; }

        .no-print { margin: 16px 30px; display: flex; gap: 12px; }
        .btn { padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; }
        .btn-print { background: #1e293b; color: white; }
        .btn-back { background: #e2e8f0; color: #1e293b; text-decoration: none; }
        @media print { .no-print { display: none; } body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn btn-print" onclick="window.print()">
        <i>🖨</i> Imprimer
    </button>
    <a class="btn btn-back" href="{{ route('cashier.session', $posReturn->cash_register_id) }}">
        ← Retour à la session
    </a>
</div>

<div class="page">
    <div class="doc-header">
        <div>
            <div class="company-logo">Complex Royal</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;">Bon de retour — Point de vente catering</div>
        </div>
        <div class="doc-title">
            <h1>Bon de retour</h1>
            <div class="ref">{{ $posReturn->reference }}</div>
            <div class="date">{{ $posReturn->return_date->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="alert-retour">
        ⚠ Ce document atteste du retour de marchandises du point de vente vers la production.
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Caissier</div>
            <div class="info-value">{{ $posReturn->returnedBy?->name ?? '—' }}</div>
            <div class="info-sub">Date : {{ $posReturn->return_date->format('d/m/Y') }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Terminal POS</div>
            <div class="info-value">{{ $posReturn->terminal?->label ?? 'Caisse ordinaire' }}</div>
            @if($posReturn->terminal?->client)
                <div class="info-sub">{{ $posReturn->terminal->client->name }}</div>
            @endif
        </div>
        <div class="info-box">
            <div class="info-label">Session</div>
            <div class="info-value">{{ $posReturn->register->label ?? '#'.$posReturn->cash_register_id }}</div>
            <div class="info-sub">Ouverture : {{ $posReturn->register->opened_at?->format('d/m/Y H:i') ?? '—' }}</div>
        </div>
    </div>

    <div class="table-title">Articles retournés</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Désignation</th>
                <th>Type</th>
                <th>Quantité retournée</th>
            </tr>
        </thead>
        <tbody>
            @foreach($posReturn->items as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td style="font-weight:600;">{{ $item->label }}</td>
                <td>
                    @if($item->item_type === 'contract')
                        <span class="badge-contract">Contrat</span>
                    @else
                        <span class="badge-extra">Extra</span>
                    @endif
                </td>
                <td style="font-weight:700;font-size:14px;">{{ $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#fff1f2;">
                <td colspan="3" style="font-weight:700;padding:8px 10px;text-align:right;">Total articles retournés :</td>
                <td style="font-weight:900;font-size:14px;padding:8px 10px;color:#be123c;">
                    {{ $posReturn->items->sum('quantity') }} unités
                </td>
            </tr>
        </tfoot>
    </table>

    @if($posReturn->notes)
    <div class="notes-box"><strong>Notes :</strong> {{ $posReturn->notes }}</div>
    @endif

    <div class="signatures">
        <div class="sig-box">
            <div class="sig-label">Caissier (retournant)</div>
            <div class="sig-role">{{ $posReturn->returnedBy?->name ?? '____________________' }}</div>
            <div class="sig-line"></div>
            <div class="sig-name">Signature &amp; date</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Responsable Production</div>
            <div class="sig-role">____________________</div>
            <div class="sig-line"></div>
            <div class="sig-name">Signature &amp; date de réception</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Gestionnaire</div>
            <div class="sig-role">____________________</div>
            <div class="sig-line"></div>
            <div class="sig-name">Visa &amp; date</div>
        </div>
    </div>

    <div style="text-align:center;font-size:10px;color:#94a3b8;margin-top:24px;padding-top:12px;border-top:1px solid #e2e8f0;">
        Imprimé le {{ now()->format('d/m/Y à H:i') }} — Complex Royal © {{ date('Y') }}
    </div>
</div>

<script>window.onload = () => window.print();</script>
</body>
</html>
