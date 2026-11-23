@extends('layouts.stock')

@php
    use App\Support\Qty;
    $prepared = $kr->isPrepared();
    $column = $prepared ? 'delivered_quantity' : 'requested_quantity';
    $pct = \App\Models\KitchenRequest::thresholdLabel();
@endphp

@section('title', 'Approuver ' . $kr->reference)
@section('header', ($prepared ? 'Approuver la sortie ' : 'Approuver la demande ') . $kr->reference)

@section('content')
<div class="py-4 max-w-4xl mx-auto space-y-4 pb-32">

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between gap-2">
            <p class="text-lg font-bold text-slate-800">{{ $kr->kitchen }}</p>
            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $prepared ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700' }}">
                {{ $prepared ? 'Sortie préparée par le magasinier' : 'Demande de la cuisine (pas encore au magasin)' }}
            </span>
        </div>
        <p class="text-sm text-slate-500">
            {{ $kr->reference }} · demandé par {{ $kr->requester->name ?? '—' }} le {{ $kr->created_at->format('d/m H:i') }}{{ $kr->needed_on ? ' · pour le ' . $kr->needed_on->format('d/m') : '' }}
        </p>
        @if($prepared)
            <p class="text-sm text-slate-500">
                Préparée par <strong>{{ $kr->preparer->name ?? '—' }}</strong> le {{ $kr->prepared_at?->format('d/m H:i') }}
                · magasin <strong>{{ $kr->stock->name ?? '—' }}</strong>
                · remise à <strong>{{ $kr->receiver->name ?? '—' }}</strong>
            </p>
        @endif
        @if($kr->notes)<p class="text-sm text-slate-600 mt-1">« {{ $kr->notes }} »</p>@endif
        <p class="text-xs text-slate-500 mt-2 bg-slate-50 rounded-lg px-3 py-2">
            <i class="fa-solid fa-circle-info text-violet-600 mr-1"></i>
            Elle vous est soumise car au moins un produit dépasse <strong>{{ $pct }} du stock</strong>.
            @if($prepared)
                Après votre approbation, le cuisinier confirme la réception et le stock diminue.
            @else
                Stock compté <strong>tous magasins confondus</strong>. Après votre approbation, la demande part au magasinier, qui fera la sortie.
            @endif
        </p>
    </div>

    <form method="POST" action="{{ route('stock.kitchen-requests.approve.store', $kr) }}" id="approveForm" class="space-y-2">
        @csrf
        <div class="hidden md:grid grid-cols-12 gap-2 px-4 text-xs font-semibold text-slate-500 uppercase">
            <span class="col-span-3">Produit</span>
            <span class="col-span-2 text-right">{{ $prepared ? 'Demandé' : '' }}</span>
            <span class="col-span-2 text-right">En stock</span>
            <span class="col-span-3 text-center">Sort</span>
            <span class="col-span-2 text-right">Reste</span>
        </div>

        @foreach($kr->items as $item)
            @php
                $u = $item->product->unit->symbol ?? '';
                $du = Qty::displayUnit($u);
                $l = $lines[$item->id];
                $initial = round((float) $item->{$column} / $du['factor'], 3);
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 p-4 grid grid-cols-2 md:grid-cols-12 gap-2 items-center appr-row"
                 data-factor="{{ $du['factor'] }}" data-unit="{{ $u }}"
                 data-stock="{{ $l['in_stock'] }}" data-reserved="{{ $l['reserved'] }}" data-strict="{{ $prepared ? 1 : 0 }}">
                <div class="col-span-2 md:col-span-3 min-w-0">
                    <p class="font-semibold text-slate-800">{{ $item->product->name ?? '—' }}</p>
                    <p class="text-xs font-semibold text-amber-700 big hidden"><i class="fa-solid fa-triangle-exclamation"></i> Plus de {{ $pct }} du stock</p>
                    <p class="text-xs font-semibold text-red-600 short hidden"><i class="fa-solid fa-circle-xmark"></i> Pas assez en stock</p>
                </div>
                <div class="md:col-span-2 text-sm md:text-right">
                    @if($prepared)<span class="md:hidden text-xs text-slate-400">Demandé : </span>{{ Qty::format($item->requested_quantity, $u) }}@endif
                </div>
                <div class="md:col-span-2 text-sm md:text-right">
                    <span class="md:hidden text-xs text-slate-400">En stock : </span><strong>{{ Qty::format($l['in_stock'], $u) }}</strong>
                    @if($l['reserved'] > 0)
                        <p class="text-[11px] text-slate-400">dont {{ Qty::format($l['reserved'], $u) }} réservé (autres sorties)</p>
                    @endif
                </div>
                <div class="col-span-2 md:col-span-3 flex md:justify-center">
                    <div class="flex items-center border-2 border-violet-300 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-violet-400">
                        <input type="number" inputmode="decimal" min="0" step="any" class="disp w-24 text-right font-bold text-lg py-2 px-2 focus:outline-none"
                               value="{{ $initial }}" data-initial="{{ $initial }}">
                        <span class="px-3 text-sm font-semibold text-slate-500 bg-violet-50 self-stretch flex items-center">{{ $du['label'] }}</span>
                    </div>
                    <input type="hidden" name="qty[{{ $item->id }}]" class="base">
                </div>
                <div class="col-span-2 md:col-span-2 text-sm md:text-right">
                    <span class="md:hidden text-xs text-slate-400">Reste après la sortie : </span><strong class="rest text-lg">—</strong>
                    <p class="text-[11px] text-slate-400 changed hidden">modifié (initial : {{ Qty::format($item->{$column}, $u) }})</p>
                </div>
            </div>
        @endforeach
    </form>

    <details class="bg-white rounded-xl border border-red-200 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-red-600">
            <i class="fa-solid fa-rotate-left mr-1"></i> {{ $prepared ? 'Renvoyer au magasinier sans approuver' : 'Refuser la demande' }}
        </summary>
        <form method="POST" action="{{ route('stock.kitchen-requests.reject', $kr) }}" class="mt-3 space-y-2">
            @csrf
            <textarea name="contest_note" rows="2" required maxlength="500" placeholder="Motif (ex : quantité trop importante, voir avec le chef…)"
                      class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300">{{ old('contest_note') }}</textarea>
            <button class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">{{ $prepared ? 'Renvoyer' : 'Refuser' }}</button>
        </form>
    </details>

    <div class="fixed bottom-0 left-0 right-0 md:left-64 z-30 bg-white border-t border-slate-200 p-3 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]">
        <div class="max-w-4xl mx-auto flex items-center gap-3">
            <p class="flex-1 text-xs text-slate-500">{{ $prepared ? 'Ensuite : le cuisinier confirme la réception.' : 'Ensuite : le magasinier fait la sortie, puis le cuisinier confirme la réception.' }}</p>
            <a href="{{ route('stock.kitchen-requests.approvals') }}" class="px-4 py-3 rounded-lg border border-slate-200 text-slate-600 text-sm">Retour</a>
            <button form="approveForm" id="submitBtn" class="px-5 py-3 rounded-lg bg-violet-600 hover:bg-violet-700 disabled:bg-slate-300 text-white text-sm font-bold">
                <i class="fa-solid fa-stamp mr-1"></i> Approuver
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const THRESHOLD = {{ \App\Models\KitchenRequest::thresholdPercent() / 100 }};
    const rows = [...document.querySelectorAll('.appr-row')];
    const nf = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 });
    const fmt = (q, u) => {
        const f = ({ g: ['kg', 1000], ml: ['L', 1000] })[(u || '').toLowerCase()];
        return f && Math.abs(q) >= f[1] ? nf.format(q / f[1]) + ' ' + f[0] : nf.format(q) + ' ' + u;
    };

    function refresh() {
        let bad = false, any = false;
        rows.forEach(r => {
            const input = r.querySelector('.disp');
            const base = Math.round((parseFloat(input.value) || 0) * (+r.dataset.factor) * 100) / 100;
            r.querySelector('.base').value = base;

            const stock = +r.dataset.stock;
            const rest = Math.round((stock - (+r.dataset.reserved) - base) * 100) / 100;
            const short = rest < -0.0001;
            const big = !short && base > stock * THRESHOLD + 0.0001;

            const restEl = r.querySelector('.rest');
            restEl.textContent = fmt(Math.max(0, rest), r.dataset.unit);
            restEl.className = 'rest text-lg ' + (short ? 'text-red-600' : (rest <= 0 ? 'text-amber-600' : 'text-emerald-700'));
            r.querySelector('.short').classList.toggle('hidden', !short);
            r.querySelector('.big').classList.toggle('hidden', !big);
            r.querySelector('.changed').classList.toggle('hidden', (parseFloat(input.value) || 0) === (parseFloat(input.dataset.initial) || 0));
            r.classList.toggle('border-red-300', short);
            r.classList.toggle('border-amber-300', big);
            // Sortie préparée : bloquée si le magasin n'a pas assez. Demande : le magasinier vérifiera.
            bad = bad || (short && r.dataset.strict === '1');
            any = any || base > 0;
        });
        document.getElementById('submitBtn').disabled = bad || !any;
    }
    rows.forEach(r => r.querySelector('.disp').addEventListener('input', refresh));
    document.getElementById('approveForm').addEventListener('submit', refresh);
    refresh();
})();
</script>
@endpush
