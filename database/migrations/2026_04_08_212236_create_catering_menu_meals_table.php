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
        Schema::create('catering_menu_meals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('catering_menu_day_id')->constrained();

            $table->enum('type', ['breakfast', 'lunch', 'dinner']);

            $table->integer('quantity');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catering_menu_meals');
    }
};
