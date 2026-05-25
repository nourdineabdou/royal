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

        echo "✅ Utilisateurs créés avec succès!\n";
        echo "   - Super Admin: admin@example.com / password123\n";
        echo "   - Admin: manager@example.com / password123\n";
        echo "   - Modérateur: moderator@example.com / password123\n";
        echo "   - Utilisateur: user@example.com / password123\n";
    }
}
