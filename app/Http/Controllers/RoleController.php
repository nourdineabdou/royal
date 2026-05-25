<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Affiche la liste de tous les rôles
     */
    public function index()
    {
        $this->perm('roles.view');
        $roles = Role::with('permissions')->paginate(10);
        return view('roles.index', compact('roles'));
    }

    /**
     * Affiche le formulaire pour créer un nouveau rôle
     */
    public function create()
    {
        $this->perm('roles.create');
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Stocke un nouveau rôle en base de données
     */
    public function store(Request $request)
    {
        $this->perm('roles.create');
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }

    /**
     * Affiche un rôle spécifique
     */
    public function show(Role $role)
    {
        $this->perm('roles.view');
        $role->load('permissions');
        return view('roles.show', compact('role'));
    }

    /**
     * Affiche le formulaire pour éditer un rôle
     */
    public function edit(Role $role)
    {
        $this->perm('roles.edit');
        $permissions = Permission::all();
        $role->load('permissions');
        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Met à jour un rôle
     */
    public function update(Request $request, Role $role)
    {
        $this->perm('roles.edit');
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id . '|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Supprime un rôle
     */
    public function destroy(Role $role)
    {
        $this->perm('roles.delete');
        if ($role->users()->exists()) {
            return redirect()->route('roles.index')
                ->with('error', 'Impossible de supprimer ce rôle car il est associé à des utilisateurs.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }
}
