@extends('layouts.production')

@section('title', 'Recette — ' . $meal->name)
@section('page_title', 'Recette de « ' . $meal->name . ' »')
@section('page_subtitle', 'Produits et quantités nécessaires pour préparer ce plat')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <form action="{{ route('meals.recipe.update', $meal) }}" method="POST">
            @csrf
            @method('PUT')

            <div id="recipe-rows" class="space-y-3 mb-4">
                @forelse($meal->recipe?->items ?? [] as $item)
                    <div class="recipe-row flex items-center gap-3">
                        <select name="product_id[]" required class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} @if($product->unit) ({{ $product->unit->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <input type="number" step="0.001" min="0.001" name="quantity[]" value="{{ $item->quantity }}" required
                               placeholder="Quantité" class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="this.closest('.recipe-row').remove()" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @empty
                @endforelse
            </div>

            <button type="button" id="add-row" class="mb-6 text-sm font-semibold text-blue-600 hover:text-blue-800">
                <i class="fas fa-plus mr-1"></i> Ajouter un ingrédient
            </button>

            <div class="flex gap-4 pt-4 border-t border-gray-200">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i>Enregistrer la recette
                </button>
                <a href="{{ route('meals.show', $meal) }}" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded-lg transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<template id="recipe-row-template">
    <div class="recipe-row flex items-center gap-3">
        <select name="product_id[]" required class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }} @if($product->unit) ({{ $product->unit->name }}) @endif</option>
            @endforeach
        </select>
        <input type="number" step="0.001" min="0.001" name="quantity[]" required
               placeholder="Quantité" class="w-40 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        <button type="button" onclick="this.closest('.recipe-row').remove()" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</template>

<script>
document.getElementById('add-row').addEventListener('click', function () {
    const tpl = document.getElementById('recipe-row-template').content.cloneNode(true);
    document.getElementById('recipe-rows').appendChild(tpl);
});
</script>
@endsection
