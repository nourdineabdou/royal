@extends('layouts.event')
@section('title', $event->client?->name . ' — ' . $event->event_type)

@section('content')

{{-- ── Header ─────────────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div class="flex items-center gap-3">
        <a href="{{ route('event.index') }}" class="text-slate-400 hover:text-slate-600 mt-1">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-xl font-bold text-slate-800">{{ $event->client?->name ?? '—' }}</h2>
                <span class="text-slate-400">·</span>
                <span class="text-slate-700 font-semibold">{{ $event->event_type }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $event->status_color }}">
                    {{ $event->status_label }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                <i class="fa-regular fa-calendar text-violet-400 mr-1"></i>{{ $event->event_date->format('d/m/Y') }}
                &nbsp;·&nbsp;
                <i class="fa-solid fa-users text-violet-400 mr-1"></i>{{ $event->guest_count }} convives
                @if($event->stock)
                &nbsp;·&nbsp;
                <i class="fa-solid fa-warehouse text-violet-400 mr-1"></i>{{ $event->stock->name }}
                @endif
            </p>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="flex items-center gap-2 flex-wrap">

        @if($event->status === 'draft')
            @can('events.edit')
            <button onclick="document.getElementById('modalEditEvent').classList.remove('hidden')"
                    class="px-3 py-2 rounded-xl text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition">
                <i class="fa-solid fa-pen mr-1"></i> Modifier
            </button>
            @endcan
            @can('events.validate')
            <form method="POST" action="{{ route('event.validate', $event) }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-3 py-2 rounded-xl text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition"
                        onclick="return confirm('Valider cet événement ? Le montant sera calculé et le stock vérifié.')">
                    <i class="fa-solid fa-check mr-1"></i> Valider
                </button>
            </form>
            @endcan
            @can('events.cancel')
            <form method="POST" action="{{ route('event.cancel', $event) }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-3 py-2 rounded-xl text-sm bg-red-50 hover:bg-red-100 text-red-600 font-medium transition"
                        onclick="return confirm('Annuler cet événement ?')">
                    <i class="fa-solid fa-ban mr-1"></i> Annuler
                </button>
            </form>
            @endcan
            <a href="{{ route('event.pdf', $event) }}" target="_blank"
               class="px-3 py-2 rounded-xl text-sm bg-slate-800 hover:bg-slate-900 text-white font-medium transition">
                <i class="fa-solid fa-file-pdf mr-1"></i> Générer PDF
            </a>
        @endif

        @if(in_array($event->status, ['validated', 'in_progress']))
        <form method="POST" action="{{ route('event.complete', $event) }}" class="inline">
            @csrf
            <button type="submit"
                    class="px-3 py-2 rounded-xl text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition"
                    onclick="return confirm('CLÔTURER cet événement ? Le stock sera décrémenté et la transaction générée. ACTION IRRÉVERSIBLE.')">
                <i class="fa-solid fa-flag-checkered mr-1"></i> Clôturer
            </button>
        </form>
        @if($event->status === 'validated')
        <form method="POST" action="{{ route('event.cancel', $event) }}" class="inline">
            @csrf
            <button type="submit"
                    class="px-3 py-2 rounded-xl text-sm bg-red-50 hover:bg-red-100 text-red-600 font-medium transition"
                    onclick="return confirm('Annuler cet événement ?')">
                <i class="fa-solid fa-ban mr-1"></i> Annuler
            </button>
        </form>
        @endif
        @endif

        @if($event->status === 'draft')
        @can('events.delete')
        <form method="POST" action="{{ route('event.destroy', $event) }}" class="inline">
            @csrf @method('DELETE')
            <button type="submit"
                    class="px-3 py-2 rounded-xl text-sm bg-red-50 hover:bg-red-100 text-red-500 font-medium transition"
                    onclick="return confirm('Supprimer définitivement cet événement ?')">
                <i class="fa-solid fa-trash"></i>
            </button>
        </form>
        @endcan
        @endif
    </div>
</div>

{{-- ── Summary cards ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-xl font-bold text-violet-700">{{ number_format($servicesTotal, 0, ',', ' ') }}</p>
        <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-briefcase mr-1"></i>Services (MRU)</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-xl font-bold text-orange-600">{{ number_format($mealsTotal, 0, ',', ' ') }}</p>
        <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-utensils mr-1"></i>Repas (MRU)</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center">
        <p class="text-xl font-bold text-blue-700">{{ number_format($optionsTotal, 0, ',', ' ') }}</p>
        <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-star mr-1"></i>Options (MRU)</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 text-center {{ $event->status === 'completed' ? 'bg-emerald-50' : '' }}">
        <p class="text-xl font-bold text-emerald-700">{{ number_format($computedTotal, 0, ',', ' ') }}</p>
        <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-sigma mr-1"></i>Total (MRU)</p>
    </div>
</div>

{{-- ── Tabs ────────────────────────────────────────────────────────────────── --}}
<div class="border-b border-slate-200 mb-5 flex gap-1 overflow-x-auto">
    @php $tabClass = 'px-4 py-2.5 text-sm font-medium border-b-2 transition whitespace-nowrap'; @endphp
    <button onclick="showTab('services')" id="btn-services"
            class="{{ $tabClass }} border-violet-600 text-violet-700">
        <i class="fa-solid fa-briefcase mr-1"></i> Services
        <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs bg-violet-100 text-violet-700">{{ $event->serviceItems->count() }}</span>
    </button>
    <button onclick="showTab('meals')" id="btn-meals"
            class="{{ $tabClass }} border-transparent text-slate-500 hover:text-slate-700">
        <i class="fa-solid fa-utensils mr-1"></i> Repas
        <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $event->eventMeals->count() }}</span>
    </button>
    <button onclick="showTab('options')" id="btn-options"
            class="{{ $tabClass }} border-transparent text-slate-500 hover:text-slate-700">
        <i class="fa-solid fa-star mr-1"></i> Options
        <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $event->options->count() }}</span>
    </button>
    <button onclick="showTab('bilan')" id="btn-bilan"
            class="{{ $tabClass }} border-transparent text-slate-500 hover:text-slate-700">
        <i class="fa-solid fa-warehouse mr-1"></i> Stock & Bilan
        @if(!$stockOk && $event->stock_id)
        <span class="ml-1 w-2 h-2 rounded-full bg-red-500 inline-block"></span>
        @endif
    </button>
