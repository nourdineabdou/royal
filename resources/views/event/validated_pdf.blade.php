<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dossier Événement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; }
        h1, h2, h3 { color: #2c3e50; margin-bottom: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { height: 70px; }
        .info-table, .details-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .info-table td { padding: 4px 8px; }
        .details-table th, .details-table td { border: 1px solid #ccc; padding: 6px 8px; }
        .details-table th { background: #f5f5f5; }
        .totaux { margin-top: 20px; font-size: 15px; }
        .signature { margin-top: 40px; }
        .signature-block { border-top: 1px solid #333; width: 250px; margin-top: 40px; text-align: center; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('royal_complex_groupe.png') }}" class="logo" alt="Logo Royal Complex">
        <h1>Royal Complex - Dossier Événement</h1>
        <div>Contact : 00 222 XX XX XX XX · BP 12345 Nouakchott</div>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Client :</strong> {{ $event->client->name ?? '-' }}</td>
            <td><strong>Téléphone :</strong> {{ $event->client->phone ?? '-' }}</td>
            <td><strong>Email :</strong> {{ $event->client->email ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Date événement :</strong> {{ $event->event_date ? $event->event_date->format('d/m/Y') : '-' }}</td>
            <td><strong>Type :</strong> {{ $event->event_type }}</td>
            <td><strong>Statut :</strong> {{ $event->status_label ?? $event->status }}</td>
        </tr>
        <tr>
            <td><strong>Nombre invités :</strong> {{ $event->guest_count }}</td>
            <td><strong>Stock :</strong> {{ $event->stock?->name ?? '-' }}</td>
            <td></td>
        </tr>
    </table>

    <h2>Services</h2>
    <table class="details-table">
        <tr>
            <th>Nom</th>
            <th>Quantité</th>
            <th>Prix unitaire</th>
            <th>Total</th>
        </tr>
        @php $servicesTotal = 0; @endphp
        @foreach($event->serviceItems as $item)
            <tr>
                <td>{{ $item->service?->name ?? '-' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 0, ',', ' ') }} MRU</td>
                <td>{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} MRU</td>
            </tr>
            @php $servicesTotal += $item->price * $item->quantity; @endphp
        @endforeach
        @if($servicesTotal == 0)
            <tr><td colspan="4" style="text-align:center;">Aucun service</td></tr>
        @endif
    </table>

    <h2>Repas</h2>
    <table class="details-table">
        <tr>
            <th>Type</th>
            <th>Nombre de convives</th>
            <th>Détails</th>
        </tr>
        @php $mealsTotal = 0; @endphp
        @foreach($event->eventMeals as $em)
            <tr>
                <td>{{ ucfirst($em->type) }}</td>
                <td>{{ $em->guest_count }}</td>
                <td>
                    @foreach($em->items as $mi)
                        {{ $mi->meal?->name ?? '-' }}<br>
                    @endforeach
                </td>
            </tr>
            @php $mealsTotal += (int)$em->guest_count * (float)$em->items->sum(fn($mi) => (float)($mi->meal?->price ?? 0)); @endphp
        @endforeach
        @if(count($event->eventMeals) == 0)
            <tr><td colspan="3" style="text-align:center;">Aucun repas</td></tr>
        @endif
    </table>

    <h2>Options</h2>
    <table class="details-table">
        <tr>
            <th>Nom</th>
            <th>Quantité</th>
            <th>Prix unitaire</th>
            <th>Total</th>
        </tr>
        @php $optionsTotal = 0; @endphp
        @foreach($event->options as $opt)
            <tr>
                <td>{{ $opt->name }}</td>
                <td>{{ $opt->quantity }}</td>
                <td>{{ number_format($opt->price, 0, ',', ' ') }} MRU</td>
                <td>{{ number_format($opt->price * $opt->quantity, 0, ',', ' ') }} MRU</td>
            </tr>
            @php $optionsTotal += $opt->price * $opt->quantity; @endphp
        @endforeach
        @if($optionsTotal == 0)
            <tr><td colspan="4" style="text-align:center;">Aucune option</td></tr>
        @endif
    </table>

    <div class="totaux">
        <strong>Total services :</strong> {{ number_format($servicesTotal, 0, ',', ' ') }} MRU<br>
        <strong>Total repas :</strong> {{ number_format($mealsTotal, 0, ',', ' ') }} MRU<br>
        <strong>Total options :</strong> {{ number_format($optionsTotal, 0, ',', ' ') }} MRU<br>
        <strong>Total général :</strong> {{ number_format($event->total_amount, 0, ',', ' ') }} MRU
    </div>

    <div class="signature">
        <div class="signature-block">Signature du client</div>
        <div class="signature-block" style="float:right;">Visa Royal Complex</div>
    </div>
</body>
</html>
