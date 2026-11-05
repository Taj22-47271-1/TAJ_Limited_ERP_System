@extends('layouts.customer')
@section('title', 'Catalog — taj. Luxury Collection')
@section('content')

@php
    $currency = $companySettings->currency_symbol ?? '৳';
@endphp

<div class="lux-shelf-section" style="margin-top: 24px;">
    {{-- Catalog Header --}}
    <div style="display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 32px;">
        <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: var(--lux-accent-gold); margin-bottom: 6px;">
            EXCLUSIVE LIVE COLLECTION
        </span>
        <h1 style="font-size: clamp(32px, 5vw, 44px); font-weight: 900; letter-spacing: -0.04em; color: var(--lux-black); margin: 0 0 10px;">
            CATALOG
        </h1>
        <p style="font-size: 14px; color: var(--lux-text-muted); max-width: 540px; margin: 0 auto;">
            Explore our curated inventory of boutique fragrances, designer apparel, and premium lifestyle essentials synchronized live with ERP stock.
        </p>
    </div>

    {{-- Filter and Search Row --}}
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 32px; border-bottom: 1px solid var(--lux-border); padding-bottom: 20px;">
        {{-- Category Pills --}}
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <a href="{{ route('shop.index') }}" 
               class="lux-size-pill {{ !request('category') && !request('filter') ? 'active' : '' }}" 
               style="height: 38px; border-radius: var(--lux-radius-full); text-decoration: none; padding: 0 18px; font-size: 12px;">
                All Products ({{ $products->total() }})
            </a>
            @foreach($categories as $category)
                <a href="{{ route('shop.index', ['category' => $category->id]) }}" 
                   class="lux-size-pill {{ (string)request('category') === (string)$category->id ? 'active' : '' }}" 
                   style="height: 38px; border-radius: var(--lux-radius-full); text-decoration: none; padding: 0 18px; font-size: 12px;">
                    {{ $category->name }}
                </a>
            @endforeach
            <a href="{{ route('shop.index', ['filter' => 'sale']) }}" 
               class="lux-size-pill {{ request('filter') === 'sale' ? 'active' : '' }}" 
               style="height: 38px; border-radius: var(--lux-radius-full); text-decoration: none; padding: 0 18px; font-size: 12px; color: #dc2626; border-color: #fca5a5;">
                🔥 Special Offers
            </a>
        </div>

        {{-- Search Input --}}
        <form method="GET" action="{{ route('shop.index') }}" style="display: flex; gap: 8px; align-items: center; min-width: 280px;">
            @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            @if(request('filter'))<input type="hidden" name="filter" value="{{ request('filter') }}">@endif
            <div style="position: relative; flex: 1;">
                <input name="q" value="{{ request('q') }}" placeholder="Search catalog..." 
                       style="width: 100%; height: 38px; padding: 0 14px 0 34px; border-radius: var(--lux-radius-full); border: 1px solid var(--lux-border-dark); font-size: 13px; outline: none; background: #fafafa;">
                <svg viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 11px; width: 16px; height: 16px; stroke: #999; fill: none; stroke-width: 2;"><path d="m21 21-4.35-4.35m2.35-5.15A7.5 7.5 0 1 1 4 11.5a7.5 7.5 0 0 1 15 0Z"/></svg>
            </div>
            <button type="submit" class="lux-btn-black" style="height: 38px; padding: 0 16px; font-size: 12px;">Search</button>
            @if(request()->filled('q') || request()->filled('category') || request()->filled('filter'))
                <a href="{{ route('shop.index') }}" style="font-size: 12px; color: var(--lux-text-muted); text-decoration: none; padding: 0 8px;">Reset</a>
            @endif
        </form>
    </div>

    {{-- Product Grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; margin-bottom: 60px;">
        @forelse($products as $product)
            @php
                $swatches = $product->available_colorways;
                $sizes = $product->normalizedSizes();
            @endphp
            <div class="lux-card">
                @if($product->has_discount)
                    <span class="lux-badge-discount">-{{ $product->discount_percent_calculated }}%</span>
                @endif
                <button type="button" class="lux-wishlist-toggle" onclick="toggleWishlist({{ $product->id }}, this)" aria-label="Wishlist">
                    ♡
                </button>
                <a href="{{ route('shop.show', $product) }}" class="lux-card-media">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                </a>
                <span class="lux-card-category">{{ $product->category?->name ?? 'Collection' }}</span>
                <a href="{{ route('shop.show', $product) }}" class="lux-card-title" title="{{ $product->name }}">{{ $product->name }}</a>

                {{-- Color Swatches Preview if available --}}
                @if(!empty($swatches))
                    <div style="display: flex; gap: 6px; margin-bottom: 8px; align-items: center;">
                        @foreach(array_slice($swatches, 0, 5) as $sw)
                            <span style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $sw['color'] }}; border: 1px solid rgba(0,0,0,0.15); display: inline-block;" title="{{ $sw['name'] }}"></span>
                        @endforeach
                        @if(count($swatches) > 5)
                            <small style="font-size: 10px; color: var(--lux-text-light); font-weight: 700;">+{{ count($swatches) - 5 }}</small>
                        @endif
                    </div>
                @endif

                <div class="lux-card-price-row">
                    <span class="lux-card-price">
                        @if($product->has_discount)
                            {{ $currency }}{{ number_format($product->effective_price, 2) }}
                            <span class="lux-card-original-price">{{ $currency }}{{ number_format($product->selling_price, 2) }}</span>
                        @else
                            {{ $currency }}{{ number_format($product->selling_price, 2) }}
                        @endif
                    </span>
                    <small style="margin-left: auto; font-size: 11px; color: var(--lux-text-light); font-weight: 600;">
                        {{ number_format($product->available_stock, 0) }} in stock
                    </small>
                </div>

                <div style="margin-top: 14px;">
                    @if($product->has_sizes || !empty($product->available_colorways))
                        <a href="{{ route('shop.show', $product) }}" class="lux-btn-black" style="width: 100%; font-size: 12px; padding: 8px 12px;">Choose options</a>
                    @else
                        <form method="POST" action="{{ route('cart.add', $product) }}">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="lux-btn-black" style="width: 100%; font-size: 12px; padding: 8px 12px;">Add to cart</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; padding: 80px 20px; text-align: center; color: var(--lux-text-muted);">
                <p style="font-size: 16px; margin-bottom: 16px;">No products found matching your criteria.</p>
                <a href="{{ route('shop.index') }}" class="lux-btn-black">View all products</a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div style="display: flex; justify-content: center; margin-bottom: 60px;">
        {{ $products->links() }}
    </div>
</div>

@endsection
