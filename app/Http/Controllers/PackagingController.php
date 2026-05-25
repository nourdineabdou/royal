<?php

namespace App\Http\Controllers;

use App\Models\Packaging;
use Illuminate\Http\Request;

class PackagingController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('packagings.view');
        $query = Packaging::query();

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
            'name' => 'required|string|max:255|unique:packagings',
            'description' => 'nullable|string',
        ]);

        Packaging::create($validated);

        return redirect()->route('packagings.index')->with('success', 'Emballage créé avec succès!');
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
            'name' => 'required|string|max:255|unique:packagings,name,' . $packaging->id,
            'description' => 'nullable|string',
        ]);

        $packaging->update($validated);

        return redirect()->route('packagings.index')->with('success', 'Emballage mis à jour avec succès!');
    }

    public function destroy(Packaging $packaging)
    {
        $this->perm('packagings.delete');
        // Vérifier si l'emballage est utilisé
        if ($packaging->products()->count() > 0) {
            return redirect()->route('packagings.index')->with('error', 'Cet emballage est utilisé par ' . $packaging->products()->count() . ' produit(s)!');
        }

        $packaging->delete();
        return redirect()->route('packagings.index')->with('success', 'Emballage supprimé avec succès!');
    }
}
