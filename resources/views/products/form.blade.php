@extends('layouts.app')
@section('title',$product->exists?'Edit Product':'Add Product')
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow dark">PRODUCT MANAGEMENT</span>
        <h1>{{ $product->exists ? 'Edit Product' : 'Add Product' }}</h1>
        <p>{{ auth()->user()->hasPermission('inventory.manage') ? 'Manage product identity, cost, image, size options and stock controls.' : 'Add or edit product details, image and customer-facing size options. Stock adjustment is restricted to Admin.' }}</p>
    </div>
    <a class="btn ghost" href="{{ route('products.index') }}">Back</a>
</div>

<div class="card">
<form class="form-grid" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('products.update',$product) : route('products.store') }}">
    @csrf
    @if($product->exists) @method('PUT') @endif

    <label class="span-2">Product name
        <input name="name" value="{{ old('name',$product->name) }}" required>
    </label>

    <label>Category
        <select name="category_id">
            <option value="">No category</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('category_id',$product->category_id)==$c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </label>

    <label>Unit
        <input name="unit" value="{{ old('unit',$product->unit ?: 'pcs') }}" required>
    </label>

    <label>SKU
        <input name="sku" value="{{ old('sku',$product->sku) }}" required>
    </label>

    <label>Barcode
        <input name="barcode" value="{{ old('barcode',$product->barcode) }}">
    </label>

    @if(auth()->user()->hasPermission('inventory.manage'))
        <label>Purchase price
            <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price',$product->purchase_price ?: 0) }}" required>
        </label>
    @endif

    <label>Selling price
        <input type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price',$product->selling_price ?: 0) }}" required>
    </label>

    @if(!$product->exists && auth()->user()->hasPermission('inventory.manage'))
        <label>Opening stock
            <input type="number" step="0.01" min="0" name="opening_stock" value="{{ old('opening_stock',0) }}">
        </label>
    @endif

    <label>Minimum stock
        <input type="number" step="0.01" min="0" name="min_stock" value="{{ old('min_stock',$product->min_stock ?: 5) }}" required>
    </label>

    <label class="span-2">Product image <small>JPG, PNG or WebP · max 2MB</small>
        <input type="file" name="image" accept="image/png,image/jpeg,image/webp">
    </label>

    @if($product->image_url)
        <div class="span-2 current-image">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
            <span>Current image</span>
        </div>
    @endif

    @php
        $sizeText = old('sizes_text', implode(', ', $product->normalizedSizes()));
        $hasSizes = old('has_sizes', $product->exists ? $product->has_sizes : false);
    @endphp

    <div class="span-2 product-size-config">
        <input type="hidden" name="has_sizes" value="0">
        <label class="check product-size-toggle">
            <input id="hasSizesToggle" type="checkbox" name="has_sizes" value="1" @checked($hasSizes)>
            Enable size selection for this product
        </label>
        <div id="sizeOptionsPanel" class="size-options-panel" @if(!$hasSizes) hidden @endif>
            <label>Available sizes
                <input id="sizesText" name="sizes_text" value="{{ $sizeText }}" placeholder="S, M, L, XL">
                <small>Use comma-separated sizes. Example: XS, S, M, L, XL, XXL. This is ideal for clothing, shoes or other products that require a size choice.</small>
            </label>
            <div class="size-preset-row" aria-label="Quick size presets">
                @foreach(['XS','S','M','L','XL','XXL','3XL'] as $preset)
                    <button type="button" class="size-preset" data-size="{{ $preset }}">{{ $preset }}</button>
                @endforeach
            </div>
        </div>
    </div>

    <label class="span-2">Description
        <textarea name="description" rows="4">{{ old('description',$product->description) }}</textarea>
    </label>

    <label class="check span-2">
        <input type="checkbox" name="status" value="1" @checked(old('status',$product->exists?$product->status:true))>
        Product is active and visible
    </label>

    <div class="span-2 form-note">
        @if($product->exists)
            <strong>Current physical stock:</strong> {{ number_format($product->stock,2) }} ·
            <strong>Reserved:</strong> {{ number_format($product->reserved_stock,2) }} ·
            <strong>Available:</strong> {{ number_format($product->available_stock,2) }}
            @if($product->has_sizes) · <strong>Sizes:</strong> {{ implode(', ', $product->normalizedSizes()) }} @endif
            @if(auth()->user()->hasPermission('inventory.manage')) · Use Stock Management to change quantities. @endif
        @else
            Cashier-created products start with zero stock. Admin can add stock from Stock Management. Enable sizes only when the customer must choose a size before buying.
        @endif
    </div>

    <div class="span-2">
        <button class="btn primary">{{ $product->exists ? 'Save Changes' : 'Create Product' }}</button>
    </div>
</form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const toggle = document.getElementById('hasSizesToggle');
    const panel = document.getElementById('sizeOptionsPanel');
    const input = document.getElementById('sizesText');
    if (!toggle || !panel || !input) return;

    const syncPanel = () => { panel.hidden = !toggle.checked; };
    toggle.addEventListener('change', syncPanel);
    syncPanel();

    document.querySelectorAll('.size-preset').forEach(button => {
        button.addEventListener('click', () => {
            const values = input.value.split(',').map(v => v.trim()).filter(Boolean);
            const exists = values.some(v => v.toLowerCase() === button.dataset.size.toLowerCase());
            if (!exists) values.push(button.dataset.size);
            input.value = values.join(', ');
            input.focus();
        });
    });
})();
</script>
@endpush
