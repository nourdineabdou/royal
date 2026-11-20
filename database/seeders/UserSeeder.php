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

        // Changement de domaine : les comptes existants en @royalhotel.mr passent en @complexroyal.com
        // (même compte : mot de passe, nom d'utilisateur et rôles inchangés), au lieu d'en créer de nouveaux.
        foreach (User::where('email', 'like', '%@royalhotel.mr')->get() as $old) {
            $new = str_replace('@royalhotel.mr', '@complexroyal.com', $old->email);
            if (!User::where('email', $new)->exists()) {
                $old->update(['email' => $new]);
            }
        }

        // Deuxième super-admin
        $saydou = User::firstOrCreate(
            ['email' => 'saydou@complexroyal.com'],
            [
                'name' => 'Saydou',
                'password' => Hash::make('royal2026'),
            ]
        );
        $saydou->assignRole('super-admin');

        // Responsable RH
        Role::firstOrCreate(['name' => 'rh', 'guard_name' => 'web']);
        $salamata = User::firstOrCreate(
            ['email' => 'salamata.ball@complexroyal.com'],
            [
                'name' => 'Salamata Ball',
                'password' => Hash::make('RhRoyal#2026'),
            ]
        );
        $salamata->assignRole('rh');

        // Circuit stock / achats (les rôles viennent de PermissionSeeder).
        // Mot de passe provisoire « password » : chacun le change à sa première connexion.
        // firstOrCreate : un compte existant garde le mot de passe que son titulaire a choisi.
        $staff = [
            ['email' => 'sambou@complexroyal.com',      'name' => 'Sambou',      'role' => 'magasinier'],
            ['email' => 'youba@complexroyal.com',       'name' => 'Youba',       'role' => 'achat'],
            ['email' => 'tandia@complexroyal.com',      'name' => 'Tandia',      'role' => 'responsable-achat'],
            ['email' => 'abdelrahim@complexroyal.com',  'name' => 'Abdel Rahim', 'role' => 'super-admin'],
            ['email' => 'fatimatou@complexroyal.com',   'name' => 'Fatimatou',   'role' => 'cuisinier'],
            ['email' => 'boris@complexroyal.com',       'name' => 'Boris',       'role' => 'cuisinier'],
        ];
        foreach ($staff as $s) {
            Role::firstOrCreate(['name' => $s['role'], 'guard_name' => 'web']);
            User::firstOrCreate(['email' => $s['email']], [
                'name' => $s['name'], 'password' => Hash::make('password'), 'must_change_password' => true,
            ])->syncRoles([$s['role']]);
        }

        echo "✅ Utilisateurs créés avec succès!\n";
        echo "   - Super Admin: nourdine@gmail.com / password123\n";
        echo "   - Super Admin: saydou@complexroyal.com / royal2026\n";
        echo "   - RH: salamata.ball@complexroyal.com / RhRoyal#2026\n";
    }
}
