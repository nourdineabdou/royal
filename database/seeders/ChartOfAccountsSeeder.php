<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

/**
 * Plan Comptable Mauritanien (PCM) — comptabilité unique et consolidée
 * pour l'ensemble du groupe (toutes sociétés confondues), transcrit tel
 * quel depuis le document fourni. [code, label, class, parent_code]
 */
class ChartOfAccountsSeeder extends Seeder
{
    private array $accounts = [
        // Classe 1 — Comptes de capitaux
        ['10', 'CAPITAL', 1, null],
        ['11', 'RESERVES', 1, null],
        ['12', 'REPORT A NOUVEAU', 1, null],
        ['13', "RESULTAT DE L'EXERCICE", 1, null],
        ['14', "SUBVENTIONS D'EQUIPEMENTS", 1, null],
        ['15', 'PLUS VALUES ET PROVISIONS REGLEMENTEES', 1, null],
        ['16', 'EMPRUNTS ET DETTES ASSIMILES A LONG ET MOYEN TERME', 1, null],
        ['19', 'PROVISIONS POUR RIQUES ET CHARGES', 1, null],

        // Classe 2 — Comptes des valeurs immobilisées
        ['20', 'FRAIS ET VALEURS INCORPORELES IMMOBILISEES', 2, null],
        ['200', 'FRAIS IMMOBILISES', 2, '20'],
        ['203', 'FRAIS DE RECHERCHE ET DU DEVELOPPEMENT', 2, '20'],
        ['205', 'VALEURS INCORPORELES IMMOBILISEES', 2, '20'],
        ['21', 'IMMOBILISATIONS CORPORELLES', 2, null],
        ['210', 'TERRAINS', 2, '21'],
        ['212', 'DROIT DE CONSTRUCTION', 2, '21'],
        ['214', "MATERIELS D'EXPLOITATION", 2, '21'],
        ['215', 'MATERIEL DE TRANSPORT', 2, '21'],
        ['216', 'MATERIEL DE BUREAU ET INFORMATIQUE', 2, '21'],
        ['218', 'MATERIEL ET MOBILIER DE BUREAU', 2, '21'],
        ['23', 'IMMOBILISATIONS EN COURS', 2, null],
        ['28', 'AMORTISSEMENTS DES IMMOBILISATIONS', 2, null],
        ['28000001', 'AMORTISSEMENTS DES IMMOBILISATIONS', 2, '28'],
        ['28000013', 'AMORTISSEMENTS DES IMMOBILISATIONS', 2, '28'],
        ['28000014', 'AMORTISSEMENTS DES IMMOBILISATIONS', 2, '28'],
        ['28000016', 'AMORTISSEMENTS DES IMMOBILISATIONS', 2, '28'],
        ['28000018', 'AMORTISSEMENTS DES IMMOBILISATIONS', 2, '28'],
        ['230', 'IMMOBILISATIONS EN COURS', 2, '28'],
        ['30', 'STOCK', 3, null],
        ['40', 'FOURNISSEURS', 4, null],
        ['41', 'CLIENTS', 4, null],
        ['42', 'PERSONNEL', 4, null],
        ['43', 'ETAT', 4, null],
        ['44', 'CNSS', 4, null],
        ['45', 'COMPTE ASSOCIE', 4, null],
        ['46', 'DEBITEURS&CREDITEURS DIVERS', 4, null],

        // Classe 5 — Trésorerie
        ['5', 'TRESORERIE', 5, null],
        ['50', 'BANQUE', 5, '5'],
        ['56', 'CAISSE', 5, '5'],
        ['57', 'DEPOSIT', 5, '5'],
        ['58', 'VIREMENT DE FONDS', 5, '5'],

        // Classe 6 — Charges
        ['6', 'CHARGES', 6, null],
        ['601', 'ACHAT POUR LE RESTAURANT', 6, '6'],
        ['602', 'ACHAT POUR LE CATERING', 6, '6'],
        ['6061', 'CARBURANT', 6, '6'],
        ['6062', 'FOURNITURES DE BUREAU', 6, '6'],
        ['6063', 'MAT&ENTRETIEN', 6, '6'],
        ['6066', 'EAU ET ELECTRICITE', 6, '6'],
        ['6068', 'ACHAT PIECE DETACHES', 6, '6'],
        ['6069', 'RESTAURATION PERSONNEL', 6, '6'],
        ['621', 'ENTRETIEN VEHICULES', 6, '6'],
        ['630', 'FRAIS DE TRANSPORT', 6, '6'],
        ['6310', 'VOYAGES ET DEPLACEMENTS', 6, '630'],
        ['632', 'FRAIS DE TELECOMMUNICATION', 6, '6'],
        ['633', 'HONORAIRES', 6, '6'],
        ['634', 'PUBLICITE ET PROPAGANDE', 6, '6'],
        ['635', 'FRAIS BANCAIRE', 6, '6'],
        ['638', 'CHARGES DIVERSES', 6, '6'],
        ['640', 'CHARGES ET PERTES DIVERSES', 6, '6'],
        ['645', 'BONS & POURBOIRES', 6, '6'],
        ['65', 'PERSONNEL', 6, '6'],
        ['65000001', 'SALAIRES', 6, '65'],
        ['66', 'IMPOTS&TAXES', 6, '6'],
        ['66000001', 'ITS', 6, '66'],
        ['66000002', 'PATENTE', 6, '66'],
        ['67', 'CHARGES FINANCIERES', 6, '6'],
        ['68', 'AMORTISSEMENTS', 6, '6'],

        // Classe 7 — Produits
        ['7', 'PRODUITS', 7, null],
        ['7000001', 'VENTES CATERING', 7, '7'],
        ['7000002', 'VENTES RESTAURANT', 7, '7'],
    ];

    public function run(): void
    {
        foreach ($this->accounts as [$code, $label, $class, $parent]) {
            ChartOfAccount::firstOrCreate(
                ['company_id' => null, 'code' => $code],
                ['label' => $label, 'class' => $class, 'parent_code' => $parent, 'is_active' => true]
            );
        }

        echo "✅ Plan comptable mauritanien créé (comptabilité unique et consolidée).\n";
    }
}
