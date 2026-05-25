@extends('layouts.accounting')

@section('title', 'Caisse #' . $register->id . ' — Comptabilité')
@section('accounting_content')
    {{-- Copié et adapté depuis pos/accounting-detail.blade.php --}}
    @include('modules.accounting.partials.detail-content', ['register' => $register, 'payments' => $payments])
@endsection
