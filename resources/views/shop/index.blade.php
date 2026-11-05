@extends('layouts.customer')
@section('title', 'Shop - Live Collection')
@section('content')

<!-- Section Header (Editorial Style from Reference Video) -->
<section class="vc-shop-head" style="margin-bottom: 12px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
    <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.15em; color: #b45309; text-transform: uppercase;">THE EDIT / 02</span>
    <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: clamp(32px, 5vw, 48px); font-weight: 800; color: #0f172a; margin: 6px 0 8px; letter-spacing: -0.02em;">
        Your new <em style="font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-weight: 400;">favourites.</em>
    </h1>
    <p style="color: #64748b; font-size: 14px; max-width: 580px; margin: 0 auto;">
        Curated designer apparel, premium tech essentials, and smart lifestyle products synchronized live with ERP inventory.
    </p>
</section>

<!-- Category Filter Pills (Reference Video Navigation) -->
<div class="editorial-filter-row" style="justify-content: center;">
    <a href="{{ route('shop.index') }}" class="editorial-filter-pill {{ !request('category') ? 'active' : '' }}">
        All Collection
    </a>
    @foreach($categories as $category)
        <a href="{{ route('shop.index', ['category' => $category->id]) }}" class="editorial-filter-pill {{ (string)request('category') === (string)$category->id ? 'active' : '' }}">
            @if(stripos($category->name, 'cloth') !== false) 👗 @elseif(stripos($category->name, 'elect') !== false) ⚡ @elseif(stripos($category->name, 'access') !== false) 🎧 @elseif(stripos($category->name, 'groc') !== false) ☕ @else 📦 @endif
            {{ $category->name }}
        </a>
    @endforeach
</div>

<!-- Search Bar & Active Count -->
<div style="max-width: 600px; margin: 0 auto 32px;">
    <form method="GET" action="{{ route('shop.index') }}" style="display: flex; gap: 8px;">
        @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
        <div style="position: relative; flex: 1;">
            <input name="q" value="{{ request('q') }}" placeholder="Search name, SKU, or category..." style="width: 100%; height: 44px; padding: 0 16px 0 42px; border-radius: 999px; border: 1px solid #cbd5e1; outline: none; font-size: 13px; background: #ffffff;">
            <svg viewBox="0 0 24 24" style="position: absolute; left: 16px; top: 13px; width: 18px; height: 18px; stroke: #94a3b8; fill: none; stroke-width: 2;"><path d="m21 21-4.35-4.35m2.35-5.15A7.5 7.5 0 1 1 4 11.5a7.5 7.5 0 0 1 15 0Z"/></svg>
        </div>
        <button type="submit" class="btn primary" style="border-radius: 999px; height: 44px; padding: 0 20px; font-size: 13px;">Search</button>
        @if(request()->filled('q') || request()->filled('category'))
            <a href="{{ route('shop.index') }}" class="btn ghost" style="border-radius: 999px; height: 44px; padding: 0 16px; font-size: 13px; display: inline-flex; align-items: center;">Clear</a>
        @endif
    </form>
</div>

<!-- Product Grid with Quick Add & Hover Effects -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; max-width: 1280px; margin: 0 auto;">
    @forelse($products as $product)
        @php
            $isCloth = $product->isClothing();
            $swatches = $product->available_colorways;
            $sizes = $product->normalizedSizes();
            $rating = 4.8 + (($product->id % 3) * 0.1);
            $reviewsCount = 45 + ($product->id * 11);
        @endphp
        <article class="editorial-card">
            <!-- Media Stage with Corner Badges & Quick Add Size Overlay -->
            <div class="editorial-card-media">
                @if($product->has_discount)
                    <span class="card-badge-left sale-badge">-{{ $product->discount_percent_calculated }}% OFF</span>
                @elseif($loop->index < 2)
                    <span class="card-badge-left" style="background: #0f172a; color: #fff; border-color: #0f172a;">NEW</span>
                @elseif($product->id % 2 === 0)
                    <span class="card-badge-left">POPULAR</span>
                @endif

                <!-- Wishlist Heart Toggle Button -->
                <button type="button" class="card-wishlist-btn" data-product-id="{{ $product->id }}" aria-label="Add to wishlist" title="Add to wishlist">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </button>

                <a href="{{ route('shop.show', $product) }}" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async">
                    @else
                        <span class="vc-product-letter">{{ strtoupper(substr($product->name,0,1)) }}</span>
                    @endif
                </a>

                <!-- Quick Add Hover Overlay Bar (Reference Video Standard) -->
                <div class="card-quick-add-bar">
                    @if($isCloth && !empty($sizes))
                        <span class="quick-add-label">Quick add</span>
                        @foreach(array_slice($sizes, 0, 5) as $sz)
                            <button type="button" class="quick-add-pill" data-product-id="{{ $product->id }}" data-size="{{ $sz }}" data-product-name="{{ $product->name }}" title="Add size {{ $sz }} to bag">
                                {{ $sz }}
                            </button>
                        @endforeach
                    @else
                        <button type="button" class="quick-add-pill" style="width: 100%; padding: 6px 12px; font-size: 11px;" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" title="Quick add to bag">
                            ⚡ Quick Add to Bag
                        </button>
                    @endif
                </div>
            </div>

            <!-- Card Body & Pricing -->
            <div class="editorial-card-body">
                <div class="card-star-rating">
                    <span>★★★★★</span>
                    <strong style="color: #0f172a; font-size: 11px;">{{ number_format($rating, 1) }}</strong>
                    <span class="card-review-count">({{ $reviewsCount }})</span>
                </div>

                <a href="{{ route('shop.show', $product) }}" class="editorial-card-title" title="{{ $product->name }}">
                    {{ $product->name }}
                </a>

                <div class="editorial-card-pricing">
                    @if($product->has_discount)
                        <del class="editorial-orig-price">{{ $companySettings->currency_symbol }}{{ number_format($product->selling_price, 2) }}</del>
                        <strong class="editorial-final-price sale-color">{{ $companySettings->currency_symbol }}{{ number_format($product->effective_price, 2) }}</strong>
                    @else
                        <strong class="editorial-final-price">{{ $companySettings->currency_symbol }}{{ number_format($product->selling_price, 2) }}</strong>
                    @endif
                </div>

                <!-- Color Swatches Dots Preview -->
                @if(!empty($swatches))
                    <div class="card-color-dots">
                        @foreach(array_slice($swatches, 0, 5) as $cw)
                            <span class="card-color-dot" style="background-color: {{ $cw['color'] }};" title="{{ $cw['name'] }}"></span>
                        @endforeach
                        @if(count($swatches) > 5)
                            <span style="font-size: 10px; color: #94a3b8; font-weight: 700;">+{{ count($swatches) - 5 }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="vc-empty vc-span-all" style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; color: #64748b;">
            <p style="font-size: 16px; margin-bottom: 12px;">No products matched your search or category filter.</p>
            <a href="{{ route('shop.index') }}" class="btn secondary">View All Products</a>
        </div>
    @endforelse
</div>

<div class="pagination customer-pagination" style="margin-top: 40px; display: flex; justify-content: center;">
    {{ $products->links() }}
</div>

@endsection
