<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute company_id (et site_id où pertinent) aux tables transactionnelles
 * et de référence sensible, conformément au cahier des charges M1.3.
 * Colonnes nullable pour ne pas bloquer les données existantes.
 */
return new class extends Migration
{
    // Tables qui reçoivent company_id uniquement
    private array $companyOnly = [
        'clients',
        'suppliers',
        'purchase_orders',
        'transactions',
        'payrolls',
        'leaves',
        'events',
        'catering_contracts',
    ];

    // Tables qui reçoivent company_id + site_id
    private array $companyAndSite = [
        'stocks',
        'orders',
        'cash_registers',
        'employees',
        'rooms',
        'bookings',
    ];

    public function up(): void
    {
        foreach ($this->companyOnly as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained()
                      ->nullOnDelete();
            });
        }

        foreach ($this->companyAndSite as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('company_id')
                  ->nullable()
                  ->after('id')
                  ->constrained()
                  ->nullOnDelete();

                $t->foreignId('site_id')
                  ->nullable()
                  ->after('company_id')
                  ->constrained()
                  ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (array_merge($this->companyOnly, $this->companyAndSite) as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropForeign([$table . '.company_id']);
                $t->dropColumn('company_id');
            });
        }

        foreach ($this->companyAndSite as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropForeign([$table . '.site_id']);
                $t->dropColumn('site_id');
            });
        }
    }
};
