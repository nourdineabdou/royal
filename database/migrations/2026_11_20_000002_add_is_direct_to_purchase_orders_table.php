<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marque les BC créés par une réception directe (livraison sans commande préalable),
 * pour filtrer les réceptions : sur demande d'achat / sur BC sans DA / directes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->boolean('is_direct')->default(false)->after('event_id');
        });

        // Historique : BC sans DA ni événement, créé dans la même minute que sa première réception
        $direct = DB::table('purchase_orders as po')
            ->whereNull('po.purchase_request_id')
            ->whereNull('po.event_id')
            ->whereExists(function ($q) {
                $q->from('goods_receipts as gr')
                  ->whereColumn('gr.purchase_order_id', 'po.id')
                  ->whereRaw('ABS(TIMESTAMPDIFF(SECOND, po.created_at, gr.created_at)) <= 60');
            })
            ->pluck('po.id');

        DB::table('purchase_orders')->whereIn('id', $direct)->update(['is_direct' => true]);
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('is_direct');
        });
    }
};
