@extends('layouts.residence')
@section('title', 'Nouvelle réservation')

@section('content')

<div class="mb-6">
    <a href="{{ route('residence.bookings') }}" class="text-sm text-slate-500 hover:text-violet-600 flex items-center gap-1 mb-2">
        <i class="fa-solid fa-arrow-left text-xs"></i> Retour aux réservations
    </a>
    <h2 class="text-xl font-bold text-slate-800">Nouvelle réservation</h2>
</div>

<form method="POST" action="{{ route('residence.bookings.store') }}"
      class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @csrf

    {{-- Left: Room + Dates --}}
    <div class="space-y-5">

        {{-- Room selection --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-door-open text-violet-500"></i> Sélection de la chambre
            </h3>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Chambre disponible <span class="text-red-500">*</span></label>
                <select name="room_id" id="roomSelect" required onchange="updateRoomInfo(this)"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    <option value="">— Choisir une chambre —</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}"
                            data-price="{{ $room->roomType->base_price ?? 0 }}"
                            data-type="{{ $room->roomType->name ?? '' }}"
                            @selected(($selectedRoom?->id ?? request('room_id')) == $room->id)>
                        Chambre {{ $room->number }} — {{ $room->roomType->name ?? '?' }}
                        ({{ number_format($room->roomType->base_price ?? 0, 0, ',', ' ') }} MRU/nuit)
                    </option>
                    @endforeach
                </select>
                @error('room_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div id="roomInfoBox" class="hidden bg-violet-50 border border-violet-200 rounded-xl p-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-600">Type :</span>
                    <span id="infoType" class="font-medium text-violet-700"></span>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-slate-600">Prix/nuit :</span>
                    <span id="infoPrice" class="font-medium text-violet-700"></span>
                </div>
            </div>
        </div>

        {{-- Dates --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-violet-500"></i> Dates de séjour
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Arrivée <span class="text-red-500">*</span></label>
                    <input type="date" name="check_in" id="checkIn" required
                           value="{{ old('check_in', date('Y-m-d')) }}"
                           min="{{ date('Y-m-d') }}"
                           onchange="calcTotal()"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    @error('check_in')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Départ <span class="text-red-500">*</span></label>
                    <input type="date" name="check_out" id="checkOut" required
                           value="{{ old('check_out', date('Y-m-d', strtotime('+1 day'))) }}"
                           onchange="calcTotal()"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    @error('check_out')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Estimation --}}
            <div id="estimationBox" class="hidden mt-4 bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-600">Durée :</span>
                    <span id="estNights" class="font-semibold text-emerald-700">—</span>
                </div>
                <div class="flex justify-between mt-1 text-base font-bold">
                    <span class="text-slate-700">Total estimé :</span>
                    <span id="estTotal" class="text-emerald-700">—</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Right: Client Info --}}
    <div class="space-y-5">
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user text-violet-500"></i> Informations client
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nom complet <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" required value="{{ old('customer_name') }}"
                           placeholder="Prénom Nom"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    @error('customer_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Téléphone</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                               placeholder="+222…"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nbre de personnes</label>
                        <input type="number" name="num_guests" value="{{ old('num_guests', 1) }}"
                               min="1" max="20"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">N° Pièce d'identité</label>
                    <input type="text" name="identity_number" value="{{ old('identity_number') }}"
                           placeholder="CNI / Passeport…"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Observations</label>
                    <textarea name="notes" rows="3" placeholder="Remarques, besoins spéciaux…"
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none resize-none">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('residence.bookings') }}"
               class="flex-1 text-center px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm hover:bg-slate-50 transition">
                Annuler
            </a>
            <button type="submit"
                    class="flex-1 px-4 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold transition shadow">
                <i class="fa-solid fa-calendar-check mr-1"></i> Créer la réservation
            </button>
        </div>
    </div>

</form>

<script>
let selectedPrice = 0;

function updateRoomInfo(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) {
        document.getElementById('roomInfoBox').classList.add('hidden');
        selectedPrice = 0;
        return;
    }
    selectedPrice = parseFloat(opt.dataset.price) || 0;
    document.getElementById('infoType').textContent  = opt.dataset.type;
    document.getElementById('infoPrice').textContent = Number(selectedPrice).toLocaleString('fr-FR') + ' MRU';
    document.getElementById('roomInfoBox').classList.remove('hidden');
    calcTotal();
}

function calcTotal() {
    const ci = document.getElementById('checkIn').value;
    const co = document.getElementById('checkOut').value;
    if (!ci || !co || !selectedPrice) { document.getElementById('estimationBox').classList.add('hidden'); return; }
    const d1 = new Date(ci), d2 = new Date(co);
    const nights = Math.round((d2 - d1) / 86400000);
    if (nights <= 0) { document.getElementById('estimationBox').classList.add('hidden'); return; }
    const total = nights * selectedPrice;
    document.getElementById('estNights').textContent = nights + ' nuit' + (nights>1?'s':'');
    document.getElementById('estTotal').textContent  = Number(total).toLocaleString('fr-FR') + ' MRU';
    document.getElementById('estimationBox').classList.remove('hidden');
    // Ensure check_out min = check_in + 1 day
    const minOut = new Date(ci); minOut.setDate(minOut.getDate()+1);
    document.getElementById('checkOut').min = minOut.toISOString().split('T')[0];
}

// Init on page load if room pre-selected
window.addEventListener('DOMContentLoaded', () => {
    updateRoomInfo(document.getElementById('roomSelect'));
});
</script>

@endsection
