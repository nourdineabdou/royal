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
        Schema::create('catering_contract_prices', function (Blueprint $table) {
             $table->id();

    $table->foreignId('catering_contract_id')->constrained()->cascadeOnDelete();

    $table->enum('type', ['breakfast', 'lunch', 'dinner']);

    $table->decimal('price', 10, 2);

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catering_contract_prices');
    }
};
