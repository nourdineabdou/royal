<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un prix de devis est donné pour un emballage précis (ex : 9 000 MRU le Sac de 25 kg).
 * Null = prix à l'unité de base du produit (produits sans emballage, ex : pièces).
 * Permet de reporter le prix du devis dans le bon de commande, quel que soit l'emballage choisi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_quote_items', function (Blueprint $table) {
            $table->foreignId('packaging_id')->nullable()->after('purchase_request_item_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_quote_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('packaging_id');
        });
    }
};
