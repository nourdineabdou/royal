<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catering_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catering_contract_id')->constrained('catering_contracts')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['draft', 'issued', 'partial', 'paid'])->default('issued');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['catering_contract_id', 'period_year', 'period_month'], 'uniq_contract_month_invoice');
            $table->index(['client_id', 'period_year', 'period_month'], 'idx_client_month_invoice');
        });

        Schema::create('catering_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catering_invoice_id')->constrained('catering_invoices')->cascadeOnDelete();
            $table->foreignId('pos_terminal_stock_item_id')->nullable()->constrained('pos_terminal_stock_items')->nullOnDelete();
            $table->string('label');
            $table->decimal('quantity', 10, 3)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('catering_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catering_invoice_id')->constrained('catering_invoices')->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained('payment_types')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->enum('status', ['pending', 'validated', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->index(['catering_invoice_id', 'status'], 'idx_invoice_payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catering_invoice_payments');
        Schema::dropIfExists('catering_invoice_items');
        Schema::dropIfExists('catering_invoices');
    }
};
