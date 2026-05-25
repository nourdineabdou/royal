<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Packaging;
use App\Models\StockItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Fournisseurs ──────────────────────────────────────────────────────
        $suppliers = [
            [
                'name'    => 'SAMI – Société Approvisionnement Mauritanie Import',
                'phone'   => '+222 45 25 10 10',
                'email'   => 'commandes@sami-mr.com',
                'address' => 'Zone industrielle de Nouakchott, Route NKC-Rosso',
            ],
            [
                'name'    => 'Agrostock Mauritanie',
                'phone'   => '+222 22 34 56 78',
                'email'   => 'contact@agrostock.mr',
                'address' => 'Marché SOCOGIM, Nouakchott',
            ],
            [
                'name'    => 'Frigomar – Produits Frais & Surgelés',
                'phone'   => '+222 36 11 22 33',
                'email'   => 'ventes@frigomar.mr',
                'address' => 'Port de Nouakchott, Zone franche',
            ],
            [
                'name'    => 'Beverages Atlantic',
                'phone'   => '+222 20 77 88 99',
                'email'   => 'orders@beverages-atlantic.mr',
                'address' => 'Zone industrielle, Nouakchott',
            ],
            [
                'name'    => 'Épices & Co. – Import Maghreb',
                'phone'   => '+222 46 55 44 33',
                'email'   => 'epices@epicesco.mr',
                'address' => 'Marché capitale, Rue 42',
            ],
        ];

        $supplierModels = [];
        foreach ($suppliers as $s) {
            $supplierModels[$s['name']] = Supplier::firstOrCreate(['name' => $s['name']], $s);
        }

        // ─── Bons de commande ─────────────────────────────────────────────────
        $stockCuisine  = Stock::where('name', 'Stock Cuisine Principale')->first();
        $stockBar      = Stock::where('name', 'Stock Bar & Boissons')->first();
        $stockReserve  = Stock::where('name', 'Stock Réserve Sèche')->first();

        if (!$stockCuisine) {
            $this->command->warn('⚠️ Stocks non trouvés. Lancez StockSeeder d\'abord.');
            return;
        }

        $farine    = Product::where('name', 'Farine de blé')->first();
        $riz       = Product::where('name', 'Riz long grain')->first();
        $sucre     = Product::where('name', 'Sucre blanc')->first();
        $huile     = Product::where('name', 'Huile végétale')->first();
        $poulet    = Product::where('name', 'Poulet entier')->first();
        $filets    = Product::where('name', 'Filets de poulet')->first();
        $boeuf     = Product::where('name', 'Viande de bœuf hachée')->first();
        $poisson   = Product::where('name', 'Poisson (capitaine)')->first();
        $tomates   = Product::where('name', 'Tomates fraîches')->first();
        $oignons   = Product::where('name', 'Oignons')->first();
        $pommes    = Product::where('name', 'Pommes de terre')->first();
        $coca      = Product::where('name', 'Coca-Cola')->first();
        $fanta     = Product::where('name', 'Fanta Orange')->first();
        $eau       = Product::where('name', 'Eau minérale')->first();
        $jus       = Product::where('name', 'Jus d\'orange')->first();
        $cumin     = Product::where('name', 'Cumin')->first();
        $paprika   = Product::where('name', 'Paprika')->first();
        $poivre    = Product::where('name', 'Poivre noir')->first();
        $café      = Product::where('name', 'Café moulu')->first();
        $sel       = Product::where('name', 'Sel')->first();
        $mozza     = Product::where('name', 'Mozzarella')->first();
        $beurre    = Product::where('name', 'Beurre')->first();
        $lait      = Product::where('name', 'Lait entier')->first();
        $oeufs     = Product::where('name', 'Œufs')->first();

        $sac50kg   = Packaging::where('name', 'Sac 50 kg')->first();
        $bidon20L  = Packaging::where('name', 'Bidon 20 L')->first();
        $pack24    = Packaging::where('name', 'Pack 24 canettes')->first();
        $btl15L    = Packaging::where('name', 'Bouteille 1.5 L')->first();
        $pack6     = Packaging::where('name', 'Pack 6 bouteilles')->first();

        $orders = [
            // BC-001 : Commande céréales & huile (SAMI) – REÇUE + PAYÉE
            [
                'supplier'       => 'SAMI – Société Approvisionnement Mauritanie Import',
                'reference'      => 'BC-2026-001',
                'status'         => 'received',
                'payment_status' => 'paid',
                'stock'          => $stockReserve,
                'items'          => [
                    [$farine, 200, 180], [$riz, 150, 200], [$sucre, 100, 220],
                ],
                'date' => Carbon::now()->subDays(15),
            ],
            // BC-002 : Commande huile + produits secs (Agrostock)  – REÇUE, paiement partiel
            [
                'supplier'       => 'Agrostock Mauritanie',
                'reference'      => 'BC-2026-002',
                'status'         => 'received',
                'payment_status' => 'partial',
                'stock'          => $stockCuisine,
                'items'          => [
                    [$huile, 80, 280], [$beurre, 20, 850], [$lait, 40, 320],
                    [$oeufs, 480, 8], [$oignons, 50, 35], [$tomates, 40, 55],
                ],
                'date' => Carbon::now()->subDays(8),
            ],
            // BC-003 : Commande viandes & poisson (Frigomar) – REÇUE + PAYÉE
            [
                'supplier'       => 'Frigomar – Produits Frais & Surgelés',
                'reference'      => 'BC-2026-003',
                'status'         => 'received',
                'payment_status' => 'paid',
                'stock'          => $stockCuisine,
                'items'          => [
                    [$poulet, 60, 380], [$filets, 40, 520], [$boeuf, 35, 600], [$poisson, 30, 450],
                ],
                'date' => Carbon::now()->subDays(5),
            ],
            // BC-004 : Boissons (Beverages Atlantic) – REÇUE + PAYÉE
            [
                'supplier'       => 'Beverages Atlantic',
                'reference'      => 'BC-2026-004',
                'status'         => 'received',
                'payment_status' => 'paid',
                'stock'          => $stockBar,
                'items'          => [
                    [$coca, 144, 65], [$fanta, 96, 65], [$eau, 240, 40],
                    [$jus, 60, 180], [$café, 10, 1200],
                ],
                'date' => Carbon::now()->subDays(3),
            ],
            // BC-005 : Épices (Épices & Co.) – EN COURS
            [
                'supplier'       => 'Épices & Co. – Import Maghreb',
                'reference'      => 'BC-2026-005',
                'status'         => 'ordered',
                'payment_status' => 'unpaid',
                'stock'          => $stockCuisine,
                'items'          => [
                    [$cumin, 10, 250], [$paprika, 10, 280], [$poivre, 8, 400], [$sel, 50, 30],
                ],
                'date' => Carbon::now()->subDays(1),
            ],
            // BC-006 : Produits laitiers & fromages (Frigomar) – COMMANDÉ
            [
                'supplier'       => 'Frigomar – Produits Frais & Surgelés',
                'reference'      => 'BC-2026-006',
                'status'         => 'pending',
                'payment_status' => 'unpaid',
                'stock'          => $stockCuisine,
                'items'          => [
                    [$mozza, 15, 900], [$beurre, 15, 850],
                ],
                'date' => Carbon::now(),
            ],
        ];

        foreach ($orders as $o) {
            $supplier = $supplierModels[$o['supplier']] ?? null;
            if (!$supplier) continue;

            // Calculer total
            $total = 0;
            foreach ($o['items'] as [$product, $qty, $price]) {
                if ($product) $total += $qty * $price;
            }

            $paid      = ($o['payment_status'] === 'paid')    ? $total :
                        (($o['payment_status'] === 'partial')  ? round($total * 0.5) : 0);
            $remaining = $total - $paid;

            $po = PurchaseOrder::firstOrCreate(
                ['reference' => $o['reference']],
                [
                    'supplier_id'    => $supplier->id,
                    'total_amount'   => $total,
                    'paid_amount'    => $paid,
                    'remaining_amount' => $remaining,
                    'payment_status' => $o['payment_status'],
                    'status'         => $o['status'],
                    'created_at'     => $o['date'],
                ]
            );

            foreach ($o['items'] as [$product, $qty, $price]) {
                if (!$product) continue;
                PurchaseOrderItem::firstOrCreate(
                    ['purchase_order_id' => $po->id, 'product_id' => $product->id],
                    ['quantity' => $qty, 'price' => $price, 'total' => $qty * $price]
                );
            }

            // Créer bon de réception pour les commandes reçues
            if (in_array($o['status'], ['received']) && $o['stock']) {
                $receipt = GoodsReceipt::firstOrCreate(
                    ['purchase_order_id' => $po->id],
                    ['stock_id' => $o['stock']->id, 'received_at' => $o['date']->addDays(1)]
                );
                foreach ($o['items'] as [$product, $qty, $price]) {
                    if (!$product) continue;
                    GoodsReceiptItem::firstOrCreate(
                        ['goods_receipt_id' => $receipt->id, 'product_id' => $product->id],
                        ['quantity' => $qty]
                    );
                    // Mettre à jour le stock
                    $item = StockItem::firstOrCreate(
                        ['stock_id' => $o['stock']->id, 'product_id' => $product->id],
                        ['quantity' => 0]
                    );
                    $item->increment('quantity', $qty);
                }
            }
        }

        $this->command->info('✅ Fournisseurs créés : ' . Supplier::count() . ' fournisseurs, ' . PurchaseOrder::count() . ' commandes d\'achat.');
    }
}
