@extends('layouts.residence')
@section('title', 'Calendrier des chambres')

@section('content')

<div class="mb-4 flex items-center justify-between flex-wrap gap-3">
    <h2 class="text-xl font-bold text-slate-800">Calendrier — {{ $date->translatedFormat('F Y') }}</h2>
    <div class="flex items-center gap-2">
        <a href="{{ route('residence.calendar', ['year' => $prev->year, 'month' => $prev->month]) }}"
           class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-sm hover:bg-slate-50 transition">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <a href="{{ route('residence.calendar') }}"
           class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-sm hover:bg-slate-50 transition font-medium">
            Aujourd'hui
        </a>
        <a href="{{ route('residence.calendar', ['year' => $next->year, 'month' => $next->month]) }}"
           class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-sm hover:bg-slate-50 transition">
            <i class="fa-solid fa-chevron-right"></i>
        </a>
    </div>
</div>

{{-- Legend --}}
<div class="flex items-center gap-4 mb-4 text-xs flex-wrap">
    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-violet-500 inline-block"></span> En séjour (checked_in)</span>
    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-yellow-400 inline-block"></span> Réservé (en attente)</span>
    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-emerald-200 inline-block"></span> Terminé</span>
    <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-slate-200 inline-block"></span> Disponible</span>
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
    <table class="min-w-full text-xs border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
                <th class="sticky left-0 bg-slate-50 px-4 py-3 text-left font-semibold text-slate-600 w-32 border-r border-slate-200">Chambre</th>
                @for($d = 1; $d <= $daysInMonth; $d++)
                @php $isToday = ($date->year == now()->year && $date->month == now()->month && $d == now()->day); @endphp
                <th class="px-1 py-3 text-center font-medium min-w-[2rem] {{ $isToday ? 'bg-violet-100 text-violet-700' : 'text-slate-500' }}">
                    {{ $d }}
                </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($rooms as $room)
            <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                <td class="sticky left-0 bg-white px-4 py-3 border-r border-slate-200">
                    <p class="font-semibold text-slate-800">{{ $room->number }}</p>
                    <p class="text-slate-400" style="font-size:0.65rem;">{{ $room->roomType->name ?? '' }}</p>
                </td>
                @for($d = 1; $d <= $daysInMonth; $d++)
                @php
                    $dayDate = \Carbon\Carbon::createFromDate($date->year, $date->month, $d);
                    $booking = $room->bookings->first(function ($b) use ($dayDate) {
                        return $dayDate->between($b->check_in, $b->check_out->subDay());
                    });
                    if ($booking) {
                        $cellClass = match($booking->status) {
                            'checked_in'  => 'bg-violet-500',
                            'pending'     => 'bg-yellow-400',
                            'checked_out' => 'bg-emerald-200',
                            default       => 'bg-slate-200',
                        };
                        $isStart = $dayDate->isSameDay($booking->check_in);
                        $isEnd   = $dayDate->isSameDay($booking->check_out->subDay());
                    } else {
                        $cellClass = '';
                    }
                    $isToday = ($date->year == now()->year && $date->month == now()->month && $d == now()->day);
                @endphp
                <td class="px-0.5 py-1 {{ $isToday ? 'bg-violet-50' : '' }}">
                    @if($booking)
                    <a href="{{ route('residence.bookings.show', $booking) }}" title="{{ $booking->customer_name }}">
                        <div class="h-7 {{ $cellClass }} {{ $isStart ? 'rounded-l-md ml-0.5' : '' }} {{ $isEnd ? 'rounded-r-md mr-0.5' : '' }} opacity-90 hover:opacity-100 transition flex items-center justify-center">
                            @if($isStart)
                            <span class="text-white font-medium truncate px-1" style="font-size:0.6rem;">{{ Str::limit($booking->customer_name, 8) }}</span>
                            @endif
                        </div>
                    </a>
                    @else
                    <div class="h-7 rounded mx-0.5 bg-slate-100/50"></div>
                    @endif
                </td>
                @endfor
            </tr>
            @empty
            <tr>
                <td colspan="{{ $daysInMonth + 1 }}" class="text-center py-12 text-slate-400">
                    Aucune chambre enregistrée
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
