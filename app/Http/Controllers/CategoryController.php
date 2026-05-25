<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->perm('categories.view');
        $query = Category::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->paginate(15);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $this->perm('categories.create');
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $this->perm('categories.create');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create($validated);
        return redirect()->route('categories.index')->with('success', 'Catégorie créée avec succès!');
    }

    public function edit(Category $category)
    {
        $this->perm('categories.edit');
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->perm('categories.edit');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);
        return redirect()->route('categories.index')->with('success', 'Catégorie modifiée avec succès!');
    }

    public function destroy(Category $category)
    {
        $this->perm('categories.delete');
        if ($category->meals()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Impossible de supprimer cette catégorie, elle contient des repas!');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Catégorie supprimée avec succès!');
    }
}
