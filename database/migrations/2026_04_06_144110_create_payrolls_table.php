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
        Schema::create('payrolls', function (Blueprint $table) {
           $table->id();

            $table->foreignId('employee_id')->constrained();

            $table->integer('month');
            $table->integer('year');

            $table->decimal('base_salary', 10, 2);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('deduction', 10, 2)->default(0);
            $table->decimal('advance_deduction', 10, 2)->default(0);

            $table->decimal('net_salary', 10, 2);

            $table->enum('status', ['pending', 'paid']);

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
