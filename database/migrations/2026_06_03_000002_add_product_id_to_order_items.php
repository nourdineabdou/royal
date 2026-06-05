<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permet à une ligne de commande de référencer un produit consommable
 * (boisson, dessert…) en plus des plats (meals).
 *  - meal_id   devient nullable (était NOT NULL)
 *  - product_id ajouté (nullable) pour les ventes libres catering
 *  - label     ajouté pour afficher le nom même si meal/product est supprimé
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Rendre meal_id nullable (les produits consommables n'ont pas de meal)
            $table->dropForeign(['meal_id']);
            $table->unsignedBigInteger('meal_id')->nullable()->change();
            $table->foreign('meal_id')->references('id')->on('meals')->nullOnDelete();

            // Ajouter product_id + label
            $table->foreignId('product_id')
                  ->nullable()
                  ->after('meal_id')
                  ->constrained('products')
                  ->nullOnDelete();

            $table->string('label')->nullable()->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'label']);

            $table->dropForeign(['meal_id']);
            $table->unsignedBigInteger('meal_id')->nullable(false)->change();
            $table->foreign('meal_id')->references('id')->on('meals')->cascadeOnDelete();
        });
    }
};
