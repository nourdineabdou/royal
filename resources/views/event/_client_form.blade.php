<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">Nom <span class="text-red-500">*</span></label>
    <input type="text" name="name" required value="{{ old('name') }}"
           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Téléphone</label>
        <input type="text" name="phone" value="{{ old('phone') }}"
               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}"
               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
    </div>
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">Entreprise</label>
    <input type="text" name="company" value="{{ old('company') }}"
           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
    <textarea name="notes" rows="2"
              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">{{ old('notes') }}</textarea>
</div>
