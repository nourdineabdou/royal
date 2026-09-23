<?php

return [
    // Compte de vente (classe 7) à créditer selon le module de la caisse.
    'sales_accounts' => [
        'restaurant' => '7000002',
        'catering'   => '7000001',
    ],

    // Compte d'achat (classe 6) à débiter selon le module du stock livré.
    'purchase_accounts' => [
        'restaurant' => '601',
        'catering'   => '602',
        'default'    => '601',
    ],

    // Compte fournisseurs (classe 4).
    'supplier_account' => '40',

    // Compte de trésorerie utilisé quand le moyen de paiement n'a pas de
    // compte comptable configuré.
    'default_cash_account' => '56',
];
