<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * « Mon mot de passe » : chaque utilisateur connecté change le sien.
 * Obligatoire à la première connexion quand l'administrateur a donné un mot de passe provisoire.
 */
class PasswordController extends Controller
{
    public function edit()
    {
        return view('auth.password', ['forced' => (bool) auth()->user()->must_change_password]);
    }

    /** Chacun change son nom d'utilisateur (ex : un numéro « 0042 »). */
    public function updateUsername(Request $request)
    {
        $user = $request->user();
        $request->merge(['username' => \App\Models\User::normalizeUsername($request->input('username'))]);

        $request->validate([
            'username' => ['required', \App\Models\User::USERNAME_RULE, \Illuminate\Validation\Rule::unique('users', 'username')->ignore($user->id)],
        ], [
            'username.required' => 'Tapez le nom d\'utilisateur.',
            'username.regex'    => 'De 3 à 30 caractères : lettres sans accent, chiffres, point, tiret ou souligné.',
            'username.unique'   => 'Ce nom d\'utilisateur est déjà pris.',
        ]);

        $user->update(['username' => $request->input('username')]);

        return redirect()->route('password.edit')->with('success', 'Nom d\'utilisateur changé : connectez-vous désormais avec « ' . $user->username . ' ».');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8), 'different:current_password', 'not_in:password,12345678,royal2026'],
        ], [
            'current_password.required'         => 'Tapez votre mot de passe actuel.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.required'                  => 'Tapez le nouveau mot de passe.',
            'password.confirmed'                 => 'Les deux nouveaux mots de passe ne sont pas identiques.',
            'password.min'                       => 'Le nouveau mot de passe doit faire au moins 8 caractères.',
            'password.different'                 => 'Le nouveau mot de passe doit être différent de l\'actuel.',
            'password.not_in'                    => 'Ce mot de passe est trop simple, choisissez-en un autre.',
        ]);

        $user->forceFill([
            'password'             => Hash::make($request->input('password')),
            'must_change_password' => false,
        ])->save();

        // Les autres sessions ouvertes avec l'ancien mot de passe sont fermées
        auth()->logoutOtherDevices($request->input('password'));

        return redirect()->route($user->isCookOnly() ? 'kitchen.requests.index' : 'dashboard-modern')
            ->with('success', 'Votre mot de passe a été changé.');
    }
}
