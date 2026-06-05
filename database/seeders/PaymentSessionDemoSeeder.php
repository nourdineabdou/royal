<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CashRegister;
use App\Models\Transaction;
use App\Models\Event;
use App\Models\CateringContract;
use Carbon\Carbon;

class PaymentSessionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $cashier = User::firstOrCreate(
            ['email' => 'caissier.demo@royalcomplex.com'],
            ['name' => 'Caissier Demo', 'password' => bcrypt('password')]
        );

        $register = CashRegister::create([
            'user_id'         => $cashier->id,
            'module'          => 'events',
            'shift'           => 'morning',
            'opening_balance' => 100000,
            'opened_at'       => Carbon::now()->subHours(2),
            'status'          => 'open',
        ]);

        $event = Event::first();
        if ($event) {
            Transaction::create([
                'type'      => 'sale',
                'module'    => 'events',
                'amount'    => 50000,
                'reference' => 'EVT-' . $event->id,
                'date'      => now()->toDateString(),
            ]);
        }

        $catering = CateringContract::first();
        if ($catering) {
            Transaction::create([
                'type'      => 'sale',
                'module'    => 'catering',
                'amount'    => 75000,
                'reference' => 'CAT-' . $catering->id,
                'date'      => now()->toDateString(),
            ]);
        }

        $register->update([
            'closed_at'       => Carbon::now(),
            'closing_balance' => 225000,
            'status'          => 'closed',
        ]);
    }
}
