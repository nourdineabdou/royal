<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Menus hebdomadaires — tous les clients</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 0; }

        .client-block { page-break-after: always; margin-bottom: 20px; }
        .client-block:last-child { page-break-after: auto; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; border-bottom: 2px solid #0f766e; padding-bottom: 8px; }
        .company-name { font-size: 16px; font-weight: bold; color: #0f766e; }
        .doc-title h2 { font-size: 16px; color: #0f766e; margin: 0 0 2px; }
        .doc-title .client-name { font-size: 13px; font-weight: bold; }
        .doc-title .week-range { font-size: 10px; color: #6b7280; }

        table { width: 100%; border-collapse: collapse; }
        thead th { background: #0f766e; color: #fff; padding: 6px; text-align: left; font-size: 10px; }
        tbody td { padding: 6px; border: 1px solid #e5e7eb; font-size: 10px; vertical-align: top; }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        .day-cell { font-weight: bold; background: #f0fdfa !important; white-space: nowrap; }
        .meal-list { margin: 0 0 3px; padding: 0; list-style: none; }
        .meal-list li { margin-bottom: 1px; }
        .qty { display: inline-block; margin-top: 2px; font-size: 9px; font-weight: bold; color: #0f766e; background: #ccfbf1; padding: 1px 5px; border-radius: 99px; }
        .empty { color: #cbd5e1; font-style: italic; }
        .no-menu { text-align:center;color:#9ca3af;padding:20px 0;font-size:11px; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:right; margin-bottom:12px;">
        <button onclick="window.print()" style="padding:6px 16px; background:#0f766e; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
            Imprimer tout
        </button>
        <button onclick="window.close()" style="padding:6px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:4px; cursor:pointer; font-size:12px; margin-left:6px;">
            Fermer
        </button>
    </div>

    @php
        $dayNames = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
        $typeLabels = ['breakfast' => 'Petit-déjeuner', 'lunch' => 'Déjeuner', 'dinner' => 'Dîner'];
        $days = collect(range(0,6))->map(fn($i) => $weekStart->copy()->addDays($i));
    @endphp

    @forelse($contracts as $contract)
    @php $menu = $menus->get($contract->id); $menuDaysByDate = $menu ? $menu->days->keyBy(fn($d) => $d->date->toDateString()) : collect(); @endphp
    <div class="client-block">
        <div class="header">
            <div class="company-name">{{ $company['name'] ?? 'Entreprise' }}</div>
            <div class="doc-title">
                <h2>MENU HEBDOMADAIRE — CATERING</h2>
                <div class="client-name">{{ $contract->client->name ?? '—' }}</div>
                <div class="week-range">Semaine du {{ $weekStart->format('d/m/Y') }} au {{ $weekStart->copy()->addDays(6)->format('d/m/Y') }}</div>
            </div>
        </div>

        @if(!$menu)
        <p class="no-menu">Aucun menu programmé pour cette semaine.</p>
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
    </div>
    @empty
    <p class="no-menu">Aucun contrat catering actif sur cette semaine.</p>
    @endforelse

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
