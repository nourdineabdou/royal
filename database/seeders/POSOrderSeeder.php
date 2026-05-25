<?php

namespace Database\Seeders;

use App\Models\CashRegister;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class POSOrderSeeder extends Seeder
{
    public function run(): void
    {
        $serveur  = User::whereHas('roles', fn($q) => $q->where('name', 'serveur'))->first()
                  ?? User::first();
        $caissier = User::whereHas('roles', fn($q) => $q->where('name', 'caissier'))->first()
                  ?? User::first();

        $cashRegister = CashRegister::where('user_id', $caissier->id)->first();

        $especes = PaymentType::where('name', 'like', '%Espèce%')->first();
        $carte   = PaymentType::where('name', 'like', '%Carte%')->first();
        $mobile  = PaymentType::where('name', 'like', '%Mobile%')->first();

        $allMeals = Meal::all();
        if ($allMeals->isEmpty()) {
            $this->command->warn('⚠️ Aucun plat trouvé. Lancez MenuSeeder d\'abord.');
            return;
        }

        // 15 commandes payées des 7 derniers jours
        for ($i = 1; $i <= 15; $i++) {
            $orderDate = Carbon::now()->subDays(rand(0, 6))->setTime(rand(11, 22), rand(0, 59));

            $order = Order::create([
                'customer_number' => 'T' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'server_id'       => $serveur->id,
                'cashier_id'      => $caissier->id,
                'total_amount'    => 0,
                'status'          => 'paid',
                'is_prepared'     => true,
                'sent_at'         => $orderDate->copy()->subMinutes(rand(15, 40)),
                'paid_at'         => $orderDate,
                'created_at'      => $orderDate,
                'updated_at'      => $orderDate,
            ]);

            $total = 0;
            $nbPlats = rand(1, 4);
            $randomMeals = $allMeals->random(min($nbPlats, $allMeals->count()));

            foreach ($randomMeals as $meal) {
                $qty = rand(1, 3);
                OrderItem::create([
                    'order_id'   => $order->id,
                    'meal_id'    => $meal->id,
                    'quantity'   => $qty,
                    'price'      => $meal->price,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
                $total += $meal->price * $qty;

                // Décrémentation du stock pour chaque ingrédient de la recette
                $stock = \App\Models\Stock::forModule('restaurant');
                if ($stock && $meal->recipe) {
                    foreach ($meal->recipe->items as $item) {
                        $product = $item->product;
                        if ($product) {
                            $stockItem = $product->stockItems()->where('stock_id', $stock->id)->first();
                            if ($stockItem) {
                                $stockItem->decrement('quantity', $item->quantity * $qty);
                            }
                        }
                    }
                }
            }

            $order->update(['total_amount' => $total]);

            // Moyen de paiement aléatoire
            $paymentTypes = array_filter([$especes, $carte, $mobile]);
            $payType = collect($paymentTypes)->random();

            Payment::create([
                'order_id'        => $order->id,
                'payment_type_id' => $payType->id,
                'amount'          => $total,
                'created_at'      => $orderDate,
                'updated_at'      => $orderDate,
            ]);

            // Transaction comptable
            Transaction::create([
                'type'      => 'sale',
                'amount'    => $total,
                'reference' => 'POS-' . $order->id,
                'date'      => $orderDate->toDateString(),
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
                'module'    => 'pos',
            ]);
        }

        // 3 commandes en cours (statut "sent" / en préparation)
        for ($i = 1; $i <= 3; $i++) {
            $orderDate = Carbon::now()->subMinutes(rand(5, 30));
            $order = Order::create([
                'customer_number' => 'T' . (15 + $i),
                'server_id'       => $serveur->id,
                'cashier_id'      => null,
                'total_amount'    => 0,
                'status'          => 'sent',
                'is_prepared'     => false,
                'sent_at'         => $orderDate,
                'created_at'      => $orderDate,
                'updated_at'      => $orderDate,
            ]);

            $total = 0;
            $randomMeals = $allMeals->random(min(rand(1, 3), $allMeals->count()));
            foreach ($randomMeals as $meal) {
                $qty = rand(1, 2);
                OrderItem::create([
                    'order_id'   => $order->id,
                    'meal_id'    => $meal->id,
                    'quantity'   => $qty,
                    'price'      => $meal->price,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
                $total += $meal->price * $qty;
            }
            $order->update(['total_amount' => $total]);
        }

        $this->command->info('✅ POS Orders : '
            . Order::count() . ' commandes, '
            . Payment::where('order_id', '>', 0)->count() . ' paiements, '
            . Transaction::where('type', 'sale')->count() . ' transactions.'
        );
    }
}
