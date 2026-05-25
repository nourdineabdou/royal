<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('identity_number')->nullable()->after('customer_phone');
            $table->unsignedSmallInteger('num_guests')->default(1)->after('identity_number');
            $table->text('notes')->nullable()->after('num_guests');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['identity_number', 'num_guests', 'notes', 'paid_amount']);
        });
    }
};
