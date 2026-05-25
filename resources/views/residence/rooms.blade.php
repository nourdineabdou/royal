@extends('layouts.residence')
@section('title', 'Chambres')

@section('content')

<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <h2 class="text-xl font-bold text-slate-800">Chambres</h2>
    @can('residence.rooms.create')
    <button onclick="document.getElementById('addRoomModal').classList.remove('hidden')"
            class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow">
        <i class="fa-solid fa-plus"></i> Ajouter une chambre
    </button>
    @endcan
</div>

{{-- Stats strip --}}
<div class="grid grid-cols-3 gap-3 mb-6">
    @php
        $avail = $rooms->where('status', 'available')->count();
        $occ   = $rooms->where('status', 'occupied')->count();
        $maint = $rooms->where('status', 'maintenance')->count();
    @endphp
    <div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm">
        <span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>
        <span class="text-sm font-medium text-slate-700">{{ $avail }} disponible{{ $avail > 1 ? 's' : '' }}</span>
    </div>
    <div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm">
        <span class="w-3 h-3 rounded-full bg-violet-500 inline-block"></span>
        <span class="text-sm font-medium text-slate-700">{{ $occ }} occupée{{ $occ > 1 ? 's' : '' }}</span>
    </div>
    <div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm">
        <span class="w-3 h-3 rounded-full bg-slate-400 inline-block"></span>
        <span class="text-sm font-medium text-slate-700">{{ $maint }} maintenance</span>
    </div>
</div>

{{-- Rooms grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @forelse($rooms as $room)
    @php
        $statusConfig = [
            'available'   => ['border-emerald-300 bg-emerald-50', 'text-emerald-700 bg-emerald-100', 'Disponible', 'fa-circle-check text-emerald-500'],
            'occupied'    => ['border-violet-300 bg-violet-50',   'text-violet-700 bg-violet-100',   'Occupée',    'fa-user text-violet-500'],
            'maintenance' => ['border-slate-300 bg-slate-50',     'text-slate-600 bg-slate-100',     'Maintenance','fa-wrench text-slate-400'],
        ][$room->status] ?? ['border-slate-200 bg-white', 'text-slate-600 bg-slate-100', $room->status, 'fa-question'];
    @endphp
    <div class="border-2 {{ $statusConfig[0] }} rounded-2xl p-4 flex flex-col gap-3">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-lg font-bold text-slate-800">Ch. {{ $room->number }}</p>
                @if($room->floor)
                <p class="text-xs text-slate-400">Étage {{ $room->floor }}</p>
                @endif
            </div>
            <i class="fa-solid {{ $statusConfig[3] }} text-xl"></i>
        </div>
        <div>
            <span class="inline-block text-xs font-medium px-2 py-1 rounded-full {{ $statusConfig[1] }}">{{ $statusConfig[2] }}</span>
            <p class="text-xs text-slate-500 mt-1">{{ $room->roomType->name ?? '—' }} · {{ number_format($room->roomType->base_price ?? 0, 0, ',', ' ') }} MRU/nuit</p>
        </div>
        @if($room->status === 'occupied' && $room->activeBooking)
        <div class="text-xs text-violet-700 bg-violet-50 rounded-lg px-3 py-1.5">
            <i class="fa-solid fa-user mr-1"></i>
            {{ $room->activeBooking->customer_name }}
            <span class="text-slate-400"> · départ {{ $room->activeBooking->check_out->format('d/m') }}</span>
        </div>
        @endif
        <div class="flex gap-2 mt-auto pt-1 border-t border-slate-100">
            @if($room->status === 'available')
            @can('residence.bookings.create')
            <a href="{{ route('residence.bookings.create', ['room_id' => $room->id]) }}"
               class="flex-1 text-center text-xs bg-violet-600 hover:bg-violet-700 text-white py-1.5 rounded-lg font-medium transition">
                <i class="fa-solid fa-plus mr-1"></i> Réserver
            </a>
            @endcan
            @endif
            @can('residence.rooms.edit')
            <button onclick='openEditRoom(@json($room))'
                    class="flex-1 text-center text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 py-1.5 rounded-lg font-medium transition">
                <i class="fa-solid fa-pen"></i> Modifier
            </button>
            @endcan
            @can('residence.rooms.delete')
            <form method="POST" action="{{ route('residence.rooms.destroy', $room) }}"
                  onsubmit="return confirm('Supprimer cette chambre ?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 py-1.5 px-2 rounded-lg hover:bg-red-50 transition">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
            @endcan
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-16 text-slate-400">
        <i class="fa-solid fa-door-closed text-5xl mb-3 block"></i>
        <p class="font-medium">Aucune chambre enregistrée</p>
        <p class="text-sm mt-1">Ajoutez des types de chambre puis créez vos chambres</p>
    </div>
    @endforelse
</div>

{{-- Add Room Modal --}}
@can('residence.rooms.create')
<div id="addRoomModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Ajouter une chambre</h3>
            <button onclick="document.getElementById('addRoomModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('residence.rooms.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Type de chambre <span class="text-red-500">*</span></label>
                <select name="room_type_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    <option value="">— Choisir —</option>
                    @foreach($roomTypes as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }} ({{ number_format($rt->base_price, 0, ',', ' ') }} MRU/nuit)</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Numéro <span class="text-red-500">*</span></label>
                    <input type="text" name="number" required placeholder="ex: 101"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Étage</label>
                    <input type="text" name="floor" placeholder="ex: 1"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addRoomModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">
                    Créer la chambre
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- Edit Room Modal --}}
@can('residence.rooms.edit')
<div id="editRoomModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Modifier la chambre</h3>
            <button onclick="document.getElementById('editRoomModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" id="editRoomForm" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Type de chambre</label>
                <select name="room_type_id" id="editRoomTypeId" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    @foreach($roomTypes as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Numéro</label>
                    <input type="text" name="number" id="editRoomNumber" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Étage</label>
                    <input type="text" name="floor" id="editRoomFloor"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Statut</label>
                <select name="status" id="editRoomStatus" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    <option value="available">Disponible</option>
                    <option value="occupied">Occupée</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('editRoomModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
<script>
function openEditRoom(room) {
    document.getElementById('editRoomForm').action = '/residence/rooms/' + room.id;
    document.getElementById('editRoomTypeId').value = room.room_type_id;
    document.getElementById('editRoomNumber').value  = room.number;
    document.getElementById('editRoomFloor').value   = room.floor ?? '';
    document.getElementById('editRoomStatus').value  = room.status;
    document.getElementById('editRoomModal').classList.remove('hidden');
}
</script>
@endcan

@endsection
