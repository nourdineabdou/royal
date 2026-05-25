@extends('layouts.event')
@section('title', 'Catalogue Services')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Catalogue des services</h2>
        <p class="text-sm text-slate-500 mt-0.5">{{ $services->total() }} service(s) disponible(s)</p>
    </div>
    @can('events.services.create')
    <button onclick="document.getElementById('modalService').classList.remove('hidden')"
            class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
        <i class="fa-solid fa-plus"></i> Nouveau service
    </button>
    @endcan
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nom</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Prix unitaire</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Utilisations</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($services as $svc)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $svc->name }}</td>
                    <td class="px-4 py-3">
                        @php
                            $typeColor = match($svc->type) {
                                'hall'      => 'bg-violet-100 text-violet-700',
                                'equipment' => 'bg-blue-100 text-blue-700',
                                'logistic'  => 'bg-amber-100 text-amber-700',
                                default     => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $typeColor }} font-medium">
                            {{ $svc->type_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-semibold text-violet-700">{{ number_format((float)$svc->price, 0, ',', ' ') }} MRU</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600 font-medium">
                            {{ $svc->event_service_items_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('events.services.edit')
                            <button onclick='openEditService(@json($svc))'
                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-violet-100 text-slate-500 hover:text-violet-600 inline-flex items-center justify-center transition">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            @endcan
                            @can('events.services.delete')
                            <form method="POST" action="{{ route('event.services.destroy', $svc) }}"
                                  onsubmit="return confirm('Supprimer ce service ?')">
                                @csrf @method('DELETE')
                                <button class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 inline-flex items-center justify-center transition">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-400 py-10">
                        <i class="fa-solid fa-briefcase text-2xl block mb-2"></i>
                        Aucun service dans le catalogue.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($services->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $services->links() }}</div>
    @endif
</div>

{{-- CREATE MODAL --}}
<div id="modalService" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Nouveau service</h3>
            <button onclick="document.getElementById('modalService').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('event.services.store') }}" class="p-6 space-y-4">
            @csrf
            @include('event._service_form')
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalService').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div id="modalEditService" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Modifier le service</h3>
            <button onclick="document.getElementById('modalEditService').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" id="editServiceForm" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="editSvcName" required
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Prix (MRU) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="editSvcPrice" min="0" step="0.01" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Type <span class="text-red-500">*</span></label>
                    <select name="type" id="editSvcType" required
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        <option value="hall">Salle</option>
                        <option value="equipment">Équipement</option>
                        <option value="logistic">Logistique</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalEditService').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-violet-600 hover:bg-violet-700 text-white font-medium transition">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditService(s) {
    document.getElementById('editServiceForm').action = '/events/services/' + s.id;
    document.getElementById('editSvcName').value  = s.name;
    document.getElementById('editSvcPrice').value = s.price;
    document.getElementById('editSvcType').value  = s.type;
    document.getElementById('modalEditService').classList.remove('hidden');
}
</script>
@endpush

@endsection
