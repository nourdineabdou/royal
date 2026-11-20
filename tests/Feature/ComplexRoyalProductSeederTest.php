<?php

namespace Tests\Feature;

use App\Models\Packaging;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPackaging;
use App\Models\Unit;
use Database\Seeders\ComplexRoyalProductSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ComplexRoyalProductSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol');
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('packagings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('reference')->nullable()->unique();
            $table->boolean('is_bulk')->default(false);
            $table->boolean('is_consumable')->default(false);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->foreignId('unit_id')->constrained();
            $table->foreignId('packaging_id')->nullable()->constrained();
            $table->foreignId('product_category_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::create('product_packagings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('packaging_id')->constrained();
            $table->decimal('quantity', 10, 2);
            $table->timestamps();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('stock_id')->constrained();
            $table->string('type');
            $table->decimal('quantity', 10, 2);
            $table->timestamps();
        });
    }

    public function test_it_adds_missing_catalog_data_without_removing_or_overwriting_existing_data(): void
    {
        $oldUnit = Unit::create(['name' => 'Kilogramme', 'symbol' => 'old']);
        $oldCategory = ProductCategory::create(['name' => 'Ancienne catégorie']);
        $oldPackaging = Packaging::create(['name' => 'Ancien emballage']);
        $oldProduct = Product::create([
            'name' => 'Ancien produit',
            'unit_id' => $oldUnit->id,
            'product_category_id' => $oldCategory->id,
            'packaging_id' => $oldPackaging->id,
        ]);
        $existingCatalogProduct = Product::create([
            'name' => 'Riz cassé personnalisé',
            'reference' => 'CR-001',
            'is_bulk' => true,
            'unit_id' => $oldUnit->id,
            'product_category_id' => $oldCategory->id,
            'packaging_id' => $oldPackaging->id,
        ]);
        DB::table('product_packagings')->insert([
            'product_id' => $existingCatalogProduct->id,
            'packaging_id' => $oldPackaging->id,
            'quantity' => 123,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $defaultStockId = DB::table('stocks')->insertGetId([
            'name' => 'Stock par défaut',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('stocks')->insert([
            'name' => 'Autre stock',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('stock_items')->insert([
            'stock_id' => $defaultStockId,
            'product_id' => $oldProduct->id,
            'quantity' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('stock_movements')->insert([
            'stock_id' => $defaultStockId,
            'product_id' => $oldProduct->id,
            'type' => 'in',
            'quantity' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seed(ComplexRoyalProductSeeder::class);
        $packagingCount = ProductPackaging::count();
        $this->seed(ComplexRoyalProductSeeder::class);

        $this->assertSame(96, Product::count());
        $this->assertSame(95, Product::whereNotNull('reference')->count());
        $this->assertSame($packagingCount, ProductPackaging::count());
        $this->assertDatabaseHas('products', ['id' => $oldProduct->id]);
        $this->assertDatabaseHas('packagings', ['id' => $oldPackaging->id]);
        $this->assertDatabaseHas('product_categories', ['id' => $oldCategory->id]);
        $this->assertSame(2, DB::table('stocks')->count());
        $this->assertSame(1, DB::table('stock_items')->count());
        $this->assertSame(1, DB::table('stock_movements')->count());
        $this->assertSame(12.0, (float) DB::table('stock_items')->value('quantity'));
        $this->assertSame(12.0, (float) DB::table('stock_movements')->value('quantity'));

        $existingCatalogProduct->refresh();
        $this->assertSame('Riz cassé personnalisé', $existingCatalogProduct->name);
        $this->assertTrue($existingCatalogProduct->is_bulk);
        $this->assertSame($oldUnit->id, $existingCatalogProduct->unit_id);
        $this->assertSame($oldCategory->id, $existingCatalogProduct->product_category_id);
        $this->assertSame($oldPackaging->id, $existingCatalogProduct->packaging_id);
        $this->assertSame(123.0, (float) ProductPackaging::where('product_id', $existingCatalogProduct->id)
            ->where('packaging_id', $oldPackaging->id)
            ->value('quantity'));

        $forks = Product::where('reference', 'CR-095')->firstOrFail();
        $this->assertSame('pcs', $forks->unit->symbol);
        $this->assertSame('Paquet de 50 pièces', $forks->packaging->name);
        $this->assertSame(50.0, (float) ProductPackaging::where('product_id', $forks->id)
            ->where('packaging_id', $forks->packaging_id)
            ->value('quantity'));
        $this->assertSame(1.0, (float) ProductPackaging::where('product_id', $forks->id)
            ->whereHas('packaging', fn ($query) => $query->where('name', 'Pièce'))
            ->value('quantity'));
    }
}
