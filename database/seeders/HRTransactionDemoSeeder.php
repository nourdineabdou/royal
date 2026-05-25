<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Transaction;
use App\Models\HRPayroll;
use Carbon\Carbon;

class HRTransactionDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Caissier paie
        $cashier = User::firstOrCreate([
            'email' => 'caissier.rh@royalcomplex.com',
        ], [
            'name' => 'Caissier RH',
            'password' => bcrypt('password'),
        ]);
        $register = CashRegister::firstOrCreate(['name' => 'Caisse RH']);
        $session = CashSession::create([
            'cash_register_id' => $register->id,
            'user_id' => $cashier->id,
            'opened_at' => Carbon::now()->subHours(1),
            'closed_at' => null,
            'opening_amount' => 200000,
        ]);
        // Paiement de la paie
        $payroll = HRPayroll::first();
        if ($payroll) {
            Transaction::create([
                'type' => 'payroll',
                'amount' => $payroll->net_salary,
                'reference' => 'PAY-' . $payroll->id,
                'date' => now(),
                'user_id' => $cashier->id,
                'cash_session_id' => $session->id,
                'module' => 'hr',
            ]);
        }
        $session->update(['closed_at' => Carbon::now()]);
    }
}
