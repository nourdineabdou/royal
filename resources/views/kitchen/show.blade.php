@extends('layouts.kitchen')
@section('title', $kr->reference)

@section('content')
@php
    use App\Support\Qty;
    $badge = [
        'pending'   => 'bg-sky-100 text-sky-700',
        'to_approve' => 'bg-violet-100 text-violet-700',
        'prepared'  => 'bg-amber-100 text-amber-800',
        'validated' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-slate-100 text-slate-500',
    ];
    $canTake = $kr->canBeTakenBy(auth()->user());
@endphp

<a href="{{ route('kitchen.requests.index') }}" class="text-sm text-slate-500"><i class="fa-solid fa-arrow-left mr-1"></i> Mes demandes</a>

<div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-2">
    <div class="flex items-start justify-between gap-2">
        <div>
            <h1 class="font-bold text-xl text-slate-800">{{ $kr->kitchen }}</h1>
            <p class="text-sm text-slate-500">Demande {{ $kr->reference }} · {{ $kr->created_at->format('d/m/Y H:i') }}{{ $kr->needed_on ? ' · pour le ' . $kr->needed_on->format('d/m') : '' }}</p>
        </div>
        <span class="shrink-0 text-xs font-bold px-2 py-1 rounded-full {{ $badge[$kr->status] ?? '' }}">{{ $kr->status_label }}</span>
    </div>
    <dl class="text-sm grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-slate-600">
        <dt class="text-slate-400">Demandé par</dt><dd>{{ $kr->requester->name ?? '—' }}@if($kr->requested_by === $kr->prepared_by) <span class="text-xs text-slate-400">(saisie au magasin)</span>@endif</dd>
        @if($kr->preparer)<dt class="text-slate-400">Remis par</dt><dd>{{ $kr->preparer->name }} · {{ $kr->prepared_at?->format('d/m H:i') }} · {{ $kr->stock->name ?? '' }}</dd>@endif
        @if($kr->approver)<dt class="text-slate-400">Approuvé par</dt><dd>{{ $kr->approver->name }} · {{ $kr->approved_at?->format('d/m H:i') }}</dd>@endif
        @if($kr->receiver)<dt class="text-slate-400">Remis à</dt><dd class="font-semibold text-slate-700">{{ $kr->receiver->name }}</dd>@endif
        @if($kr->validator)<dt class="text-slate-400">Pris par</dt><dd>{{ $kr->validator->name }} · {{ $kr->validated_at?->format('d/m H:i') }}</dd>@endif
        @if($kr->bs_number)<dt class="text-slate-400">Bon de sortie</dt><dd class="font-semibold text-emerald-700">{{ $kr->bs_number }}</dd>@endif
        @if($kr->notes)<dt class="text-slate-400">Remarque</dt><dd>{{ $kr->notes }}</dd>@endif
    </dl>
    @if($kr->status === 'to_approve')
        <p class="text-sm bg-violet-50 text-violet-700 rounded-xl px-3 py-2"><i class="fa-solid fa-stamp mr-1"></i>Quantité importante (plus de {{ \App\Models\KitchenRequest::thresholdLabel() }} du stock) : en attente de l'approbation du responsable des achats.</p>
    @endif
    @if(in_array($kr->status, ['pending', 'cancelled']) && $kr->contest_note)
        <p class="text-sm bg-red-50 text-red-700 rounded-xl px-3 py-2"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $kr->contest_note }}</p>
    @endif
</div>

