<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Passe les avances d'une déduction unique (un seul mois) à un
 * remboursement échelonné : un pourcentage du salaire est déduit chaque
 * mois jusqu'à ce que le solde restant atteigne zéro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advances', function (Blueprint $table) {
            $table->decimal('repayment_percentage', 5, 2)->nullable()->after('amount');
            $table->decimal('remaining_balance', 10, 2)->nullable()->after('repayment_percentage');
        });

        DB::statement("ALTER TABLE advances MODIFY status ENUM('pending','approved','deducted','completed') NOT NULL");

        // Rétro-compatibilité : les avances déjà déduites (ancien système) sont soldées ;
        // les autres démarrent avec un solde restant égal au montant accordé.
        DB::table('advances')->where('status', 'deducted')->update(['remaining_balance' => 0]);
        DB::table('advances')->where('status', '!=', 'deducted')->update([
            'remaining_balance' => DB::raw('amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('advances', function (Blueprint $table) {
            $table->dropColumn(['repayment_percentage', 'remaining_balance']);
        });

        DB::statement("ALTER TABLE advances MODIFY status ENUM('pending','approved','deducted') NOT NULL");
    }
};
