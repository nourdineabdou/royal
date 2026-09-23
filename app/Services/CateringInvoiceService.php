<?php

namespace App\Services;

use App\Models\CateringContract;
use App\Models\CateringInvoice;
use App\Models\PosTerminalTicketLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Génère la facture mensuelle d'un contrat catering à partir des
 * consommations enregistrées (PosTerminalTicketLog, plats "contrat").
 */
class CateringInvoiceService
{
    /**
     * @return array{invoice: ?CateringInvoice, error: ?string}
     */
    public function generateForContract(CateringContract $contract, int $year, int $month): array
    {
        $contract->loadMissing(['client', 'prices']);

        $invoiceExists = CateringInvoice::where('catering_contract_id', $contract->id)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->exists();

        if ($invoiceExists) {
            return ['invoice' => null, 'error' => 'Une facture existe deja pour ce contrat et ce mois.'];
        }

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $effectiveStart = $periodStart->copy()->max($contract->start_date->copy()->startOfDay());
        $effectiveEnd = $periodEnd->copy()->min($contract->end_date->copy()->endOfDay());

        if ($effectiveStart->gt($effectiveEnd)) {
            return ['invoice' => null, 'error' => 'Ce contrat est hors periode pour le mois selectionne.'];
        }

        $baseUnitPrice = (float) ($contract->prices()->avg('price') ?? 0);

        $logs = PosTerminalTicketLog::query()
            ->with('stockItem')
            ->whereBetween('served_at', [$effectiveStart, $effectiveEnd])
            ->whereHas('stockItem', function ($q) {
                $q->where('item_type', 'contract');
            })
            ->whereHas('stockItem.terminal', function ($q) use ($contract) {
                $q->where('client_id', $contract->client_id);
            })
            ->get();

        if ($logs->isEmpty()) {
            return ['invoice' => null, 'error' => 'Aucune consommation trouvee pour generer la facture de ce mois.'];
        }

        $grouped = $logs->groupBy('pos_terminal_stock_item_id');
        $invoiceNumber = sprintf('CAT-INV-%04d%02d-%05d', $year, $month, $contract->id);

        $invoice = DB::transaction(function () use ($contract, $year, $month, $effectiveStart, $effectiveEnd, $grouped, $baseUnitPrice, $invoiceNumber) {
            $invoice = CateringInvoice::create([
                'catering_contract_id' => $contract->id,
                'client_id' => $contract->client_id,
                'invoice_number' => $invoiceNumber,
                'period_year' => $year,
                'period_month' => $month,
                'period_start' => $effectiveStart->toDateString(),
                'period_end' => $effectiveEnd->toDateString(),
                'status' => 'issued',
                'total_amount' => 0,
                'paid_amount' => 0,
            ]);

            $total = 0;

            foreach ($grouped as $stockItemId => $rows) {
                $qty = (float) $rows->sum('qty');
                if ($qty <= 0) {
                    continue;
                }

                $stockItem = $rows->first()->stockItem;
                $unitPrice = $baseUnitPrice;
                $lineTotal = round($qty * $unitPrice, 2);
                $total += $lineTotal;

                $invoice->items()->create([
                    'pos_terminal_stock_item_id' => $stockItemId,
                    'label' => $stockItem?->item_label ?: 'Repas catering',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $invoice->update(['total_amount' => round($total, 2)]);

            return $invoice;
        });

        return ['invoice' => $invoice, 'error' => null];
    }
}
