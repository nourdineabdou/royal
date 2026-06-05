<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Client;
use App\Models\PosTerminal;
use App\Models\Stock;
use App\Models\PosTerminalTicketLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gestion des sessions de caisse par le caissier lui-même.
 *
 * Nouveau flux :
 * - L'admin paramètre les terminaux POS via PosTerminalController
 * - Chaque terminal est assigné à un caissier matin et/ou soir
 * - Le caissier se connecte et voit AUTOMATIQUEMENT son terminal assigné
 * - Il n'a plus à choisir le type, le module, le client, etc.
 */
class CashRegisterController extends Controller
{
    private function canManageSessionOpenClose($user): bool
    {
        return $user->hasRole(['admin', 'accountant', 'super-admin', 'manager']);
    }

    // ── Page d'accueil caissier : ouvrir / voir sa caisse ─────────────────

    /**
     * Affiche la page d'ouverture de session pour le caissier connecté.
     * Le système détecte automatiquement le terminal POS assigné à cet utilisateur.
     * Si non assigné → message d'erreur "contactez l'admin".
     */
    public function open()
    {
        $user = auth()->user();

        if (!$this->canManageSessionOpenClose($user)) {
            abort(403, 'Consultation uniquement: vous ne pouvez pas ouvrir ou fermer une caisse.');
        }

        // Session déjà ouverte → rediriger directement
        $existing = CashRegister::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if ($existing) {
            return redirect()->route('cashier.session', $existing->id)
                ->with('info', 'Vous avez déjà une session ouverte.');
        }

        // Trouver le terminal POS assigné à cet utilisateur
        $terminal = PosTerminal::forUser($user->id);

        $activeTerminalSession = null;
        if ($terminal) {
            $activeTerminalSession = CashRegister::with('user')
                ->where('pos_terminal_id', $terminal->id)
                ->where('status', 'open')
                ->latest('opened_at')
                ->first();
        }

        return view('cashier.open', compact('terminal', 'user', 'activeTerminalSession'));
    }

    /**
     * Le caissier ouvre sa session (basée sur son terminal assigné).
     */
    public function startSession(Request $request)
    {
        $user = auth()->user();

        if (!$this->canManageSessionOpenClose($user)) {
            abort(403, 'Consultation uniquement: vous ne pouvez pas ouvrir ou fermer une caisse.');
        }

        // Anti-doublon
        $existing = CashRegister::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();
        if ($existing) {
            return redirect()->route('cashier.session', $existing->id)
                ->with('info', 'Session déjà ouverte.');
        }

        // Vérifier l'assignation
        $terminal = PosTerminal::forUser($user->id);
        if (!$terminal) {
            return back()->with('error', 'Vous n\'êtes assigné à aucun terminal POS. Contactez votre administrateur.');
        }

        // Un seul caissier à la fois par point de vente.
        $activeTerminalSession = CashRegister::with('user')
            ->where('pos_terminal_id', $terminal->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if ($activeTerminalSession) {
            if ($activeTerminalSession->user_id === $user->id) {
                return redirect()->route('cashier.session', $activeTerminalSession->id)
                    ->with('info', 'Vous avez déjà une session ouverte sur ce point de vente.');
            }

            return back()->with('error', 'Cette caisse est déjà ouverte par ' . ($activeTerminalSession->user->name ?? 'un autre caissier') . '. Fermez d\'abord cette session avant d\'en ouvrir une nouvelle.');
        }

        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $shift = $terminal->shiftFor($user->id);

        $register = CashRegister::create([
            'pos_terminal_id' => $terminal->id,
            'user_id'         => $user->id,
            'shift'           => $shift,
            'module'          => $terminal->module,
            'type'            => $terminal->type,
            'label'           => $terminal->label,
            'client_id'       => $terminal->client_id,
            'stock_id'        => $terminal->stock_id,
            'opening_balance' => $request->opening_balance,
            'opened_at'       => now(),
            'status'          => 'open',
        ]);

        return redirect()->route('cashier.session', $register->id)
            ->with('success', 'Session ouverte. Bonne journée ' . $user->name . ' !');
    }

    /**
     * Affiche la session active du caissier (commandes + transferts en attente).
     */
    public function session($id)
    {
        $register = CashRegister::with([
            'user', 'client', 'stock',
        ])->findOrFail($id);

        // Sécurité : seul le caissier propriétaire ou un gestionnaire peut voir
        if ($register->user_id !== auth()->id() && !auth()->user()->hasRole(['admin', 'accountant', 'super-admin'])) {
            abort(403, 'Accès refusé.');
        }

        // Stock cumulé du terminal (toutes sessions confondues)
        $terminalStockItems = collect();
        if ($register->pos_terminal_id) {
            $terminalStockItems = \App\Models\PosTerminalStockItem::where('pos_terminal_id', $register->pos_terminal_id)
                ->where(function($q) {
                    $q->where('quantity_received', '>', 0);
                })
                ->orderBy('item_type')
                ->orderBy('label')
                ->get();
        }

        // Transferts validés pour cette session (pour la vue par session)
        $validatedTransfers = \App\Models\PosTransfer::with(['items.meal', 'items.product', 'preparedBy'])
            ->where('cash_register_id', $register->id)
            ->where('status', 'validated')
            ->orderByDesc('transfer_date')
            ->get();

        // Totaux de la session
        $sessionOrders = $register->orders()
            ->where('status', 'paid')
            ->with('items.meal')
            ->get();

        $cashTotal = $register->payments()
            ->whereHas('paymentType', fn($q) => $q->where('name', 'like', '%espèce%')->orWhere('name', 'like', '%cash%'))
            ->sum('amount');

        $totalSales = $register->payments()->sum('amount');

        return view('cashier.session', compact(
            'register', 'sessionOrders', 'cashTotal', 'totalSales',
            'terminalStockItems', 'validatedTransfers'
        ));
    }

    /**
     * Le caissier ferme sa session et génère le rapport.
     */
    public function closeSession(Request $request, $id)
    {
        $user = auth()->user();

        if (!$this->canManageSessionOpenClose($user)) {
            abort(403, 'Consultation uniquement: vous ne pouvez pas ouvrir ou fermer une caisse.');
        }

        $register = CashRegister::with(['payments', 'orders', 'posTransfers.items'])
            ->findOrFail($id);

        if ($register->user_id !== $user->id && !$user->hasRole(['admin', 'accountant', 'super-admin'])) {
            abort(403, 'Vous ne pouvez pas fermer cette session.');
        }

        if ($register->status !== 'open') {
            return back()->with('error', 'Cette session est déjà fermée.');
        }

        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'accounting_note' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($register, $request) {
            $register->update([
                'status'          => 'closed',
                'closed_at'       => now(),
                'closing_balance' => $request->closing_balance,
                'accounting_note' => $request->accounting_note,
            ]);

            // Marquer les transferts validés comme 'closed'
            $register->posTransfers()
                ->where('status', 'validated')
                ->update(['status' => 'closed']);
        });

        return redirect()->route('cashier.report', $register->id)
            ->with('success', 'Session fermée. Voici votre rapport de clôture.');
    }

