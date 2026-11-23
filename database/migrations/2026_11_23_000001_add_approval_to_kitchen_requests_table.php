<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sorties vers les cuisines : approbation du responsable des achats entre la remise
 * du magasinier et la confirmation du cuisinier (statut « to_approve »).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitchen_requests', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('receiver_id')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('kitchen_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('approved_at');
        });
    }
};
