<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->enum('status', ['open', 'closed', 'validated', 'flagged'])
                  ->default('open')
                  ->after('shift');

            $table->decimal('declared_excess', 10, 2)->nullable()->after('closing_balance');
            $table->text('accounting_note')->nullable()->after('declared_excess');

            $table->unsignedBigInteger('validated_by')->nullable()->after('accounting_note');
            $table->timestamp('validated_at')->nullable()->after('validated_by');

            $table->foreign('validated_by')->references('id')->on('users')->nullOnDelete();
        });

        // Backfill status for existing rows
        DB::statement("UPDATE cash_registers SET status = CASE WHEN closed_at IS NOT NULL THEN 'closed' ELSE 'open' END");
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn(['status', 'declared_excess', 'accounting_note', 'validated_by', 'validated_at']);
        });
    }
};
