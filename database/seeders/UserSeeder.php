<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'nourdine@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
            ]
        );
        // si role n'existe pas, il sera créé automatiquement grâce à la méthode assignRole
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->assignRole('super-admin');

        // Deuxième super-admin
        $saydou = User::firstOrCreate(
            ['email' => 'saydou@royalhotel.mr'],
            [
                'name' => 'Saydou',
                'password' => Hash::make('royal2026'),
            ]
        );
        $saydou->assignRole('super-admin');

        // Responsable RH
        Role::firstOrCreate(['name' => 'rh', 'guard_name' => 'web']);
        $salamata = User::firstOrCreate(
            ['email' => 'salamata.ball@royalhotel.mr'],
            [
                'name' => 'Salamata Ball',
                'password' => Hash::make('RhRoyal#2026'),
            ]
        );
        $salamata->assignRole('rh');

        echo "✅ Utilisateurs créés avec succès!\n";
        echo "   - Super Admin: nourdine@gmail.com / password123\n";
        echo "   - Super Admin: saydou@royalhotel.mr / royal2026\n";
        echo "   - RH: salamata.ball@royalhotel.mr / RhRoyal#2026\n";
    }
}
