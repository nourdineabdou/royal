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

    public function test_it_seeds_the_catalog_categories_units_and_known_packaging_conversions_idempotently(): void
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

        $this->assertSame(53, Product::count());
        $this->assertSame(11, ProductCategory::count());
        $this->assertSame($packagingCount, ProductPackaging::count());
        $this->assertDatabaseMissing('products', ['id' => $oldProduct->id]);
        $this->assertDatabaseMissing('packagings', ['id' => $oldPackaging->id]);
        $this->assertSame(2, DB::table('stocks')->count());
        $this->assertSame(0, DB::table('stock_items')->count());
        $this->assertSame(0, DB::table('stock_movements')->count());

        $rice = Product::where('reference', 'CR-001')->firstOrFail();
        $this->assertSame('Céréales & féculents', $rice->productCategory->name);
        $this->assertSame('g', $rice->unit->symbol);
        $this->assertSame('Sac de 25 kg', $rice->packaging->name);
        $this->assertSame('25000.00', $rice->productPackagings()->firstOrFail()->quantity);

        $pastaCarton = Product::where('reference', 'CR-005')->firstOrFail();
        $this->assertSame(6000.0, (float) ProductPackaging::where('product_id', $pastaCarton->id)
            ->where('packaging_id', $pastaCarton->packaging_id)
            ->value('quantity'));
        $this->assertSame(500.0, (float) ProductPackaging::where('product_id', $pastaCarton->id)
            ->whereHas('packaging', fn ($query) => $query->where('name', 'Paquet de 500 g'))
            ->value('quantity'));

        $sauce = Product::where('reference', 'CR-040')->firstOrFail();
        $this->assertSame(950.0, (float) ProductPackaging::where('product_id', $sauce->id)
            ->where('packaging_id', $sauce->packaging_id)
            ->value('quantity'));

        $uncertainPackage = Product::where('reference', 'CR-021')->firstOrFail();
        $this->assertSame('Carton de 2 sachets', $uncertainPackage->packaging->name);
        $this->assertSame(0, $uncertainPackage->productPackagings()->count());
    }
}
