<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    // Valider une caisse
    public function validateRegister($id)
    {
        $register = \App\Models\CashRegister::findOrFail($id);
        if ($register->status === 'closed') {
            $register->status = 'validated';
            $register->validated_at = now();
            $register->validated_by = auth()->id();
            $register->save();
        }
        return redirect()->route('accounting.detail', $register->id)->with('success', 'La caisse a été validée avec succès.');
    }
    // Créer une nouvelle session de caisse
    public function store(Request $request)
    {
        $request->validate([
            'shift' => 'required|in:morning,evening',
            'module' => 'required|string',
            'cashier_user_id' => 'required|exists:users,id',
            'opening_balance' => 'required|numeric|min:0',
        ]);
        $register = new \App\Models\CashRegister();
        $register->shift = $request->shift;
        $register->module = $request->module;
        $register->user_id = $request->cashier_user_id;
        $register->opened_at = now();
        $register->opening_balance = $request->opening_balance;
        $register->status = 'open';
        // Champs additionnels si fournis (optionnels)
        if ($request->has('declared_excess')) $register->declared_excess = $request->declared_excess;
        if ($request->has('closing_balance')) $register->closing_balance = $request->closing_balance;
        if ($request->has('closing_history')) $register->closing_history = $request->closing_history;
        if ($request->has('accounting_note')) $register->accounting_note = $request->accounting_note;
        $register->save();
        return redirect()->route('accounting.detail', $register->id)->with('success', 'Caisse ouverte avec succès.');
    }
    // Fermer une caisse
    public function close($id)
    {
        $register = \App\Models\CashRegister::findOrFail($id);
        if ($register->status === 'open') {
            $register->status = 'closed';
            $register->closed_at = now();
            $register->save();
        }
        return redirect()->route('accounting.detail', $register->id)->with('success', 'La caisse a été fermée avec succès.');
    }
    // Dashboard sessions caisse (index)
    public function index(Request $request)
    {
        $registers = \App\Models\CashRegister::orderByDesc('opened_at')->paginate(30);
        $cashiers = \App\Models\User::role('caissier')->get();
        $cateringInvoicesPending = \App\Models\CateringInvoice::with('client')
            ->where('status', '!=', 'paid')
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->take(10)
            ->get();
        return view('modules.accounting.index', compact('registers', 'cashiers', 'cateringInvoicesPending'));
    }

    public function sessions(Request $request)
    {
        $registers = \App\Models\CashRegister::orderByDesc('opened_at')->paginate(30);
        $cashiers = \App\Models\User::role('caissier')->get();
        $cateringInvoicesPending = \App\Models\CateringInvoice::with('client')
            ->where('status', '!=', 'paid')
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->take(10)
            ->get();
        return view('modules.accounting.index', compact('registers', 'cashiers', 'cateringInvoicesPending'));
    }

    public function detail($id)
    {
        $register = \App\Models\CashRegister::with(['user', 'validator', 'payments'])->findOrFail($id);
        $payments = $register->payments ?? collect();
        // Regrouper les paiements par type
        $byType = $payments->groupBy('type')->map(function($group, $type) {
            return [
                'name' => $type,
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ];
        })->values();
        // Calculer le total encaissé
        $systemTotal = $payments->sum('amount');
        return view('modules.accounting.detail', compact('register', 'payments', 'byType', 'systemTotal'));
    }

    public function traces(Request $request)
    {
        $filters = [
            'module'          => $request->query('module'),
            'type'            => $request->query('type'),
            'cashier_user_id' => $request->query('cashier_user_id'),
            'from_date'       => $request->query('from_date'),
            'to_date'         => $request->query('to_date'),
        ];

        $cashiers = \App\Models\User::role('caissier')->orderBy('name')->get(['id', 'name']);

        $transactions = Transaction::orderByDesc('date')
            ->when($filters['type'], fn($q, $v) => $q->where('type', $v))
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('date', '<=', $v))
            ->orderByDesc('id')
            ->paginate(50, ['*'], 'tx_page');

        $orders = \App\Models\Order::with(['items.meal', 'cashRegister.user', 'payment.paymentType'])
            ->where('status', 'paid')
            ->when($filters['module'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('module', $v));
            })
            ->when($filters['cashier_user_id'], function ($q, $v) {
                $q->whereHas('cashRegister', fn($r) => $r->where('user_id', $v));
            })
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('paid_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('paid_at', '<=', $v))
            ->orderByDesc('paid_at')
            ->paginate(50, ['*'], 'order_page');

        $stockMovements = \App\Models\StockMovement::with(['product.unit', 'stock', 'user'])
            ->whereNotNull('origin_module')
            ->when($filters['module'], fn($q, $v) => $q->where('origin_module', $v))
            ->when($filters['from_date'], fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to_date'], fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->paginate(50, ['*'], 'sm_page');

        return view('modules.accounting.traces', compact('transactions', 'orders', 'stockMovements', 'cashiers', 'filters'));
    }

    public function transactions(Request $request)
    {
        $filters = [
            'module' => $request->query('module'),
            'type' => $request->query('type'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ];

        $query = Transaction::query();
        if ($filters['module']) {
            $query->where('module', $filters['module']);
        }
        if ($filters['type']) {
            $query->where('type', $filters['type']);
        }
        if ($filters['from_date']) {
            $query->whereDate('date', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->whereDate('date', '<=', $filters['to_date']);
        }
        $transactions = $query->orderByDesc('date')->paginate(50)->withQueryString();

        $modules = Transaction::select('module')->whereNotNull('module')->distinct()->pluck('module');
        $types = Transaction::select('type')->whereNotNull('type')->distinct()->pluck('type');

        return view('accounting.transactions', compact('transactions', 'filters', 'modules', 'types'));
    }

    public function export(Request $request, $format)
    {
        $query = Transaction::query();
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }
        $transactions = $query->orderByDesc('date')->get();

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="transactions.csv"',
            ];
            $callback = function() use ($transactions) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Date', 'Module', 'Type', 'Montant', 'Référence']);
                foreach ($transactions as $t) {
                    fputcsv($out, [$t->date, $t->module, $t->type, $t->amount, $t->reference]);
                }
                fclose($out);
            };
            return response()->stream($callback, 200, $headers);
        } elseif ($format === 'xlsx') {
            $data = $transactions->map(function($t) {
                return [
                    'Date' => $t->date,
                    'Module' => $t->module,
                    'Type' => $t->type,
                    'Montant' => $t->amount,
                    'Référence' => $t->reference,
                ];
            });
            return \Excel::download(new \App\Exports\GenericExport($data), 'transactions.xlsx');
        }
        abort(404);
    }
    // Dashboard global comptabilité
    public function dashboard()
    {
        // Statistiques globales
        $totalTransactions = \App\Models\Transaction::count();
        $totalAmount = \App\Models\Transaction::sum('amount');
        $totalSessions = \App\Models\CashRegister::count();
        $openSessions = \App\Models\CashRegister::where('status', 'open')->count();
        $validatedSessions = \App\Models\CashRegister::where('status', 'validated')->count();
        $recentTransactions = \App\Models\Transaction::orderByDesc('date')->limit(5)->get();

        // Statistiques par module
        $modulesStats = \App\Models\Transaction::select('module')
            ->selectRaw('COUNT(*) as transactions_count')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('module')
            ->orderByDesc('total_amount')
            ->get();

        return view('modules.accounting.dashboard', compact(
            'totalTransactions', 'totalAmount', 'totalSessions', 'openSessions', 'validatedSessions', 'recentTransactions', 'modulesStats'
        ));
    }

    // ── Journal, Grand Livre, Balance (partie double / PCM) ────────────────

    private function journalQuery(Request $request)
    {
        $journalCode = $request->query('journal_code');
        $fromDate    = $request->query('from_date');
        $toDate      = $request->query('to_date');

        $query = JournalEntry::with('lines.chartOfAccount')->orderByDesc('entry_date')->orderByDesc('id');

        if ($journalCode) {
            $query->where('journal_code', $journalCode);
        }
        if ($fromDate) {
            $query->whereDate('entry_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('entry_date', '<=', $toDate);
        }

        return [$query, $journalCode, $fromDate, $toDate];
    }

    public function journal(Request $request)
    {
        [$query, $journalCode, $fromDate, $toDate] = $this->journalQuery($request);

        $entries = $query->paginate(15)->withQueryString();

        return view('modules.accounting.journal', compact('entries', 'journalCode', 'fromDate', 'toDate'));
    }

    /**
     * Export du journal au format CSV (compatible Excel — BOM UTF-8),
     * avec les mêmes filtres que l'écran (journal_code, from_date, to_date).
     */
    public function exportJournal(Request $request)
    {
        [$query] = $this->journalQuery($request);

        $entries = $query->get();

        $fileName = 'journal-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($entries) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['Date', 'Journal', 'Référence', 'Libellé', 'Compte', 'Intitulé du compte', 'Débit', 'Crédit'], ';');

            foreach ($entries as $entry) {
                foreach ($entry->lines as $line) {
                    fputcsv($out, [
                        $entry->entry_date->format('d/m/Y'),
                        $entry->journal_code,
                        $entry->reference,
                        $entry->label,
                        $line->chartOfAccount->code,
                        $line->chartOfAccount->label,
                        $line->debit > 0 ? number_format($line->debit, 2, ',', '') : '',
                        $line->credit > 0 ? number_format($line->credit, 2, ',', '') : '',
                    ], ';');
                }
            }

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function ledger(Request $request)
    {
        $accounts = ChartOfAccount::forCompany()->orderBy('code')->get();

        $accountId = $request->query('chart_of_account_id');
        $fromDate  = $request->query('from_date');
        $toDate    = $request->query('to_date');

        $lines = collect();
        $account = null;
        $runningBalance = 0;

        if ($accountId) {
            $account = ChartOfAccount::findOrFail($accountId);

            $query = $account->lines()->with('journalEntry')
                ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                    if ($fromDate) {
                        $q->whereDate('entry_date', '>=', $fromDate);
                    }
                    if ($toDate) {
                        $q->whereDate('entry_date', '<=', $toDate);
                    }
                });

            $lines = $query->get()
                ->sortBy(fn ($line) => $line->journalEntry->entry_date . '-' . $line->id)
                ->values()
                ->map(function ($line) use (&$runningBalance) {
                    $runningBalance += (float) $line->debit - (float) $line->credit;
                    $line->running_balance = $runningBalance;
                    return $line;
                });
        }

        return view('modules.accounting.grand-livre', compact('accounts', 'account', 'lines', 'accountId', 'fromDate', 'toDate'));
    }

    public function trialBalance(Request $request)
    {
        $fromDate = $request->query('from_date');
        $toDate   = $request->query('to_date');

        $accounts = ChartOfAccount::forCompany()
            ->with(['lines' => function ($q) use ($fromDate, $toDate) {
                $q->whereHas('journalEntry', function ($jq) use ($fromDate, $toDate) {
                    if ($fromDate) {
                        $jq->whereDate('entry_date', '>=', $fromDate);
                    }
                    if ($toDate) {
                        $jq->whereDate('entry_date', '<=', $toDate);
                    }
                });
            }])
            ->orderBy('code')
            ->get()
            ->map(function ($account) {
                $account->total_debit  = $account->lines->sum('debit');
                $account->total_credit = $account->lines->sum('credit');
                $account->balance      = $account->total_debit - $account->total_credit;
                return $account;
            })
            ->filter(fn ($account) => $account->total_debit > 0 || $account->total_credit > 0)
            ->values();

        $totalDebit  = $accounts->sum('total_debit');
        $totalCredit = $accounts->sum('total_credit');

        return view('modules.accounting.balance', compact('accounts', 'totalDebit', 'totalCredit', 'fromDate', 'toDate'));
    }

    /**
     * Comptabilité → Fournisseurs → Factures.
     * Une commande d'achat devient une "facture fournisseur" dès qu'elle est confirmée/envoyée —
     * le comptable la retrouve ici sans avoir à aller dans le module Achats (intégration inter-modules).
     */
    public function supplierInvoices(Request $request)
    {
        $this->perm('accounting.view');

        $query = PurchaseOrder::with('supplier', 'invoiceValidatedBy')
            ->whereIn('status', ['ordered', 'partial', 'received']);

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();
        $suppliers = \App\Models\Supplier::orderBy('name')->get();

        return view('modules.accounting.supplier-invoices', compact('invoices', 'suppliers'));
    }

    /**
     * Comptabilité → Fournisseurs → Soldes : combien on doit à chaque fournisseur, tout confondu
     * (comme le "solde" fournisseur dans Odoo — utile pour savoir qui payer en priorité).
     */
    public function supplierBalances()
    {
        $this->perm('accounting.view');

        $suppliers = \App\Models\Supplier::query()
            ->withCount(['purchaseOrders' => function ($q) {
                $q->whereIn('status', ['ordered', 'partial', 'received']);
            }])
            ->withSum(['purchaseOrders as invoiced_total' => function ($q) {
                $q->whereIn('status', ['ordered', 'partial', 'received']);
            }], 'total_amount')
            ->withSum(['purchaseOrders as paid_total' => function ($q) {
                $q->whereIn('status', ['ordered', 'partial', 'received']);
            }], 'paid_amount')
            ->withSum(['purchaseOrders as balance_due' => function ($q) {
                $q->whereIn('status', ['ordered', 'partial', 'received']);
            }], 'remaining_amount')
            ->having('purchase_orders_count', '>', 0)
            ->orderByDesc('balance_due')
            ->paginate(20);

        return view('modules.accounting.supplier-balances', compact('suppliers'));
    }

    /**
     * Comptabilité → Clients → Soldes : combien chaque client doit encore, symétrique aux soldes fournisseurs.
     * Pour l'instant limité au Catering (seul module avec un vrai lien client + une vraie facture/paiement) —
     * Événements et Résidence n'ont pas encore de modèle de facturation client lié à `Client` pour être inclus ici.
     */
    public function clientBalances()
    {
        $this->perm('accounting.view');

        $clients = \App\Models\Client::query()
            ->withCount('cateringInvoices')
            ->withSum('cateringInvoices as invoiced_total', 'total_amount')
            ->withSum('cateringInvoices as paid_total', 'paid_amount')
            ->having('catering_invoices_count', '>', 0)
            ->orderByDesc('invoiced_total')
            ->paginate(20);

        $clients->getCollection()->transform(function ($client) {
            $client->balance_due = ($client->invoiced_total ?? 0) - ($client->paid_total ?? 0);
            return $client;
        });

        return view('modules.accounting.client-balances', compact('clients'));
    }

    /**
     * Bilan (Actif / Passif) — photo à une date donnée, construite à partir du Plan Comptable Mauritanien :
     * classes 2/3/5 = Actif, classe 1 = Passif (capitaux), classe 4 (tiers) se répartit selon le sens du solde
     * (débiteur → Actif, créditeur → Passif), et le résultat de l'exercice (classes 6/7) vient équilibrer le Passif.
     */
    public function balanceSheet(Request $request)
    {
        $this->perm('accounting.view');
        $toDate = $request->query('to_date', now()->toDateString());

        $accounts = ChartOfAccount::forCompany()
            ->with(['lines' => function ($q) use ($toDate) {
                $q->whereHas('journalEntry', fn ($jq) => $jq->whereDate('entry_date', '<=', $toDate));
            }])
            ->orderBy('code')
            ->get()
            ->map(function ($account) {
                $account->balance = $account->lines->sum('debit') - $account->lines->sum('credit');
                return $account;
            })
            ->filter(fn ($a) => abs($a->balance) > 0.001);

        $actif = collect();
        $passif = collect();

        foreach ($accounts as $account) {
            if (in_array($account->class, [2, 3, 5])) {
                if ($account->balance != 0) $actif->push($account);
            } elseif ($account->class == 1) {
                if ($account->balance < 0) $passif->push($account); // classe 1 est naturellement créditrice
            } elseif ($account->class == 4) {
                if ($account->balance > 0) $actif->push($account);   // tiers débiteurs (ex: clients)
                else $passif->push($account);                        // tiers créditeurs (ex: fournisseurs)
            }
        }

        // Résultat de l'exercice = Produits (classe 7) - Charges (classe 6), sur toute la période jusqu'à to_date
        $chargesTotal  = ChartOfAccount::forCompany()->where('class', 6)
            ->with(['lines' => fn ($q) => $q->whereHas('journalEntry', fn ($jq) => $jq->whereDate('entry_date', '<=', $toDate))])
            ->get()->sum(fn ($a) => $a->lines->sum('debit') - $a->lines->sum('credit'));
        $produitsTotal = ChartOfAccount::forCompany()->where('class', 7)
            ->with(['lines' => fn ($q) => $q->whereHas('journalEntry', fn ($jq) => $jq->whereDate('entry_date', '<=', $toDate))])
            ->get()->sum(fn ($a) => $a->lines->sum('credit') - $a->lines->sum('debit'));
        $resultatExercice = $produitsTotal - $chargesTotal;

        $totalActif  = $actif->sum('balance');
        $totalPassif = $passif->sum(fn ($a) => abs($a->balance)) + $resultatExercice;

        return view('modules.accounting.balance-sheet', compact(
            'actif', 'passif', 'totalActif', 'totalPassif', 'resultatExercice', 'toDate'
        ));
    }

    /**
     * Compte de Résultat — Charges (classe 6) et Produits (classe 7) sur une période.
     */
    public function incomeStatement(Request $request)
    {
        $this->perm('accounting.view');
        $fromDate = $request->query('from_date', now()->startOfYear()->toDateString());
        $toDate   = $request->query('to_date', now()->toDateString());

        $fetch = function (int $class) use ($fromDate, $toDate) {
            return ChartOfAccount::forCompany()->where('class', $class)
                ->with(['lines' => function ($q) use ($fromDate, $toDate) {
                    $q->whereHas('journalEntry', function ($jq) use ($fromDate, $toDate) {
                        $jq->whereDate('entry_date', '>=', $fromDate)->whereDate('entry_date', '<=', $toDate);
                    });
                }])
                ->orderBy('code')
                ->get()
                ->map(function ($account) {
                    $account->total_debit  = $account->lines->sum('debit');
                    $account->total_credit = $account->lines->sum('credit');
                    return $account;
                })
                ->filter(fn ($a) => $a->total_debit > 0 || $a->total_credit > 0)
                ->values();
        };

        $charges  = $fetch(6);
        $produits = $fetch(7);

        $totalCharges  = $charges->sum(fn ($a) => $a->total_debit - $a->total_credit);
        $totalProduits = $produits->sum(fn ($a) => $a->total_credit - $a->total_debit);
        $resultatNet   = $totalProduits - $totalCharges;

        return view('modules.accounting.income-statement', compact(
            'charges', 'produits', 'totalCharges', 'totalProduits', 'resultatNet', 'fromDate', 'toDate'
        ));
    }

    /**
     * Rapprochement bancaire : pointer les mouvements d'un compte de trésorerie (Banque, Caisse...)
     * contre le relevé bancaire réel, pour détecter les écarts.
     */
    public function bankReconciliation(Request $request)
    {
        $this->perm('accounting.view');

        $treasuryAccounts = ChartOfAccount::forCompany()->where('class', 5)->orderBy('code')->get();
        $accountId = $request->query('account_id', $treasuryAccounts->first()?->id);
        $toDate    = $request->query('to_date', now()->toDateString());
        $statementBalance = $request->query('statement_balance');

        $lines = collect();
        $bookBalance = 0;
        $reconciledBalance = 0;

        if ($accountId) {
            $lines = JournalEntryLine::with('journalEntry')
                ->where('chart_of_account_id', $accountId)
                ->whereHas('journalEntry', fn ($q) => $q->whereDate('entry_date', '<=', $toDate))
                ->get()
                ->sortByDesc(fn ($l) => $l->journalEntry->entry_date)
                ->values();

            $bookBalance = $lines->sum('debit') - $lines->sum('credit');
            $reconciledBalance = $lines->where('is_reconciled', true)->sum('debit') - $lines->where('is_reconciled', true)->sum('credit');
        }

        $diff = $statementBalance !== null ? ((float) $statementBalance - $reconciledBalance) : null;

        return view('modules.accounting.bank-reconciliation', compact(
            'treasuryAccounts', 'accountId', 'toDate', 'lines', 'bookBalance', 'reconciledBalance', 'statementBalance', 'diff'
        ));
    }

    public function toggleReconciliation(JournalEntryLine $line)
    {
        $this->perm('accounting.view');

        $newState = !$line->is_reconciled;
        $line->update([
            'is_reconciled' => $newState,
            'reconciled_at' => $newState ? now() : null,
            'reconciled_by' => $newState ? auth()->id() : null,
        ]);

        return response()->json(['success' => true, 'is_reconciled' => $line->is_reconciled]);
    }
}
