<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Transaction;
use App\Models\Event;
use App\Models\CateringContract;
use App\Models\ResidenceBooking;
use Carbon\Carbon;

class PaymentSessionDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un caissier
        $cashier = User::firstOrCreate([
            'email' => 'caissier.demo@royalcomplex.com',
        ], [
            'name' => 'Caissier Démo',
            'password' => bcrypt('password'),
        ]);

        // Créer une caisse
        $register = CashRegister::firstOrCreate([
            'name' => 'Caisse principale',
        ]);

        // Ouvrir une session de caisse
        $session = CashSession::create([
            'cash_register_id' => $register->id,
            'user_id' => $cashier->id,
            'opened_at' => Carbon::now()->subHours(2),
            'closed_at' => null,
            'opening_amount' => 100000,
        ]);

        // Paiement d'un événement
        $event = Event::first();
        if ($event) {
            Transaction::create([
                'type' => 'event',
                'amount' => 50000,
                'reference' => 'EVT-' . $event->id,
                'date' => now(),
                'user_id' => $cashier->id,
                'cash_session_id' => $session->id,
                'module' => 'event',
            ]);
            // Décrémentation du stock pour chaque plat servi à l'événement
            foreach ($event->stockUsages as $usage) {
                $product = $usage->product;
                if ($product) {
                    $stockItem = $product->stockItems()->where('stock_id', $event->stock_id)->first();
                    if ($stockItem) {
                        $stockItem->decrement('quantity', $usage->quantity);
                    }
                }
            }
        }

        // Paiement d'un contrat catering
        $catering = CateringContract::first();
        if ($catering) {
            Transaction::create([
                'type' => 'catering',
                'amount' => 75000,
                'reference' => 'CAT-' . $catering->id,
                'date' => now(),
                'user_id' => $cashier->id,
                'cash_session_id' => $session->id,
                'module' => 'catering',
            ]);
            // Décrémentation du stock pour chaque plat consommé dans le catering (exemple simplifié)
            $stock = \App\Models\Stock::forModule('catering');
            if ($stock) {
                $meals = \App\Models\Meal::take(2)->get();
                foreach ($meals as $meal) {
                    if ($meal->recipe) {
                        foreach ($meal->recipe->items as $item) {
                            $product = $item->product;
                            if ($product) {
                                $stockItem = $product->stockItems()->where('stock_id', $stock->id)->first();
                                if ($stockItem) {
                                    $stockItem->decrement('quantity', $item->quantity * 10);
                                }
                            }
                        }
                    }
                }
            }
        }

        // Paiement d'une réservation résidence
        $res = ResidenceBooking::first();
        if ($res) {
            Transaction::create([
                'type' => 'residence',
                'amount' => 30000,
                'reference' => 'RES-' . $res->id,
                'date' => now(),
                'user_id' => $cashier->id,
                'cash_session_id' => $session->id,
                'module' => 'residence',
            ]);
            // Décrémentation du stock pour la résidence (exemple : minibar, room service, à adapter selon modèle)
            $stock = \App\Models\Stock::where('name', 'Stock Réserve Sèche')->first();
            if ($stock) {
                $products = \App\Models\Product::take(2)->get();
                foreach ($products as $product) {
                    $stockItem = $product->stockItems()->where('stock_id', $stock->id)->first();
                    if ($stockItem) {
                        $stockItem->decrement('quantity', 2);
                    }
                }
            }
        }

        // Fermer la session de caisse
        $session->update(['closed_at' => Carbon::now()]);
    }
}
