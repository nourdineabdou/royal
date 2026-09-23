<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->foreignId('order_item_id')->nullable()->after('goods_receipt_id')->constrained('purchase_order_items')->nullOnDelete();
            // Quantité reçue exprimée dans la même unité que la ligne de commande (ex: "colis"),
            // par opposition à `quantity` qui reste la quantité convertie en unité de stock.
            $table->decimal('received_quantity', 12, 2)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_item_id');
            $table->dropColumn('received_quantity');
        });
    }
};
