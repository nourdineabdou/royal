<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Transaction;
use App\Models\User;
use App\Models\CashRegister;
use Carbon\Carbon;

class PurchaseStockDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un fournisseur
        $supplier = Supplier::firstOrCreate([
            'name' => 'Fournisseur Démo',
        ], [
            'phone' => '222123456',
            'email' => 'fournisseur@demo.com',
        ]);

        // Créer un caissier et une caisse
        $cashier = User::firstOrCreate([
            'email' => 'caissier.achat@royalcomplex.com',
        ], [
            'name' => 'Caissier Achat',
            'password' => bcrypt('password'),
        ]);

        $register = CashRegister::create([
            'user_id'         => $cashier->id,
            'module'          => 'restaurant',
            'shift'           => 'morning',
            'opening_balance' => 500000,
            'opened_at'       => Carbon::now()->subHours(1),
            'status'          => 'open',
        ]);

        // Pour chaque stock, créer un achat complet
        foreach (Stock::all() as $stock) {
            $order = PurchaseOrder::create([
                'supplier_id'  => $supplier->id,
                'status'       => 'received',
                'total_amount' => 0,
            ]);

            $total = 0;
            // Ajouter des produits (3 par stock)
            $products = Product::inRandomOrder()->take(3)->get();
            foreach ($products as $product) {
                $qty   = rand(10, 50);
                $price = rand(500, 2000);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id'        => $product->id,
                    'quantity'          => $qty,
                    'price'             => $price,
                    'total'             => $qty * $price,
                ]);

                $total += $qty * $price;

                // Mettre à jour le stock
                StockItem::updateOrCreate(
                    ['stock_id' => $stock->id, 'product_id' => $product->id],
                    ['quantity' => $qty]
                );
            }

            $order->update(['total_amount' => $total]);

            // Enregistrer la transaction d'achat
            Transaction::create([
                'type'      => 'purchase',
                'module'    => 'restaurant',
                'amount'    => $total,
                'reference' => 'PO-' . $order->id,
                'date'      => now()->toDateString(),
            ]);
        }

        $register->update([
            'closed_at'       => Carbon::now(),
            'closing_balance' => 500000,
            'status'          => 'closed',
        ]);
    }
}
