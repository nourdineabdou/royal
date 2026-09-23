<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bon Cuisine #{{ $order->id }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Courier New', Courier, monospace;
        font-size: 13px;
        color: #000;
        background: #fff;
        width: 80mm;
        min-width: 58mm;
        padding: 5mm 4mm;
    }

    /* ── En-tête ────────────────────────────────── */
    .header {
        text-align: center;
        margin-bottom: 5px;
    }
    .header .label {
        font-size: 11px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #555;
    }
    .header .company-name {
        font-size: 15px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .header .ticket-type {
        font-size: 17px;
        font-weight: bold;
        border: 2px solid #000;
        display: inline-block;
        padding: 2px 14px;
        margin-top: 5px;
        letter-spacing: 2px;
    }

    /* ── Séparateurs ────────────────────────────── */
    .sep       { border:none; border-top:1px dashed #555; margin:5px 0; }
    .sep-solid { border:none; border-top:2px solid #000;  margin:5px 0; }

    /* ── Infos commande ─────────────────────────── */
    .order-meta { margin-bottom: 4px; }
    .order-meta .row {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin-bottom: 2px;
    }
    .order-meta .row .val { font-weight: bold; }

    /* ── Numéro ordre — très visible ────────────── */
    .order-number-big {
        text-align: center;
        font-size: 36px;
        font-weight: bold;
        letter-spacing: 3px;
        line-height: 1;
        margin: 6px 0;
    }

    /* ── Lignes articles ────────────────────────── */
    .item-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 6px;
        font-size: 13px;
        line-height: 1.35;
    }
    .item-qty {
        width: 30px;
        font-size: 18px;
        font-weight: bold;
        flex-shrink: 0;
        text-align: center;
        margin-right: 6px;
    }
    .item-body { flex: 1; }
    .item-name { font-weight: bold; font-size: 14px; }
    .item-acc  { font-size: 11px; color: #444; margin-top: 2px; }

    /* ── Pied ───────────────────────────────────── */
    .footer {
        text-align: center;
        font-size: 11px;
        color: #555;
        margin-top: 6px;
    }

    /* ── Boutons écran ──────────────────────────── */
    .screen-actions {
        margin-top: 12px;
        display: flex;
        gap: 8px;
        justify-content: center;
    }
    .btn-print {
        background: #b45309; color: #fff; border: none;
        padding: 8px 18px; border-radius: 6px;
        font-size: 13px; cursor: pointer; font-family: sans-serif;
    }
    .btn-close {
        background: #6b7280; color: #fff; border: none;
        padding: 8px 18px; border-radius: 6px;
        font-size: 13px; cursor: pointer; font-family: sans-serif;
    }

    @media print {
        .screen-actions { display: none !important; }
        body { width: 80mm; padding: 3mm 3mm; }
    }
</style>
</head>
<body>

{{-- ══ EN-TÊTE ════════════════════════════════════════════════ --}}
<div class="header">
    <div class="label">{{ $company['name'] }}</div>
    <div class="ticket-type">⚑ BON CUISINE</div>
</div>

<hr class="sep-solid">

{{-- ══ NUMÉRO COMMANDE BIEN VISIBLE ═══════════════════════════ --}}
<div class="order-number-big">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</div>

<hr class="sep">

{{-- ══ INFO TABLE / HEURE ══════════════════════════════════════ --}}
<div class="order-meta">
    <div class="row">
        <span>Heure</span>
        <span class="val">{{ $order->created_at->format('H:i') }}</span>
    </div>
    @if($order->customer_number)
    <div class="row">
        <span>Table</span>
        <span class="val" style="font-size:16px;">{{ $order->customer_number }}</span>
    </div>
    @endif
    @if($order->server)
    <div class="row">
        <span>Serveur</span>
        <span class="val">{{ $order->server->name }}</span>
    </div>
    @endif
</div>

<hr class="sep-solid">

{{-- ══ ARTICLES (SANS PRIX) ════════════════════════════════════ --}}
{{-- Les extras vendus tels quels (boissons...) n'ont pas besoin d'être préparés en cuisine --}}
@foreach($order->items->whereNotNull('meal_id') as $item)
<div class="item-row">
    <span class="item-qty">{{ $item->quantity }}×</span>
    <div class="item-body">
        <div class="item-name">{{ $item->meal->name ?? '—' }}</div>
        @if($item->accompaniments && $item->accompaniments->count())
        <div class="item-acc">
            + {{ $item->accompaniments->pluck('name')->join(', ') }}
        </div>
        @endif
    </div>
</div>
@endforeach

<hr class="sep-solid">

{{-- ══ PIED ════════════════════════════════════════════════════ --}}
<div class="footer">
    Imprimé le {{ now()->format('d/m/Y à H:i') }}
</div>

{{-- ══ BOUTONS ÉCRAN ══════════════════════════════════════════ --}}
<div class="screen-actions">
    <button class="btn-print" onclick="window.print()">🖨 Imprimer</button>
    <button class="btn-close" onclick="window.close()">✕ Fermer</button>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(() => window.print(), 300);
    });
</script>
</body>
</html>
