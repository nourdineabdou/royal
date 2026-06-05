<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute :
 *   - type        : 'ordinary' (resto classique) | 'catering_pos' (point de vente catering)
 *   - client_id   : nullable FK — pour les caisses catering_pos liées à un client
 *   - stock_id    : nullable FK — stock rattaché au point de vente catering
 *   - label       : nom libre donné à la caisse (paramétrable)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->string('label')->nullable()->after('module');
            $table->enum('type', ['ordinary', 'catering_pos'])->default('ordinary')->after('label');
            $table->foreignId('client_id')->nullable()->after('type')
                  ->constrained()->nullOnDelete();
            $table->foreignId('stock_id')->nullable()->after('client_id')
                  ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['stock_id']);
            $table->dropColumn(['label', 'type', 'client_id', 'stock_id']);
        });
    }
};
