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
        Schema::create('room_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained();
            $table->foreignId('payment_type_id')->constrained();

            $table->foreignId('cash_register_id')->constrained(); // 🔥 lien caisse

            $table->decimal('amount', 10, 2);

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_payments');
    }
};
