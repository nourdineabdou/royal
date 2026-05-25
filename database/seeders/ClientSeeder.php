<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            // ── Entreprises ────────────────────────────────────────────────────
            [
                'name'    => 'Abdallah Ould Mohamed',
                'phone'   => '+222 20 12 34 56',
                'email'   => 'abdallah@somelec.mr',
                'company' => 'SOMELEC',
                'notes'   => 'Client régulier. Contrat catering actif pour 50 personnes. Paiement mensuel par virement.',
            ],
            [
                'name'    => 'Fatimata Mint Cheikh',
                'phone'   => '+222 36 98 76 54',
                'email'   => 'fatimata@snpt.mr',
                'company' => 'SNPT',
                'notes'   => 'Responsable RH. Organise des événements trimestriels et repas d\'entreprise.',
            ],
            [
                'name'    => 'Mohamed El Moctar',
                'phone'   => '+222 22 45 67 89',
                'email'   => 'm.elmoctar@bim.mr',
                'company' => 'BIM (Banque pour le Commerce)',
                'notes'   => 'Contrat catering déjeuner uniquement. 30 couverts/jour.',
            ],
            [
                'name'    => 'Aminetou Ould Sidi',
                'phone'   => '+222 46 11 22 33',
                'email'   => 'aminetou@mauritel.mr',
                'company' => 'Mauritel',
                'notes'   => 'Séminaires et conférences. Préfère les formules complètes (matin + déjeuner).',
            ],
            [
                'name'    => 'Sidi Ahmed Ould Bah',
                'phone'   => '+222 20 55 44 33',
                'email'   => 'sidi.ahmed@groupebah.mr',
                'company' => 'Groupe Bah Industries',
                'notes'   => 'Grand compte. Événements annuels + catering régulier pour 80 personnes.',
            ],
            [
                'name'    => 'Mariem Ould Djibril',
                'phone'   => '+222 36 77 88 99',
                'email'   => 'mariem.dj@ministere.mr',
                'company' => 'Ministère de l\'Économie',
                'notes'   => 'Réceptions officielles et ateliers ministériels.',
            ],
            // ── Clients individuels / familiaux ────────────────────────────────
            [
                'name'    => 'Khadijatou Mint Taleb',
                'phone'   => '+222 22 30 40 50',
                'email'   => 'khadijatou@gmail.com',
                'company' => null,
                'notes'   => 'Organisation de mariage en juin. Contactée pour devis 200 personnes.',
            ],
            [
                'name'    => 'Ibrahima Diallo',
                'phone'   => '+222 46 60 70 80',
                'email'   => 'ibrahima.diallo@yahoo.fr',
                'company' => null,
                'notes'   => 'Séjour longue durée à l\'hôtel. Commande régulière en chambre.',
            ],
            [
                'name'    => 'Ousmane Sy',
                'phone'   => '+222 20 81 92 03',
                'email'   => 'ousmane.sy@orange.fr',
                'company' => null,
                'notes'   => 'Délégation sénégalaise. Réservation salle conférence prévue.',
            ],
            [
                'name'    => 'Ndeye Fall',
                'phone'   => '+222 36 14 25 36',
                'email'   => 'ndeye.fall@gmail.com',
                'company' => null,
                'notes'   => 'Fête anniversaire 50 couverts prévue le mois prochain.',
            ],
        ];

        foreach ($clients as $c) {
            Client::firstOrCreate(['email' => $c['email']], $c);
        }

        $this->command->info('✅ Clients créés : ' . Client::count() . ' clients.');
    }
}
