{{-- Détail session caisse - adapté depuis pos/accounting-detail.blade.php --}}
@extends('layouts.accounting')

@section('accounting_content')
@php
	$statusMap = [
		'open'      => ['class' => 'banner-open',      'icon' => 'fa-circle',            'label' => 'Caisse ouverte'],
		'closed'    => ['class' => 'banner-closed',    'icon' => 'fa-lock',              'label' => 'En attente de validation'],
		'validated' => ['class' => 'banner-validated', 'icon' => 'fa-check-circle',      'label' => 'Validée par la comptabilité'],
		'flagged'   => ['class' => 'banner-flagged',   'icon' => 'fa-exclamation-circle','label' => 'Problème signalé'],
	];
	$st = $statusMap[$register->status ?? 'open'];
	$typeColors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16'];
	$typeIndex  = 0;
@endphp
<div class="detail-content">
   <!-- Breadcrumb -->
   <div class="breadcrumb flex items-center gap-2 mb-4">
	   <a href="{{ route('modules.accounting') }}" class="text-indigo-600 hover:underline flex items-center gap-1">
		   <i class="fas fa-home"></i> Dashboard
	   </a>
	   <span class="text-gray-400">›</span>
	   <span class="text-gray-700 font-semibold">Session caisse <span class="text-indigo-600">#{{ $register->id }}</span></span>
	   <span class="status-banner {{ $st['class'] }} ml-4 px-3 py-1 rounded-full text-xs font-bold" style="background:#f3f4f6;">
		   <i class="fas {{ $st['icon'] }} mr-1" style="font-size:10px;"></i>
		   {{ $st['label'] }}
	   </span>
	   @if(($register->status ?? 'open') === 'open')
	   <form method="POST" action="{{ route('accounting.close', $register->id) }}" class="ml-4 inline-block">
		   @csrf
		   <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow transition">
			   <i class="fas fa-door-closed mr-1"></i> Fermer la caisse
		   </button>
	   </form>
	   @elseif(($register->status ?? '') === 'closed')
	   <form method="POST" action="{{ route('accounting.validate', $register->id) }}" class="ml-4 inline-block">
		   @csrf
		   <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow transition">
			   <i class="fas fa-check-circle mr-1"></i> Valider la caisse
		   </button>
	   </form>
	   @endif
   </div>
   @include('modules.accounting.partials.detail-content-body', get_defined_vars())
</div>
@endsection