</div>

{{-- ═══ TAB: SERVICES ═══════════════════════════════════════════════════════ --}}
<div id="tab-services">
    @if($event->status === 'draft')
    @can('events.service-items.manage')
    <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-plus text-violet-500"></i> Ajouter un service
        </h3>
        <form method="POST" action="{{ route('event.service-items.store', $event) }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="md:col-span-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Service <span class="text-red-500">*</span></label>
                    <select name="service_id" id="serviceSelect" required onchange="fillServicePrice(this)"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        <option value="">— Choisir —</option>
                        @foreach($availableServices as $svc)
                        <option value="{{ $svc->id }}" data-price="{{ $svc->price }}">
                            {{ $svc->name }} ({{ $svc->type_label }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Quantité <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" min="1" value="1" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Prix unitaire (MRU) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="servicePrice" min="0" step="0.01" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
            </div>
            <button type="submit" class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                <i class="fa-solid fa-plus"></i> Ajouter
            </button>
        </form>
    </div>
    @endcan
    @endif

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Service</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Qté</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">P.U.</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Sous-total</th>
                    @if($event->status === 'draft')
                    <th class="px-4 py-3"></th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($event->serviceItems as $si)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $si->service?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">
                            {{ $si->service?->type_label ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $si->quantity }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ number_format((float)$si->price, 0, ',', ' ') }} MRU</td>
                    <td class="px-4 py-3 font-semibold text-violet-700">{{ number_format($si->subtotal, 0, ',', ' ') }} MRU</td>
                    @if($event->status === 'draft')
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('event.service-items.destroy', $si) }}"
                              onsubmit="return confirm('Retirer ce service ?')">
                            @csrf @method('DELETE')
                            <button class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 inline-flex items-center justify-center transition">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-400 py-8">Aucun service ajouté.</td>
                </tr>
                @endforelse
                @if($event->serviceItems->count() > 0)
                <tr class="bg-violet-50">
                    <td colspan="4" class="px-4 py-2 text-right text-sm font-semibold text-slate-700">Total services :</td>
                    <td class="px-4 py-2 font-bold text-violet-700">{{ number_format($servicesTotal, 0, ',', ' ') }} MRU</td>
                    @if($event->status === 'draft')<td></td>@endif
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- ═══ TAB: MEALS ══════════════════════════════════════════════════════════ --}}
<div id="tab-meals" class="hidden">
    @if($event->status === 'draft')
    <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-plus text-violet-500"></i> Ajouter un service repas
        </h3>
        <form method="POST" action="{{ route('event.meals.store', $event) }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Type de service <span class="text-red-500">*</span></label>
                    <select name="type" required
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        <option value="">— Choisir —</option>
                        <option value="breakfast">🌅 Petit-déjeuner</option>
                        <option value="lunch">☀️ Déjeuner</option>
                        <option value="dinner">🌙 Dîner</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre de convives <span class="text-red-500">*</span></label>
                    <input type="number" name="guest_count" min="1" value="{{ $event->guest_count }}" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-2">Plats au menu</label>
                <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-2 border border-slate-100 rounded-xl">
                    @foreach($availableMeals as $m)
                    <label class="flex items-center gap-1.5 text-xs cursor-pointer px-2.5 py-1.5 border border-slate-200 rounded-lg hover:border-violet-400 transition">
                        <input type="checkbox" name="meal_ids[]" value="{{ $m->id }}" class="w-3.5 h-3.5 rounded text-violet-500">
                        {{ $m->name }}
                        @if($m->price > 0)
                        <span class="text-slate-400">({{ number_format((float)$m->price, 0, ',', ' ') }})</span>
                        @endif
                    </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                <i class="fa-solid fa-plus"></i> Ajouter ce service repas
            </button>
        </form>
    </div>
    @endif

    <div class="space-y-4">
        @forelse($event->eventMeals as $em)
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $em->type_color }}">
                        {{ $em->type_label }}
                    </span>
                    <span class="text-sm text-slate-600">
                        <i class="fa-solid fa-users text-slate-400 mr-1"></i>{{ $em->guest_count }} convives
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    @php $mealSubtotal = $em->guest_count * $em->items->sum(fn($mi) => (float)($mi->meal?->price ?? 0)); @endphp
                    @if($mealSubtotal > 0)
                    <span class="text-sm font-bold text-violet-700">{{ number_format($mealSubtotal, 0, ',', ' ') }} MRU</span>
                    @endif
                    @if($event->status === 'draft')
                    <form method="POST" action="{{ route('event.meals.destroy', $em) }}"
                          onsubmit="return confirm('Retirer ce service repas ?')">
                        @csrf @method('DELETE')
                        <button class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 inline-flex items-center justify-center transition">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @if($em->items->count() > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($em->items as $mi)
                <span class="text-xs px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                    {{ $mi->meal?->name ?? '—' }}
                    @if(($mi->meal?->price ?? 0) > 0)
                    <span class="text-slate-400">· {{ number_format((float)$mi->meal->price, 0, ',', ' ') }}</span>
                    @endif
                </span>
                @endforeach
            </div>
            @else
            <p class="text-xs text-slate-400 italic">Aucun plat défini</p>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm p-8 text-center text-slate-400">
            <i class="fa-solid fa-utensils text-2xl block mb-2"></i>
            Aucun service repas ajouté.
        </div>
        @endforelse
    </div>