    /**
     * Rapport de clôture (affiché + imprimable) — en attente validation comptable.
     */
    public function report($id)
    {
        $register = CashRegister::with([
            'user', 'client', 'stock', 'validator',
            'payments.paymentType',
            'orders.items.meal.category',
            'orders.items.product',
            'posTransfers.items.meal',
            'posTransfers.items.product',
            'posTransfers.items.packaging',
        ])->findOrFail($id);

        $restaurantOrders = $register->orders()
            ->where('status', 'paid')
            ->with(['items.meal.category', 'items.product'])
            ->orderBy('created_at')
            ->get();

        $paymentBreakdown = $register->payments()
            ->with('paymentType')
            ->get()
            ->groupBy(fn($payment) => $payment->paymentType?->name ?? 'Autre')
            ->map(fn($group) => $group->sum('amount'));

        $terminalStockItems = collect();
        $contractMealsConsumed = collect();
        $extraSold             = collect();
        $remainingStock         = collect();
        $contractTicketLogsByItem = collect();

        if ($register->isCateringPos()) {
            if ($register->pos_terminal_id) {
                $terminalStockItems = \App\Models\PosTerminalStockItem::with('product.unit')
                    ->where('pos_terminal_id', $register->pos_terminal_id)
                    ->orderBy('item_type')
                    ->orderBy('label')
                    ->get();
            }

            foreach ($terminalStockItems as $item) {
                if ($item->item_type === 'contract') {
                    $contractMealsConsumed->push($item);
                } else {
                    $extraSold->push($item);
                }
            }

            $contractItemIds = $contractMealsConsumed->pluck('id')->all();
            if (!empty($contractItemIds)) {
                $contractTicketLogsByItem = PosTerminalTicketLog::where('cash_register_id', $register->id)
                    ->whereIn('pos_terminal_stock_item_id', $contractItemIds)
                    ->orderBy('served_at')
                    ->get()
                    ->groupBy('pos_terminal_stock_item_id');
            }
        }

        $totalCash  = $register->payments()
            ->whereHas('paymentType', fn($q) => $q->where('name', 'like', '%espèce%')->orWhere('name', 'like', '%cash%'))
            ->sum('amount');
        $totalSales = $register->payments()->sum('amount');
        $variance   = (float)$register->closing_balance - (float)$register->opening_balance - $totalCash;

        return view('cashier.report', compact(
            'register', 'restaurantOrders', 'contractMealsConsumed', 'extraSold',
            'remainingStock', 'paymentBreakdown', 'terminalStockItems',
            'contractTicketLogsByItem',
            'totalCash', 'totalSales', 'variance'
        ));
    }

