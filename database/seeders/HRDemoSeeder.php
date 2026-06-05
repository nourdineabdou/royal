<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Leave;
use App\Models\Payroll;
use Carbon\Carbon;

class HRDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Intitulé de poste requis
        $jobServeur   = JobTitle::firstOrCreate(['name' => 'Serveur'],   ['base_salary' => 120000]);
        $jobCuisinier = JobTitle::firstOrCreate(['name' => 'Cuisinier'], ['base_salary' => 100000]);

        // Employé avec compte utilisateur
        $user = User::firstOrCreate([
            'email' => 'employe.demo@royalcomplex.com',
        ], [
            'name'     => 'Employé Démo',
            'password' => bcrypt('password'),
        ]);
        $employee = Employee::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'first_name'   => 'Employé',
            'last_name'    => 'Démo',
            'job_title_id' => $jobServeur->id,
            'hire_date'    => Carbon::now()->subYears(2)->toDateString(),
            'status'       => 'active',
        ]);

        // Employé sans compte utilisateur
        $employee2 = Employee::firstOrCreate([
            'first_name' => 'Sans',
            'last_name'  => 'Compte',
        ], [
            'job_title_id' => $jobCuisinier->id,
            'hire_date'    => Carbon::now()->subYears(1)->toDateString(),
            'status'       => 'active',
        ]);

        // Congé annuel validé
        Leave::create([
            'employee_id' => $employee->id,
            'start_date'  => Carbon::now()->subMonths(2)->toDateString(),
            'end_date'    => Carbon::now()->subMonths(2)->addDays(10)->toDateString(),
            'days'        => 10,
            'type'        => 'annual',
            'status'      => 'approved',
        ]);

        // Congé non payé (remplace absence)
        Leave::create([
            'employee_id' => $employee2->id,
            'start_date'  => Carbon::now()->subDays(5)->toDateString(),
            'end_date'    => Carbon::now()->subDays(5)->toDateString(),
            'days'        => 1,
            'type'        => 'unpaid',
            'status'      => 'approved',
            'reason'      => 'Non justifiée',
        ]);

        // Fiche de paie
        $prevMonth = Carbon::now()->subMonth();
        Payroll::create([
            'employee_id' => $employee->id,
            'month'       => (int) $prevMonth->format('m'),
            'year'        => (int) $prevMonth->format('Y'),
            'base_salary' => 120000,
            'bonus'       => 10000,
            'deduction'   => 5000,
            'net_salary'  => 125000,
            'status'      => 'paid',
            'paid_at'     => Carbon::now(),
        ]);
    }
}
