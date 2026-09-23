<?php

namespace App\Console\Commands;

use App\Models\CateringContract;
use App\Services\CateringInvoiceService;
use Illuminate\Console\Command;

class GenerateCateringMonthlyInvoices extends Command
{
    protected $signature = 'catering:generate-invoices {--year=} {--month=}';
    protected $description = "Génère la facture mensuelle de chaque contrat catering actif pour le mois indiqué (mois précédent par défaut)";

    public function handle(CateringInvoiceService $invoiceService): int
    {
        $reference = $this->option('month')
            ? now()->setDate((int) ($this->option('year') ?: now()->year), (int) $this->option('month'), 1)
            : now()->subMonthNoOverflow();

        $year = (int) ($this->option('year') ?: $reference->year);
        $month = (int) ($this->option('month') ?: $reference->month);

        $contracts = CateringContract::where('status', 'active')->get();

        $created = 0;
        $skipped = 0;

        foreach ($contracts as $contract) {
            $result = $invoiceService->generateForContract($contract, $year, $month);

            if ($result['invoice']) {
                $created++;
                $this->info("Contrat #{$contract->id} : facture {$result['invoice']->invoice_number} générée.");
            } else {
                $skipped++;
                $this->line("Contrat #{$contract->id} : ignoré ({$result['error']}).");
            }
        }

        $this->info("Terminé pour {$month}/{$year} : {$created} facture(s) générée(s), {$skipped} ignoré(s).");

        return self::SUCCESS;
    }
}
