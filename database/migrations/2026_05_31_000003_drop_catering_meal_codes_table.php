<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer la FK et colonne dans catering_consumptions d'abord
        Schema::table('catering_consumptions', function (Blueprint $table) {
            $table->dropForeign(['catering_meal_code_id']);
            $table->dropColumn('catering_meal_code_id');
        });

        // Supprimer la table catering_meal_codes (remplacée par le système de transferts POS)
        Schema::dropIfExists('catering_meal_codes');
    }

    public function down(): void
    {
        Schema::create('catering_meal_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catering_menu_meal_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('catering_consumptions', function (Blueprint $table) {
            $table->foreignId('catering_meal_code_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