</div>

{{-- ═══ TAB: OPTIONS ════════════════════════════════════════════════════════ --}}
<div id="tab-options" class="hidden">
    @if($event->status === 'draft')
    <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-plus text-violet-500"></i> Ajouter une option
        </h3>
        <form method="POST" action="{{ route('event.options.store', $event) }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="md:col-span-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nom de l'option <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Ex: Décoration florale, Pièce montée…"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Prix (MRU) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" min="0" step="0.01" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Quantité <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" min="1" value="1" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
            </div>
            @if($availableRecipes->count() > 0)
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-2">Recettes liées (impact stock)</label>
                <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-2 border border-slate-100 rounded-xl">
                    @foreach($availableRecipes as $r)
                    <label class="flex items-center gap-1.5 text-xs cursor-pointer px-2.5 py-1.5 border border-slate-200 rounded-lg hover:border-violet-400 transition">
                        <input type="checkbox" name="recipe_ids[]" value="{{ $r->id }}" class="w-3.5 h-3.5 rounded text-violet-500">
                        {{ $r->meal?->name ?? 'Recette #'.$r->id }}
                    </label>
                    @endforeach
                </div>
            </div>
            @endif
            <button type="submit" class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                <i class="fa-solid fa-plus"></i> Ajouter cette option
            </button>
        </form>
    </div>
    @endif

    <div class="space-y-4">
        @forelse($event->options as $opt)
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div>
                    <p class="font-semibold text-slate-800">{{ $opt->name }}</p>
                    <p class="text-sm text-slate-500 mt-0.5">
                        {{ number_format((float)$opt->price, 0, ',', ' ') }} MRU × {{ $opt->quantity }}
                        = <span class="font-bold text-violet-700">{{ number_format($opt->subtotal, 0, ',', ' ') }} MRU</span>
                    </p>
                </div>
                @if($event->status === 'draft')
                <form method="POST" action="{{ route('event.options.destroy', $opt) }}"
                      onsubmit="return confirm('Retirer cette option ?')">
                    @csrf @method('DELETE')
                    <button class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 inline-flex items-center justify-center transition">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </form>
                @endif
            </div>
            @if($opt->items->count() > 0)
            <div class="flex flex-wrap gap-2 mt-2">
                <span class="text-xs text-slate-400">Recettes :</span>
                @foreach($opt->items as $oi)
                <span class="text-xs px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700">
                    {{ $oi->recipe?->meal?->name ?? 'Recette #'.$oi->recipe_id }}
                </span>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm p-8 text-center text-slate-400">
            <i class="fa-regular fa-star text-2xl block mb-2"></i>
            Aucune option ajoutée.
        </div>
        @endforelse
    </div>
