@extends('layouts.customer')
@section('title',$product->name)
@section('content')
@php
    $isLowStock = $product->available_stock <= $product->min_stock;
    $sizes = $product->normalizedSizes();
    $isClothing = $product->has_sizes || ($product->category && stripos($product->category->name, 'cloth') !== false);
@endphp

@if($isClothing)
    @include('shop.partials.clothing-showcase', ['clothingProducts' => collect([$product])->merge($relatedClothing ?? [])])
@endif

<div class="fashion-product-detail">
    <section class="fashion-product-media">
        @if($product->image_url)
            <button type="button" class="product-zoom-stage" data-product-zoom data-open-product-image aria-label="Open large product image">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
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
        <div class="fashion-price">{{ $companySettings->currency_symbol }}{{ number_format($product->selling_price,2) }}</div>

        <div class="fashion-stock {{ $isLowStock ? 'low' : 'available' }}">
            <span></span>
            {{ $isLowStock ? 'LOW STOCK' : 'IN STOCK' }} · {{ number_format($product->available_stock,0) }} {{ $product->unit }} available
        </div>

        <form class="fashion-buy-form" method="POST" action="{{ route('cart.add',$product) }}">
            @csrf

            @if($product->has_sizes && count($sizes))
                <fieldset class="fashion-size-fieldset">
                    <legend>Size</legend>
                    <div class="fashion-size-grid">
                        @foreach($sizes as $size)
                            <label class="fashion-size-pill">
                                <input type="radio" name="size" value="{{ $size }}" @checked(old('size')===$size) required>
                                <span>{{ $size }}</span>
                            </label>
                        @endforeach
                    </div>
                    <small>Select a size before adding this item to your cart.</small>
                </fieldset>
            @endif

            <div class="fashion-quantity-row">
                <label>
                    <span>Quantity</span>
                    <input type="number" name="quantity" value="1" min="1" max="{{ floor($product->available_stock) }}">
                </label>
                <div>
                    <span>SKU</span>
                    <strong>{{ $product->sku }}</strong>
                </div>
            </div>

            <div class="fashion-buy-actions">
                <button class="fashion-buy-now" type="submit" name="buy_now" value="1">Buy it now</button>
                <button class="fashion-add-cart" type="submit">Add to cart</button>
            </div>
        </form>

        <div class="fashion-benefit-strip primary">
            <span>◎ Quality checked</span>
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
    <img src="{{ $product->image_url }}" alt="{{ $product->name }} enlarged">
    <div><strong>{{ $product->name }}</strong><span>Click outside the image or × to close.</span></div>
</dialog>
@endif
@endsection
