<?php

return [
    // Active/desactive l'auto-fermeture des sessions caisse oubliees.
    'auto_close_enabled' => (bool) env('CASHIER_AUTO_CLOSE_ENABLED', false),

    // Nombre d'heures apres minuit avant fermeture automatique.
    'auto_close_grace_hours_after_midnight' => (int) env('CASHIER_AUTO_CLOSE_GRACE_HOURS', 4),

    // Frequence max d'execution du controle (en secondes) pour eviter de scanner a chaque requete.
    'auto_close_cooldown_seconds' => (int) env('CASHIER_AUTO_CLOSE_COOLDOWN_SECONDS', 120),
];
