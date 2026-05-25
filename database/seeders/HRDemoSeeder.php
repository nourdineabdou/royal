<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\HRContract;
use App\Models\HRLeave;
use App\Models\HRAbsence;
use App\Models\HRPayroll;
use Carbon\Carbon;

class HRDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Employé avec compte utilisateur
        $user = User::firstOrCreate([
            'email' => 'employe.demo@royalcomplex.com',
        ], [
            'name' => 'Employé Démo',
            'password' => bcrypt('password'),
        ]);
        $employee = Employee::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'name' => 'Employé Démo',
            'function' => 'Serveur',
            'hiring_date' => Carbon::now()->subYears(2),
        ]);

        // Employé sans compte utilisateur
        $employee2 = Employee::firstOrCreate([
            'name' => 'Employé Sans Compte',
        ], [
            'function' => 'Cuisinier',
            'hiring_date' => Carbon::now()->subYears(1),
        ]);

        // Contrat RH
        HRContract::firstOrCreate([
            'employee_id' => $employee->id,
        ], [
            'start_date' => Carbon::now()->subYears(2),
            'end_date' => null,
            'type' => 'CDI',
            'salary' => 120000,
        ]);

        // Congé validé
        HRLeave::create([
            'employee_id' => $employee->id,
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->subMonths(2)->addDays(10),
            'type' => 'annuel',
            'status' => 'approved',
        ]);

        // Absence non justifiée
        HRAbsence::create([
            'employee_id' => $employee2->id,
            'date' => Carbon::now()->subDays(5),
            'reason' => 'Non justifiée',
        ]);

        // Fiche de paie
        HRPayroll::create([
            'employee_id' => $employee->id,
            'month' => Carbon::now()->subMonth()->format('Y-m'),
            'base_salary' => 120000,
            'bonus' => 10000,
            'deduction' => 5000,
            'net_salary' => 125000,
            'status' => 'paid',
        ]);
    }
}
