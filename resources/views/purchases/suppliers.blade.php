@extends('layouts.purchases')
@section('title', 'Fournisseurs')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold text-slate-800">Fournisseurs</h2>
    @can('purchases.suppliers.create')
    <button onclick="document.getElementById('addSupplierModal').classList.remove('hidden')"
            class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium shadow-sm transition">
        <i class="fa-solid fa-plus"></i> Ajouter fournisseur
    </button>
    @endcan
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr class="text-xs text-slate-400 uppercase">
                    <th class="px-5 py-3 text-left">Nom</th>
                    <th class="px-5 py-3 text-left">Contact</th>
                    <th class="px-5 py-3 text-left">Email</th>
                    <th class="px-5 py-3 text-right">Nb commandes</th>
                    <th class="px-5 py-3 text-right">Total achats</th>
                    <th class="px-5 py-3 text-right">Impayé</th>
                    <th class="px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $sup)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $sup->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $sup->phone ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $sup->email ?? '—' }}</td>
                    <td class="px-5 py-3 text-right text-slate-600">{{ $sup->purchase_orders_count }}</td>
                    <td class="px-5 py-3 text-right font-medium">{{ number_format($sup->purchase_orders_sum_total_amount ?? 0, 0, ',', ' ') }} MRU</td>
                    <td class="px-5 py-3 text-right font-bold {{ ($sup->purchase_orders_sum_remaining_amount ?? 0) > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                        {{ number_format($sup->purchase_orders_sum_remaining_amount ?? 0, 0, ',', ' ') }} MRU
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @can('purchases.suppliers.edit')
                            <button onclick='openEditSupplier(@json($sup))'
                                    class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 flex items-center justify-center transition" title="Modifier">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            @endcan
                            @can('purchases.suppliers.delete')
                            <form method="POST" action="{{ route('purchases.suppliers.destroy', $sup) }}" onsubmit="return confirm('Supprimer ce fournisseur ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 flex items-center justify-center transition" title="Supprimer">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Aucun fournisseur enregistré</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $suppliers->links() }}</div>
</div>

{{-- ADD MODAL --}}
<div id="addSupplierModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Nouveau fournisseur</h3>
            <button onclick="document.getElementById('addSupplierModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('purchases.suppliers.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Téléphone</label>
                    <input type="text" name="phone" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Adresse</label>
                <textarea name="address" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addSupplierModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-orange-500 hover:bg-orange-600 text-white font-medium transition">Ajouter</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div id="editSupplierModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-bold text-slate-800">Modifier fournisseur</h3>
            <button onclick="document.getElementById('editSupplierModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="editSupplierForm" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="editName" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Téléphone</label>
                    <input type="text" name="phone" id="editPhone" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" id="editEmail" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Adresse</label>
                <textarea name="address" id="editAddress" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('editSupplierModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 transition">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-orange-500 hover:bg-orange-600 text-white font-medium transition">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditSupplier(s) {
    document.getElementById('editSupplierForm').action = '/purchases/suppliers/' + s.id;
    document.getElementById('editName').value    = s.name    || '';
    document.getElementById('editPhone').value   = s.phone   || '';
    document.getElementById('editEmail').value   = s.email   || '';
    document.getElementById('editAddress').value = s.address || '';
    document.getElementById('editSupplierModal').classList.remove('hidden');
}
</script>
@endpush
