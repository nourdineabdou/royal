{{-- Shared contract form partial. Used in contracts.blade.php create + edit modals. --}}
{{-- Requires $clients to be available in view scope. $edit=false for creation. --}}

@php $sfx = ($edit ?? false) ? '_edit' : ''; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Client --}}
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Client <span class="text-red-500">*</span></label>
        <select name="client_id" required
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
            <option value="">— Sélectionner un client —</option>
            @foreach($clients as $cl)
            <option value="{{ $cl->id }}">{{ $cl->name }}{{ $cl->company ? ' (' . $cl->company . ')' : '' }}</option>
            @endforeach
        </select>
    </div>

    {{-- Dates --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Date début <span class="text-red-500">*</span></label>
        <input type="date" name="start_date" required
               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Date fin <span class="text-red-500">*</span></label>
        <input type="date" name="end_date" required
               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
    </div>

    {{-- Guest count + Status --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre de convives <span class="text-red-500">*</span></label>
        <input type="number" name="guest_count" min="1" required placeholder="50"
               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Statut <span class="text-red-500">*</span></label>
        <select name="status" required
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
            <option value="active">Actif</option>
            <option value="paused">Suspendu</option>
            <option value="ended">Terminé</option>
        </select>
    </div>

    {{-- Active days --}}
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-2">Jours actifs <span class="text-red-500">*</span></label>
        <div class="flex flex-wrap gap-2">
            @foreach([1=>'Lun',2=>'Mar',3=>'Mer',4=>'Jeu',5=>'Ven',6=>'Sam',7=>'Dim'] as $num => $label)
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" name="active_days[]" value="{{ $num }}"
                       class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                       {{ in_array($num, [1,2,3,4,5]) && !($edit ?? false) ? 'checked' : '' }}>
                <span class="text-sm text-slate-700">{{ $label }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Meal types with conditional prices --}}
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-2">Types de repas inclus</label>
        <div class="space-y-3">
            @foreach(['breakfast'=>['label'=>'Petit-déjeuner','icon'=>'🌅','color'=>'amber'],
                       'lunch'    =>['label'=>'Déjeuner',      'icon'=>'☀️', 'color'=>'blue'],
                       'dinner'   =>['label'=>'Dîner',          'icon'=>'🌙','color'=>'indigo']] as $type => $cfg)
            <div class="flex items-center gap-4 p-3 border border-slate-200 rounded-xl">
                <label class="flex items-center gap-2 cursor-pointer min-w-[160px]">
                    <input type="checkbox" name="has_{{ $type }}"
                           class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 meal-toggle"
                           data-type="{{ $type }}" data-sfx="{{ $sfx }}"
                           {{ $type === 'lunch' && !($edit ?? false) ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-slate-700">{{ $cfg['icon'] }} {{ $cfg['label'] }}</span>
                </label>
                <div id="price_row_{{ $type }}{{ $sfx }}" class="flex items-center gap-2 flex-1"
                     style="{{ ($type === 'lunch' && !($edit??false)) ? '' : 'display:none' }}">
                    <input type="number" name="price_{{ $type }}" step="0.01" min="0" placeholder="Prix MRU"
                           class="w-40 border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <span class="text-xs text-slate-400">MRU / code</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

<script>
// Toggle price field visibility when meal checkbox changes
document.querySelectorAll('.meal-toggle').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var row = document.getElementById('price_row_' + this.dataset.type + this.dataset.sfx);
        if (row) row.style.display = this.checked ? '' : 'none';
    });
});
</script>
