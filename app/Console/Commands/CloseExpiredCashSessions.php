<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CashRegister; // À adapter si le modèle s'appelle différemment
use Carbon\Carbon;

class CloseExpiredCashSessions extends Command
{
    protected $signature = 'cash:close-expired';
    protected $description = 'Ferme automatiquement les sessions de caisse non clôturées après l\'heure limite';

    public function handle()
    {
        // Heure limite (5h du matin)
        $limit = Carbon::now()->setTime(5, 0, 0);
        // On ferme toutes les sessions ouvertes dont l'ouverture est antérieure à aujourd'hui 5h
        $sessions = CashRegister::whereNull('closed_at')
            ->where('opened_at', '<', $limit->copy()->subDay()->endOfDay())
            ->get();

        foreach ($sessions as $session) {
            $session->closed_at = now();
            $session->closed_by = 'system'; // ou user_id = 0 selon ta structure
            $session->save();
        }

        $this->info($sessions->count().' sessions fermées automatiquement.');
    }
}
