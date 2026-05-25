<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catering_meal_codes', function (Blueprint $table) {
            $table->id();

        $table->foreignId('catering_menu_meal_id')->constrained();

        $table->string('code')->unique();

        $table->boolean('is_used')->default(false);

        $table->timestamp('used_at')->nullable();

        $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catering_meal_codes');
    }
};
