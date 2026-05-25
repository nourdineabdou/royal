<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Stock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Transaction;
use App\Models\User;
use App\Models\CashRegister;
use App\Models\CashSession;
use Carbon\Carbon;

class PurchaseStockDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un fournisseur
        $supplier = Supplier::firstOrCreate([
            'name' => 'Fournisseur Démo',
        ], [
            'contact' => '222123456',
            'email' => 'fournisseur@demo.com',
        ]);

        // Créer un caissier et une caisse
        $cashier = User::firstOrCreate([
            'email' => 'caissier.achat@royalcomplex.com',
        ], [
            'name' => 'Caissier Achat',
            'password' => bcrypt('password'),
        ]);
        $register = CashRegister::firstOrCreate(['name' => 'Caisse Achats']);
        $session = CashSession::create([
            'cash_register_id' => $register->id,
            'user_id' => $cashier->id,
            'opened_at' => Carbon::now()->subHours(1),
            'closed_at' => null,
            'opening_amount' => 500000,
        ]);

        // Pour chaque stock, créer un achat complet
        foreach (Stock::all() as $stock) {
            $order = PurchaseOrder::create([
                'supplier_id' => $supplier->id,
                'stock_id' => $stock->id,
                'status' => 'received',
                'ordered_at' => Carbon::now()->subDays(2),
                'received_at' => Carbon::now()->subDay(),
                'total_amount' => 0,
            ]);
            $total = 0;
            // Ajouter des produits (3 par stock)
            $products = Product::inRandomOrder()->take(3)->get();
            foreach ($products as $product) {
                $qty = rand(10, 50);
                $price = rand(500, 2000);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                ]);
                $total += $qty * $price;
                // Simuler l'entrée en stock
                $product->stocks()->updateExistingPivot($stock->id, [
                    'quantity' => $qty
                ], false);
            }
            $order->update(['total_amount' => $total]);
            // Paiement de l'achat
            Transaction::create([
                'type' => 'purchase',
                'amount' => $total,
                'reference' => 'PO-' . $order->id,
                'date' => now(),
                'user_id' => $cashier->id,
                'cash_session_id' => $session->id,
                'module' => 'purchase',
            ]);
        }
        $session->update(['closed_at' => Carbon::now()]);
    }
}
