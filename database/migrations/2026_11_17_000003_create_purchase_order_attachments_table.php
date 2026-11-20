<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documents joints à un bon de commande : devis du fournisseur, facture, BL… (photo ou scan PDF).
 * Le devis n'est pas saisi ligne par ligne : on joint simplement le fichier reçu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('devis'); // devis | facture | bl | autre
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_attachments');
    }
};
