<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\Unit;
use App\Models\Packaging;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('products.view');
        $query = Product::with('unit', 'productPackagings.packaging');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(12);
        $units = Unit::all();
        $packagings = Packaging::all();

        return view('parameters.products.index', compact('products', 'units', 'packagings'));
    }

    public function create()
    {
        $this->perm('products.create');
        $units = Unit::all();
        $packagings = Packaging::all();
        return view('parameters.products.create', compact('units', 'packagings'));
    }

    public function store(Request $request)
    {
        $this->perm('products.create');
        $validated = $request->validate([
            'name'                  => 'required|string|max:255|unique:products',
            'unit_id'               => 'required|exists:units,id',
            'is_bulk'               => 'boolean',
            'is_consumable'         => 'boolean',
            'sale_price'            => 'nullable|numeric|min:0',
            'packaging_id'          => 'nullable|array',
            'packaging_id.*'        => 'nullable|exists:packagings,id',
            'packaging_quantity'    => 'nullable|array',
            'packaging_quantity.*'  => 'nullable|numeric|min:0.01',
        ]);

        $validated['is_bulk'] = $request->boolean('is_bulk');
        $validated['is_consumable'] = $request->boolean('is_consumable');
        if (!$validated['is_consumable']) {
            $validated['sale_price'] = null;
        }

        $packagingIds = $validated['packaging_id'] ?? [];
        $packagingQuantities = $validated['packaging_quantity'] ?? [];
        unset($validated['packaging_id'], $validated['packaging_quantity']);
        $validated['packaging_id'] = $packagingIds[0] ?? null;

        $product = Product::create($validated);

        $this->syncPackagings($product, $packagingIds, $packagingQuantities);

        return redirect()->route('purchases.products.index')->with('success', 'Produit créé avec succès!');
    }

    public function edit(Product $product)
    {
        $this->perm('products.edit');
        $units = Unit::all();
        $packagings = Packaging::all();
        $product->load('productPackagings.packaging');
        return view('parameters.products.edit', compact('product', 'units', 'packagings'));
    }

    public function update(Request $request, Product $product)
    {
        $this->perm('products.edit');
        $validated = $request->validate([
            'name'                  => 'required|string|max:255|unique:products,name,' . $product->id,
            'unit_id'               => 'required|exists:units,id',
            'is_bulk'               => 'boolean',
            'is_consumable'         => 'boolean',
            'sale_price'            => 'nullable|numeric|min:0',
            'packaging_id'          => 'nullable|array',
            'packaging_id.*'        => 'nullable|exists:packagings,id',
            'packaging_quantity'    => 'nullable|array',
            'packaging_quantity.*'  => 'nullable|numeric|min:0.01',
        ]);

        $validated['is_bulk'] = $request->boolean('is_bulk');
        $validated['is_consumable'] = $request->boolean('is_consumable');
        if (!$validated['is_consumable']) {
            $validated['sale_price'] = null;
        }

        $packagingIds = $validated['packaging_id'] ?? [];
        $packagingQuantities = $validated['packaging_quantity'] ?? [];
        unset($validated['packaging_id'], $validated['packaging_quantity']);
        $validated['packaging_id'] = $packagingIds[0] ?? null;

        $product->update($validated);

        $this->syncPackagings($product, $packagingIds, $packagingQuantities);

        return redirect()->route('purchases.products.index')->with('success', 'Produit mis à jour avec succès!');
    }

    public function destroy(Product $product)
    {
        $this->perm('products.delete');
        $product->delete();
        return redirect()->route('purchases.products.index')->with('success', 'Produit supprimé avec succès!');
    }

    public function show(Product $product)
    {
        $this->perm('products.view');
        $product->load('unit', 'productPackagings.packaging');
        return view('parameters.products.show', compact('product'));
    }

    /**
     * Replace a product's packaging conversions with the given rows.
     * Each packaging is independent — its quantity always converts to the product's base unit_id
     * (kg/g/L/mL), never to another packaging. See the Odoo/SAP "flat packaging" pattern.
     */
    private function syncPackagings(Product $product, array $packagingIds, array $quantities): void
    {
        $keepIds = [];
        foreach ($packagingIds as $i => $pkgId) {
            $qty = $quantities[$i] ?? null;
            if (!$pkgId || $qty === null || $qty === '') {
                continue;
            }
            $pp = ProductPackaging::updateOrCreate(
                ['product_id' => $product->id, 'packaging_id' => $pkgId],
                ['quantity' => $qty]
            );
            $keepIds[] = $pp->packaging_id;
        }
        ProductPackaging::where('product_id', $product->id)->whereNotIn('packaging_id', $keepIds)->delete();
    }
}
