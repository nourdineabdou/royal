<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('units.view');
        $query = Unit::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $units = $query->paginate(15);

        return view('parameters.units.index', compact('units'));
    }

    public function create()
    {
        $this->perm('units.create');
        return view('parameters.units.create');
    }

    public function store(Request $request)
    {
        $this->perm('units.create');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units',
            'symbol' => 'required|string|max:10|unique:units',
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', 'Unité créée avec succès!');
    }

    public function edit(Unit $unit)
    {
        $this->perm('units.edit');
        return view('parameters.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $this->perm('units.edit');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'symbol' => 'required|string|max:10|unique:units,symbol,' . $unit->id,
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')->with('success', 'Unité mise à jour avec succès!');
    }

    public function destroy(Unit $unit)
    {
        $this->perm('units.delete');
        // Vérifier si l'unité est utilisée
        if ($unit->products()->count() > 0) {
            return redirect()->route('units.index')->with('error', 'Cette unité est utilisée par ' . $unit->products()->count() . ' produit(s)!');
        }

        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unité supprimée avec succès!');
    }
}
