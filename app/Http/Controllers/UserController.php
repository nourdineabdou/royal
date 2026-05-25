<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Affiche la liste de tous les utilisateurs
     */
    public function index()
    {
        $this->perm('users.view');
        $users = User::with('roles')->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Affiche le formulaire pour créer un nouvel utilisateur
     */
    public function create()
    {
        $this->perm('users.create');
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Stocke un nouvel utilisateur en base de données
     */
    public function store(Request $request)
    {
        $this->perm('users.create');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Affiche un utilisateur spécifique
     */
    public function show(User $user)
    {
        $this->perm('users.view');
        $user->load('roles');
        return view('users.show', compact('user'));
    }

    /**
     * Affiche le formulaire pour éditer un utilisateur
     */
    public function edit(User $user)
    {
        $this->perm('users.edit');
        $roles = Role::all();
        $user->load('roles');
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Met à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $this->perm('users.edit');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id . '|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprime un utilisateur
     */
    public function destroy(User $user)
    {
        $this->perm('users.delete');
        // Éviter la suppression de l'utilisateur authentifié
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Affiche et met à jour les permissions d'un utilisateur
     */
    public function updatePermissions(Request $request, User $user)
    {
        $this->perm('users.permissions.edit');
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('users.show', $user)
            ->with('success', 'Permissions de l\'utilisateur mises à jour.');
    }
}