{{-- ── Produits ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border {{ $canTake ? 'border-2 border-amber-300' : 'border-slate-200' }} divide-y divide-slate-100">
    <div class="grid grid-cols-[1fr_auto_auto] gap-3 px-4 py-2 text-[11px] font-semibold uppercase text-slate-400">
        <span>Produit</span><span class="text-right w-20">Demandé</span><span class="text-right w-20">{{ ($kr->isPrepared() && in_array($kr->status, ['to_approve', 'prepared', 'validated'])) ? 'Remis' : '' }}</span>
    </div>
    @foreach($kr->items as $item)
        @php
            $u = $item->product->unit->symbol ?? '';
            $diff = $item->delivered_quantity !== null && abs((float) $item->delivered_quantity - (float) $item->requested_quantity) > 0.001;
        @endphp
        <div class="grid grid-cols-[1fr_auto_auto] gap-3 px-4 py-3 items-center">
            <span class="font-semibold text-slate-800 text-base break-words">{{ $item->product->name ?? '—' }}</span>
            <span class="text-right w-20 text-slate-500">{{ Qty::format($item->requested_quantity, $u) }}</span>
            <span class="text-right w-20 font-bold {{ $diff ? 'text-amber-700' : 'text-slate-800' }}">
                @if(($kr->isPrepared() && in_array($kr->status, ['to_approve', 'prepared', 'validated']))){{ Qty::format($item->delivered_quantity ?? 0, $u) }}@endif
            </span>
            @if($item->siteAllocations->isNotEmpty())
                <p class="col-span-3 -mt-2 text-xs text-violet-700">
                    <i class="fa-solid fa-location-dot mr-1"></i>{{ $item->siteAllocations->map(fn ($a) => ($a->site->name ?? '—') . ' ' . Qty::format($a->quantity, $u))->implode(' · ') }}
                </p>
            @endif
        </div>
    @endforeach
</div>

{{-- ── Actions ─────────────────────────────────────────────────────── --}}
@if($canTake)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 space-y-3">
        <p class="text-sm text-amber-900"><strong>Vérifiez les quantités remises.</strong> En validant, vous confirmez avoir pris la marchandise : elle sort du stock du magasin et le bon de sortie est créé.</p>
        <form method="POST" action="{{ route('kitchen.requests.take', $kr) }}" id="takeForm" class="space-y-3">
            @csrf
            @if($errors->has('sites') || $errors->has('stock') || $errors->has('status'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-3 py-2 text-sm">
                    @foreach(array_merge($errors->get('sites'), $errors->get('stock'), $errors->get('status')) as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif

            @if($sites->count() === 1)
                <p class="text-sm text-violet-800 bg-violet-50 rounded-xl px-3 py-2">
                    <i class="fa-solid fa-location-dot mr-1"></i> Tout sera attribué au site <strong>{{ $sites->first()->name }}</strong>.
                </p>
            @elseif($sites->count() > 1)
                {{-- Plusieurs sites : le cuisinier dit où vont les produits (tout sur un site, ou réparti) --}}
                <div class="bg-white rounded-xl border border-violet-200 p-3 space-y-3">
                    <p class="text-sm font-semibold text-violet-800"><i class="fa-solid fa-location-dot mr-1"></i> Pour quel site sont ces produits ?</p>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <select id="allTo" class="k-input w-full sm:flex-1 px-3 py-2.5 border border-slate-300 rounded-xl bg-white">
                            @foreach($sites as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                        </select>
                        <button type="button" id="allToBtn" class="w-full sm:w-auto whitespace-nowrap px-4 py-2.5 rounded-xl bg-violet-600 text-white text-base font-semibold">Tout pour ce site</button>
                    </div>
                    <p class="text-xs text-slate-500">Ou répartissez produit par produit :</p>
                    @foreach($kr->items->filter(fn ($i) => (float) $i->delivered_quantity > 0) as $item)
                        @php $u = $item->product->unit->symbol ?? ''; $du = Qty::displayUnit($u); @endphp
                        <div class="split-row border-t border-slate-100 pt-2" data-total="{{ (float) $item->delivered_quantity }}" data-factor="{{ $du['factor'] }}" data-unit="{{ $u }}">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-800">{{ $item->product->name ?? '—' }}</span>
                                <span class="text-xs left font-semibold">—</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
                                @foreach($sites as $s)
                                    <label class="flex items-center gap-2 border border-slate-200 rounded-xl px-3 py-2">
                                        <span class="flex-1 text-sm text-slate-700 break-words">{{ $s->name }}</span>
                                        <input type="number" inputmode="decimal" min="0" step="any" data-site="{{ $s->id }}"
                                               class="disp k-input w-20 text-right font-bold py-1 border-b border-violet-300 focus:outline-none" value="{{ $loop->first ? round((float) $item->delivered_quantity / $du['factor'], 3) : 0 }}">
                                        <span class="text-xs text-slate-400">{{ $du['label'] }}</span>
                                        <input type="hidden" name="sites[{{ $item->id }}][{{ $s->id }}]" class="base">
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <button id="takeBtn" class="w-full py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white font-bold text-lg">
                <i class="fa-solid fa-check mr-1"></i> Je confirme, je prends
            </button>
        </form>
        @if($sites->count() > 1)
        <script>
        (function () {
            const rows = [...document.querySelectorAll('.split-row')];
            const nf = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 });
            const fmt = (q, u) => {
                const f = ({ g: ['kg', 1000], ml: ['L', 1000] })[(u || '').toLowerCase()];
                return f && Math.abs(q) >= f[1] ? nf.format(q / f[1]) + ' ' + f[0] : nf.format(q) + ' ' + u;
            };
            function refresh() {
                let ok = true;
                rows.forEach(r => {
                    let sum = 0;
                    r.querySelectorAll('.disp').forEach(i => {
                        const base = Math.round((parseFloat(i.value) || 0) * (+r.dataset.factor) * 100) / 100;
                        i.parentElement.querySelector('.base').value = base;
                        sum += base;
                    });
                    const left = Math.round(((+r.dataset.total) - sum) * 100) / 100;
                    const el = r.querySelector('.left');
                    if (Math.abs(left) <= 0.01) { el.textContent = '✓ tout réparti'; el.className = 'text-xs left font-semibold text-emerald-600'; }
                    else { el.textContent = (left > 0 ? 'reste ' : 'trop de ') + fmt(Math.abs(left), r.dataset.unit); el.className = 'text-xs left font-semibold text-red-600'; ok = false; }
                });
                document.getElementById('takeBtn').disabled = !ok;
            }
            document.getElementById('allToBtn').addEventListener('click', () => {
                const sid = document.getElementById('allTo').value;
                rows.forEach(r => r.querySelectorAll('.disp').forEach(i => {
                    i.value = i.dataset.site === sid ? Math.round((+r.dataset.total) / (+r.dataset.factor) * 1000) / 1000 : 0;
                }));
                refresh();
            });
            rows.forEach(r => r.querySelectorAll('.disp').forEach(i => i.addEventListener('input', refresh)));
            document.getElementById('takeForm').addEventListener('submit', () => { refresh(); setTimeout(() => document.getElementById('takeBtn').disabled = true); });
            refresh();
        })();
        </script>
        @else
        <script>document.getElementById('takeForm').addEventListener('submit', () => setTimeout(() => document.getElementById('takeBtn').disabled = true));</script>
        @endif
        <details class="bg-white rounded-xl border border-amber-200">
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-red-700">Ce n'est pas correct (écart, produit abîmé…)</summary>
            <form method="POST" action="{{ route('kitchen.requests.contest', $kr) }}" class="px-4 pb-4 space-y-2">
                @csrf
                <textarea name="contest_note" required maxlength="500" rows="2" placeholder="Ex : il manque 2 kg de riz"
                          class="k-input w-full px-3 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-300"></textarea>
                <button class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold">Renvoyer au magasinier</button>
            </form>
        </details>
    </div>
@endif

@if($kr->status === 'pending' && ($kr->requested_by === auth()->id() || auth()->user()->hasRole(['admin', 'super-admin'])))
    <form method="POST" action="{{ route('kitchen.requests.cancel', $kr) }}" onsubmit="return confirm('Annuler cette demande ?')">
        @csrf
        <button class="w-full py-3 rounded-xl border border-slate-300 text-slate-600 font-semibold bg-white">Annuler la demande</button>
    </form>
@endif

@if($kr->status === 'validated')
    <a href="{{ route('kitchen.requests.print', $kr) }}" target="_blank"
       class="flex items-center justify-center gap-2 w-full py-3 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold">
        <i class="fa-solid fa-print"></i> Imprimer le bon de sortie {{ $kr->bs_number }}
    </a>
@endif
@endsection
