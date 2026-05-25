@php $typeIndex = 0; @endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- Session info -->
    <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-2 border border-gray-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="rounded-full p-3 bg-gradient-to-br from-indigo-500 to-indigo-700 text-white text-xl">
                <i class="fas fa-cash-register"></i>
            </div>
            <div class="font-bold text-lg text-gray-800">Informations de la session</div>
        </div>
        <div class="flex flex-col gap-2">
            <div class="flex justify-between items-center">
                <span class="text-gray-500 font-medium flex items-center gap-1"><i class="fas fa-user"></i> Caissier</span>
                <span class="text-gray-800">{{ $register->user?->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500 font-medium flex items-center gap-1"><i class="fas fa-clock"></i> Poste</span>
                <span class="text-gray-800">
                    @if($register->shift === 'morning')
                        <i class="fas fa-sun text-yellow-500"></i> Matin
                    @else
                        <i class="fas fa-moon text-purple-600"></i> Soir
                    @endif
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500 font-medium flex items-center gap-1"><i class="fas fa-layer-group"></i> Module</span>
                <span class="text-gray-800">{{ ucfirst($register->module ?? 'restaurant') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500 font-medium flex items-center gap-1"><i class="fas fa-door-open"></i> Ouverture</span>
                <span class="text-gray-800">{{ $register->opened_at?->format('d/m/Y à H:i') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500 font-medium flex items-center gap-1"><i class="fas fa-door-closed"></i> Fermeture</span>
                <span class="text-gray-800">{{ $register->closed_at?->format('d/m/Y à H:i') ?? 'Toujours ouverte' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500 font-medium flex items-center gap-1"><i class="fas fa-receipt"></i> Transactions</span>
                <span class="text-gray-800">{{ count($payments) }} paiement(s)</span>
            </div>
            @if($register->validated_at)
            <div class="flex justify-between items-center">
                <span class="text-gray-500 font-medium flex items-center gap-1"><i class="fas fa-user-check"></i> Validé par</span>
                <span class="text-gray-800">{{ $register->validator?->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500 font-medium flex items-center gap-1"><i class="fas fa-calendar-check"></i> Validé le</span>
                <span class="text-gray-800">{{ $register->validated_at?->format('d/m/Y à H:i') }}</span>
            </div>
            @endif
        </div>
    </div>
    <!-- Encaissements par mode de paiement -->
    <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-2 border border-gray-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="rounded-full p-3 bg-gradient-to-br from-green-400 to-green-700 text-white text-xl">
                <i class="fas fa-coins"></i>
            </div>
            <div class="font-bold text-lg text-gray-800">Encaissements par mode de paiement</div>
        </div>
        <div class="flex flex-col gap-2">
            @forelse($byType as $type)
            @php $color = $typeColors[$typeIndex % count($typeColors)]; $typeIndex++; @endphp
            <div class="flex justify-between items-center py-2 px-2 rounded hover:bg-gray-50">
                <div class="flex items-center gap-3">
                    <span class="inline-block w-3 h-3 rounded-full" style="background:{{ $color }};"></span>
                    <div>
                        <div class="font-semibold text-sm">{{ $type['name'] }}</div>
                        <div class="text-xs text-gray-400">{{ $type['count'] }} transaction(s)</div>
                    </div>
                </div>
                <div class="font-bold text-base" style="color:{{ $color }};">
                    {{ number_format($type['total'], 2, ',', ' ') }} MRU
                </div>
            </div>
            @empty
            <div class="text-center text-gray-400 py-6 italic">
                Aucun paiement enregistré pour cette session
            </div>
            @endforelse
            @if(count($byType) > 0)
            <div class="border-t-2 border-gray-200 mt-4 pt-4 flex justify-between items-center text-lg font-bold text-gray-700">
                <span>TOTAL ENCAISSÉ</span>
                <span class="text-indigo-600">{{ number_format($systemTotal, 2, ',', ' ') }} MRU</span>
            </div>
            @endif
        </div>
    </div>
</div>
