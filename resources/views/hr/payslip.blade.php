<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de Paie — {{ $payroll->employee->full_name }} — {{ $payroll->month }}/{{ $payroll->year }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A4; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 0; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 2px solid #1e3a5f; padding-bottom: 12px; }
        .company-name { font-size: 20px; font-weight: bold; color: #1e3a5f; }
        .company-info { font-size: 11px; color: #4b5563; margin-top: 4px; line-height: 1.5; }
        .doc-title { text-align: right; }
        .doc-title h2 { font-size: 18px; color: #1e3a5f; margin: 0 0 6px; }
        .doc-title .doc-ref { font-size: 13px; font-weight: bold; }
        .doc-title .doc-date { font-size: 11px; color: #6b7280; }

        .parties { display: flex; gap: 24px; margin-bottom: 20px; }
        .party-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 14px; }
        .party-box h4 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; }
        .party-box p { margin: 0; font-size: 12px; line-height: 1.6; }
        .party-box strong { font-size: 13px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #1e3a5f; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
        thead th.right { text-align: right; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; font-size: 11px; vertical-align: middle; }
        tbody td.right { text-align: right; }
        tbody tr.subtotal td { font-weight: bold; background: #f9fafb; }

        .totals { display: flex; justify-content: flex-end; margin-bottom: 24px; }
        .totals-box { width: 280px; border: 1px solid #e5e7eb; border-radius: 4px; overflow: hidden; }
        .totals-box .row { display: flex; justify-content: space-between; padding: 6px 12px; font-size: 12px; border-bottom: 1px solid #f3f4f6; }
        .totals-box .row:last-child { border-bottom: none; background: #1e3a5f; color: #fff; font-weight: bold; font-size: 13px; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: bold; }
        .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }

        .footer { border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 10px; color: #9ca3af; text-align: center; }
        .signatures { display: flex; gap: 40px; margin-top: 28px; }
        .sig { flex: 1; border-top: 1px solid #d1d5db; padding-top: 6px; text-align: center; font-size: 11px; color: #4b5563; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:right; margin-bottom:12px;">
        <button onclick="window.print()" style="padding:6px 16px; background:#1e3a5f; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
            Imprimer / Télécharger en PDF
        </button>
        <button onclick="window.close()" style="padding:6px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:4px; cursor:pointer; font-size:12px; margin-left:6px;">
            Fermer
        </button>
    </div>

    @php
        $mois = \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, 1)->translatedFormat('F Y');
    @endphp

    <div class="header">
        <div style="display:flex; align-items:center; gap:14px;">
            @if(!empty($company['logo']))
            <img src="{{ asset($company['logo']) }}" alt="{{ $company['name'] }}" style="height:70px; width:auto; object-fit:contain;">
            @endif
            <div>
                <div class="company-name">{{ $company['name'] ?? 'Entreprise' }}</div>
                <div class="company-info">
                    @if(!empty($company['address'])){{ $company['address'] }}@endif
                    @if(!empty($company['phone']))<br>Tél: {{ $company['phone'] }}@endif
                </div>
            </div>
        </div>
        <div class="doc-title">
            <h2>FICHE DE PAIE</h2>
            <div class="doc-ref">{{ ucfirst($mois) }}</div>
            <div class="doc-date">Bulletin #{{ $payroll->id }}</div>
            <div style="margin-top:4px;">
                <span class="badge {{ $payroll->status === 'paid' ? 'badge-paid' : 'badge-pending' }}">
                    {{ $payroll->status === 'paid' ? 'Payé' : 'En attente' }}
                </span>
            </div>
        </div>
    </div>

    <div class="parties">
        <div class="party-box">
            <h4>Employeur</h4>
            <p><strong>{{ $company['name'] ?? 'Entreprise' }}</strong></p>
            @if(!empty($company['address']))<p>{{ $company['address'] }}</p>@endif
        </div>
        <div class="party-box">
            <h4>Employé</h4>
            <p><strong>{{ $payroll->employee->full_name }}</strong></p>
            <p>{{ $payroll->employee->jobTitle->name ?? '—' }}</p>
            @if($payroll->employee->hire_date)<p>Embauché le {{ $payroll->employee->hire_date->format('d/m/Y') }}</p>@endif
            @if($payroll->employee->phone)<p>Tél: {{ $payroll->employee->phone }}</p>@endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Rubrique</th>
                <th class="right">Montant (MRU)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Salaire de base</td>
                <td class="right">{{ number_format($payroll->base_salary, 2, ',', ' ') }}</td>
            </tr>
            @if($payroll->bonus > 0)
            <tr>
                <td>Prime / Bonus</td>
                <td class="right">+ {{ number_format($payroll->bonus, 2, ',', ' ') }}</td>
            </tr>
            @endif
            <tr class="subtotal">
                <td>Total brut</td>
                <td class="right">{{ number_format($payroll->base_salary + $payroll->bonus, 2, ',', ' ') }}</td>
            </tr>
            @if($payroll->deduction > 0)
            <tr>
                <td>Retenue</td>
                <td class="right">− {{ number_format($payroll->deduction, 2, ',', ' ') }}</td>
            </tr>
            @endif
            @if($payroll->advance_deduction > 0)
            <tr>
                <td>Remboursement avance sur salaire</td>
                <td class="right">− {{ number_format($payroll->advance_deduction, 2, ',', ' ') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-box">
            <div class="row"><span>Mode de paiement</span><span>{{ $payroll->paymentType->name ?? '—' }}</span></div>
            <div class="row"><span>Date de paiement</span><span>{{ $payroll->paid_at?->format('d/m/Y') ?? '—' }}</span></div>
            <div class="row"><span>NET À PAYER</span><span>{{ number_format($payroll->net_salary, 2, ',', ' ') }} MRU</span></div>
        </div>
    </div>

    <div class="signatures">
        <div class="sig">Signature Employé</div>
        <div class="sig">Signature Employeur</div>
    </div>

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} · {{ $company['name'] ?? '' }}
    </div>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
