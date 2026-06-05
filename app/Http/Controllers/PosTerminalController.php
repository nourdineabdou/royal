<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Client;
use App\Models\PosTerminal;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Administration des terminaux POS (points de vente).
 *
 * Accessible uniquement par les rôles admin / super-admin.
 * Permet de créer, modifier, supprimer et assigner les caissiers aux terminaux.
 */
class PosTerminalController extends Controller
{
    private function requireAdmin(): void
    {
        if (!auth()->user()->hasRole(['admin', 'super-admin', 'manager'])) {
            abort(403);
        }
    }

    public function index()
    {
        $this->requireAdmin();

        $terminals = PosTerminal::with([
            'client', 'stock',
            'cashierMorning', 'cashierEvening',
            'activeSession.user',
        ])->orderBy('label')->get();

        return view('settings.pos-terminals.index', compact('terminals'));
    }

    public function create()
    {
        $this->requireAdmin();

        $clients  = Client::orderBy('name')->get();
        $stocks   = Stock::orderBy('name')->get();
        $cashiers = User::role('caissier')->orderBy('name')->get();

        return view('settings.pos-terminals.create', compact('clients', 'stocks', 'cashiers'));
    }

    public function store(Request $request)
    {
        $this->requireAdmin();

        $data = $request->validate([
            'label'              => 'required|string|max:100',
            'type'               => 'required|in:ordinary,catering_pos',
            'module'             => 'required|in:restaurant,catering,events,residence',
            'client_id'          => 'nullable|exists:clients,id',
            'stock_id'           => 'nullable|exists:stocks,id',
            'cashier_morning_id' => 'nullable|exists:users,id',
            'cashier_evening_id' => 'nullable|exists:users,id',
            'is_active'          => 'boolean',
            'notes'              => 'nullable|string|max:500',
        ]);

        if ($request->type === 'catering_pos') {
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'stock_id'  => 'required|exists:stocks,id',
            ]);
        }

        $data['is_active'] = $request->boolean('is_active', true);

        PosTerminal::create($data);

        return redirect()->route('settings.pos-terminals.index')
            ->with('success', 'Terminal POS créé avec succès.');
    }

    public function edit(PosTerminal $posTerminal)
    {
        $this->requireAdmin();

        $clients  = Client::orderBy('name')->get();
        $stocks   = Stock::orderBy('name')->get();
        $cashiers = User::role('caissier')->orderBy('name')->get();

        return view('settings.pos-terminals.create', compact('posTerminal', 'clients', 'stocks', 'cashiers'));
    }

    public function update(Request $request, PosTerminal $posTerminal)
    {
        $this->requireAdmin();

        $data = $request->validate([
            'label'              => 'required|string|max:100',
            'type'               => 'required|in:ordinary,catering_pos',
            'module'             => 'required|in:restaurant,catering,events,residence',
            'client_id'          => 'nullable|exists:clients,id',
            'stock_id'           => 'nullable|exists:stocks,id',
            'cashier_morning_id' => 'nullable|exists:users,id',
            'cashier_evening_id' => 'nullable|exists:users,id',
            'is_active'          => 'boolean',
            'notes'              => 'nullable|string|max:500',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $posTerminal->update($data);

        return redirect()->route('settings.pos-terminals.index')
            ->with('success', 'Terminal POS mis à jour.');
    }

    public function destroy(PosTerminal $posTerminal)
    {
        $this->requireAdmin();

        if ($posTerminal->activeSession()->exists()) {
            return back()->with('error', 'Impossible de supprimer un terminal avec une session ouverte.');
        }

        $posTerminal->delete();

        return redirect()->route('settings.pos-terminals.index')
            ->with('success', 'Terminal POS supprimé.');
    }

    /**
     * Dissocier un caissier d'un terminal (retirer l'affectation matin ou soir).
     */
    public function dissociate(Request $request, PosTerminal $posTerminal)
    {
        $this->requireAdmin();

        $shift = $request->validate(['shift' => 'required|in:morning,evening'])['shift'];

        if ($shift === 'morning') {
            $posTerminal->update(['cashier_morning_id' => null]);
        } else {
            $posTerminal->update(['cashier_evening_id' => null]);
        }

        return back()->with('success', 'Caissier dissocié du terminal.');
    }
}
