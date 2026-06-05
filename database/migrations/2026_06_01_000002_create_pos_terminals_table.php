<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crée la table pos_terminals : définition permanente des points de vente.
 *
 * Un terminal est configuré par l'admin et associé à un (ou deux) caissiers.
 * Quand le caissier se connecte, il voit automatiquement son terminal assigné
 * sans avoir à choisir le type, le module, etc.
 *
 * Relation : pos_terminals (1) —→ (N) cash_registers (sessions d'ouverture)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_terminals', function (Blueprint $table) {
            $table->id();

            // Identité du point de vente
            $table->string('label');                                         // "POS Catering Abdallah — Matin"
            $table->enum('type', ['ordinary', 'catering_pos'])->default('ordinary');
            $table->enum('module', ['restaurant', 'catering', 'events', 'residence'])->default('restaurant');

            // Rattachement catering (optionnel pour ordinary)
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stock_id')->nullable()->constrained()->nullOnDelete();

            // Caissiers affectés (un par shift)
            $table->foreignId('cashier_morning_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cashier_evening_id')->nullable()->constrained('users')->nullOnDelete();

            // Actif / inactif
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        // Ajouter pos_terminal_id aux sessions de caisse
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->foreignId('pos_terminal_id')->nullable()->after('id')
                  ->constrained('pos_terminals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropForeign(['pos_terminal_id']);
            $table->dropColumn('pos_terminal_id');
        });

        Schema::dropIfExists('pos_terminals');
    }
};
