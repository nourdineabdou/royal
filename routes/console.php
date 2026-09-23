<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Génère les factures mensuelles catering le 1er de chaque mois pour le mois qui vient de se terminer.
Schedule::command('catering:generate-invoices')->monthlyOn(1, '01:00');
