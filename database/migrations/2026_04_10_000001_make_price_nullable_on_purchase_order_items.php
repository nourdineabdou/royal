<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->default(null)->change();
            $table->decimal('total', 10, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable(false)->change();
            $table->decimal('total', 10, 2)->nullable(false)->change();
        });
    }
};
