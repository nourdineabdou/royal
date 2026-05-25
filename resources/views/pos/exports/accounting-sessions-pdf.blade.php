<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Sessions Comptables</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; margin: 20px; }
        h1 { margin: 0 0 8px; font-size: 18px; }
        .meta { margin-bottom: 12px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .small { font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Sessions de caisse - Export comptable</h1>
    <div class="meta">
        Généré le {{ now()->format('d/m/Y H:i') }}
        · Module: {{ $filters['module'] ?: 'Tous' }}
        · Caissier ID: {{ $filters['cashier_user_id'] ?: 'Tous' }}
        · Période: {{ $filters['from_date'] ?: '...' }} → {{ $filters['to_date'] ?: '...' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Module</th>
                <th>Caissier</th>
                <th>Poste</th>
                <th>Ouverture</th>
                <th>Fermeture</th>
                <th>Statut</th>
                <th class="right">Fond</th>
                <th class="right">Encaissé</th>
                <th class="right">Solde réel</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ ucfirst($r->module) }}</td>
                    <td>{{ $r->user?->name ?? '—' }}</td>
                    <td>{{ $r->shift === 'morning' ? 'Matin' : 'Soir' }}</td>
                    <td>{{ $r->opened_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $r->closed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>{{ $r->status }}</td>
                    <td class="right">{{ number_format($r->opening_balance, 2, ',', ' ') }}</td>
                    <td class="right">{{ number_format((float)($r->payments_sum_amount ?? 0), 2, ',', ' ') }}</td>
                    <td class="right">{{ $r->closing_balance !== null ? number_format((float)$r->closing_balance, 2, ',', ' ') : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="small">Aucune session trouvée.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