</div>

{{-- ═══ TAB: BILAN ══════════════════════════════════════════════════════════ --}}
<div id="tab-bilan" class="hidden">
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

        {{-- Total breakdown --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-violet-400"></i> Récapitulatif financier
            </h3>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-50">
                    <tr>
                        <td class="py-2 text-slate-600"><i class="fa-solid fa-briefcase text-violet-400 w-5 mr-2"></i>Services</td>
                        <td class="py-2 text-right font-medium text-slate-800">{{ number_format($servicesTotal, 0, ',', ' ') }} MRU</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-slate-600"><i class="fa-solid fa-utensils text-orange-400 w-5 mr-2"></i>Repas</td>
                        <td class="py-2 text-right font-medium text-slate-800">{{ number_format($mealsTotal, 0, ',', ' ') }} MRU</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-slate-600"><i class="fa-regular fa-star text-blue-400 w-5 mr-2"></i>Options</td>
                        <td class="py-2 text-right font-medium text-slate-800">{{ number_format($optionsTotal, 0, ',', ' ') }} MRU</td>
                    </tr>
                    <tr class="bg-violet-50">
                        <td class="py-3 px-2 font-bold text-slate-800 rounded-l-xl"><i class="fa-solid fa-sigma text-violet-500 w-5 mr-2"></i>TOTAL</td>
                        <td class="py-3 px-2 text-right font-bold text-violet-700 text-base rounded-r-xl">{{ number_format($computedTotal, 0, ',', ' ') }} MRU</td>
                    </tr>
                </tbody>
            </table>
            @if($event->status === 'completed' && $event->transaction)
            <div class="mt-4 p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                <p class="text-xs font-semibold text-emerald-700 mb-1"><i class="fa-solid fa-receipt mr-1"></i> Transaction comptable</p>
                <p class="text-sm text-emerald-800 font-mono">{{ $event->transaction->reference }}</p>
                <p class="text-xs text-emerald-600 mt-0.5">{{ $event->completed_at?->format('d/m/Y à H:i') }}</p>
            </div>
            @endif
            @if($event->validated_at)
            <div class="mt-3 flex items-center gap-2 text-xs text-blue-600">
                <i class="fa-solid fa-check-circle"></i>
                Validé le {{ $event->validated_at->format('d/m/Y à H:i') }}
            </div>
            @endif
        </div>

        {{-- Stock Requirements --}}
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-warehouse text-violet-400"></i> Besoins en stock
                </h3>
                @if($event->stock_id)
                <button onclick="refreshStock()"
                        class="text-xs text-violet-600 hover:text-violet-800 flex items-center gap-1">
                    <i class="fa-solid fa-rotate-right" id="refreshIcon"></i> Actualiser
                </button>
                @endif
            </div>

            @if(!$event->stock_id)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-2"></i>
                Aucun stock assigné. Modifiez l'événement pour lier un stock.
            </div>
            @elseif(count($stockRequirements) === 0)
            <p class="text-sm text-slate-400 text-center py-6">Aucun ingrédient calculé (ajoutez des repas ou options avec recettes).</p>
            @else
            @if(!$stockOk)
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-3 text-xs text-red-700 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                Certains produits sont en quantité insuffisante.
            </div>
            @else
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 mb-3 text-xs text-emerald-700 flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                Stock suffisant pour cet événement.
            </div>
            @endif
            <div id="stockTable" class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-1.5 text-left text-slate-500 font-semibold uppercase">Produit</th>
                            <th class="py-1.5 text-right text-slate-500 font-semibold uppercase">Besoin</th>
                            <th class="py-1.5 text-right text-slate-500 font-semibold uppercase">Dispo</th>
                            <th class="py-1.5 text-center text-slate-500 font-semibold uppercase">OK</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($stockRequirements as $req)
                        <tr class="{{ !$req['ok'] ? 'bg-red-50' : '' }}">
                            <td class="py-1.5 font-medium text-slate-800">{{ $req['product']?->name ?? '—' }}</td>
                            <td class="py-1.5 text-right text-slate-700">
                                {{ round($req['qty'], 2) }} {{ $req['product']?->unit?->name ?? '' }}
                            </td>
                            <td class="py-1.5 text-right {{ $req['ok'] ? 'text-emerald-700' : 'text-red-600 font-bold' }}">
                                {{ round($req['available'], 2) }} {{ $req['product']?->unit?->name ?? '' }}
                            </td>
                            <td class="py-1.5 text-center">
                                @if($req['ok'])
                                <i class="fa-solid fa-check text-emerald-500"></i>
                                @else
                                <i class="fa-solid fa-xmark text-red-500"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Stock usages (if completed) --}}
    @if($event->status === 'completed' && $event->stockUsages->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm p-5 mt-5">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-box-open text-emerald-400"></i> Consommations de stock enregistrées
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Produit</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 uppercase">Quantité consommée</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($event->stockUsages as $su)
                    <tr>
                        <td class="px-4 py-2 font-medium text-slate-800">{{ $su->product?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-right text-slate-700">
                            {{ number_format((float)$su->quantity, 2, ',', ' ') }}
                            {{ $su->product?->unit?->name ?? '' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- ── EDIT EVENT MODAL ─────────────────────────────────────────────────── --}}
<div id="modalEditEvent" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white">
            <h3 class="font-bold text-slate-800">Modifier l'événement</h3>
            <button onclick="document.getElementById('modalEditEvent').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('event.update', $event) }}" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Client <span class="text-red-500">*</span></label>
                <select name="client_id" required
                        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    <option value="">— Choisir —</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ $event->client_id == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}{{ $c->company ? ' — '.$c->company : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Type d'événement <span class="text-red-500">*</span></label>
                <input type="text" name="event_type" required value="{{ $event->event_type }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="event_date" required value="{{ $event->event_date->format('Y-m-d') }}"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Convives <span class="text-red-500">*</span></label>
                    <input type="number" name="guest_count" required min="1" value="{{ $event->guest_count }}"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Stock lié</label>
                <select name="stock_id"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    <option value="">— Aucun —</option>
                    @foreach($stocks as $s)
                    <option value="{{ $s->id }}" {{ $event->stock_id == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t">
                <button type="button" onclick="document.getElementById('modalEditEvent').classList.add('hidden')"
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
function showTab(name) {
    ['services','meals','options','bilan'].forEach(t => {
        document.getElementById('tab-' + t).classList.add('hidden');
        const btn = document.getElementById('btn-' + t);
        btn.classList.remove('border-violet-600','text-violet-700');
        btn.classList.add('border-transparent','text-slate-500');
    });
    document.getElementById('tab-' + name).classList.remove('hidden');
    const active = document.getElementById('btn-' + name);
    active.classList.add('border-violet-600','text-violet-700');
    active.classList.remove('border-transparent','text-slate-500');
}

function fillServicePrice(select) {
    const opt = select.options[select.selectedIndex];
    document.getElementById('servicePrice').value = opt.dataset.price || '';
}

async function refreshStock() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('animate-spin');
    try {
        const res = await fetch('{{ route("event.stock-preview", $event) }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        if (data.error) { alert(data.error); return; }
        const tbody = document.querySelector('#stockTable tbody');
        if (!tbody) return;
        tbody.innerHTML = data.items.map(item => `
            <tr class="${!item.ok ? 'bg-red-50' : ''}">
                <td class="py-1.5 font-medium text-slate-800">${item.product}</td>
                <td class="py-1.5 text-right text-slate-700">${item.required} ${item.unit}</td>
                <td class="py-1.5 text-right ${item.ok ? 'text-emerald-700' : 'text-red-600 font-bold'}">${item.available} ${item.unit}</td>
                <td class="py-1.5 text-center">${item.ok
                    ? '<i class="fa-solid fa-check text-emerald-500"></i>'
                    : '<i class="fa-solid fa-xmark text-red-500"></i>'}</td>
            </tr>
        `).join('');
    } catch(e) { alert('Erreur lors du rafraîchissement.'); }
    finally { icon.classList.remove('animate-spin'); }
}
</script>
@endpush

@endsection
