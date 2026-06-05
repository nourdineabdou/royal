<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transferts de production vers les points de vente catering.
 *
 * pos_transfers        : entête du bon de transfert
 * pos_transfer_items   : lignes (plat ou produit/semi-fini + emballage)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Entête bon de transfert ────────────────────────────────────────
        Schema::create('pos_transfers', function (Blueprint $table) {
            $table->id();

            // Origine
            $table->foreignId('from_stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->foreignId('prepared_by')->constrained('users')->cascadeOnDelete(); // chef production

            // Destination
            $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete(); // caisse catering_pos
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();        // client catering
            $table->foreignId('catering_contract_id')->nullable()
                  ->constrained()->nullOnDelete();

            // Livraison
            $table->string('driver_name')->nullable();
            $table->string('reference')->unique();   // ex. TRNF-2026-0001
            $table->date('transfer_date');

            // Statuts
            // pending   : créé par production, en attente validation caissier
            // in_transit: imprimé + chauffeur en route
            // validated : caissier a confirmé réception
            // closed    : caissier a fermé sa session + comptable validé
            $table->enum('status', ['pending', 'in_transit', 'validated', 'closed'])
                  ->default('pending');

            // Signatures (base64 ou simple flag)
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('driver_signed_at')->nullable();
            $table->timestamp('cashier_validated_at')->nullable();
            $table->foreignId('cashier_validated_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            // Notes
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Lignes du bon de transfert ─────────────────────────────────────
        Schema::create('pos_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_transfer_id')->constrained()->cascadeOnDelete();

            // Soit un plat (meals), soit un produit/semi-fini (products)
            $table->foreignId('meal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Si produit, emballage optionnel
            $table->foreignId('packaging_id')->nullable()->constrained()->nullOnDelete();

            $table->string('label');         // nom affiché sur le PDF
            $table->decimal('quantity', 10, 3);
            $table->string('unit')->nullable(); // unité d'affichage

            // Catégorie : 'contract' (plat contrat → gratuit) | 'extra' (vente)
            $table->enum('item_type', ['contract', 'extra'])->default('contract');
            $table->decimal('unit_price', 10, 2)->default(0); // 0 pour les plats contrat

            // Quantité décrémentée au service (mis à jour par le caissier)
            $table->decimal('served_qty', 10, 3)->default(0);
            $table->decimal('sold_qty', 10, 3)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transfer_items');
        Schema::dropIfExists('pos_transfers');
    }
};
