<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Procédure stock Complex Royal :
 *  - seuil minimum par produit (en unité de base) → alerte « sous le seuil » et demande d'achat ;
 *  - demande de cuisine → préparée/remise par le magasinier → validée par le cuisinier
 *    (c'est la validation qui sort le stock et crée le bon de sortie BS).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('min_stock', 12, 2)->nullable()->after('barcode');
        });

        Schema::create('kitchen_requests', function (Blueprint $table) {
            $table->id();
            $table->string('kitchen', 100);                       // cuisine demandeuse (ex : Cuisine TCN)
            $table->foreignId('stock_id')->nullable()->constrained()->nullOnDelete(); // magasin d'où sortent les produits
            $table->string('status', 20)->default('pending');     // pending | prepared | validated | cancelled
            $table->date('needed_on')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('prepared_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('contest_note')->nullable();             // écart signalé par le cuisinier
            $table->string('bs_number', 20)->nullable()->unique(); // bon de sortie, attribué à la validation
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('kitchen_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->decimal('requested_quantity', 12, 2);        // unité de base
            $table->decimal('delivered_quantity', 12, 2)->nullable(); // remis par le magasinier
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_request_items');
        Schema::dropIfExists('kitchen_requests');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('min_stock');
        });
    }
};
