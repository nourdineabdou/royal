@extends('layouts.purchases')

@section('title', 'Modifier un Produit')

@section('content')
@include('parameters.products._form', [
    'product'     => $product,
    'action'      => route('purchases.products.update', $product),
    'method'      => 'PUT',
    'title'       => 'Modifier : ' . $product->name,
    'submitLabel' => 'Enregistrer',
])
@endsection
