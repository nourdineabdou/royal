<?php

namespace App\Http\Controllers;

use App\Models\Accompaniment;
use Illuminate\Http\Request;

class AccompanimentController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('accompaniments.view');
        $query = Accompaniment::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $accompaniments = $query->paginate(15);
        return view('accompaniments.index', compact('accompaniments'));
    }

    public function create()
    {
        $this->perm('accompaniments.create');
        return view('accompaniments.create');
    }

    public function store(Request $request)
    {
        $this->perm('accompaniments.create');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:accompaniments,name',
            'type' => 'required|in:single,multiple',
            'price' => 'required|numeric|min:0',
        ]);

        Accompaniment::create($validated);
        return redirect()->route('accompaniments.index')->with('success', 'Accompagnement créé avec succès!');
    }

    public function edit(Accompaniment $accompaniment)
    {
        $this->perm('accompaniments.edit');
        return view('accompaniments.edit', compact('accompaniment'));
    }

    public function update(Request $request, Accompaniment $accompaniment)
    {
        $this->perm('accompaniments.edit');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:accompaniments,name,' . $accompaniment->id,
            'type' => 'required|in:single,multiple',
            'price' => 'required|numeric|min:0',
        ]);

        $accompaniment->update($validated);
        return redirect()->route('accompaniments.index')->with('success', 'Accompagnement modifié avec succès!');
    }

    public function destroy(Accompaniment $accompaniment)
    {
        $this->perm('accompaniments.delete');
        $accompaniment->delete();
        return redirect()->route('accompaniments.index')->with('success', 'Accompagnement supprimé avec succès!');
    }
}
