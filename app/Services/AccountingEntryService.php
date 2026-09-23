<?php

namespace App\Services;

use App\Models\Advance;
use App\Models\CateringInvoicePayment;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\GoodsReceipt;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\SupplierPayment;
use App\Models\SupplierReturn;

/**
 * Génère les écritures comptables en partie double (PCM) à partir des
 * événements de vente et d'achat déjà enregistrés ailleurs dans l'app.
 */
class AccountingEntryService
{
    private function account(string $code): ChartOfAccount
    {
        $account = ChartOfAccount::forCompany()->where('code', $code)->first();

        if (!$account) {
            $account = ChartOfAccount::where('code', $code)->whereNull('company_id')->first();
        }

        if (!$account) {
            throw new \RuntimeException("Compte comptable introuvable pour le code {$code}. Vérifiez le plan comptable.");
        }

        return $account;
    }

    private function cashAccountFor(?int $paymentTypeId): ChartOfAccount
    {
        if ($paymentTypeId) {
            $paymentType = \App\Models\PaymentType::find($paymentTypeId);
            if ($paymentType && $paymentType->chart_of_account_id) {
                return $this->account($paymentType->chartOfAccount->code);
            }
        }

        return $this->account(config('accounting.default_cash_account'));
    }

    private function journalCodeForTreasury(ChartOfAccount $treasuryAccount): string
    {
        return match (true) {
            str_starts_with($treasuryAccount->code, '56') => 'CA',
            str_starts_with($treasuryAccount->code, '50') => 'BQ',
            default => 'OD',
        };
    }

    /**
     * Encaissement d'une vente (POS restaurant ou vente libre catering).
     */
    public function postSale(Payment $payment, string $module): ?JournalEntry
    {
        if (JournalEntry::where('source_type', Payment::class)->where('source_id', $payment->id)->exists()) {
            return null;
        }

        $salesCode = config("accounting.sales_accounts.{$module}");
        if (!$salesCode) {
            return null;
        }

        $cashAccount  = $this->cashAccountFor($payment->payment_type_id);
        $salesAccount = $this->account($salesCode);

        return $this->createEntry(
            journalCode: 'VE',
            entryDate: now()->toDateString(),
            reference: 'VENTE-' . $payment->order_id,
            label: 'Vente ' . $module . ' — commande #' . $payment->order_id,
            sourceType: Payment::class,
            sourceId: $payment->id,
            debitAccount: $cashAccount,
            creditAccount: $salesAccount,
            amount: (float) $payment->amount,
        );
    }

    /**
     * Réception fournisseur : la dette fournisseur naît au moment de la
     * livraison, pas au moment du paiement.
     */
    public function postPurchaseReceipt(GoodsReceipt $receipt, float $amount, string $module = 'default'): ?JournalEntry
    {
        if ($amount <= 0) {
            return null;
        }

        if (JournalEntry::where('source_type', GoodsReceipt::class)->where('source_id', $receipt->id)->exists()) {
            return null;
        }

        $purchaseCode   = config("accounting.purchase_accounts.{$module}", config('accounting.purchase_accounts.default'));
        $purchaseAccount  = $this->account($purchaseCode);
        $supplierAccount  = $this->account(config('accounting.supplier_account'));

        return $this->createEntry(
            journalCode: 'AC',
            entryDate: now()->toDateString(),
            reference: 'RECEP-' . $receipt->id,
            label: 'Réception fournisseur — commande #' . $receipt->purchase_order_id,
            sourceType: GoodsReceipt::class,
            sourceId: $receipt->id,
            debitAccount: $purchaseAccount,
            creditAccount: $supplierAccount,
            amount: $amount,
        );
    }

    /**
     * Règlement d'une commande fournisseur.
     */
    /**
     * Retour de marchandise à un fournisseur — l'inverse d'une réception : réduit la dette envers
     * le fournisseur (débit) et annule d'autant la charge d'achat déjà comptabilisée (crédit).
     */
    public function postSupplierReturn(SupplierReturn $return, string $module = 'default'): ?JournalEntry
    {
        if ($return->total_amount <= 0) {
            return null;
        }

        if (JournalEntry::where('source_type', SupplierReturn::class)->where('source_id', $return->id)->exists()) {
            return null;
        }

        $purchaseCode    = config("accounting.purchase_accounts.{$module}", config('accounting.purchase_accounts.default'));
        $purchaseAccount = $this->account($purchaseCode);
        $supplierAccount = $this->account(config('accounting.supplier_account'));

        return $this->createEntry(
            journalCode: 'AC',
            entryDate: $return->returned_at?->toDateString() ?? now()->toDateString(),
            reference: 'RETOUR-' . $return->id,
            label: 'Retour fournisseur — commande #' . $return->purchase_order_id,
            sourceType: SupplierReturn::class,
            sourceId: $return->id,
            debitAccount: $supplierAccount,
            creditAccount: $purchaseAccount,
            amount: (float) $return->total_amount,
        );
    }

