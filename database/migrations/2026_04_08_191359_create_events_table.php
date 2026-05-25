<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
             $table->id();

    $table->foreignId('client_id')->constrained();

    $table->string('event_type');

    $table->date('event_date');

    $table->integer('guest_count');

    $table->foreignId('stock_id')->nullable()->constrained();

    $table->decimal('total_amount', 10, 2)->default(0);

    $table->enum('status', [
        'draft',
        'validated',
        'in_progress',
        'completed',
        'cancelled'
    ])->default('draft');

    $table->timestamp('validated_at')->nullable();
    $table->timestamp('completed_at')->nullable();

    $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
