<?php

/**
 * EXEMPLE: Comment utiliser les rôles et permissions dans vos contrôleurs métier
 * 
 * Ce fichier montre différentes façons de protéger vos routes et contrôleurs
 * avec le système de rôles et permissions que vous venez de mettre en place.
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExampleBusinessController extends Controller
{
    use AuthorizesRequests;

    /**
     * ✅ Méthode 1: Vérifier la permission dans le constructeur
     */
    public function __construct()
    {
        // Toutes les actions nécessitent que l'utilisateur soit authentifié
        $this->middleware('auth');

        // Seuls les admins peuvent accéder à la méthode destroy
        $this->middleware('permission:delete user', ['only' => 'destroy']);

        // Les modérateurs peuvent voir et éditer
        $this->middleware('permission:edit user', ['only' => ['edit', 'update']]);
    }

    /**
     * ✅ Méthode 2: Vérifier la permission dans les actions
     */
    public function edit($id)
    {
        // Vérifier si l'utilisateur a la permission
        if (!auth()->user()->can('edit user')) {
            abort(403, 'Vous n\'avez pas la permission d\'éditer les utilisateurs');
        }

        // Continuer avec la logique
    }

    /**
     * ✅ Méthode 3: Vérifier le rôle directement
     */
    public function admin($id)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Seuls les admins peuvent accéder à cette page');
        }

        // Logique admin
    }

    /**
     * ✅ Méthode 4: Vérifier si l'utilisateur a l'un de plusieurs rôles
     */
    public function moderation($id)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'moderator'])) {
            abort(403, 'Vous devez être admin ou modérateur');
        }

        // Logique de modération
    }

    /**
     * ✅ Méthode 5: Utiliser des policies (avancé)
     */
    public function show(User $user)
    {
        // Laravel utilise automatiquement votre UserPolicy
        $this->authorize('view', $user);

        return view('users.show', ['user' => $user]);
    }
}

// ============================================================================
// UTILISATION DANS LES ROUTES (routes/web.php)
// ============================================================================

/**
 * Option 1: Grouper par middleware permission
 */
Route::middleware(['auth', 'permission:view users'])->group(function () {
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index']);
});

/**
 * Option 2: Middleware par route
 */
Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])
    ->middleware(['auth', 'permission:create user']);

Route::put('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])
    ->middleware(['auth', 'permission:edit user']);

Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])
    ->middleware(['auth', 'permission:delete user']);

// ============================================================================
// UTILISATION DANS LES VUES (Blade)
// ============================================================================

/**
 * Dans vos fichiers .blade.php:
 */

// Vérifier une permission
if (auth()->user()->can('edit user')) {
    // Afficher le bouton éditer
}

// Vérifier un rôle
if (auth()->user()->hasRole('admin')) {
    // Afficher le contenu admin
}

// Vérifier l'un de plusieurs rôles
if (auth()->user()->hasAnyRole(['admin', 'moderator'])) {
    // Afficher pour les admins et modérateurs
}

// Code complet d'une vue:
/**
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Liste des Utilisateurs</h1>

        @can('create user')
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                Créer un utilisateur
            </a>
        @endcan

        <table>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>
                        @can('edit user')
                            <a href="{{ route('users.edit', $user) }}">Éditer</a>
                        @endcan

                        @can('delete user')
                            <form method="POST" action="{{ route('users.destroy', $user) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Supprimer</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
 */

// ============================================================================
// CRÉATION D'UNE POLICY (avancé)
// ============================================================================

/**
 * php artisan make:policy UserPolicy --model=User
 * 
 * app/Policies/UserPolicy.php
 */

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir un utilisateur
     */
    public function view(User $user, User $model): bool
    {
        return $user->can('view users');
    }

    /**
     * Déterminer si l'utilisateur peut éditer un utilisateur
     */
    public function update(User $user, User $model): bool
    {
        return $user->can('edit user');
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un utilisateur
     */
    public function delete(User $user, User $model): bool
    {
        return $user->can('delete user') && $user->id !== $model->id;
    }
}

// ============================================================================
// ASTUCES ET BONNES PRATIQUES
// ============================================================================

/**
 * 1. Cache des Permissions:
 *    Les permissions sont cachées par Laravel. Si vous les modifiez:
 *    php artisan permission:cache-reset
 * 
 * 2. Accès Rapide dans les Contrôleurs:
 *    auth()->user()->can('edit user')  // Vérifier permission
 *    auth()->user()->hasRole('admin')  // Vérifier rôle
 * 
 * 3. Accès dans les Vues Blade:
 *    @can('edit user')   <!-- Condition permission -->
 *    @role('admin')      <!-- Condition rôle -->
 *    @endcan
 * 
 * 4. Ordre des Vérifications:
 *    1. Vérifier si l'utilisateur est authentifié
 *    2. Vérifier la permission ou le rôle
 *    3. Continuer avec la logique
 * 
 * 5. Erreurs de Permission:
 *    abort(403, 'Message personnalisé')  // Montrer une erreur 403
 *    Gate::allows('edit user')            // Vérifier une permission
 *    $this->authorize('edit', $user)      // Vérifier avec une policy
 */
