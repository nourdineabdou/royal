@extends('layouts.accounting')

@section('title', 'Traces Comptables Globales — Complex Royal')
@section('accounting_content')
    {{-- Copié et adapté depuis pos/accounting-traces.blade.php --}}
    @include('modules.accounting.partials.traces-content', get_defined_vars())
@endsection
