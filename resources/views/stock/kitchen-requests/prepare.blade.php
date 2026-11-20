@extends('layouts.stock')

@section('title', 'Préparer ' . $kr->reference)
@section('header', 'Préparer la demande ' . $kr->reference)

@section('content')
@php use App\Support\Qty; @endphp
<form method="POST" action="{{ route('stock.kitchen-requests.prepare.store', $kr) }}" id="prepForm" class="py-4 max-w-3xl mx-auto space-y-4 pb-32">
    @csrf

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 space-y-3">
        <div>
            <p class="text-lg font-bold text-slate-800">{{ $kr->kitchen }}</p>
            <p class="text-sm text-slate-500">
                {{ $kr->reference }} · demandé par {{ $kr->requester->name ?? '—' }} le {{ $kr->created_at->format('d/m H:i') }}{{ $kr->needed_on ? ' · pour le ' . $kr->needed_on->format('d/m') : '' }}
            </p>
            @if($kr->notes)<p class="text-sm text-slate-600 mt-1">« {{ $kr->notes }} »</p>@endif
            @if($kr->contest_note)
                <p class="text-sm bg-red-50 text-red-700 rounded-lg px-3 py-2 mt-2"><i class="fa-solid fa-rotate-left mr-1"></i>Renvoyée par la cuisine : {{ $kr->contest_note }}</p>
            @endif
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Magasin d'où sortent les produits</label>
            <select name="stock_id" id="stockSelect" style="width:100%">
                @foreach($stocks as $s)
                    <option value="{{ $s->id }}" @selected($s->id === $defaultStock)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="receiver_id" class="block text-xs font-semibold text-slate-500 mb-1">Cuisinier qui reçoit <span class="text-red-500">*</span></label>
            <select id="receiver_id" name="receiver_id" required
                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-base sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <option value="">— Choisir le cuisinier —</option>
                @foreach($cooks as $cook)<option value="{{ $cook->id }}" @selected(old('receiver_id', $defaultReceiver) == $cook->id)>{{ $cook->name }}</option>@endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">C'est lui qui se connecte pour confirmer, et qui signe le bon de sortie.</p>
        </div>
    </div>

    <div class="flex items-center justify-between px-1">
        <h2 class="font-semibold text-slate-700 text-sm">Quantités remises</h2>
        <button type="button" id="fillBtn" class="text-xs font-semibold px-3 py-2 rounded-lg border border-emerald-300 text-emerald-700 bg-emerald-50">
            <i class="fa-solid fa-check-double mr-1"></i>Tout comme demandé
        </button>
    </div>

    <div class="space-y-2">
        @foreach($kr->items as $item)
            @php
                $u = $item->product->unit->symbol ?? '';
                $du = Qty::displayUnit($u);
                $prev = $item->delivered_quantity ?? $item->requested_quantity;
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col sm:flex-row sm:items-center gap-3 prep-row"
                 data-item="{{ $item->id }}" data-pid="{{ $item->product_id }}" data-factor="{{ $du['factor'] }}" data-unit="{{ $u }}" data-requested="{{ (float) $item->requested_quantity }}">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-800">{{ $item->product->name ?? '—' }}</p>
                    <p class="text-xs text-slate-500">
                        Demandé : <strong>{{ Qty::format($item->requested_quantity, $u) }}</strong>
                        · Disponible : <strong class="avail">—</strong>
                    </p>
                    <p class="text-xs text-red-600 font-semibold short hidden"><i class="fa-solid fa-triangle-exclamation"></i> Pas assez dans ce magasin</p>
                </div>
                <div class="flex items-center border-2 border-emerald-300 rounded-xl overflow-hidden shrink-0 focus-within:ring-2 focus-within:ring-emerald-400">
                    <input type="number" inputmode="decimal" min="0" step="any" class="disp w-24 text-right font-bold text-lg py-2 px-2 focus:outline-none" style="font-size:18px"
                           value="{{ round($prev / $du['factor'], 3) }}">
                    <span class="px-3 text-sm font-semibold text-slate-500 bg-emerald-50 self-stretch flex items-center">{{ $du['label'] }}</span>
                </div>
                <input type="hidden" name="delivered[{{ $item->id }}]" class="base">
            </div>
        @endforeach
    </div>

    <div class="fixed bottom-0 left-0 right-0 md:left-64 z-30 bg-white border-t border-slate-200 p-3 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]">
        <div class="max-w-3xl mx-auto flex items-center gap-3">
            <p class="flex-1 text-xs text-slate-500">Après remise, la cuisine doit valider : le stock diminuera à ce moment-là.</p>
            <button id="submitBtn" class="px-5 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white text-sm font-bold">
                <i class="fa-solid fa-hand-holding mr-1"></i> Remettre à la cuisine
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const AVAIL = @json($available, JSON_FORCE_OBJECT);
    const rows = [...document.querySelectorAll('.prep-row')];
    const nf = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 });
    const fmt = (q, u) => {
        const f = ({ g: ['kg', 1000], ml: ['L', 1000] })[(u || '').toLowerCase()];
        return f && Math.abs(q) >= f[1] ? nf.format(q / f[1]) + ' ' + f[0] : nf.format(q) + ' ' + u;
    };
    const stockId = () => document.getElementById('stockSelect').value;

    function refresh() {
        let bad = false;
        rows.forEach(r => {
            const avail = ((AVAIL[stockId()] || {})[r.dataset.pid]) || 0;
            const base = Math.round((parseFloat(r.querySelector('.disp').value) || 0) * (+r.dataset.factor) * 100) / 100;
            r.querySelector('.base').value = base;
            r.querySelector('.avail').textContent = fmt(avail, r.dataset.unit);
            const short = base > avail + 0.0001;
            r.querySelector('.short').classList.toggle('hidden', !short);
            r.classList.toggle('border-red-300', short);
            bad = bad || short;
        });
        document.getElementById('submitBtn').disabled = bad;
    }
    rows.forEach(r => r.querySelector('.disp').addEventListener('input', refresh));
    $('#stockSelect').on('change', refresh);
    document.getElementById('fillBtn').addEventListener('click', () => {
        rows.forEach(r => { r.querySelector('.disp').value = Math.round(r.dataset.requested / r.dataset.factor * 1000) / 1000; });
        refresh();
    });
    document.getElementById('prepForm').addEventListener('submit', () => { refresh(); });
    refresh();
})();
</script>
@endpush
