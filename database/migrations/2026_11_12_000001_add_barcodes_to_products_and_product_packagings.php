<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Codes-barres multi-niveaux (modèle Odoo / SAP / GS1) :
 *  - products.barcode            → code de l'unité de base (1 pcs, 1 g, 1 mL…)
 *  - product_packagings.barcode  → code d'un conditionnement (bouteille, caisse…),
 *                                  qui vaut product_packagings.quantity unités de base.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode', 64)->nullable()->unique()->after('reference');
        });

        Schema::table('product_packagings', function (Blueprint $table) {
            $table->string('barcode', 64)->nullable()->unique()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('product_packagings', function (Blueprint $table) {
            $table->dropUnique(['barcode']);
            $table->dropColumn('barcode');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['barcode']);
            $table->dropColumn('barcode');
        });
    }
};
