<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Circuit d'approbation des demandes d'achat (procédure Complex Royal) :
 *   DA du magasinier → approuvée par le responsable achat → validée par la responsable RH → commandes (BC).
 *   Une DA créée par le responsable achat passe directement à la validation RH.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreignId('achat_approved_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->timestamp('achat_approved_at')->nullable()->after('achat_approved_by');
            $table->foreignId('rh_approved_by')->nullable()->after('achat_approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rh_approved_at')->nullable()->after('rh_approved_by');
            $table->foreignId('rejected_by')->nullable()->after('rh_approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('achat_approved_by');
            $table->dropConstrainedForeignId('rh_approved_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn(['achat_approved_at', 'rh_approved_at', 'rejected_at', 'rejection_reason']);
        });
    }
};
