<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cuisinier qui reçoit la sortie : choisi par le magasinier, c'est lui seul qui la confirme
 * et dont le nom figure sur le bon de sortie à côté de sa signature.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitchen_requests', function (Blueprint $table) {
            $table->foreignId('receiver_id')->nullable()->after('prepared_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kitchen_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('receiver_id');
        });
    }
};
