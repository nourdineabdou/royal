<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Unit;
use App\Models\Packaging;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('products.view');
        $query = Product::with('unit', 'packaging');

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
            'name'         => 'required|string|max:255|unique:products',
            'unit_id'      => 'required|exists:units,id',
            'packaging_id' => 'nullable|exists:packagings,id',
            'is_bulk'      => 'boolean',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Produit créé avec succès!');
    }

    public function edit(Product $product)
    {
        $this->perm('products.edit');
        $units = Unit::all();
        $packagings = Packaging::all();
        return view('parameters.products.edit', compact('product', 'units', 'packagings'));
    }

    public function update(Request $request, Product $product)
    {
        $this->perm('products.edit');
        $validated = $request->validate([
            'name'         => 'required|string|max:255|unique:products,name,' . $product->id,
            'unit_id'      => 'required|exists:units,id',
            'packaging_id' => 'nullable|exists:packagings,id',
            'is_bulk'      => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Produit mis à jour avec succès!');
    }

    public function destroy(Product $product)
    {
        $this->perm('products.delete');
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produit supprimé avec succès!');
    }

    public function show(Product $product)
    {
        $this->perm('products.view');
        return view('parameters.products.show', compact('product'));
    }
}
