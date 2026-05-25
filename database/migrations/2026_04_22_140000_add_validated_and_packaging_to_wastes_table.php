<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wastes', function (Blueprint $table) {
            $table->timestamp('validated_at')->nullable()->after('reason');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete()->after('validated_at');
            $table->foreignId('packaging_id')->nullable()->constrained()->nullOnDelete()->after('validated_by');
        });
    }

    public function down(): void
    {
        Schema::table('wastes', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn('validated_by');
            $table->dropColumn('validated_at');
            $table->dropForeign(['packaging_id']);
            $table->dropColumn('packaging_id');
        });
    }
};
