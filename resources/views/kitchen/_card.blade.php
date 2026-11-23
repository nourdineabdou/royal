{{--
    Carte d'une demande de sortie, lisible sur téléphone.
    $kr : la demande · $highlight : true pour « à confirmer » (bordure et bouton) · $compact : historique
--}}
@php
    use App\Support\Qty;
    $highlight = $highlight ?? false;
    $compact = $compact ?? false;
    $badge = [
        'pending'    => 'bg-sky-100 text-sky-800',
        'to_approve' => 'bg-violet-100 text-violet-800',
        'prepared'   => 'bg-amber-100 text-amber-900',
        'validated'  => 'bg-emerald-100 text-emerald-800',
        'cancelled'  => 'bg-slate-200 text-slate-600',
    ];
    $prepared = $kr->isPrepared() && in_array($kr->status, ['to_approve', 'prepared', 'validated']);
    $explain = match ($kr->status) {
        'to_approve' => $kr->isPrepared()
            ? ['fa-stamp', 'text-violet-700', 'Préparée par ' . ($kr->preparer->name ?? 'le magasinier') . ' : en attente de l\'approbation du responsable des achats (quantité importante).']
            : ['fa-stamp', 'text-violet-700', 'Quantité importante : en attente de l\'approbation du responsable des achats, avant d\'aller au magasin.'],
        'pending' => $kr->contest_note
            ? ['fa-rotate-left', 'text-red-700', 'Renvoyée au magasinier — ' . $kr->contest_note]
            : ['fa-warehouse', 'text-sky-700', $kr->approved_at
                ? 'Approuvée par ' . ($kr->approver->name ?? 'le responsable des achats') . ' : chez le magasinier, en préparation.'
                : 'Chez le magasinier, en préparation.'],
        'prepared' => ['fa-hand-holding', 'text-amber-800', 'Remise par ' . ($kr->preparer->name ?? 'le magasinier')
            . ($kr->receiver ? ' à ' . $kr->receiver->name : '') . ' le ' . $kr->prepared_at?->format('d/m à H:i') . '.'],
        'validated' => ['fa-circle-check', 'text-emerald-700', 'Reçue par ' . ($kr->validator->name ?? '—') . ' le '
            . $kr->validated_at?->format('d/m à H:i') . ($kr->bs_number ? ' · ' . $kr->bs_number : '') . '.'],
        'cancelled' => ['fa-ban', 'text-slate-600', $kr->contest_note ?: 'Annulée.'],
        default => null,
    };
@endphp

<a href="{{ route('kitchen.requests.show', $kr) }}"
   class="block bg-white rounded-2xl shadow-sm active:bg-stone-50 {{ $highlight ? 'border-2 border-amber-400' : 'border border-slate-200' }}">
    <div class="p-4 space-y-2">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="font-bold text-slate-900 text-base leading-snug break-words">{{ $kr->kitchen }}</p>
                <p class="text-sm text-slate-500">{{ $kr->reference }} · {{ $kr->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <span class="shrink-0 text-xs font-bold px-2.5 py-1 rounded-full text-center leading-tight max-w-[45%] {{ $badge[$kr->status] ?? '' }}">{{ $kr->status_label }}</span>
        </div>

        @if($explain)
            <p class="text-sm {{ $explain[1] }} leading-snug"><i class="fa-solid {{ $explain[0] }} mr-1"></i>{{ $explain[2] }}</p>
        @endif
    </div>

    @unless($compact)
        {{-- Produits de la demande : nom à gauche, quantité bien visible à droite --}}
        <div class="border-t border-slate-100">
            <div class="flex px-4 pt-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                <span class="flex-1">Produit</span>
                <span class="w-24 text-right">Demandé</span>
                @if($prepared)<span class="w-24 text-right">Remis</span>@endif
            </div>
            <ul class="divide-y divide-slate-100">
                @foreach($kr->items as $item)
                    @php
                        $u = $item->product->unit->symbol ?? '';
                        $diff = $prepared && abs((float) $item->delivered_quantity - (float) $item->requested_quantity) > 0.001;
                    @endphp
                    <li class="flex items-center px-4 py-2.5 gap-2">
                        <span class="flex-1 text-base text-slate-800 leading-snug break-words">{{ $item->product->name ?? '—' }}</span>
                        <span class="w-24 text-right text-base {{ $prepared ? 'text-slate-500' : 'font-bold text-slate-900' }}">{{ Qty::format($item->requested_quantity, $u) }}</span>
                        @if($prepared)
                            <span class="w-24 text-right text-base font-bold {{ $diff ? 'text-amber-700' : 'text-slate-900' }}">{{ Qty::format($item->delivered_quantity ?? 0, $u) }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <p class="px-4 pb-3 -mt-1 text-sm text-slate-600 leading-snug">{{ $kr->summary(' · ') }}</p>
    @endunless

    @if($highlight)
        <div class="px-4 pb-4 pt-1">
            <span class="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl bg-emerald-600 text-white font-bold text-base">
                <i class="fa-solid fa-check"></i> Vérifier et confirmer la réception
            </span>
        </div>
    @endif
</a>
