<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('phone')->nullable()->after('name');
            $table->string('email')->nullable()->after('phone');
            $table->string('company')->nullable()->after('email');
            $table->text('notes')->nullable()->after('company');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['name', 'phone', 'email', 'company', 'notes']);
        });
    }
};
