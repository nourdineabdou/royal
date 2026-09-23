<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Attendance;

class AuthController extends Controller
{
    /**
     * Affiche la page de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Traite la connexion de l'utilisateur
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->employee) {
                $hasTodayAttendance = Attendance::where('employee_id', $user->employee->id)
                    ->where('date', today())
                    ->whereNotNull('check_in')
                    ->exists();
                if (! $hasTodayAttendance) {
                    return redirect()->intended(route('hr.clock'))->with('success', 'Bienvenue! Veuillez pointer votre arrivée.');
                }
            }

            if ($user->hasRole('caissier')) {
                return redirect()->intended(route('cashier.open'))->with('success', 'Bienvenue!');
            }

            return redirect()->intended('dashboard-modern')->with('success', 'Bienvenue!');
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Vous avez been déconnecté.');
    }

    /**
     * Affiche la page d'inscription
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Crée un nouvel utilisateur et le connecte
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assigner le rôle 'user' par défaut
        $user->assignRole('user');

        Auth::login($user);

        return redirect('dashboard')->with('success', 'Inscription réussie! Bienvenue!');
    }
}
