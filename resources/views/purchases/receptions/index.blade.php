@extends('layouts.purchases')

@section('title', 'Réceptions (BL)')

@section('content')
<div class="max-w-5xl mx-auto space-y-5">

    {{-- ── En-tête ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Réceptions fournisseurs</h1>
            <p class="text-sm text-slate-500">Toute marchandise qui arrive entre en stock ici, avec son bon de livraison.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
        <a href="{{ route('purchases.guide') }}#scenario-complet"
           class="inline-flex items-center justify-center gap-2 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 px-4 py-3 rounded-xl text-sm font-semibold">
            <i class="fa-solid fa-book-open"></i> Guide
        </a>
        <a href="{{ route('purchases.receptions.direct') }}"
           class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-xl text-sm font-semibold shadow-sm">
            <i class="fa-solid fa-basket-shopping"></i> Réception directe (sans commande)
        </a>
        </div>
    </div>

    @if($toConfirm->isNotEmpty())
    <section class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <p class="text-sm text-amber-800 mb-2"><i class="fa-solid fa-circle-info mr-1"></i>
            <strong>{{ $toConfirm->count() }} bon(s) de commande pas encore confirmé(s).</strong>
            On ne peut faire le BL qu'après le clic sur <strong>« Confirmer & envoyer »</strong> dans la commande (responsable des achats).</p>
        <div class="flex flex-wrap gap-2">
            @foreach($toConfirm as $o)
            <a href="{{ route('purchases.orders.show', $o) }}" class="text-xs bg-white border border-amber-300 hover:border-amber-500 rounded-lg px-3 py-1.5 text-amber-800 font-semibold">
                {{ $o->reference }} · {{ $o->supplier->name ?? 'Fournisseur à préciser' }}
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ── À recevoir ──────────────────────────────────────────────────── --}}
    <section>
        <h2 class="text-sm font-semibold text-slate-600 mb-2 flex items-center gap-2">
            <i class="fa-solid fa-truck text-orange-500"></i> Commandes à réceptionner
            <span class="bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded-full">{{ $toReceive->count() }}</span>
        </h2>
        @if($toReceive->isEmpty())
            <div class="bg-white rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-400">
                Aucune commande confirmée en attente de livraison.
            </div>
        @else
            @if($toReceive->count() > 3)
            <div class="relative mb-3">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="search" id="orderSearch" placeholder="Rechercher : n° de commande, fournisseur, produit…"
                       class="w-full pl-10 pr-3 py-3 rounded-xl border border-slate-200 bg-white text-base focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            @endif
            <p id="noOrder" class="hidden text-sm text-slate-400 text-center py-4">Aucune commande ne correspond.</p>
            <div class="grid sm:grid-cols-2 gap-3" id="orderList">
                @foreach($toReceive as $o)
                    @php
                        $ordered  = $o->items->sum(fn ($i) => (float) $i->quantity);
                        $received = $o->items->sum(fn ($i) => $i->received_quantity);
                        $pct = $ordered > 0 ? min(100, round($received / $ordered * 100)) : 0;
                    @endphp
                    <a href="{{ route('purchases.receptions.create', $o) }}"
                       data-search="{{ \Illuminate\Support\Str::lower($o->reference . ' ' . ($o->supplier->name ?? '') . ' ' . $o->items->map(fn ($i) => $i->product->name ?? '')->implode(' ')) }}"
                       class="block bg-white rounded-xl border border-slate-200 hover:border-emerald-400 hover:shadow-md transition p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800">{{ $o->reference }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ $o->supplier->name ?? 'Fournisseur à préciser' }} · {{ $o->created_at->format('d/m/Y') }}</p>
                            </div>
                            <span class="shrink-0 text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $o->status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700' }}">
                                {{ $o->status === 'partial' ? 'Reliquat' : 'Attendue' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-2 line-clamp-2">
                            {{ $o->items->count() }} ligne(s) :
                            {{ $o->items->map(fn ($i) => $i->product->name ?? '—')->unique()->take(4)->implode(', ') }}{{ $o->items->count() > 4 ? '…' : '' }}
                        </p>
                        <div class="mt-2 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-emerald-700"><i class="fa-solid fa-truck-ramp-box mr-1"></i>Réceptionner</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ── Historique ─────────────────────────────────────────────────── --}}
    @php
        $origins = \App\Models\PurchaseOrder::ORIGINS;
        $badge = ['request' => 'bg-sky-100 text-sky-700', 'direct' => 'bg-emerald-100 text-emerald-700', 'manual' => 'bg-slate-100 text-slate-600', 'event' => 'bg-violet-100 text-violet-700'];
        $type = $filters['type'] ?? null;
        $keep = fn (array $extra) => array_filter(array_merge(['from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null, 'type' => $type], $extra));
    @endphp
    <section id="historique">
        <h2 class="text-sm font-semibold text-slate-600 mb-2 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Réceptions enregistrées
            <span class="bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full">{{ $receipts->total() }}</span>
        </h2>

        {{-- Filtres : période + origine --}}
        <form method="GET" action="{{ route('purchases.deliveries') }}#historique" class="bg-white rounded-xl border border-slate-200 p-3 mb-3 space-y-3">
            @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <label for="from" class="block text-[11px] font-semibold text-slate-500 mb-1">Du</label>
                    <input type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                </div>
                <div>
                    <label for="to" class="block text-[11px] font-semibold text-slate-500 mb-1">Au</label>
                    <input type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                </div>
                <button class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold"><i class="fa-solid fa-filter mr-1"></i>Filtrer</button>
                <div class="flex flex-wrap gap-1 text-xs">
                    @foreach(['Aujourd\'hui' => [now(), now()], '7 jours' => [now()->subDays(6), now()], 'Ce mois' => [now()->startOfMonth(), now()]] as $lbl => [$a, $b])
                        <a href="{{ route('purchases.deliveries', $keep(['from' => $a->toDateString(), 'to' => $b->toDateString()])) }}#historique"
                           class="px-2.5 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">{{ $lbl }}</a>
                    @endforeach
                </div>
                @if(array_filter($filters))
                    <a href="{{ route('purchases.deliveries') }}#historique" class="ml-auto text-sm text-red-600 hover:text-red-700 py-2"><i class="fa-solid fa-xmark mr-1"></i>Effacer les filtres</a>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('purchases.deliveries', $keep(['type' => null])) }}#historique"
                   class="px-3 py-1.5 rounded-full text-xs font-semibold border {{ !$type ? 'bg-slate-800 text-white border-slate-800' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    Toutes · {{ $counts->sum() }}
                </a>
                @foreach($origins as $key => $label)
                    @continue($key === 'event' && !$counts[$key])
                    <a href="{{ route('purchases.deliveries', $keep(['type' => $key])) }}#historique"
                       class="px-3 py-1.5 rounded-full text-xs font-semibold border {{ $type === $key ? 'bg-slate-800 text-white border-slate-800' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                        {{ $label }} · {{ $counts[$key] }}
                    </a>
                @endforeach
            </div>
        </form>

        <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100">
            @forelse($receipts as $r)
                @php
                    $origin = $r->purchaseOrder?->origin;
                    $da  = $origin === 'request' ? $r->purchaseOrder->purchaseRequest : null;
                    $cmp = $da ? ($comparisons[$da->id] ?? null) : null;
                @endphp
                <div class="p-3 sm:px-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 flex flex-wrap items-center gap-x-2 gap-y-1">
                            {{ $r->reference }}
                            @if($r->supplier_delivery_note)
                                <span class="font-normal text-slate-500">· BL {{ $r->supplier_delivery_note }}</span>
                            @endif
                            @if($origin)
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $badge[$origin] }}">
                                    {{ $origins[$origin] }}@if($da) · {{ $da->reference }}@endif
                                </span>
                            @endif
                            @if($cmp)
                                @php
                                    [$cmpCls, $cmpIcon, $cmpLbl] = match ($cmp['status']) {
                                        'ok'      => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'fa-circle-check', 'Conforme à la DA'],
                                        'waiting' => ['bg-amber-50 text-amber-700 border-amber-200', 'fa-hourglass-half', 'Reliquat attendu'],
                                        default   => ['bg-red-50 text-red-700 border-red-200', 'fa-triangle-exclamation', 'Écart avec la DA'],
                                    };
                                @endphp
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $cmpCls }}"><i class="fa-solid {{ $cmpIcon }} mr-1"></i>{{ $cmpLbl }}</span>
                            @endif
                        </p>
                        <p class="text-xs text-slate-500 truncate">
                            {{ optional($r->received_at)->format('d/m/Y H:i') }}
                            · {{ $r->purchaseOrder->supplier->name ?? '—' }}
                            · {{ $r->stock->name ?? '—' }}
                            · {{ $r->items_count }} ligne(s)
                            @if($r->receivedBy) · reçu par {{ $r->receivedBy->name }}@unless($r->receivedBy->hasRole('magasinier')) <span class="text-amber-700 font-semibold">(en remplacement du magasinier)</span>@endunless @endif
                        </p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        @if($r->purchaseOrder)
                            <a href="{{ route('purchases.orders.show', $r->purchaseOrder) }}" class="text-xs px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
                                {{ $r->purchaseOrder->reference }}
                            </a>
                            <a href="{{ route('purchases.orders.print.livraison', [$r->purchaseOrder, $r]) }}" target="_blank"
                               class="text-xs px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" title="Imprimer le BL">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Demandé (DA) / commandé (BC) / reçu (BL), tous BC et réceptions de la DA confondus --}}
                @if($cmp)
                    <details class="mt-2 group" @if($cmp['status'] === 'gap') open @endif>
                        <summary class="cursor-pointer text-xs font-semibold text-slate-500 hover:text-slate-700 select-none">
                            <i class="fa-solid fa-scale-balanced mr-1"></i>Comparer avec la demande {{ $da->reference }}
                        </summary>
                        <div class="mt-2 overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead class="text-[10px] uppercase text-slate-400 border-b">
                                    <tr>
                                        <th class="py-1.5 pr-2 text-left">Produit</th>
                                        <th class="py-1.5 px-2 text-right">Demandé</th>
                                        <th class="py-1.5 px-2 text-right">Commandé</th>
                                        <th class="py-1.5 px-2 text-right">Reçu (total)</th>
                                        <th class="py-1.5 pl-2 text-left">Écart</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($cmp['lines'] as $l)
                                        @php
                                            $p = $l['product']; $sym = $p->unit->symbol ?? '';
                                            $fmt = fn ($q) => $q > 0 ? \App\Support\Qty::format($q, $sym) : '—';
                                            $inPack = fn ($q) => $q > 0 ? $p->inMainPackaging($q) : null;
                                            $gap = $p->inMainPackaging(abs($l['diff'])) ?? \App\Support\Qty::format(abs($l['diff']), $sym);
                                        @endphp
                                        <tr class="{{ in_array($l['state'], ['short', 'over', 'extra']) ? 'bg-red-50/50' : '' }}">
                                            <td class="py-1.5 pr-2 font-medium text-slate-700">{{ $p->name ?? '—' }}</td>
                                            @foreach(['requested', 'ordered', 'received'] as $col)
                                                <td class="py-1.5 px-2 text-right whitespace-nowrap">
                                                    {{ $fmt($l[$col]) }}
                                                    @if($inPack($l[$col]))<span class="block text-[10px] text-slate-400">{{ $inPack($l[$col]) }}</span>@endif
                                                </td>
                                            @endforeach
                                            <td class="py-1.5 pl-2 whitespace-nowrap">
                                                @switch($l['state'])
                                                    @case('ok')      <span class="text-emerald-700 font-semibold"><i class="fa-solid fa-check"></i> Conforme</span> @break
                                                    @case('waiting') <span class="text-amber-700 font-semibold">Encore attendu : {{ $gap }}</span> @break
                                                    @case('short')   <span class="text-red-700 font-semibold">Manque {{ $gap }}</span> @break
                                                    @case('over')    <span class="text-red-700 font-semibold">Surplus +{{ $gap }}</span> @break
                                                    @case('extra')   <span class="text-red-700 font-semibold">Non demandé dans la DA</span> @break
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endif
                </div>
            @empty
                <p class="p-6 text-center text-sm text-slate-400">{{ array_filter($filters) ? 'Aucune réception pour ces filtres.' : 'Aucune réception pour l’instant.' }}</p>
            @endforelse
        </div>
        <div class="mt-3">{{ $receipts->links() }}</div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('orderSearch');
    if (!input) return;
    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        let shown = 0;
        document.querySelectorAll('#orderList [data-search]').forEach(a => {
            const ok = !q || a.dataset.search.includes(q);
            a.classList.toggle('hidden', !ok);
            if (ok) shown++;
        });
        document.getElementById('noOrder').classList.toggle('hidden', shown > 0);
    });
})();
</script>
@endpush
