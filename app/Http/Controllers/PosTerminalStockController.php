<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\PosTerminalStockItem;
use App\Models\PosTerminalTicketLog;
use App\Models\Transaction;
use App\Services\AccountingEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * API endpoints pour la gestion du stock d'un terminal POS.
 * - distribute : décrémente quantity_served (plat contrat)
 * - sell       : décrémente quantity_sold (produit extra) et encaisse le paiement
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

        $activeRegister = CashRegister::where('pos_terminal_id', $terminal->id)
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
     * Vente d'un produit extra : marque la quantité vendue ET encaisse le
     * paiement (Order + Payment + Transaction + écriture comptable),
     * même schéma que POSController::cateringCreateOrder().
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

        $request->validate([
            'qty' => 'required|numeric|min:0.5',
            'payment_type_id' => 'required|exists:payment_types,id',
        ]);

        if (!PaymentType::isPosAllowed((int) $request->payment_type_id)) {
            return response()->json([
                'error' => 'Mode de paiement non autorise en point de vente. Utilisez espece ou wallet (Bankily, Sadad, Masrivi, Click).',
            ], 422);
        }

        if ($request->qty > $stockItem->available_qty) {
            return response()->json(['error' => 'Quantité insuffisante.'], 422);
        }

        $activeRegister = CashRegister::where('pos_terminal_id', $terminal->id)
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if (!$activeRegister) {
            return response()->json(['error' => 'Aucune caisse active pour ce terminal.'], 422);
        }

        $price = $stockItem->product ? (float) $stockItem->product->sale_price : 0;
        $qty = (float) $request->qty;
        $total = $price * $qty;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'server_id'        => $user->id,
                'cashier_id'       => $user->id,
                'cash_register_id' => $activeRegister->id,
                'total_amount'     => $total,
                'status'           => 'paid',
                'is_prepared'      => true,
                'paid_at'          => now(),
            ]);

            OrderItem::create([
                'order_id'   => $order->id,
                'meal_id'    => null,
                'product_id' => $stockItem->product_id,
                'label'      => $stockItem->label,
                'quantity'   => $qty,
                'price'      => $price,
                'cost_price' => 0,
            ]);

            $stockItem->increment('quantity_sold', $qty);

            $payment = Payment::create([
                'order_id'         => $order->id,
                'payment_type_id'  => $request->payment_type_id,
                'amount'           => $order->total_amount,
                'cash_register_id' => $activeRegister->id,
            ]);

            Transaction::create([
                'type'      => 'sale',
                'amount'    => $order->total_amount,
                'reference' => 'CAT-' . $order->id,
                'date'      => now()->toDateString(),
                'module'    => 'catering',
            ]);

            app(AccountingEntryService::class)->postSale($payment, 'catering');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'success'   => true,
            'remaining' => $stockItem->fresh()->available_qty,
            'total'     => $total,
        ]);
    }
}
