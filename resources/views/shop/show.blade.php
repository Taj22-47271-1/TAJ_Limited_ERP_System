@extends('layouts.customer')
@section('title', $product->name)
@section('content')
@php
    $isLowStock = $product->available_stock <= $product->min_stock;
    $sizes = $product->normalizedSizes();
    $colorways = $product->available_colorways;
    $initialColorway = $colorways[0] ?? null;
    $initialImage = $initialColorway['image_url'] ?? $product->image_url;
    $initialSku = $initialColorway['sku'] ?? $product->sku;
    $isClothing = $product->has_sizes || ($product->category && stripos($product->category->name, 'cloth') !== false);
@endphp

@if($isClothing)
    @include('shop.partials.clothing-showcase', ['clothingProducts' => collect([$product])->merge($relatedClothing ?? [])])
@endif

<div class="fashion-product-detail">
    <section class="fashion-product-media">
        @if($product->image_url)
            <button type="button" class="product-zoom-stage" data-product-zoom data-open-product-image aria-label="Open large product image">
                <img src="{{ $initialImage }}" alt="{{ $product->name }}" id="productMainImg" loading="eager" fetchpriority="high">
                <span class="zoom-hint" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="m21 21-4.35-4.35m2.35-5.15A7.5 7.5 0 1 1 4 11.5a7.5 7.5 0 0 1 15 0Zm-10.5-3v7m-3.5-3.5h7" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    Hover to zoom
                </span>
            </button>
        @else
            <div class="product-zoom-stage product-zoom-placeholder"><span class="vc-product-letter vc-product-letter-large">{{ strtoupper(substr($product->name,0,1)) }}</span></div>
        @endif
    </section>

    <aside class="fashion-product-info">
        <a class="fashion-back" href="{{ route('shop.index') }}">← Back to shop</a>
        <span class="fashion-category">{{ $product->category?->name ?? 'TAJ LIMITED' }}</span>
        <h1>{{ $product->name }}</h1>

        <!-- Discount & Pricing Block -->
        <div class="fashion-price-block">
            @if($product->has_discount)
                <div class="fashion-price-row">
                    <del class="fashion-orig-price">{{ $companySettings->currency_symbol }}{{ number_format($product->selling_price, 2) }}</del>
                    <span class="fashion-price-highlight">{{ $companySettings->currency_symbol }}{{ number_format($product->effective_price, 2) }}</span>
                    <span class="fashion-discount-pill">-{{ $product->discount_percent_calculated }}% OFF</span>
                </div>
                <div class="fashion-savings-note">
                    Festive Special: Save {{ $companySettings->currency_symbol }}{{ number_format($product->selling_price - $product->effective_price, 2) }} today!
                </div>
            @else
                <div class="fashion-price">{{ $companySettings->currency_symbol }}{{ number_format($product->selling_price, 2) }}</div>
            @endif
        </div>

        <div class="fashion-stock {{ $isLowStock ? 'low' : 'available' }}" id="productStockDisplay">
            <span></span>
            {{ $isLowStock ? 'LOW STOCK' : 'IN STOCK' }} · {{ number_format($product->available_stock,0) }} {{ $product->unit }} available
        </div>

        <form class="fashion-buy-form" method="POST" action="{{ route('cart.add', $product) }}" id="productBuyForm">
            @csrf

            <!-- SELECT COLORWAY -->
            @if(!empty($colorways) && count($colorways) > 0)
                <div class="fashion-colorway-section">
                    <div class="fashion-colorway-header">
                        <span class="fashion-colorway-label">SELECT COLORWAY:</span>
                        <strong class="fashion-colorway-name" id="currentColorwayLabel">{{ $initialColorway['name'] }}</strong>
                        <span class="fashion-colorway-tone" id="currentColorwayTag">@if(!empty($initialColorway['tag']))({{ $initialColorway['tag'] }})@endif</span>
                    </div>
                    <div class="fashion-colorway-palette" role="radiogroup" aria-label="Available Colorways">
                        @foreach($colorways as $cwIdx => $cw)
                            <button type="button" 
                                    class="colorway-palette-btn {{ $cwIdx === 0 ? 'active' : '' }}" 
                                    data-key="{{ $cw['key'] }}"
                                    data-name="{{ $cw['name'] }}"
                                    data-color="{{ $cw['color'] }}"
                                    data-sku="{{ $cw['sku'] }}"
                                    data-image="{{ $cw['image_url'] }}"
                                    data-stock="{{ $cw['stock'] }}"
                                    data-tag="{{ $cw['tag'] ?? '' }}"
                                    title="{{ $cw['name'] }}">
                                <span class="palette-swatch" style="background-color: {{ $cw['color'] }};"></span>
                                <span class="palette-label">{{ $cw['name'] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="color" id="productColorInput" value="{{ $initialColorway['name'] }}">
                </div>
            @endif

            <!-- SELECT SIZE -->
            @if($product->has_sizes && count($sizes))
                <fieldset class="fashion-size-fieldset">
                    <legend>Size</legend>
                    <div class="fashion-size-grid">
                        @foreach($sizes as $size)
                            <label class="fashion-size-pill">
                                <input type="radio" name="size" value="{{ $size }}" @checked(old('size')===$size || (empty(old('size')) && $loop->first)) required>
                                <span>{{ $size }}</span>
                            </label>
                        @endforeach
                    </div>
                    <small>Select your size before adding to cart.</small>
                </fieldset>
            @endif

            <div class="fashion-quantity-row">
                <label>
                    <span>Quantity</span>
                    <input type="number" name="quantity" value="1" min="1" max="{{ floor($product->available_stock) }}">
                </label>
                <div>
                    <span>SKU</span>
                    <strong id="productSkuDisplay">{{ $initialSku }}</strong>
                </div>
            </div>

            <div class="fashion-buy-actions">
                <button class="fashion-buy-now" type="submit" name="buy_now" value="1">Buy it now</button>
                <button class="fashion-add-cart" type="submit">Add to cart</button>
            </div>
        </form>

        <div class="fashion-benefit-strip primary">
            <span>◎ Quality checked</span>
            @if(!empty($colorways) && count($colorways) > 0)<span>🎨 {{ count($colorways) }} Colorways available</span>@endif
            @if($product->has_sizes)<span>◌ Size selectable</span>@else<span>◌ Ready to order</span>@endif
        </div>
        <div class="fashion-benefit-strip secondary">
            <span>✓ Secure checkout</span>
            <span>🚚 Fast order processing</span>
        </div>

        <details class="fashion-description" open>
            <summary>Product details</summary>
            <p>{{ $product->description ?: 'Quality product available from TAJ Limited. Product availability is synchronized with the live ERP inventory.' }}</p>
        </details>

        <div class="fashion-product-meta">
            <div><span>Category</span><strong>{{ $product->category?->name ?? 'General' }}</strong></div>
            <div><span>Live inventory</span><strong>{{ number_format($product->available_stock,0) }} {{ $product->unit }}</strong></div>
        </div>
    </aside>
</div>

@if($product->image_url)
<dialog id="productImageLightbox" class="product-image-lightbox">
    <form method="dialog"><button class="lightbox-close" aria-label="Close">×</button></form>
    <img src="{{ $initialImage }}" alt="{{ $product->name }} enlarged" id="productLightboxImg">
    <div><strong>{{ $product->name }}</strong><span>Click outside the image or × to close.</span></div>
</dialog>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const paletteBtns = document.querySelectorAll('.colorway-palette-btn');
    const colorLabel = document.getElementById('currentColorwayLabel');
    const colorTag = document.getElementById('currentColorwayTag');
    const colorInput = document.getElementById('productColorInput');
    const skuDisplay = document.getElementById('productSkuDisplay');
    const mainImg = document.getElementById('productMainImg');
    const lightboxImg = document.getElementById('productLightboxImg');
    const stockDisplay = document.getElementById('productStockDisplay');

    paletteBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            paletteBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const name = this.dataset.name;
            const sku = this.dataset.sku;
            const img = this.dataset.image;
            const tag = this.dataset.tag;
            const stock = this.dataset.stock;

            if (colorLabel) colorLabel.textContent = name;
            if (colorTag) colorTag.textContent = tag ? `(${tag})` : '';
            if (colorInput) colorInput.value = name;
            if (skuDisplay && sku) skuDisplay.textContent = sku;

            if (img && mainImg) {
                mainImg.style.opacity = '0.3';
                setTimeout(() => {
                    mainImg.src = img;
                    if (lightboxImg) lightboxImg.src = img;
                    mainImg.style.opacity = '1';
                }, 150);
            }

            if (stockDisplay && stock) {
                const numStock = parseFloat(stock);
                stockDisplay.className = `fashion-stock ${numStock <= 5 ? 'low' : 'available'}`;
                stockDisplay.innerHTML = `<span></span> ${numStock <= 5 ? 'LOW STOCK' : 'IN STOCK'} · ${numStock.toLocaleString()} available`;
            }
        });
    });
});
</script>
@endsection
