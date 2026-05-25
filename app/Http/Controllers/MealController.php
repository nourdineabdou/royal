<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\Category;
use App\Models\Accompaniment;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('meals.view');
        $query = Meal::with('category', 'accompaniments');

        // Filtrer par catégorie
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Recherche
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $meals = $query->paginate(12);
        $categories = Category::all();

        return view('meals.index', compact('meals', 'categories'));
    }

    public function create()
    {
        $this->perm('meals.create');
        $categories = Category::all();
        $accompaniments = Accompaniment::all();
        return view('meals.create', compact('categories', 'accompaniments'));
    }

    public function store(Request $request)
    {
        $this->perm('meals.create');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'accompaniments' => 'array|nullable',
        ]);

        // Upload image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('meals', 'public');
            $validated['image'] = $path;
        }

        $meal = Meal::create($validated);

        // Attacher accompagnements
        if ($request->accompaniments) {
            $meal->accompaniments()->attach($request->accompaniments);
        }

        return redirect()->route('meals.index')->with('success', 'Repas créé avec succès!');
    }

    public function edit(Meal $meal)
    {
        $this->perm('meals.edit');
        $categories = Category::all();
        $accompaniments = Accompaniment::all();
        return view('meals.edit', compact('meal', 'categories', 'accompaniments'));
    }

    public function update(Request $request, Meal $meal)
    {
        $this->perm('meals.edit');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'accompaniments' => 'array|nullable',
        ]);

        // Upload nouvelle image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('meals', 'public');
            $validated['image'] = $path;
        }

        $meal->update($validated);

        // Sync accompagnements
        if ($request->accompaniments) {
            $meal->accompaniments()->sync($request->accompaniments);
        } else {
            $meal->accompaniments()->detach();
        }

        return redirect()->route('meals.index')->with('success', 'Repas modifié avec succès!');
    }

    public function destroy(Meal $meal)
    {
        $this->perm('meals.delete');
        $meal->delete();
        return redirect()->route('meals.index')->with('success', 'Repas supprimé avec succès!');
    }

    public function show(Meal $meal)
    {
        $this->perm('meals.view');
        $meal->load('category', 'accompaniments');
        return view('meals.show', compact('meal'));
    }
}
