@extends('layouts.pos')
@section('title', 'Rentabilité des repas')

@section('content')
<style>
.prof-wrap { padding: 28px 32px; width: 100%; box-sizing: border-box; }
.prof-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
.prof-title  { font-size:20px; font-weight:700; color:#1f2937; }
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:24px; }
.kpi { background:#fff; border-radius:14px; padding:16px 18px; box-shadow:0 1px 4px rgba(0,0,0,.08); }
.kpi-val { font-size:22px; font-weight:800; }
.kpi-lbl { font-size:11px; color:#6b7280; margin-top:3px; }
.kpi.green .kpi-val { color:#059669; }
.kpi.red   .kpi-val { color:#dc2626; }
.kpi.blue  .kpi-val { color:#4f46e5; }
.kpi.amber .kpi-val { color:#d97706; }

.filter-bar { background:#fff; border-radius:14px; padding:16px 20px; box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:20px;
    display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; }
.filter-bar label { font-size:11px; font-weight:600; color:#6b7280; display:block; margin-bottom:4px; }
.filter-bar input, .filter-bar select {
    border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px; font-size:13px;
    outline:none; transition:.2s;
}
.filter-bar input:focus, .filter-bar select:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.btn-primary { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; border:none; border-radius:8px;
    padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer; transition:.2s; }
.btn-primary:hover { opacity:.88; }

.chart-wrap { background:#fff; border-radius:14px; padding:20px; box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:20px; }
.chart-title { font-size:14px; font-weight:700; color:#374151; margin-bottom:14px; }

.table-wrap { background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.08); }
.table-head { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; padding:14px 20px;
    display:flex; align-items:center; justify-content:space-between; }
.table-head h3 { font-size:14px; font-weight:700; margin:0; }
table.pt { width:100%; border-collapse:collapse; font-size:13px; }
table.pt thead th { padding:11px 16px; background:#f8fafc; color:#374151; font-weight:600;
    border-bottom:1px solid #e5e7eb; white-space:nowrap; text-align:left; }
table.pt thead th.r { text-align:right; }
table.pt tbody td { padding:12px 16px; border-bottom:1px solid #f3f4f6; color:#374151; }
table.pt tbody td.r { text-align:right; font-variant-numeric:tabular-nums; }
table.pt tbody tr:hover { background:#f9fafb; }
table.pt tbody tr:last-child td { border-bottom:none; }
.margin-bar { height:8px; border-radius:4px; background:#e5e7eb; overflow:hidden; min-width:60px; }
.margin-fill { height:100%; border-radius:4px; }
.badge { font-size:11px; font-weight:700; padding:2px 8px; border-radius:20px; }
.badge-green { background:#d1fae5; color:#059669; }
.badge-red   { background:#fee2e2; color:#dc2626; }
.no-recipe   { font-size:11px; color:#9ca3af; font-style:italic; }
</style>

<div class="prof-wrap">

    {{-- Header --}}
    <div class="prof-header">
        <div>
            <div class="prof-title"><i class="fas fa-chart-line" style="color:#6366f1;margin-right:8px;"></i>Rentabilité des repas</div>
            <p style="color:#6b7280;font-size:13px;margin-top:2px;">{{ $label }}</p>
        </div>
        <a href="{{ route('pos.accounting') }}"
           style="font-size:13px;color:#6366f1;text-decoration:none;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-arrow-left"></i> Retour comptabilité
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filter-bar">
        <div>
            <label>Période</label>
            <select name="period">
                <option value="day"   @selected($period==='day')>Jour</option>
                <option value="week"  @selected($period==='week')>Semaine</option>
                <option value="month" @selected($period==='month')>Mois</option>
                <option value="year"  @selected($period==='year')>Année</option>
            </select>
        </div>
        <div>
            <label>Date de référence</label>
            <input type="date" name="date" value="{{ $dateInput }}">
        </div>
        <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Filtrer</button>
    </form>

    {{-- KPIs --}}
    <div class="kpi-grid">
        <div class="kpi blue">
            <div class="kpi-val">{{ number_format($totalRevenue, 0, ',', ' ') }} <span style="font-size:14px;font-weight:400">MRU</span></div>
            <div class="kpi-lbl">Chiffre d'affaires</div>
        </div>
        <div class="kpi red">
            <div class="kpi-val">{{ number_format($totalCost, 0, ',', ' ') }} <span style="font-size:14px;font-weight:400">MRU</span></div>
            <div class="kpi-lbl">Coût total des recettes</div>
        </div>
        <div class="kpi green">
            <div class="kpi-val">{{ number_format($totalProfit, 0, ',', ' ') }} <span style="font-size:14px;font-weight:400">MRU</span></div>
            <div class="kpi-lbl">Bénéfice net</div>
        </div>
        <div class="kpi amber">
            <div class="kpi-val">{{ $margin }}%</div>
            <div class="kpi-lbl">Marge bénéficiaire</div>
        </div>
        <div class="kpi" style="">
            <div class="kpi-val" style="color:#374151;">{{ $rows->count() }}</div>
            <div class="kpi-lbl">Repas différents vendus</div>
        </div>
        <div class="kpi" style="">
            <div class="kpi-val" style="color:#374151;">{{ number_format($rows->sum('qty_sold'), 0, ',', ' ') }}</div>
            <div class="kpi-lbl">Portions vendues</div>
        </div>
    </div>

    {{-- Chart --}}
    @if($daily->isNotEmpty())
    <div class="chart-wrap">
        <div class="chart-title"><i class="fas fa-chart-bar" style="color:#6366f1;margin-right:6px;"></i>Évolution sur la période</div>
        <canvas id="profitChart" height="90"></canvas>
    </div>
    @endif

    {{-- Table --}}
    <div class="table-wrap">
        <div class="table-head">
            <h3><i class="fas fa-utensils" style="margin-right:6px;"></i>Détail par repas</h3>
            <span style="font-size:12px;opacity:.8;">{{ $rows->count() }} repas · {{ number_format($rows->sum('qty_sold'), 0, ',', ' ') }} portions vendues</span>
        </div>
        <table class="pt">
            <thead>
                <tr>
                    <th style="width:30%;">Repas</th>
                    <th class="r" style="width:9%;">Qté vendue</th>
                    <th class="r" style="width:13%;">Prix unitaire</th>
                    <th class="r" style="width:14%;">CA (prix × qté)</th>
                    <th class="r" style="width:9%;">Coût unitaire</th>
                    <th class="r" style="width:14%;">Coût total (recette × qté)</th>
                    <th class="r" style="width:14%;">Bénéfice</th>
                    <th class="r" style="width:10%;">Marge</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                @php
                    $hasRecipe  = $row->total_cost > 0;
                    $unitSell   = $row->qty_sold > 0 ? round($row->revenue / $row->qty_sold, 0) : 0;
                    $unitCost   = $row->qty_sold > 0 ? round($row->total_cost / $row->qty_sold, 0) : 0;
                    $rowMargin  = $row->revenue > 0 ? round(($row->profit / $row->revenue) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>
                        <span style="font-weight:600;color:#1f2937;font-size:14px;">{{ $row->meal_name }}</span>
                        @if(!$hasRecipe)
                        <br><span class="no-recipe"><i class="fas fa-exclamation-triangle"></i> Pas de recette / coût non renseigné</span>
                        @endif
                    </td>
                    <td class="r">
                        <span style="font-size:16px;font-weight:700;color:#374151;">{{ number_format($row->qty_sold, 0, ',', ' ') }}</span>
                    </td>
                    <td class="r" style="color:#4f46e5;">
                        {{ number_format($unitSell, 0, ',', ' ') }} MRU
                    </td>
                    <td class="r">
                        <span style="font-weight:700;font-size:14px;color:#1d4ed8;">
                            {{ number_format($row->revenue, 0, ',', ' ') }} MRU
                        </span>
                        <br><span style="font-size:11px;color:#9ca3af;">= {{ number_format($unitSell, 0, ',', ' ') }} × {{ number_format($row->qty_sold, 0) }}</span>
                    </td>
                    <td class="r" style="color:#dc2626;">
                        @if($hasRecipe)
                            {{ number_format($unitCost, 0, ',', ' ') }} MRU
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td class="r">
                        @if($hasRecipe)
                        <span style="font-weight:700;font-size:14px;color:#dc2626;">
                            {{ number_format($row->total_cost, 0, ',', ' ') }} MRU
                        </span>
                        <br><span style="font-size:11px;color:#9ca3af;">= {{ number_format($unitCost, 0, ',', ' ') }} × {{ number_format($row->qty_sold, 0) }}</span>
                        @else
                        <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td class="r">
                        @if($hasRecipe)
                        <span style="font-weight:800;font-size:15px;color:{{ $row->profit >= 0 ? '#059669' : '#dc2626' }};">
                            {{ number_format($row->profit, 0, ',', ' ') }} MRU
                        </span>
                        @else
                        <span style="color:#9ca3af;font-style:italic;">N/A</span>
                        @endif
                    </td>
                    <td class="r">
                        @if($hasRecipe)
                        <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end;">
                            <div class="margin-bar" style="width:60px;">
                                <div class="margin-fill" style="width:{{ max(0,min(100,$rowMargin)) }}%;background:{{ $rowMargin >= 30 ? '#10b981' : ($rowMargin >= 10 ? '#f59e0b' : '#ef4444') }};"></div>
                            </div>
                            <span class="badge {{ $rowMargin >= 10 ? 'badge-green' : 'badge-red' }}" style="font-size:12px;">{{ $rowMargin }}%</span>
                        </div>
                        @else
                        <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:50px;color:#9ca3af;font-size:15px;">
                    <i class="fas fa-inbox" style="font-size:30px;display:block;margin-bottom:8px;"></i>
                    Aucune vente sur cette période
                </td></tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
            <tfoot>
                <tr style="background:linear-gradient(135deg,#f8fafc,#f1f5f9);font-weight:700;border-top:2px solid #e5e7eb;">
                    <td style="color:#374151;font-size:14px;padding:14px 16px;">TOTAL PÉRIODE</td>
                    <td class="r" style="font-size:16px;color:#374151;">{{ number_format($rows->sum('qty_sold'), 0, ',', ' ') }}</td>
                    <td class="r" style="color:#9ca3af;">—</td>
                    <td class="r" style="color:#1d4ed8;font-size:15px;">{{ number_format($totalRevenue, 0, ',', ' ') }} MRU</td>
                    <td class="r" style="color:#9ca3af;">—</td>
                    <td class="r" style="color:#dc2626;font-size:15px;">{{ number_format($totalCost, 0, ',', ' ') }} MRU</td>
                    <td class="r" style="color:#059669;font-size:16px;font-weight:800;">{{ number_format($totalProfit, 0, ',', ' ') }} MRU</td>
                    <td class="r"><span class="badge {{ $margin >= 10 ? 'badge-green' : 'badge-red' }}" style="font-size:13px;padding:4px 10px;">{{ $margin }}%</span></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

</div>
@endsection

@section('scripts')
@if($daily->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('profitChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($daily->pluck('day')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))),
        datasets: [
            {
                label: "Chiffre d'affaires",
                data: @json($daily->pluck('revenue')),
                backgroundColor: 'rgba(99,102,241,0.7)',
                borderRadius: 6,
                order: 2,
            },
            {
                label: 'Coût recettes',
                data: @json($daily->pluck('cost')),
                backgroundColor: 'rgba(239,68,68,0.6)',
                borderRadius: 6,
                order: 2,
            },
            {
                label: 'Bénéfice',
                data: @json($daily->pluck('profit')),
                type: 'line',
                borderColor: '#059669',
                backgroundColor: 'rgba(16,185,129,0.1)',
                borderWidth: 2,
                pointRadius: 4,
                tension: 0.3,
                fill: true,
                order: 1,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.dataset.label + ': ' + Number(ctx.raw).toLocaleString('fr-FR') + ' MRU'
                }
            }
        },
        scales: {
            y: {
                ticks: {
                    callback: v => Number(v).toLocaleString('fr-FR')
                }
            }
        }
    }
});
</script>
@endif
@endsection