    public function postSupplierPayment(SupplierPayment $payment): ?JournalEntry
    {
        if (JournalEntry::where('source_type', SupplierPayment::class)->where('source_id', $payment->id)->exists()) {
            return null;
        }

        $supplierAccount = $this->account(config('accounting.supplier_account'));
        $cashAccount     = $this->cashAccountFor($payment->payment_type_id);

        return $this->createEntry(
            journalCode: $this->journalCodeForTreasury($cashAccount),
            entryDate: $payment->paid_at?->toDateString() ?? now()->toDateString(),
            reference: 'REGL-' . $payment->purchase_order_id,
            label: 'Règlement fournisseur — commande #' . $payment->purchase_order_id,
            sourceType: SupplierPayment::class,
            sourceId: $payment->id,
            debitAccount: $supplierAccount,
            creditAccount: $cashAccount,
            amount: (float) $payment->amount,
        );
    }

    /**
     * Encaissement d'un paiement de facture catering (contrat mensuel),
     * une fois validé par la comptabilité.
     */
    public function postCateringInvoicePayment(CateringInvoicePayment $payment): ?JournalEntry
    {
        if (JournalEntry::where('source_type', CateringInvoicePayment::class)->where('source_id', $payment->id)->exists()) {
            return null;
        }

        $cashAccount  = $this->cashAccountFor($payment->payment_type_id);
        $salesAccount = $this->account(config('accounting.sales_accounts.catering'));

        return $this->createEntry(
            journalCode: 'VE',
            entryDate: $payment->payment_date?->toDateString() ?? now()->toDateString(),
            reference: 'CATINVPAY-' . $payment->catering_invoice_id . '-' . $payment->id,
            label: 'Paiement facture catering #' . $payment->catering_invoice_id,
            sourceType: CateringInvoicePayment::class,
            sourceId: $payment->id,
            debitAccount: $cashAccount,
            creditAccount: $salesAccount,
            amount: (float) $payment->amount,
        );
    }

    /**
     * Dépense courante réglée directement par la comptabilité
     * (wifi, électricité, eau, carburant, etc.).
     */
    public function postExpense(Expense $expense): ?JournalEntry
    {
        if (JournalEntry::where('source_type', Expense::class)->where('source_id', $expense->id)->exists()) {
            return null;
        }

        $expense->loadMissing('expenseType');

        $chargeAccount = $this->account($expense->expenseType->chartOfAccount->code);
        $cashAccount   = $this->cashAccountFor($expense->payment_type_id);

        return $this->createEntry(
            journalCode: $this->journalCodeForTreasury($cashAccount),
            entryDate: $expense->payment_date?->toDateString() ?? now()->toDateString(),
            reference: 'DEP-' . $expense->id,
            label: 'Dépense ' . $expense->expenseType->name,
            sourceType: Expense::class,
            sourceId: $expense->id,
            debitAccount: $chargeAccount,
            creditAccount: $cashAccount,
            amount: (float) $expense->amount,
        );
    }

