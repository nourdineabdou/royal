<?php

namespace App\Http\Controllers;

use App\Models\Packaging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PackagingController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('packagings.view');
        $query = Packaging::withCount('productPackagings');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $packagings = $query->paginate(15);

        return view('parameters.packagings.index', compact('packagings'));
    }

    public function create()
    {
        $this->perm('packagings.create');
        return view('parameters.packagings.create');
    }

    public function store(Request $request)
    {
        $this->perm('packagings.create');
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:packagings',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('packagings', 'public');
        }

        Packaging::create($validated);

        return redirect()->route('purchases.packagings.index')->with('success', 'Emballage créé avec succès!');
    }

    public function edit(Packaging $packaging)
    {
        $this->perm('packagings.edit');
        return view('parameters.packagings.edit', compact('packaging'));
    }

    public function update(Request $request, Packaging $packaging)
    {
        $this->perm('packagings.edit');
        $validated = $request->validate([
            'name'         => 'required|string|max:255|unique:packagings,name,' . $packaging->id,
            'description'  => 'nullable|string',
            'image'        => 'nullable|image|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($packaging->image) {
                Storage::disk('public')->delete($packaging->image);
            }
            $validated['image'] = $request->file('image')->store('packagings', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($packaging->image) {
                Storage::disk('public')->delete($packaging->image);
            }
            $validated['image'] = null;
        }
        unset($validated['remove_image']);

        $packaging->update($validated);

        return redirect()->route('purchases.packagings.index')->with('success', 'Emballage mis à jour avec succès!');
    }

    public function destroy(Packaging $packaging)
    {
        $this->perm('packagings.delete');
        // Vérifier si l'emballage est utilisé (par n'importe quel produit, via product_packagings)
        if ($packaging->productPackagings()->count() > 0) {
            return redirect()->route('purchases.packagings.index')->with('error', 'Cet emballage est utilisé par ' . $packaging->productPackagings()->count() . ' produit(s)!');
        }

        if ($packaging->image) {
            Storage::disk('public')->delete($packaging->image);
        }

        $packaging->delete();
        return redirect()->route('purchases.packagings.index')->with('success', 'Emballage supprimé avec succès!');
    }
}
