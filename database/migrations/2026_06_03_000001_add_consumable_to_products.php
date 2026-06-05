<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rend un produit "consommable/vendable directement" dans le POS Catering vente libre.
 *  - is_consumable : true pour boissons, desserts, etc.
 *  - sale_price    : prix de vente à l'unité de base (MRU)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_consumable')->default(false)->after('is_bulk');
            $table->decimal('sale_price', 10, 2)->nullable()->after('is_consumable');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_consumable', 'sale_price']);
        });
    }
};
