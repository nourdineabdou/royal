<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            // Plat destiné en priorité aux contrats de catering (géré depuis le module Catering) —
            // les plats non cochés restent des "extras" vendables hors contrat.
            $table->boolean('is_catering')->default(false)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->dropColumn('is_catering');
        });
    }
};
