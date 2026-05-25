<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Affiche la liste de toutes les permissions
     */
    public function index()
    {
        $this->perm('permissions.view');
        $permissions = Permission::paginate(10);
        return view('permissions.index', compact('permissions'));
    }

    /**
     * Affiche le formulaire pour créer une nouvelle permission
     */
    public function create()
    {
        $this->perm('permissions.create');
        return view('permissions.create');
    }

    /**
     * Stocke une nouvelle permission en base de données
     */
    public function store(Request $request)
    {
        $this->perm('permissions.create');
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Permission::create($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission créée avec succès.');
    }

    /**
     * Affiche une permission spécifique
     */
    public function show(Permission $permission)
    {
        $this->perm('permissions.view');
        return view('permissions.show', compact('permission'));
    }

    /**
     * Affiche le formulaire pour éditer une permission
     */
    public function edit(Permission $permission)
    {
        $this->perm('permissions.edit');
        return view('permissions.edit', compact('permission'));
    }

    /**
     * Met à jour une permission
     */
    public function update(Request $request, Permission $permission)
    {
        $this->perm('permissions.edit');
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id . '|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $permission->update($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission mise à jour avec succès.');
    }

    /**
     * Supprime une permission
     */
    public function destroy(Permission $permission)
    {
        $this->perm('permissions.delete');
        if ($permission->roles()->exists()) {
            return redirect()->route('permissions.index')
                ->with('error', 'Impossible de supprimer cette permission car elle est associée à des rôles.');
        }

        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission supprimée avec succès.');
    }
}
