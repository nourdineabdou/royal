<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\JobTitle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class HRSeeder extends Seeder
{
    public function run(): void
    {
        // Rôles (s'assurent qu'ils existent)
        $roleAdmin    = Role::firstOrCreate(['name' => 'admin',    'guard_name' => 'web']);
        $roleServeur  = Role::firstOrCreate(['name' => 'serveur',  'guard_name' => 'web']);
        $roleCaissier = Role::firstOrCreate(['name' => 'caissier', 'guard_name' => 'web']);
        $roleManager  = Role::firstOrCreate(['name' => 'manager',  'guard_name' => 'web']);

        // ─── Employés avec compte utilisateur ─────────────────────────────────
        $staff = [
            [
                'user' => ['name' => 'Ahmed Lebah',    'email' => 'ahmed.lebah@royalhotel.mr',   'password' => 'password123'],
                'role' => $roleManager,
                'emp'  => ['first_name' => 'Ahmed',    'last_name' => 'Lebah',    'phone' => '+222 20 11 22 33', 'address' => 'Nouakchott - Tevragh-Zeina', 'hire_date' => '2022-01-15', 'salary_base' => 150000, 'status' => 'active', 'job' => 'Directeur Restauration'],
            ],
            [
                'user' => ['name' => 'Hamidou Traoré', 'email' => 'hamidou.t@royalhotel.mr',     'password' => 'password123'],
                'role' => $roleAdmin,
                'emp'  => ['first_name' => 'Hamidou',  'last_name' => 'Traoré',   'phone' => '+222 36 44 55 66', 'address' => 'Nouakchott - Ksar',         'hire_date' => '2022-03-01', 'salary_base' => 120000, 'status' => 'active', 'job' => 'Chef Cuisinier'],
            ],
            [
                'user' => ['name' => 'Mint Veilat',    'email' => 'mint.veilat@royalhotel.mr',   'password' => 'password123'],
                'role' => $roleCaissier,
                'emp'  => ['first_name' => 'Veilat',   'last_name' => 'Mint',     'phone' => '+222 22 77 88 99', 'address' => 'Nouakchott - Sebkha',       'hire_date' => '2023-06-01', 'salary_base' => 45000,  'status' => 'active', 'job' => 'Caissier'],
            ],
            [
                'user' => ['name' => 'Moussa Diallo',  'email' => 'moussa.d@royalhotel.mr',      'password' => 'password123'],
                'role' => $roleServeur,
                'emp'  => ['first_name' => 'Moussa',   'last_name' => 'Diallo',   'phone' => '+222 46 33 44 55', 'address' => 'Nouakchott - El Mina',      'hire_date' => '2023-09-15', 'salary_base' => 40000,  'status' => 'active', 'job' => 'Serveur'],
            ],
            [
                'user' => ['name' => 'Binta Sow',      'email' => 'binta.sow@royalhotel.mr',     'password' => 'password123'],
                'role' => $roleServeur,
                'emp'  => ['first_name' => 'Binta',    'last_name' => 'Sow',      'phone' => '+222 46 66 77 88', 'address' => 'Nouakchott - Teyarett',     'hire_date' => '2024-01-10', 'salary_base' => 40000,  'status' => 'active', 'job' => 'Serveur'],
            ],
            [
                'user' => ['name' => 'Samba Sy',       'email' => 'samba.sy@royalhotel.mr',      'password' => 'password123'],
                'role' => $roleCaissier,
                'emp'  => ['first_name' => 'Samba',    'last_name' => 'Sy',       'phone' => '+222 22 99 00 11', 'address' => 'Nouakchott - Toujounine',   'hire_date' => '2023-11-01', 'salary_base' => 45000,  'status' => 'active', 'job' => 'Caissier'],
            ],
        ];

        foreach ($staff as $s) {
            $user = User::firstOrCreate(
                ['email' => $s['user']['email']],
                ['name' => $s['user']['name'], 'password' => Hash::make($s['user']['password'])]
            );
            $user->assignRole($s['role']);

            $jobTitle = JobTitle::where('name', $s['emp']['job'])->first();
            if ($jobTitle) {
                Employee::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'job_title_id' => $jobTitle->id,
                        'first_name'   => $s['emp']['first_name'],
                        'last_name'    => $s['emp']['last_name'],
                        'phone'        => $s['emp']['phone'],
                        'address'      => $s['emp']['address'],
                        'hire_date'    => $s['emp']['hire_date'],
                        'salary_base'  => $s['emp']['salary_base'],
                        'status'       => $s['emp']['status'],
                    ]
                );
            }
        }

        // ─── Employés sans compte utilisateur ─────────────────────────────────
        $staffSansCompte = [
            ['first_name' => 'Aicha',       'last_name' => 'Ould Bilal',    'phone' => '+222 36 12 34 56', 'address' => 'Nouakchott',       'hire_date' => '2021-07-01', 'salary_base' => 30000, 'status' => 'active', 'job' => 'Femme de chambre'],
            ['first_name' => 'Demba',       'last_name' => 'Camara',        'phone' => '+222 20 98 87 76', 'address' => 'Nouakchott',       'hire_date' => '2022-09-01', 'salary_base' => 38000, 'status' => 'active', 'job' => 'Agent de sécurité'],
            ['first_name' => 'Salimata',    'last_name' => 'Konaté',        'phone' => '+222 46 54 32 10', 'address' => 'Nouakchott',       'hire_date' => '2023-02-15', 'salary_base' => 70000, 'status' => 'active', 'job' => 'Pâtissier'],
            ['first_name' => 'Mohamed',     'last_name' => 'Ould Tijani',   'phone' => '+222 22 11 22 33', 'address' => 'Nouakchott',       'hire_date' => '2022-11-01', 'salary_base' => 60000, 'status' => 'active', 'job' => 'Cuisinier'],
            ['first_name' => 'Oum El Kheir','last_name' => 'Mint Ahmed',    'phone' => '+222 36 45 67 89', 'address' => 'Nouakchott',       'hire_date' => '2023-04-01', 'salary_base' => 48000, 'status' => 'active', 'job' => 'Réceptionniste'],
            ['first_name' => 'Boubacar',    'last_name' => 'Ba',            'phone' => '+222 20 23 34 45', 'address' => 'Nouakchott',       'hire_date' => '2024-03-01', 'salary_base' => 30000, 'status' => 'active', 'job' => 'Plongeur'],
            ['first_name' => 'Zeinabou',    'last_name' => 'Ould Meïra',    'phone' => '+222 46 88 99 00', 'address' => 'Nouakchott',       'hire_date' => '2021-10-01', 'salary_base' => 65000, 'status' => 'active', 'job' => 'Responsable Stock'],
            ['first_name' => 'Lamine',      'last_name' => 'Diallo',        'phone' => '+222 22 56 78 90', 'address' => 'Nouakchott',       'hire_date' => '2022-05-15', 'salary_base' => 80000, 'status' => 'active', 'job' => 'Responsable Catering'],
            ['first_name' => 'Isselmou',    'last_name' => 'Ould Sidi',     'phone' => '+222 36 78 90 12', 'address' => 'Nouakchott',       'hire_date' => '2020-06-01', 'salary_base' => 85000, 'status' => 'active', 'job' => 'Comptable'],
            ['first_name' => 'Fatoumata',   'last_name' => 'Balde',         'phone' => '+222 20 34 56 78', 'address' => 'Nouakchott',       'hire_date' => '2023-08-01', 'salary_base' => 40000, 'status' => 'active', 'job' => 'Serveur'],
            ['first_name' => 'Idoumou',     'last_name' => 'Ould Mohamed',  'phone' => '+222 46 90 12 34', 'address' => 'Nouakchott',       'hire_date' => '2021-03-15', 'salary_base' => 38000, 'status' => 'inactive', 'job' => 'Agent de sécurité'],
            ['first_name' => 'Rokia',       'last_name' => 'Coulibaly',     'phone' => '+222 22 12 23 34', 'address' => 'Nouakchott',       'hire_date' => '2022-12-01', 'salary_base' => 35000, 'status' => 'active', 'job' => 'Femme de chambre'],
        ];

        foreach ($staffSansCompte as $e) {
            $jobTitle = JobTitle::where('name', $e['job'])->first();
            if (!$jobTitle) continue;
            Employee::firstOrCreate(
                ['first_name' => $e['first_name'], 'last_name' => $e['last_name']],
                [
                    'job_title_id' => $jobTitle->id,
                    'phone'        => $e['phone'],
                    'address'      => $e['address'],
                    'hire_date'    => $e['hire_date'],
                    'salary_base'  => $e['salary_base'],
                    'status'       => $e['status'],
                    'user_id'      => null,
                ]
            );
        }

        $this->command->info('✅ RH créé : ' . Employee::count() . ' employés, ' . User::count() . ' comptes utilisateurs.');
    }
}
