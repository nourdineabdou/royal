<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CashierSeeder extends Seeder
{
    public function run(): void
    {
        // Crée le rôle caissier s'il n'existe pas
        $cashierRole = Role::firstOrCreate(['name' => 'caissier', 'guard_name' => 'web']);

        $modules = ['restaurant', 'catering', 'events', 'residence'];
        $shifts = ['morning' => 'Matin', 'evening' => 'Soir'];

        $users = [];
        foreach ($modules as $module) {
            foreach ($shifts as $shiftKey => $shiftLabel) {
                $name = ucfirst($module) . ' Caissier ' . $shiftLabel;
                $email = $module . '.' . $shiftKey . '@example.com';
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => Hash::make('password123'),
                    ]
                );
                $user->assignRole($cashierRole);
                $users[] = $user;
            }
        }
        // Un caissier générique qui peut faire matin/soir
        $generic = User::firstOrCreate(
            ['email' => 'caissier@example.com'],
            [
                'name' => 'Caissier Général',
                'password' => Hash::make('password123'),
            ]
        );
        $generic->assignRole($cashierRole);
    }
}
