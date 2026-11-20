<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Mise à jour de l'hébergeur (base déjà en service) — à lancer APRÈS les migrations :
 *
 *     php artisan migrate --force
 *     php artisan db:seed --class=DeploiementSeeder --force
 *
 * Relançable sans risque : chaque seeder ne crée que ce qui manque (rien n'est supprimé,
 * aucun stock n'est touché, les mots de passe déjà choisis sont conservés).
 *
 *  1. Rôles et permissions (magasinier, acheteur, responsable des achats, cuisinier…)
 *  2. Données de référence : unités, emballages, catégories…
 *  3. Catalogue produits Complex Royal avec leurs emballages (sac, carton…)
 *  4. Utilisateurs (Sambou, Youba, Tandia, Abdel Rahim, Fatimatou, Boris…) avec leur rôle ;
 *     mot de passe provisoire « password » à changer à la première connexion.
 */
class DeploiementSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            ReferenceDataSeeder::class,
            ComplexRoyalProductSeeder::class,
            UserSeeder::class,
        ]);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('');
        $this->command?->info('Bilan : ' . Product::count() . ' produits, ' . ProductPackaging::count() . ' emballages produit, ' . User::count() . ' utilisateurs.');
        foreach (User::with('roles')->orderBy('id')->get() as $u) {
            $this->command?->line(sprintf('  %-18s %-34s %s%s', $u->username, $u->email, $u->roles->pluck('name')->join(', '),
                $u->must_change_password ? '  (mot de passe provisoire)' : ''));
        }
    }
}
