@extends('layouts.accounting')

@section('title', 'Comptabilité Caisse — Complex Royal')
@section('accounting_content')
    {{-- Copié et adapté depuis pos/accounting.blade.php --}}
    @include('modules.accounting.partials.index-content', get_defined_vars())
@endsection
