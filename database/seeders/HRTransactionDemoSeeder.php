<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CashRegister;
use App\Models\Transaction;
use App\Models\Payroll;
use Carbon\Carbon;

class HRTransactionDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Caissier paie
        $cashier = User::firstOrCreate([
            'email' => 'caissier.rh@royalcomplex.com',
        ], [
            'name'     => 'Caissier RH',
            'password' => bcrypt('password'),
        ]);

        $register = CashRegister::create([
            'user_id'         => $cashier->id,
            'module'          => 'restaurant',
            'shift'           => 'morning',
            'opening_balance' => 200000,
            'opened_at'       => Carbon::now()->subHours(1),
            'status'          => 'open',
        ]);

        // Paiement de la paie
        $payroll = Payroll::first();
        if ($payroll) {
            Transaction::create([
                'type'      => 'salary',
                'module'    => 'restaurant',
                'amount'    => $payroll->net_salary,
                'reference' => 'PAY-' . $payroll->id,
                'date'      => now()->toDateString(),
            ]);
        }

        $register->update([
            'closed_at'       => Carbon::now(),
            'closing_balance' => 200000,
            'status'          => 'closed',
        ]);
    }
}
