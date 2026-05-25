<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add module to cash_registers
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->enum('module', ['restaurant', 'catering', 'events', 'residence'])
                  ->default('restaurant')
                  ->after('user_id');
        });

        // Add cash_register_id to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('cash_register_id')->nullable()->constrained()->nullOnDelete()->after('cashier_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cash_register_id']);
            $table->dropColumn('cash_register_id');
        });

        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn('module');
        });
    }
};
