<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // cdi, cdd, stage, prestation
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null pour un CDI
            $table->date('trial_period_end')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('status')->default('active'); // active, ended, terminated
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
