<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">Nom <span class="text-red-500">*</span></label>
    <input type="text" name="name" required value="{{ old('name') }}"
           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Prix (MRU) <span class="text-red-500">*</span></label>
        <input type="number" name="price" min="0" step="0.01" required value="{{ old('price') }}"
               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Type <span class="text-red-500">*</span></label>
        <select name="type" required
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            <option value="">— Choisir —</option>
            <option value="hall" {{ old('type') === 'hall' ? 'selected' : '' }}>Salle</option>
            <option value="equipment" {{ old('type') === 'equipment' ? 'selected' : '' }}>Équipement</option>
            <option value="logistic" {{ old('type') === 'logistic' ? 'selected' : '' }}>Logistique</option>
        </select>
    </div>
</div>
