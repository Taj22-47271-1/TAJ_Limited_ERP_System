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



    <div class="span-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin: 10px 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div>
                <strong style="font-size: 14px; color: #0f172a;">🏷️ Pricing & Promotional Discounts</strong>
                <p style="margin: 2px 0 0; font-size: 12px; color: #64748b;">Set regular selling price and optional discount. Entering percentage or sale price automatically computes the other.</p>
            </div>
            <button type="button" id="clearDiscountBtn" class="btn ghost" style="font-size: 11px; padding: 4px 10px; height: auto;">Clear Discount</button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <label style="margin: 0;">Regular Selling Price (Original)
                <input type="number" step="0.01" min="0" name="selling_price" id="sellingPriceInput" value="{{ old('selling_price',$product->selling_price ?: 0) }}" required placeholder="e.g. 2500.00">
            </label>

            <label style="margin: 0;">Discount % (e.g. 20%)
                <input type="number" step="0.1" min="0" max="100" name="discount_percent" id="discountPercentInput" value="{{ old('discount_percent', $product->discount_percent) }}" placeholder="e.g. 20">
                <small style="color: #64748b; font-size: 11px;">Badge percentage (e.g. 20% OFF).</small>
            </label>

            <label style="margin: 0;">Discounted Sale Price
                <input type="number" step="0.01" min="0" name="discount_price" id="discountPriceInput" value="{{ old('discount_price', $product->discount_price) }}" placeholder="e.g. 1999.00">
                <small style="color: #64748b; font-size: 11px;">Actual price customer pays at checkout.</small>
            </label>
        </div>

        <!-- Live Calculation Preview Card -->
        <div id="pricingPreviewBox" style="margin-top: 14px; padding: 12px 16px; border-radius: 8px; background: #ffffff; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Customer Sees:</span>
                <del id="previewOrigPrice" style="color: #94a3b8; font-size: 13px; text-decoration: line-through;">{{ $companySettings->currency_symbol }}0.00</del>
                <strong id="previewFinalPrice" style="color: #0f172a; font-size: 16px; font-weight: 800;">{{ $companySettings->currency_symbol }}0.00</strong>
                <span id="previewDiscountBadge" style="background: #fee2e2; color: #dc2626; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 999px; display: none;">-0% OFF</span>
            </div>
            <div id="previewSavingsText" style="font-size: 12px; color: #059669; font-weight: 600;"></div>
        </div>
    </div>



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

