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
        Schema::create('stock_movements', function (Blueprint $table) {
           $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('stock_id')->constrained();

            $table->enum('type', ['in', 'out', 'transfer']);
            $table->decimal('quantity', 10, 2);

            $table->foreignId('source_stock_id')->nullable();
            $table->foreignId('destination_stock_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
