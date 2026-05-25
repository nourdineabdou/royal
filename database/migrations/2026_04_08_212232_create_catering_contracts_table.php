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
        Schema::create('catering_contracts', function (Blueprint $table) {
             $table->id();

        $table->foreignId('client_id')->constrained();

        $table->date('start_date');
        $table->date('end_date');

        $table->integer('guest_count');

        $table->json('active_days'); // ex: [1,2,3,4,5]

        $table->boolean('has_breakfast')->default(false);
        $table->boolean('has_lunch')->default(false);
        $table->boolean('has_dinner')->default(false);

        $table->enum('status', ['active', 'paused', 'ended'])->default('active');

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catering_contracts');
    }
};
