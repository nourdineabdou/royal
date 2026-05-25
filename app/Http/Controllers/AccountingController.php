<?php

namespace App\Http\Controllers;

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
        return view('modules.accounting.index', compact('registers', 'cashiers'));
    }

    public function sessions(Request $request)
    {
        $registers = \App\Models\CashRegister::orderByDesc('opened_at')->paginate(30);
        $cashiers = \App\Models\User::role('caissier')->get();
        return view('modules.accounting.index', compact('registers', 'cashiers'));
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
        $transactions = \App\Models\Transaction::orderByDesc('date')->paginate(50);
        $orders = \App\Models\Booking::orderByDesc('created_at')->paginate(50);
        $stockMovements = \App\Models\EventStockUsage::orderByDesc('created_at')->paginate(50);
        $cashiers = \App\Models\User::role('caissier')->get();
        $filters = $request->all();
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

        $modules = Transaction::select('module')->distinct()->pluck('module');
        $types = Transaction::select('type')->distinct()->pluck('type');

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
}
