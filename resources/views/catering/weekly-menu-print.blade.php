<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Menu de la semaine — {{ $contract->client->name ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 0; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; border-bottom: 2px solid #0f766e; padding-bottom: 10px; }
        .company-name { font-size: 18px; font-weight: bold; color: #0f766e; }
        .doc-title h2 { font-size: 20px; color: #0f766e; margin: 0 0 4px; }
        .doc-title .client-name { font-size: 14px; font-weight: bold; }
        .doc-title .week-range { font-size: 11px; color: #6b7280; }

        table { width: 100%; border-collapse: collapse; }
        thead th { background: #0f766e; color: #fff; padding: 8px; text-align: left; font-size: 11px; }
        tbody td { padding: 8px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: top; }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        .day-cell { font-weight: bold; background: #f0fdfa !important; white-space: nowrap; }
        .meal-list { margin: 0 0 4px; padding: 0; list-style: none; }
        .meal-list li { margin-bottom: 2px; }
        .qty { display: inline-block; margin-top: 2px; font-size: 10px; font-weight: bold; color: #0f766e; background: #ccfbf1; padding: 1px 6px; border-radius: 99px; }
        .empty { color: #cbd5e1; font-style: italic; }

        .footer { margin-top: 16px; font-size: 10px; color: #9ca3af; text-align: center; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:right; margin-bottom:12px;">
        <button onclick="window.print()" style="padding:6px 16px; background:#0f766e; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
            Imprimer
        </button>
        <button onclick="window.close()" style="padding:6px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:4px; cursor:pointer; font-size:12px; margin-left:6px;">
            Fermer
        </button>
    </div>

    <div class="header">
        <div>
            <div class="company-name">{{ $company['name'] ?? 'Entreprise' }}</div>
        </div>
        <div class="doc-title">
            <h2>MENU HEBDOMADAIRE — CATERING</h2>
            <div class="client-name">{{ $contract->client->name ?? '—' }}</div>
            <div class="week-range">Semaine du {{ $weekStart->format('d/m/Y') }} au {{ $weekStart->copy()->addDays(6)->format('d/m/Y') }}</div>
        </div>
    </div>

    @php
        $dayNames = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
        $typeLabels = ['breakfast' => 'Petit-déjeuner', 'lunch' => 'Déjeuner', 'dinner' => 'Dîner'];
        $days = collect(range(0,6))->map(fn($i) => $weekStart->copy()->addDays($i));
        $menuDaysByDate = $menu ? $menu->days->keyBy(fn($d) => $d->date->toDateString()) : collect();
    @endphp

    @if(!$menu)
    <p style="text-align:center;color:#9ca3af;padding:40px 0;">Aucun menu programmé pour cette semaine.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>Jour</th>
                @foreach($typeLabels as $label)
                <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($days as $date)
            @php $menuDay = $menuDaysByDate->get($date->toDateString()); @endphp
            <tr>
                <td class="day-cell">{{ $dayNames[$date->dayOfWeekIso] }}<br>{{ $date->format('d/m/Y') }}</td>
                @foreach(array_keys($typeLabels) as $type)
                    @php $meal = $menuDay?->meals->firstWhere('type', $type); @endphp
                    <td>
                        @if($meal && $meal->items->isNotEmpty())
                            <ul class="meal-list">
                                @foreach($meal->items as $item)
                                <li>{{ $item->meal->name ?? '—' }}</li>
                                @endforeach
                            </ul>
                            <span class="qty">{{ $meal->quantity }} repas</span>
                        @else
                            <span class="empty">—</span>
                        @endif
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} · {{ $company['name'] ?? '' }}
    </div>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
