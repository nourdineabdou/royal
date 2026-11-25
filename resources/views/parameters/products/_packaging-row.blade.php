{{-- Un emballage du produit : type, contenance, code-barres (+ caméra). Variables : $row, $packagings. --}}
<div class="packaging-row rounded-xl border border-gray-200 bg-gray-50 p-3 sm:p-4 space-y-3">
    <div class="flex items-center justify-between gap-2">
        <p class="text-sm font-bold text-gray-700">Emballage <span class="pkg-num"></span></p>
        <button type="button" class="pkg-remove inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 active:bg-red-100 text-sm font-semibold">
            <i class="fas fa-trash"></i> Retirer
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Type d'emballage</label>
            <select name="packaging_id[]" style="width:100%" class="w-full px-3 py-3 border border-gray-300 rounded-xl bg-white text-base focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">— Choisir —</option>
                @foreach($packagings as $packaging)
                    <option value="{{ $packaging->id }}" @selected($row['packaging_id'] == $packaging->id)>{{ $packaging->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Contenance d'un emballage</label>
            <div class="flex items-center border border-gray-300 rounded-xl bg-white overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
                <input type="number" step="0.01" min="0.01" name="packaging_quantity[]" inputmode="decimal" placeholder="Ex : 12000"
                       value="{{ $row['quantity'] }}" class="w-full min-w-0 px-3 py-3 text-base focus:outline-none">
                <span class="pkg-unit px-3 self-stretch flex items-center bg-gray-100 text-sm font-semibold text-gray-600"></span>
            </div>
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-semibold text-gray-600">Code-barres de cet emballage</label>
            <span class="pkg-code-state text-xs font-semibold"></span>
        </div>
        <div class="flex gap-2">
            <input type="text" name="packaging_barcode[]" data-bc-cam="1" inputmode="numeric" maxlength="64" autocomplete="off"
                   placeholder="Scanner ou taper" value="{{ $row['barcode'] ?? '' }}"
                   class="flex-1 min-w-0 px-3 py-3 border border-gray-300 rounded-xl bg-white font-mono text-base focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="button" title="Scanner avec la caméra"
                    class="bc-cam-btn shrink-0 inline-flex items-center justify-center gap-2 px-4 rounded-xl bg-slate-800 active:bg-slate-900 hover:bg-slate-900 text-white font-semibold">
                <i class="fas fa-camera"></i><span>Scanner</span>
            </button>
        </div>
    </div>
</div>
