<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\PaymentType;
use App\Services\AccountingEntryService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('accounting.view');

        $types = ExpenseType::with('chartOfAccount')->where('is_active', true)->orderBy('name')->get();
        $expenses = Expense::with(['expenseType', 'paymentType', 'creator'])
            ->when($request->expense_type_id, fn ($q, $v) => $q->where('expense_type_id', $v))
            ->when($request->from_date, fn ($q, $v) => $q->whereDate('payment_date', '>=', $v))
            ->when($request->to_date, fn ($q, $v) => $q->whereDate('payment_date', '<=', $v))
            ->orderByDesc('payment_date')
            ->paginate(20)
            ->withQueryString();

        $chargeAccounts = ChartOfAccount::forCompany()->where('class', 6)->orderBy('code')->get();
        $paymentTypes = PaymentType::orderBy('name')->get();

        $totalThisMonth = Expense::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        return view('modules.accounting.expenses.index', compact(
            'types', 'expenses', 'chargeAccounts', 'paymentTypes', 'totalThisMonth'
        ));
    }

    public function storeType(Request $request)
    {
        $this->perm('accounting.view');

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'chart_of_account_id'  => 'required|exists:chart_of_accounts,id',
            'is_fixed_amount'      => 'boolean',
            'fixed_amount'         => 'nullable|numeric|min:0|required_if:is_fixed_amount,1',
        ]);
        $validated['is_fixed_amount'] = $request->boolean('is_fixed_amount');
        if (!$validated['is_fixed_amount']) {
            $validated['fixed_amount'] = null;
        }

        ExpenseType::create($validated);

        return back()->with('success', 'Service de dépense créé.');
    }

    public function store(Request $request, AccountingEntryService $accountingEntryService)
    {
        $this->perm('accounting.view');

        $validated = $request->validate([
            'expense_type_id' => 'required|exists:expense_types,id',
            'payment_type_id' => 'required|exists:payment_types,id',
            'amount'          => 'required|numeric|min:0.01',
            'payment_date'    => 'required|date',
            'notes'           => 'nullable|string|max:500',
        ]);
        $validated['created_by'] = auth()->id();

        $expense = Expense::create($validated);
        $accountingEntryService->postExpense($expense);

        return back()->with('success', 'Dépense enregistrée et payée.');
    }
}