    /**
     * Paiement d'une fiche de paie (salaire net versé à un employé).
     *
     * Débit 65000001 SALAIRES pour le brut (base + bonus - déductions),
     * Crédit 42 PERSONNEL pour la part remboursée d'une avance (le cas
     * échéant — rend le remboursement visible sur le compte d'avance),
     * Crédit trésorerie pour le net réellement versé.
     */
    public function postPayrollPayment(Payroll $payroll): ?JournalEntry
    {
        if (JournalEntry::where('source_type', Payroll::class)->where('source_id', $payroll->id)->exists()) {
            return null;
        }

        $payroll->loadMissing('employee');

        $salaryAccount    = $this->account('65000001');
        $personnelAccount = $this->account('42');
        $cashAccount      = $this->cashAccountFor($payroll->payment_type_id);

        $grossExpense  = round((float) $payroll->base_salary + (float) $payroll->bonus - (float) $payroll->deduction, 2);
        $advanceAmount = round((float) $payroll->advance_deduction, 2);
        $netPaid       = round((float) $payroll->net_salary, 2);

        $label = 'Salaire ' . ($payroll->employee->full_name ?? '#' . $payroll->employee_id) . ' — ' . $payroll->month . '/' . $payroll->year;

        $lines = [
            ['account' => $salaryAccount, 'debit' => $grossExpense, 'credit' => 0],
        ];
        if ($advanceAmount > 0) {
            $lines[] = ['account' => $personnelAccount, 'debit' => 0, 'credit' => $advanceAmount, 'label' => 'Remboursement avance sur salaire'];
        }
        $lines[] = ['account' => $cashAccount, 'debit' => 0, 'credit' => $netPaid];

        return $this->createMultiLineEntry(
            journalCode: $this->journalCodeForTreasury($cashAccount),
            entryDate: $payroll->paid_at?->toDateString() ?? now()->toDateString(),
            reference: 'PAIE-' . $payroll->id,
            label: $label,
            sourceType: Payroll::class,
            sourceId: $payroll->id,
            lines: $lines,
        );
    }

    /**
     * Avance accordée à un employé (créance sur le personnel, compte 42),
     * réglée en trésorerie au moment du versement.
     */
    public function postAdvanceGiven(Advance $advance): ?JournalEntry
    {
        if (JournalEntry::where('source_type', Advance::class)->where('source_id', $advance->id)->exists()) {
            return null;
        }

        $advance->loadMissing('employee');

        $personnelAccount = $this->account('42');
        $cashAccount      = $this->account(config('accounting.default_cash_account'));

        return $this->createEntry(
            journalCode: $this->journalCodeForTreasury($cashAccount),
            entryDate: $advance->date?->toDateString() ?? now()->toDateString(),
            reference: 'ADV-' . $advance->id,
            label: 'Avance sur salaire — ' . ($advance->employee->full_name ?? '#' . $advance->employee_id),
            sourceType: Advance::class,
            sourceId: $advance->id,
            debitAccount: $personnelAccount,
            creditAccount: $cashAccount,
            amount: (float) $advance->amount,
        );
    }

    private function createEntry(
        string $journalCode,
        string $entryDate,
        string $reference,
        string $label,
        string $sourceType,
        int $sourceId,
        ChartOfAccount $debitAccount,
        ChartOfAccount $creditAccount,
        float $amount,
    ): JournalEntry {
        $entry = JournalEntry::create([
            'company_id'  => $debitAccount->company_id,
            'journal_code' => $journalCode,
            'entry_date'  => $entryDate,
            'reference'   => $reference,
            'label'       => $label,
            'source_type' => $sourceType,
            'source_id'   => $sourceId,
            'created_by'  => auth()->id(),
        ]);

        $entry->lines()->createMany([
            ['chart_of_account_id' => $debitAccount->id, 'debit' => $amount, 'credit' => 0, 'label' => $label],
            ['chart_of_account_id' => $creditAccount->id, 'debit' => 0, 'credit' => $amount, 'label' => $label],
        ]);

        return $entry;
    }

    /**
     * Écriture à plus de deux lignes (ex: salaire ventilé entre charge,
     * remboursement d'avance et trésorerie). Chaque ligne : ['account' =>
     * ChartOfAccount, 'debit' => float, 'credit' => float, 'label'? => string].
     */
    private function createMultiLineEntry(
        string $journalCode,
        string $entryDate,
        string $reference,
        string $label,
        string $sourceType,
        int $sourceId,
        array $lines,
    ): JournalEntry {
        $entry = JournalEntry::create([
            'company_id'  => $lines[0]['account']->company_id,
            'journal_code' => $journalCode,
            'entry_date'  => $entryDate,
            'reference'   => $reference,
            'label'       => $label,
            'source_type' => $sourceType,
            'source_id'   => $sourceId,
            'created_by'  => auth()->id(),
        ]);

        $entry->lines()->createMany(array_map(fn (array $l) => [
            'chart_of_account_id' => $l['account']->id,
            'debit'  => $l['debit'] ?? 0,
            'credit' => $l['credit'] ?? 0,
            'label'  => $l['label'] ?? $label,
        ], $lines));

        return $entry;
    }
}
