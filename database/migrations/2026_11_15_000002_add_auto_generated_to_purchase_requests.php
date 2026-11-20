<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demande d'achat préparée automatiquement par le système avec tous les produits
 * du Stock Catering passés sous leur seuil minimum (brouillon relu puis envoyé par le magasinier).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->boolean('auto_generated')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('auto_generated');
        });
    }
};
