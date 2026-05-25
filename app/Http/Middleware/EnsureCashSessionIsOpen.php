<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\CashRegister; // À adapter si le modèle s'appelle différemment

class EnsureCashSessionIsOpen
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $session = CashRegister::where('user_id', $user->id)->whereNull('closed_at')->first();
        if (!$session) {
            return redirect()->route('dashboard-modern')->with('error', 'Votre session de caisse est fermée.');
        }
        return $next($request);
    }
}
