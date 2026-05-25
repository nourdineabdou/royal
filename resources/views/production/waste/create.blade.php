@extends('layouts.production')

@section('title', 'Nouvelle sortie de stock (perte)')
@section('page_title', 'Sortie de stock pour perte/gaspillage')
@section('page_subtitle', 'Déclarer un produit périmé, gâté ou cassé')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-xl shadow p-8">
    <h2 class="text-2xl font-bold mb-6">Nouvelle sortie de stock</h2>
    <form method="POST" action="{{ route('production.waste.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Produit</label>
            <select name="product_id" id="product_id" class="w-full border rounded px-3 py-2 select2" required>
                <option value="">-- Sélectionner --</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
            @error('product_id')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </div>

        <div id="product-info" class="mb-4" style="display:none">
            <div class="p-3 bg-gray-100 rounded">
                <div id="packaging-select-block" style="display:none">
                    <label class="block text-gray-700 font-semibold mb-2">Emballage</label>
                    <select name="packaging_id" id="packaging_id" class="w-full border rounded px-3 py-2">
                        <!-- Options dynamiques -->
                    </select>
                </div>
                <div id="packaging-static-block" style="display:none">
                    <strong>Emballage :</strong> <span id="packaging-name">-</span>
                </div>
                <div><strong>Unité de base :</strong> <span id="unit-name">-</span></div>
                <div><strong>Quantité disponible :</strong> <span id="stock-quantity">-</span></div>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Stock</label>
            <select name="stock_id" id="stock_id" class="w-full border rounded px-3 py-2" required>
                <option value="">-- Sélectionner --</option>
                @foreach($stocks as $stock)
                    <option value="{{ $stock->id }}">{{ $stock->name }}</option>
                @endforeach
            </select>
            @error('stock_id')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Quantité à retirer</label>
            <input type="number" name="quantity" min="1" step="any" class="w-full border rounded px-3 py-2" required>
            @error('quantity')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Raison</label>
            <input type="text" name="reason" class="w-full border rounded px-3 py-2" required placeholder="Ex: Périmé, cassé, gâté...">
            @error('reason')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </div>
        <div class="flex justify-end">
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-semibold shadow">Valider la sortie</button>
        </div>
    </form>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser Select2 sur le champ produit
    $('.select2').select2({
        width: '100%',
        placeholder: '-- Sélectionner --',
        allowClear: true
    });
    function updateProductInfo() {
        const productId = document.getElementById('product_id').value;
        const stockId = document.getElementById('stock_id').value;
        if (!productId || !stockId) {
            document.getElementById('product-info').style.display = 'none';
            return;
        }
        fetch(`/api/production/product-info?product_id=${productId}&stock_id=${stockId}`)
            .then(res => res.json())
            .then(data => {
                // Gestion emballages multiples
                if (data.packagings && data.packagings.length > 1) {
                    const select = document.getElementById('packaging_id');
                    select.innerHTML = '';
                    data.packagings.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = p.name + (p.is_bulk ? ' (Vrac)' : '');
                        select.appendChild(opt);
                    });
                    document.getElementById('packaging-select-block').style.display = '';
                    document.getElementById('packaging-static-block').style.display = 'none';
                } else {
                    document.getElementById('packaging-select-block').style.display = 'none';
                    document.getElementById('packaging-static-block').style.display = '';
                    document.getElementById('packaging-name').textContent = data.packaging || '-';
                }
                document.getElementById('unit-name').textContent = data.unit || '-';
                document.getElementById('stock-quantity').textContent = data.quantity ?? '-';
                document.getElementById('product-info').style.display = '';
            })
            .catch(() => {
                document.getElementById('product-info').style.display = 'none';
            });
    }
    document.getElementById('product_id').addEventListener('change', updateProductInfo);
    document.getElementById('stock_id').addEventListener('change', updateProductInfo);
});
</script>
</div>
@endsection
