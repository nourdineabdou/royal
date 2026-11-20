<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lignes ajoutées à la main dans la DA automatique (par le magasinier ou l'acheteur) :
 * la mise à jour automatique (produits sous le seuil) ne doit pas les retirer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->boolean('added_manually')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropColumn('added_manually');
        });
    }
};
