<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 1. Ajoute pos_terminal_id sur pos_transfers (transfert ciblé par terminal, pas par session)
 * 2. Crée pos_terminal_stock_items (stock cumulé au terminal toutes sessions confondues)
 * 3. Crée pos_returns + pos_return_items (retours caissier avec impression)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. pos_transfers : ajouter pos_terminal_id ───────────────────────
        Schema::table('pos_transfers', function (Blueprint $table) {
            $table->foreignId('pos_terminal_id')
                  ->nullable()
                  ->after('cash_register_id')
                  ->constrained('pos_terminals')
                  ->nullOnDelete();
        });

        // ── 2. Stock cumulé au terminal (toutes sessions confondues) ─────────
        Schema::create('pos_terminal_stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_terminal_id')->constrained('pos_terminals')->cascadeOnDelete();
            $table->string('label');
            $table->enum('item_type', ['contract', 'extra'])->default('contract');
            $table->foreignId('meal_id')->nullable()->constrained('meals')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('quantity_received', 10, 3)->default(0); // cumul des transferts validés
            $table->decimal('quantity_served', 10, 3)->default(0);   // distribué (contrat)
            $table->decimal('quantity_sold', 10, 3)->default(0);     // vendu (extra)
            $table->decimal('quantity_returned', 10, 3)->default(0); // retourné en production
            $table->timestamps();

            $table->unique(['pos_terminal_id', 'item_type', 'meal_id', 'product_id'], 'unique_terminal_item');
        });

        // ── 3. Bons de retour ─────────────────────────────────────────────────
        Schema::create('pos_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers')->cascadeOnDelete();
            $table->foreignId('pos_terminal_id')->nullable()->constrained('pos_terminals')->nullOnDelete();
            $table->string('reference')->unique();
            $table->date('return_date');
            $table->foreignId('returned_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_return_id')->constrained('pos_returns')->cascadeOnDelete();
            $table->foreignId('pos_transfer_item_id')->nullable()->constrained('pos_transfer_items')->nullOnDelete();
            $table->foreignId('pos_terminal_stock_item_id')->nullable()->constrained('pos_terminal_stock_items')->nullOnDelete();
            $table->string('label');
            $table->decimal('quantity', 10, 3);
            $table->string('unit')->nullable();
            $table->enum('item_type', ['contract', 'extra'])->default('contract');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('pos_transfers', function (Blueprint $table) {
            $table->dropForeign(['pos_terminal_id']);
            $table->dropColumn('pos_terminal_id');
        });
        Schema::dropIfExists('pos_return_items');
        Schema::dropIfExists('pos_returns');
        Schema::dropIfExists('pos_terminal_stock_items');
    }
};
