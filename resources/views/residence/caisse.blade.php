@extends('layouts.residence')
@section('title', 'Caisse Résidence')

@section('content')

<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-800">Caisse Résidence</h2>
    <p class="text-sm text-slate-500 mt-0.5">Gestion de la caisse dédiée aux paiements de la résidence</p>
</div>

{{-- Open / Closed Banner --}}
@if($openRegister)
<div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 mb-6 flex items-center justify-between flex-wrap gap-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white">
            <i class="fa-solid fa-cash-register"></i>
        </div>
        <div>
            <p class="font-semibold text-emerald-800">Caisse ouverte</p>
            <p class="text-xs text-emerald-600">
                Ouverte le {{ \Carbon\Carbon::parse($openRegister->opened_at)->format('d/m/Y à H:i') }}
                par {{ $openRegister->user->name ?? '?' }} ·
                Solde d'ouverture : {{ number_format($openRegister->opening_balance, 0, ',', ' ') }} MRU
            </p>
        </div>
    </div>
    @can('residence.caisse.close')
    <button onclick="document.getElementById('closeModal').classList.remove('hidden')"
            class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-xl text-sm font-medium border border-red-200 transition">
        <i class="fa-solid fa-lock mr-1"></i> Clôturer la caisse
    </button>
    @endcan
</div>
@else
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6 flex items-center justify-between flex-wrap gap-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-amber-400 flex items-center justify-center text-white">
            <i class="fa-solid fa-lock-open"></i>
        </div>
        <div>
            <p class="font-semibold text-amber-800">Caisse fermée</p>
            <p class="text-xs text-amber-600">Aucune caisse résidence n'est actuellement ouverte</p>
        </div>
    </div>
    @can('residence.caisse.open')
    <button onclick="document.getElementById('openModal').classList.remove('hidden')"
            class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-medium transition shadow">
        <i class="fa-solid fa-unlock mr-1"></i> Ouvrir la caisse
    </button>
    @endcan
</div>
@endif

{{-- KPI row --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-violet-100 flex items-center justify-center text-violet-600">
            <i class="fa-solid fa-coins text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400">Encaissements aujourd'hui</p>
            <p class="text-xl font-bold text-slate-800">{{ number_format($todayRevenue, 0, ',', ' ') }} <span class="text-sm font-normal text-slate-400">MRU</span></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
            <i class="fa-solid fa-calendar text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-slate-400">Sessions ce mois</p>
            <p class="text-xl font-bold text-slate-800">{{ $registers->total() }}</p>
        </div>
    </div>
</div>

{{-- History --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-slate-700">Historique des sessions</h3>
    </div>
    <table class="w-full text-sm">
        <thead class="text-xs text-slate-400 uppercase bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-5 py-3 text-left">Date ouverture</th>
                <th class="px-5 py-3 text-left">Caissier</th>
                <th class="px-5 py-3 text-right">Solde ouverture</th>
                <th class="px-5 py-3 text-right">Solde clôture</th>
                <th class="px-5 py-3 text-center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registers as $reg)
            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                <td class="px-5 py-3 text-slate-700">{{ \Carbon\Carbon::parse($reg->opened_at)->format('d/m/Y H:i') }}</td>
                <td class="px-5 py-3 text-slate-600">{{ $reg->user->name ?? '—' }}</td>
                <td class="px-5 py-3 text-right font-medium">{{ number_format($reg->opening_balance, 0, ',', ' ') }}</td>
                <td class="px-5 py-3 text-right text-slate-500">
                    {{ $reg->closing_balance !== null ? number_format($reg->closing_balance, 0, ',', ' ') : '—' }}
                </td>
                <td class="px-5 py-3 text-center">
                    @if($reg->status === 'open')
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">Ouverte</span>
                    @elseif($reg->status === 'closed')
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-full text-xs font-medium">Clôturée</span>
                    @else
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded-full text-xs font-medium">{{ ucfirst($reg->status) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-10 text-slate-400">Aucune session enregistrée</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($registers->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $registers->links() }}
    </div>
    @endif
</div>

{{-- Open register modal --}}
@can('residence.caisse.open')
<div id="openModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Ouvrir la caisse</h3>
            <button onclick="document.getElementById('openModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('residence.caisse.open') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Solde d'ouverture (MRU)</label>
                <input type="number" name="opening_balance" required min="0" step="0.01" value="0"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('openModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">
                    <i class="fa-solid fa-unlock mr-1"></i> Ouvrir
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- Close register modal --}}
@if($openRegister)
@can('residence.caisse.close')
<div id="closeModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Clôturer la caisse</h3>
            <button onclick="document.getElementById('closeModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('residence.caisse.close', $openRegister) }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Solde de clôture (MRU)</label>
                <input type="number" name="closing_balance" required min="0" step="0.01" value="0"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Note comptable</label>
                <textarea name="accounting_note" rows="2" placeholder="Observations…"
                          class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('closeModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-red-600 hover:bg-red-700 text-white font-medium transition">
                    <i class="fa-solid fa-lock mr-1"></i> Clôturer
                </button>
            </div>
        </form>
    </div>
</div>
@endcan
@endif

@endsection
