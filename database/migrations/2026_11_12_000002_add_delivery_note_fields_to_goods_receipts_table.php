<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réception (BL) : traçabilité comme dans Odoo — n° du BL papier du fournisseur,
 * personne qui a réceptionné, remarque (écart, produit abîmé…).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->string('supplier_delivery_note', 100)->nullable()->after('stock_id');
            $table->foreignId('received_by')->nullable()->after('supplier_delivery_note')->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable()->after('received_at');
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('received_by');
            $table->dropColumn(['supplier_delivery_note', 'notes']);
        });
    }
};
