@extends('layouts.stock')

@section('title', 'Transfert de stock')
@section('header', 'Transfert entre stocks')

@section('content')
<div class="py-4">
    <div class="max-w-xl mx-auto">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center text-teal-600">
                    <i class="fa-solid fa-right-left text-lg"></i>
                </div>
                <div>
                    <h2 class="font-bold text-slate-800">Transfert de stock</h2>
                    <p class="text-xs text-slate-500">Déplacer des produits d'un stock vers un autre</p>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-5">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('stock.storeTransfer') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Stock source <span class="text-red-500">*</span></label>
                    <select name="source_stock_id" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
                        <option value="">Sélectionner le stock source…</option>
                        @foreach($stocks as $s)
                            <option value="{{ $s->id }}" {{ old('source_stock_id', request('source_stock_id')) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                                @if($s->module) ({{ \App\Models\Stock::MODULES[$s->module] ?? $s->module }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-center text-slate-400">
                    <i class="fa-solid fa-arrow-down text-2xl"></i>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Stock destination <span class="text-red-500">*</span></label>
                    <select name="destination_stock_id" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
                        <option value="">Sélectionner le stock destination…</option>
                        @foreach($stocks as $s)
                            <option value="{{ $s->id }}" {{ old('destination_stock_id', request('destination_stock_id')) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                                @if($s->module) ({{ \App\Models\Stock::MODULES[$s->module] ?? $s->module }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Produit <span class="text-red-500">*</span></label>
                    <select name="product_id" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
                        <option value="">Sélectionner un produit…</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id', request('product_id')) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Quantité <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" step="0.01" min="0.001"
                           value="{{ old('quantity', request('quantity')) }}" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300"
                           placeholder="Ex. 10.5">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Notes (optionnel)</label>
                    <textarea name="notes" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300"
                              placeholder="Raison du transfert…">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('stock.index') }}"
                       class="flex-1 text-center border border-slate-200 text-sm py-2.5 rounded-lg hover:bg-slate-50 font-medium">
                        Annuler
                    </a>
                    <button type="submit"
                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm py-2.5 rounded-lg font-semibold">
                        <i class="fa-solid fa-right-left mr-2"></i>Transférer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
