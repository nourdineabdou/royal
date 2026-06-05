<?php

namespace App\Http\Middleware;

use App\Models\CashRegister;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AutoCloseExpiredCashRegisters
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('cashier.auto_close_enabled', false)) {
            return $next($request);
        }

        $cooldown = max(10, (int) config('cashier.auto_close_cooldown_seconds', 120));
        $lockKey = 'cashier:auto-close:lock';

        // Evite de lancer le nettoyage a chaque requete.
        if (!Cache::add($lockKey, now()->timestamp, $cooldown)) {
            return $next($request);
        }

        $graceHours = max(0, (int) config('cashier.auto_close_grace_hours_after_midnight', 4));
        $now = now();

        $openRegisters = CashRegister::with(['payments.paymentType'])
            ->where('status', 'open')
            ->whereNotNull('opened_at')
            ->get();

        foreach ($openRegisters as $register) {
            $deadline = $register->opened_at->copy()->startOfDay()->addDay()->addHours($graceHours);

            if ($now->lt($deadline)) {
                continue;
            }

            $cashTotal = $register->payments
                ->filter(function ($payment) {
                    $name = strtolower((string) ($payment->paymentType->name ?? ''));
                    return str_contains($name, 'espece') || str_contains($name, 'esp') || str_contains($name, 'cash');
                })
                ->sum('amount');

            $closingBalance = (float) $register->opening_balance + (float) $cashTotal;
            $note = trim((string) $register->accounting_note);
            $autoNote = 'Fermeture automatique systeme: session depassee apres minuit + delai.';

            $register->update([
                'status' => 'closed',
                'closed_at' => $now,
                'closing_balance' => $register->closing_balance ?? $closingBalance,
                'accounting_note' => $note === '' ? $autoNote : ($note . ' | ' . $autoNote),
            ]);

            $register->posTransfers()
                ->where('status', 'validated')
                ->update(['status' => 'closed']);
        }

        return $next($request);
    }
}
