<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add module linkage to stocks (one stock per module)
        Schema::table('stocks', function (Blueprint $table) {
            $table->string('module', 30)->nullable()->after('location')
                  ->comment('restaurant | catering | events — null = non linked');
        });

        // Enrich stock_movements with origin traceability
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('origin_module', 30)->nullable()->after('destination_stock_id')
                  ->comment('restaurant | catering | events | transfer | manual');
            $table->string('origin_type', 30)->nullable()->after('origin_module')
                  ->comment('order | event | catering_consumption | transfer | manual');
            $table->unsignedBigInteger('origin_id')->nullable()->after('origin_type')
                  ->comment('ID of the originating record (order_id, event_id, …)');
            $table->foreignId('user_id')->nullable()->after('origin_id')
                  ->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['origin_module', 'origin_type', 'origin_id', 'user_id', 'notes']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('module');
        });
    }
};
