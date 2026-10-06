{{--
    Liste de permissions à cocher, groupée par module, avec recherche et
    sélection rapide. Attend :
    - $permissions : Collection de Permission (id, name)
    - $checkedIds  : array des id déjà cochés
--}}
@php
    $groupLabels = [
        'accompaniments' => 'Accompagnements',
        'accounting'     => 'Comptabilité',
        'auth'           => 'Authentification',
        'categories'     => 'Catégories (plats)',
        'catering'       => 'Catering',
        'dashboard'      => 'Tableau de bord',
        'events'         => 'Événements',
        'hr'             => 'Ressources Humaines',
        'meals'          => 'Plats',
        'packagings'     => 'Emballages',
        'payment-types'  => 'Types de paiement',
        'permissions'    => 'Permissions',
        'pos'            => 'Point de Vente',
        'production'     => 'Production',
        'products'       => 'Produits',
        'purchases'      => 'Achats',
        'residence'      => 'Résidence',
        'roles'          => 'Rôles',
        'settings'       => 'Paramètres',
        'stock'          => 'Stock',
        'units'          => 'Unités',
        'users'          => 'Utilisateurs',
    ];

    $groups = $permissions
        ->sortBy('name')
        ->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });
@endphp

<div class="permission-checklist" data-component="permission-checklist">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
        <div class="relative flex-1">
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" class="perm-search w-full border rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                   placeholder="Rechercher une permission (ex: paie, employé, caisse...)">
        </div>
        <div class="flex items-center gap-2 text-sm">
            <button type="button" class="perm-check-all px-3 py-2 rounded-lg border text-gray-600 hover:bg-gray-50 font-medium">Tout cocher</button>
            <button type="button" class="perm-uncheck-all px-3 py-2 rounded-lg border text-gray-600 hover:bg-gray-50 font-medium">Tout décocher</button>
            <span class="perm-count text-gray-400 whitespace-nowrap"></span>
        </div>
    </div>

    <div class="space-y-2 perm-groups">
        @foreach($groups as $prefix => $groupPermissions)
            @php $groupChecked = $groupPermissions->filter(fn($p) => in_array($p->id, $checkedIds))->count(); @endphp
            <details class="perm-group border rounded-lg overflow-hidden" {{ $groupChecked > 0 ? 'open' : '' }}>
                <summary class="flex items-center justify-between gap-3 px-4 py-2.5 bg-gray-50 cursor-pointer select-none list-none">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fas fa-chevron-right text-xs text-gray-400 perm-chevron transition-transform"></i>
                        <span class="font-semibold text-gray-800 truncate">{{ $groupLabels[$prefix] ?? ucfirst($prefix) }}</span>
                        <span class="text-xs text-gray-400">({{ $groupPermissions->count() }})</span>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="group-checked-count text-xs font-medium px-2 py-0.5 rounded-full {{ $groupChecked > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }}">
                            {{ $groupChecked }}/{{ $groupPermissions->count() }}
                        </span>
                        <button type="button" class="perm-group-toggle text-xs text-blue-600 hover:text-blue-800 font-medium">Tout/Aucun</button>
                    </div>
                </summary>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1.5 p-4 pt-3 bg-white">
                    @foreach($groupPermissions as $permission)
                        <label class="perm-row flex items-start gap-2 py-1 px-1.5 rounded hover:bg-gray-50 cursor-pointer" data-search="{{ strtolower($permission->name.' '.$permission->description) }}">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                   {{ in_array($permission->id, $checkedIds) ? 'checked' : '' }}
                                   class="perm-checkbox mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-400">
                            <span class="min-w-0">
                                <span class="block text-sm text-gray-800 font-mono">{{ $permission->name }}</span>
                                @if($permission->description)
                                    <span class="block text-xs text-gray-400">{{ $permission->description }}</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </details>
        @endforeach
    </div>
    <p class="perm-empty hidden text-center text-sm text-gray-400 py-6">Aucune permission ne correspond à la recherche.</p>
</div>

@once
<script>
(function () {
    function initPermissionChecklist(root) {
        const search      = root.querySelector('.perm-search');
        const checkAll    = root.querySelector('.perm-check-all');
        const uncheckAll  = root.querySelector('.perm-uncheck-all');
        const countLabel  = root.querySelector('.perm-count');
        const groups      = Array.from(root.querySelectorAll('.perm-group'));
        const emptyNotice = root.querySelector('.perm-empty');

        function updateGlobalCount() {
            const total   = root.querySelectorAll('.perm-checkbox').length;
            const checked = root.querySelectorAll('.perm-checkbox:checked').length;
            if (countLabel) countLabel.textContent = checked + ' / ' + total + ' sélectionnée(s)';
        }

        function updateGroupCount(group) {
            const boxes   = group.querySelectorAll('.perm-checkbox');
            const checked = group.querySelectorAll('.perm-checkbox:checked');
            const badge   = group.querySelector('.group-checked-count');
            if (!badge) return;
            badge.textContent = checked.length + '/' + boxes.length;
            badge.classList.toggle('bg-blue-100', checked.length > 0);
            badge.classList.toggle('text-blue-700', checked.length > 0);
            badge.classList.toggle('bg-gray-100', checked.length === 0);
            badge.classList.toggle('text-gray-400', checked.length === 0);
        }

        root.querySelectorAll('.perm-checkbox').forEach(function (box) {
            box.addEventListener('change', function () {
                updateGroupCount(box.closest('.perm-group'));
                updateGlobalCount();
            });
        });

        groups.forEach(function (group) {
            const toggleBtn = group.querySelector('.perm-group-toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const boxes = group.querySelectorAll('.perm-checkbox');
                    const anyUnchecked = Array.from(boxes).some(function (b) { return !b.checked; });
                    boxes.forEach(function (b) { b.checked = anyUnchecked; });
                    updateGroupCount(group);
                    updateGlobalCount();
                });
            }
        });

        if (checkAll) {
            checkAll.addEventListener('click', function () {
                root.querySelectorAll('.perm-row:not([style*="display: none"]) .perm-checkbox').forEach(function (b) { b.checked = true; });
                groups.forEach(updateGroupCount);
                updateGlobalCount();
            });
        }
        if (uncheckAll) {
            uncheckAll.addEventListener('click', function () {
                root.querySelectorAll('.perm-checkbox').forEach(function (b) { b.checked = false; });
                groups.forEach(updateGroupCount);
                updateGlobalCount();
            });
        }

        if (search) {
            search.addEventListener('input', function () {
                const term = search.value.trim().toLowerCase();
                let anyVisible = false;
                groups.forEach(function (group) {
                    let groupHasMatch = false;
                    group.querySelectorAll('.perm-row').forEach(function (row) {
                        const match = !term || row.dataset.search.indexOf(term) !== -1;
                        row.style.display = match ? '' : 'none';
                        if (match) groupHasMatch = true;
                    });
                    group.style.display = groupHasMatch ? '' : 'none';
                    if (term && groupHasMatch) group.setAttribute('open', '');
                    if (groupHasMatch) anyVisible = true;
                });
                if (emptyNotice) emptyNotice.classList.toggle('hidden', anyVisible);
            });
        }

        updateGlobalCount();
    }

    function initAll() {
        document.querySelectorAll('[data-component="permission-checklist"]').forEach(initPermissionChecklist);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
</script>
<style>
    .perm-group[open] > summary .perm-chevron { transform: rotate(90deg); }
    .perm-group summary::-webkit-details-marker { display: none; }
</style>
@endonce
