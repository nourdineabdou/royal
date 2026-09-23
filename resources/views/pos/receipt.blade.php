<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ticket #{{ $order->id }}</title>
<style>
    /* ── Reset ─────────────────────────────────── */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Courier New', Courier, monospace;
        font-size: 12px;
        color: #000;
        background: #fff;
        width: 80mm;          /* cible imprimante 80 mm */
        min-width: 58mm;
        padding: 6mm 4mm;
    }

    /* ── En-tête société ───────────────────────── */
    .header {
        text-align: center;
        margin-bottom: 6px;
    }
    .header img {
        max-width: 50mm;
        max-height: 20mm;
        object-fit: contain;
        display: block;
        margin: 0 auto 4px;
    }
    .header .company-name {
        font-size: 16px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .header .company-sub {
        font-size: 11px;
        color: #333;
        margin-top: 2px;
        line-height: 1.5;
    }

    /* ── Séparateurs ───────────────────────────── */
    .sep {
        border: none;
        border-top: 1px dashed #555;
        margin: 6px 0;
    }
    .sep-solid {
        border: none;
        border-top: 1px solid #000;
        margin: 6px 0;
    }

    /* ── Infos commande ────────────────────────── */
    .order-info {
        margin-bottom: 4px;
    }
    .order-info .row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px;
        font-size: 11px;
    }
    .order-info .row .label { color: #555; }
    .order-info .row .val   { font-weight: bold; }

    /* ── Lignes articles ───────────────────────── */
    .items-header {
        display: flex;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        color: #555;
        margin-bottom: 3px;
    }
    .item-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 3px;
        font-size: 11px;
        line-height: 1.4;
    }
    .item-name  { flex: 1;    padding-right: 4px; word-break: break-word; }
    .item-qty   { width: 28px; text-align: center; flex-shrink: 0; }
    .item-price { width: 38px; text-align: right;  flex-shrink: 0; }
    .item-total { width: 42px; text-align: right;  flex-shrink: 0; font-weight: bold; }

    /* ── Totaux ────────────────────────────────── */
    .totals { margin-top: 4px; }
    .totals .row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px;
        font-size: 11px;
    }
    .totals .grand-total {
        display: flex;
        justify-content: space-between;
        font-size: 15px;
        font-weight: bold;
        margin-top: 4px;
    }

    /* ── Monnaie rendue ─────────────────────────── */
    .change-block {
        margin-top: 4px;
        font-size: 11px;
    }
    .change-block .row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px;
    }

    /* ── Pied de ticket ─────────────────────────── */
    .footer {
        text-align: center;
        margin-top: 8px;
        font-size: 11px;
        color: #333;
        line-height: 1.6;
    }
    .footer .thanks {
        font-size: 13px;
        font-weight: bold;
        color: #000;
        margin-bottom: 3px;
    }

    /* ── Actions (écran uniquement) ─────────────── */
    .screen-actions {
        margin-top: 12px;
        text-align: center;
        display: flex;
        gap: 8px;
        justify-content: center;
    }
    .btn-print {
        background: #1d4ed8;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
        font-family: sans-serif;
    }
    .btn-close {
        background: #6b7280;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
        font-family: sans-serif;
    }

    /* ── Impression : masquer les boutons ───────── */
    @media print {
        .screen-actions { display: none !important; }
        body { width: 80mm; padding: 4mm 3mm; }
    }
</style>
</head>
<body>

{{-- ══ EN-TÊTE SOCIÉTÉ ══════════════════════════════════════════ --}}
<div class="header">
    @if($company['logo'])
        <img src="{{ asset($company['logo']) }}" alt="Logo">
    @endif
    <div class="company-name">{{ $company['name'] }}</div>
    <div class="company-sub">
        {{ $company['address'] }}<br>
        Tél : {{ $company['phone'] }}
    </div>
</div>

<hr class="sep-solid">

{{-- ══ INFOS COMMANDE ══════════════════════════════════════════ --}}
<div class="order-info">
    <div class="row">
        <span class="label">Ticket N°</span>
        <span class="val">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
    </div>
    <div class="row">
        <span class="label">Date</span>
        <span class="val">{{ $order->paid_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</span>
    </div>
    @if($order->customer_number)
    <div class="row">
        <span class="label">Table</span>
        <span class="val">{{ $order->customer_number }}</span>
    </div>
    @endif
    @if($order->server)
    <div class="row">
        <span class="label">Serveur</span>
        <span class="val">{{ $order->server->name }}</span>
    </div>
    @endif
    @if($order->payment?->paymentType)
    <div class="row">
        <span class="label">Paiement</span>
        <span class="val">{{ $order->payment->paymentType->name }}</span>
    </div>
    @endif
</div>

<hr class="sep">

{{-- ══ EN-TÊTE COLONNES ════════════════════════════════════════ --}}
<div class="items-header">
    <span class="item-name">Article</span>
    <span class="item-qty">Qté</span>
    <span class="item-price">P.U</span>
    <span class="item-total">Total</span>
</div>
<hr class="sep">

{{-- ══ LIGNES ARTICLES ══════════════════════════════════════════ --}}
@foreach($order->items as $item)
<div class="item-row">
    <span class="item-name">{{ $item->meal->name ?? $item->product->name ?? '—' }}</span>
    <span class="item-qty">{{ $item->quantity }}</span>
    <span class="item-price">{{ number_format($item->price, 0, ',', ' ') }}</span>
    <span class="item-total">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }}</span>
</div>
@endforeach

<hr class="sep-solid">

{{-- ══ TOTAUX ══════════════════════════════════════════════════ --}}
<div class="totals">
    <div class="grand-total">
        <span>TOTAL</span>
        <span>{{ number_format($order->total_amount, 0, ',', ' ') }} MRU</span>
    </div>

    @if($change > 0)
    <hr class="sep">
    <div class="change-block">
        <div class="row">
            <span>Reçu</span>
            <span>{{ number_format($received, 0, ',', ' ') }} MRU</span>
        </div>
        <div class="row" style="font-weight:bold;">
            <span>Monnaie rendue</span>
            <span>{{ number_format($change, 0, ',', ' ') }} MRU</span>
        </div>
    </div>
    @endif
</div>

<hr class="sep">

{{-- ══ PIED DE TICKET ══════════════════════════════════════════ --}}
<div class="footer">
    <div class="thanks">Merci de votre visite !</div>
    <div>{{ $company['name'] }} vous souhaite</div>
    <div>une excellente journée.</div>
</div>

{{-- ══ BOUTONS ÉCRAN ════════════════════════════════════════════ --}}
<div class="screen-actions">
    <button class="btn-print" onclick="window.print()">🖨 Imprimer</button>
    <button class="btn-close" onclick="window.close()">✕ Fermer</button>
</div>

<script>
    // Auto-impression à l'ouverture de la fenêtre
    window.addEventListener('load', function () {
        setTimeout(() => window.print(), 300);
    });
</script>
</body>
</html>
