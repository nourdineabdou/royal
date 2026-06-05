<?php

namespace App\Http\Controllers;

use App\Models\PosTerminalStockItem;
use App\Models\PosTerminalTicketLog;
use Illuminate\Http\Request;

/**
 * API endpoints pour la gestion du stock d'un terminal POS.
 * - distribute : décrémente quantity_served (plat contrat)
 * - sell       : décrémente quantity_sold (produit extra)
 */
class PosTerminalStockController extends Controller
{
    /**
     * POST /pos-terminal-stock/{id}/distribute
     * Décrémente la quantité distribuée (plat contrat catering).
     */
    public function distribute(Request $request, $id)
    {
        $stockItem = PosTerminalStockItem::findOrFail($id);

        // Vérifier que le caissier connecté est lié à ce terminal
        $user = auth()->user();
        $terminal = $stockItem->terminal;
        if ($terminal->cashier_morning_id !== $user->id && $terminal->cashier_evening_id !== $user->id) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $request->validate([
            'qty' => 'required|numeric|min:0.5',
            'ticket_code' => 'nullable|string|max:100',
        ]);

        $ticketCode = trim((string) $request->input('ticket_code', ''));
        $ticketCode = $ticketCode !== '' ? $ticketCode : null;

        $newServed = $stockItem->quantity_served + $request->qty;
        $available = $stockItem->available_qty;

        if ($request->qty > $available) {
            return response()->json(['error' => 'Quantité insuffisante (disponible: ' . $available . ').'], 422);
        }

        $stockItem->increment('quantity_served', $request->qty);

        $activeRegister = \App\Models\CashRegister::where('pos_terminal_id', $terminal->id)
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        PosTerminalTicketLog::create([
            'pos_terminal_stock_item_id' => $stockItem->id,
            'cash_register_id' => $activeRegister?->id,
            'user_id' => $user->id,
            'ticket_code' => $ticketCode,
            'qty' => $request->qty,
            'served_at' => now(),
        ]);

        return response()->json([
            'success'   => true,
            'remaining' => $stockItem->fresh()->available_qty,
            'ticket_code' => $ticketCode,
        ]);
    }

    /**
     * POST /pos-terminal-stock/{id}/sell
     * Marque une quantité comme vendue (produit extra).
     */
    public function sell(Request $request, $id)
    {
        $stockItem = PosTerminalStockItem::findOrFail($id);

        $user = auth()->user();
        $terminal = $stockItem->terminal;
        if ($terminal->cashier_morning_id !== $user->id && $terminal->cashier_evening_id !== $user->id) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        if ($stockItem->item_type !== 'extra') {
            return response()->json(['error' => 'Cet article est un plat contrat, pas un produit à vendre.'], 422);
        }

        $request->validate(['qty' => 'required|numeric|min:0.5']);

        if ($request->qty > $stockItem->available_qty) {
            return response()->json(['error' => 'Quantité insuffisante.'], 422);
        }

        $stockItem->increment('quantity_sold', $request->qty);

        return response()->json([
            'success'   => true,
            'remaining' => $stockItem->fresh()->available_qty,
        ]);
    }
}