    /**
     * Export CSV des codes ticket saisis pendant la session.
     */
    public function exportTicketCodesCsv($id)
    {
        $register = CashRegister::findOrFail($id);

        if ($register->user_id !== auth()->id() && !auth()->user()->hasRole(['admin', 'accountant', 'super-admin'])) {
            abort(403, 'Accès refusé.');
        }

        $logs = PosTerminalTicketLog::with(['stockItem.meal', 'stockItem.product', 'user'])
            ->where('cash_register_id', $register->id)
            ->orderBy('served_at')
            ->orderBy('id')
            ->get();

        $fileName = 'ticket-codes-session-' . $register->id . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($logs, $register) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel.
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [
                'session_id',
                'point_de_vente',
                'date_heure_service',
                'plat',
                'code_ticket',
                'quantite',
                'caissier',
            ], ';');

            foreach ($logs as $log) {
                $label = $log->stockItem?->meal?->name
                    ?? $log->stockItem?->product?->name
                    ?? $log->stockItem?->label
                    ?? 'Article';

                fputcsv($out, [
                    $register->id,
                    $register->label,
                    optional($log->served_at)->format('d/m/Y H:i:s') ?? optional($log->created_at)->format('d/m/Y H:i:s'),
                    $label,
                    $log->ticket_code ?? '',
                    (string) $log->qty,
                    $log->user?->name ?? '',
                ], ';');
            }

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ── Vue comptable : toutes les sessions ────────────────────────────────

    /**
     * Vue comptable : liste de toutes les sessions (filtrables).
     */
    public function accountantIndex(Request $request)
    {
        $this->perm('accounting.view');

        $query = CashRegister::with(['user', 'client'])
            ->orderByDesc('opened_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->module) {
            $query->where('module', $request->module);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        $registers = $query->paginate(30)->withQueryString();

        $openCount   = CashRegister::where('status', 'open')->count();
        $closedCount = CashRegister::where('status', 'closed')->count();

        return view('cashier.accountant', compact('registers', 'openCount', 'closedCount'));
    }

    /**
     * Comptable valide une session fermée.
     */
    public function validateSession(Request $request, $id)
    {
        $this->perm('accounting.validate');

        $register = CashRegister::findOrFail($id);

        if ($register->status !== 'closed') {
            return back()->with('error', 'Seules les sessions fermées peuvent être validées.');
        }

        $register->update([
            'status'       => 'validated',
            'validated_at' => now(),
            'validated_by' => auth()->id(),
            'accounting_note' => $request->accounting_note ?? $register->accounting_note,
        ]);

        return back()->with('success', 'Session validée.');
    }

    // ── Paramètres : créer une définition de caisse ────────────────────────

    /**
     * Liste des caisses paramétrées (page paramètres).
     */
    public function settingsIndex()
    {
        $this->perm('settings.cashregisters');

        $registers = CashRegister::with(['user', 'client', 'stock'])
            ->latest()
            ->paginate(20);

        $clients = Client::orderBy('name')->get();
        $stocks  = Stock::orderBy('name')->get();

        return view('settings.cash-registers', compact('registers', 'clients', 'stocks'));
    }

    /**
     * Crée une nouvelle définition de caisse (paramètre permanent).
     * Cette caisse sera disponible à l'ouverture de session.
     */
    public function settingsStore(Request $request)
    {
        $this->perm('settings.cashregisters');

        $request->validate([
            'label'     => 'required|string|max:100',
            'module'    => 'required|in:restaurant,catering,events,residence',
            'type'      => 'required|in:ordinary,catering_pos',
            'client_id' => 'nullable|exists:clients,id',
            'stock_id'  => 'nullable|exists:stocks,id',
        ]);

        if ($request->type === 'catering_pos') {
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'stock_id'  => 'required|exists:stocks,id',
            ]);
        }

        CashRegister::create([
            'label'     => $request->label,
            'module'    => $request->module,
            'type'      => $request->type,
            'client_id' => $request->client_id,
            'stock_id'  => $request->stock_id,
            'status'    => 'closed',      // pas encore ouverte
            'shift'     => 'morning',     // défaut, sera écrasé à l'ouverture
            'opening_balance' => 0,
            'opened_at' => now(),
        ]);

        return back()->with('success', 'Caisse créée.');
    }
}
