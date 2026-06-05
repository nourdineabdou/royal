<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_terminal_ticket_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_terminal_stock_item_id')->constrained('pos_terminal_stock_items')->cascadeOnDelete();
            $table->foreignId('cash_register_id')->nullable()->constrained('cash_registers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ticket_code')->nullable();
            $table->decimal('qty', 10, 3)->default(1);
            $table->timestamp('served_at')->nullable();
            $table->timestamps();

            $table->index(['cash_register_id', 'pos_terminal_stock_item_id'], 'idx_ticket_logs_register_item');
            $table->index('served_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_terminal_ticket_logs');
    }
};
