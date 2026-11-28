<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Stock;
use Illuminate\Database\Seeder;

/**
 * Magasin par défaut « Economat ».
 *
 *     php artisan db:seed --class=MagasinSeeder --force
 *
 * Relançable sans risque : le magasin n'est créé que s'il n'existe pas, rien n'est supprimé
 * et aucune quantité n'est touchée. Il devient le magasin de référence (module catering :
 * stock initial, alertes de rupture, sorties cuisine) si aucun autre magasin ne l'est déjà.
 */
class MagasinSeeder extends Seeder
{
    public function run(): void
    {
        $stock = Stock::firstOrCreate(
            ['name' => 'Economat'],
            ['location' => 'Magasin principal', 'company_id' => Company::query()->value('id')]
        );

        $ref = Stock::forModule('catering');
        if (! $ref) {
            $stock->update(['module' => 'catering']);
            $ref = $stock;
        }

        $this->command?->info(
            ($stock->wasRecentlyCreated ? 'Magasin « Economat » créé.' : 'Magasin « Economat » déjà présent.')
            . ' Magasin de référence : ' . $ref->name . '.'
        );
    }
}
