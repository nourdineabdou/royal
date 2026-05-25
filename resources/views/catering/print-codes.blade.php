<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codes â€” {{ $meal->type_label }} Â· {{ $meal->menuDay->date->format('d/m/Y') ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: white; color: #1e293b; font-size: 14px; }

        /* Screen-only header */
        .no-print {
            background: #f0fdf4; padding: 12px 20px;
            border-bottom: 2px solid #14b8a6;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .no-print .info { display: flex; align-items: center; gap: 12px; }
        .no-print h1 { font-size: 1rem; font-weight: 700; color: #0f766e; }
        .no-print p  { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
        .no-print .actions { display: flex; gap: 8px; }
        .btn-print { background: #0f766e; color: white; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; font-size: 0.875rem; font-weight: 600; }
        .btn-back  { background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 0.875rem; text-decoration: none; display: inline-block; }

        /* Print header */
        .print-header { padding: 20px 20px 16px; border-bottom: 2px solid #0f766e; page-break-inside: avoid; }
        .print-header .title { font-size: 1.3rem; font-weight: 800; color: #0f766e; }
        .print-header .meta  { font-size: 0.8rem; color: #64748b; margin-top: 4px; }
        .print-header .dishes { margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap; }
        .print-header .dish-tag { background: #e0f2fe; color: #0369a1; padding: 2px 10px; border-radius: 20px; font-size: 0.72rem; }

        /* Code cards grid */
        .codes-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; padding: 16px 20px; }
        .code-card {
            border: 2px dashed #94a3b8; border-radius: 10px; padding: 12px 8px;
            text-align: center; page-break-inside: avoid;
        }
        .code-card.used { opacity: 0.4; }
        .code-card .num { font-size: 0.6rem; color: #94a3b8; margin-bottom: 4px; }
        .code-card .code-val { font-family: 'Courier New', monospace; font-size: 1.05rem; font-weight: 800; letter-spacing: 1px; color: #0f172a; }
        .code-card .meal-type { font-size: 0.65rem; color: #64748b; margin-top: 4px; }
        .code-card .used-badge { font-size: 0.6rem; color: #ef4444; font-weight: 700; margin-top: 2px; }

        /* Footer stats */
        .stats { padding: 12px 20px; border-top: 1px solid #e2e8f0; font-size: 0.8rem; color: #64748b; }

        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px; }
            .codes-grid { grid-template-columns: repeat(5, 1fr); gap: 8px; padding: 10px 12px; }
            .code-card { padding: 8px 4px; border-radius: 6px; }
            .code-card .code-val { font-size: 0.9rem; }
            .print-header { padding: 12px 12px 10px; }
            .stats { padding: 8px 12px; }
        }
    </style>
</head>
<body>

{{-- Screen-only toolbar --}}
<div class="no-print">
    <div class="info">
        <div>
            <h1>{{ $meal->type_icon }} {{ $meal->type_label }} â€” {{ $meal->menuDay->date->format('d/m/Y') ?? '' }}</h1>
            <p>{{ $meal->menuDay->weeklyMenu->contract->client->name ?? 'â€”' }}
               Â· {{ $meal->codes->count() }} code(s)</p>
        </div>
    </div>
    <div class="actions">
        <a href="{{ url()->previous() }}" class="btn-back">â† Retour</a>
        <button class="btn-print" onclick="window.print()">
            ðŸ–¨ï¸ Imprimer
        </button>
    </div>
</div>

{{-- Print header --}}
<div class="print-header">
    <div class="title">
        {{ $meal->type_icon }} {{ $meal->type_label }} â€” {{ $meal->menuDay->date->format('l d/m/Y') ?? '' }}
    </div>
    <div class="meta">
        Client : <strong>{{ $meal->menuDay->weeklyMenu->contract->client->name ?? 'â€”' }}</strong>
        Â· {{ $meal->codes->count() }} codes gÃ©nÃ©rÃ©s
        Â· {{ $meal->codes->where('is_used', true)->count() }} utilisÃ©s
    </div>
    @if($meal->items->count() > 0)
    <div class="dishes">
        @foreach($meal->items as $item)
        <span class="dish-tag">{{ $item->meal->name ?? 'â€”' }}</span>
        @endforeach
    </div>
    @endif
</div>

{{-- Codes grid --}}
<div class="codes-grid">
    @foreach($meal->codes as $i => $code)
    <div class="code-card{{ $code->is_used ? ' used' : '' }}">
        <div class="num">#{{ str_pad($loop->iteration, 3, '0', STR_PAD_LEFT) }}</div>
        <div class="code-val">{{ $code->code }}</div>
        <div class="meal-type">{{ $meal->type_label }}</div>
        @if($code->is_used)
        <div class="used-badge">âœ“ UTILISÃ‰</div>
        @endif
    </div>
    @endforeach
</div>

{{-- Stats footer --}}
<div class="stats">
    Total : {{ $meal->codes->count() }} codes Â·
    UtilisÃ©s : {{ $meal->codes->where('is_used', true)->count() }} Â·
    Disponibles : {{ $meal->codes->where('is_used', false)->count() }}
</div>

</body>

</html>
