<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un transfert peut être créé en état "pending" avant qu'une caisse soit ouverte.
 * La caisse est assignée quand le caissier ouvre sa session et valide la réception.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_transfers', function (Blueprint $table) {
            // Supprimer l'ancienne FK
            $table->dropForeign(['cash_register_id']);
            // Rendre nullable + recréer la FK
            $table->foreignId('cash_register_id')->nullable()->change();
            $table->foreign('cash_register_id')->references('id')->on('cash_registers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_transfers', function (Blueprint $table) {
            $table->dropForeign(['cash_register_id']);
            $table->foreignId('cash_register_id')->nullable(false)->change();
            $table->foreign('cash_register_id')->references('id')->on('cash_registers')->cascadeOnDelete();
        });
    }
};
