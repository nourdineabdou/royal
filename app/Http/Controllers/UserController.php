<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
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
        if ($request->filled('username')) {
            $request->merge(['username' => User::normalizeUsername($request->input('username'))]);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['nullable', User::USERNAME_RULE, 'unique:users,username'],
            'email' => 'required|string|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ], [
            'username.regex'  => 'Nom d’utilisateur : 3 à 30 caractères, lettres sans accent, chiffres, point, tiret ou souligné.',
            'username.unique' => 'Ce nom d’utilisateur est déjà pris.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'] ?? null, // vide : tiré du nom
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'must_change_password' => true, // provisoire : à changer à la première connexion
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles(Role::whereIn('id', $validated['roles'])->get());
        }

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé. Il devra changer son mot de passe à sa première connexion.');
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
        $user->load('roles', 'sites');
        $sites = \App\Models\Site::active()->orderBy('name')->get(['id', 'name']);
        return view('users.edit', compact('user', 'roles', 'sites'));
    }

    /**
     * Met à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $this->perm('users.edit');
        $request->merge(['username' => $request->filled('username')
            ? User::normalizeUsername($request->input('username'))
            : User::uniqueUsernameFrom($request->input('name'), $user->id)]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', User::USERNAME_RULE, 'unique:users,username,' . $user->id],
            'email' => 'required|string|email|unique:users,email,' . $user->id . '|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'sites' => 'nullable|array',
            'sites.*' => 'exists:sites,id',
        ], [
            'username.regex'  => 'Nom d’utilisateur : 3 à 30 caractères, lettres sans accent, chiffres, point, tiret ou souligné.',
            'username.unique' => 'Ce nom d’utilisateur est déjà pris.',
        ]);

        // Sites servis (cuisiniers) : un seul → sorties attribuées automatiquement ; plusieurs → répartition à la réception
        $user->sites()->sync($validated['sites'] ?? []);

        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ]);

        // Mot de passe réinitialisé par l'administrateur : provisoire, à changer à la prochaine connexion
        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
                'must_change_password' => $user->id !== auth()->id(),
            ]);
        }

        if (!empty($validated['roles'])) {
            $user->syncRoles(Role::whereIn('id', $validated['roles'])->get());
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
