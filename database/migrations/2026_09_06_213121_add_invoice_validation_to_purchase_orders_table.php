<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // "Contrôle comptable" de la facture fournisseur — doit être fait avant d'autoriser le paiement.
            $table->timestamp('invoice_validated_at')->nullable()->after('status');
            $table->foreignId('invoice_validated_by')->nullable()->after('invoice_validated_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_validated_by');
            $table->dropColumn('invoice_validated_at');
        });
    }
};
