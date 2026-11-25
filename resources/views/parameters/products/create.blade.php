@extends('layouts.purchases')

@section('title', 'Ajouter un Produit')

@section('content')
@include('parameters.products._form', [
    'product'     => null,
    'action'      => route('purchases.products.store'),
    'method'      => 'POST',
    'title'       => 'Nouveau produit',
    'submitLabel' => 'Enregistrer',
])
@endsection
