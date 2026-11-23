<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sorties par site :
 *  - site_user : un cuisinier est rattaché à un ou plusieurs sites ;
 *  - kitchen_request_item_sites : à la réception, chaque produit reçu est attribué à un site
 *    (tout sur un site, ou réparti) → consommation par site.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['site_id', 'user_id']);
        });

        Schema::create('kitchen_request_item_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_request_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained();
            $table->decimal('quantity', 12, 2); // unité de base
            $table->timestamps();
            $table->unique(['kitchen_request_item_id', 'site_id'], 'kr_item_site_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_request_item_sites');
        Schema::dropIfExists('site_user');
    }
};
