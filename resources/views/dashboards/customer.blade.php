@extends('layouts.customer')
@section('title', 'taj. — Luxury Store')
@section('content')

@php
    $currency = $companySettings->currency_symbol ?? '৳';
    $shelfItems = $perfumeProducts->isNotEmpty() ? $perfumeProducts : $featuredProducts->take(6);
    $bestsellerHero = $perfumeProducts->firstWhere('sku', 'PERF-006') ?? $perfumeProducts->first() ?? $featuredProducts->first();
    $carouselItems = $perfumeProducts->where('id', '!=', $bestsellerHero?->id)->take(3);
    if ($carouselItems->isEmpty()) {
        $carouselItems = $featuredProducts->where('id', '!=', $bestsellerHero?->id)->take(3);
    }
@endphp

{{-- =========================================================================
     1. NEW ARRIVALS FLOATING BOTTLE SHELF (img_35)
     ========================================================================= --}}
<section class="lux-shelf-section">
    <div class="lux-shelf-header">
        <span class="lux-shelf-title">NEW ARRIVALS</span>
        <a href="{{ route('shop.index') }}" style="font-size: 12px; font-weight: 700; color: var(--lux-black); text-decoration: none;">View catalog →</a>
    </div>
    <div class="lux-shelf-scroll">
        @foreach($shelfItems as $item)
            <a href="{{ route('shop.show', $item) }}" class="lux-shelf-item">
                <div class="lux-shelf-stage">
                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy">
                </div>
                <div class="lux-shelf-name">{{ Str::limit($item->name, 22) }}</div>
                <div class="lux-shelf-price">
                    @if($item->has_discount)
                        {{ $currency }}{{ number_format($item->effective_price, 2) }}
                    @else
                        {{ $currency }}{{ number_format($item->selling_price, 2) }}
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- =========================================================================
     2. SPECIAL OFFER SPLIT HERO BANNER (img_35)
     ========================================================================= --}}
<section class="lux-split-hero">
    {{-- Left Card: Lifestyle / Model --}}
    <div class="lux-hero-card" style="background-image: url('{{ asset('assets/images/behance/hero_split_left.jpg') }}');">
        <div class="lux-hero-card-content">
            <span class="lux-hero-tag">SPECIAL OFFER</span>
            <h2 class="lux-hero-headline">Up to 40% off — discover this season's finest fragrances</h2>
            <p class="lux-hero-subtext">Handcrafted essences, rare oriental woods, and timeless luxury editions with complimentary delivery on eligible orders.</p>
            <a href="{{ route('shop.index', ['filter' => 'sale']) }}" class="lux-hero-cta">Shop Special Offers <span>→</span></a>
        </div>
    </div>

    {{-- Right Card: Pedestal Bottle / Timeless Hits --}}
    <div class="lux-hero-card" style="background-image: url('{{ asset('assets/images/behance/hero_split_right.jpg') }}');">
        <div class="lux-hero-card-content">
            <span class="lux-hero-tag">TIMELESS HITS</span>
            <h2 class="lux-hero-headline">Iconic Signatures & Rare Blends</h2>
            <p class="lux-hero-subtext">Explore our most celebrated luxury masterpieces crafted for the discerning connoisseur with pure botanical extracts.</p>
            <a href="{{ route('shop.index') }}" class="lux-hero-cta">Explore Collection <span>→</span></a>
        </div>
    </div>
</section>

{{-- =========================================================================
     3. BESTSELLERS SHOWCASE & CAROUSEL (img_42)
     ========================================================================= --}}
<section class="lux-bestsellers-section" id="bestsellers">
    <div class="lux-section-header">
        <div>
            <h2>BESTSELLERS</h2>
            <p>The most coveted luxury perfumes and boutique creations chosen by our clients worldwide.</p>
        </div>
        <a href="{{ route('shop.index') }}">All products <span>→</span></a>
    </div>

    <div class="lux-bestseller-stage-wrap">
        {{-- Spotlight Hero Stage on Left --}}
        @if($bestsellerHero)
            <div class="lux-spotlight-card">
                <div>
                    <span class="lux-hero-tag" style="background: rgba(0,0,0,0.06); color: #000; border-color: rgba(0,0,0,0.1); margin-bottom: 16px;">FEATURED MASTERPIECE</span>
                    <div class="lux-spotlight-img-wrap">
                        <img src="{{ $bestsellerHero->image_url }}" alt="{{ $bestsellerHero->name }}">
                    </div>
                </div>
                <div class="lux-spotlight-info">
                    <h3>{{ $bestsellerHero->name }}</h3>
                    <p>{{ Str::limit($bestsellerHero->description, 100) }}</p>
                    <div class="lux-spotlight-action">
                        <div class="lux-spotlight-price">
                            @if($bestsellerHero->has_discount)
                                {{ $currency }}{{ number_format($bestsellerHero->effective_price, 2) }}
                                <del style="font-size: 13px; color: var(--lux-text-light); font-weight: 500; margin-left: 6px;">{{ $currency }}{{ number_format($bestsellerHero->selling_price, 2) }}</del>
                            @else
                                {{ $currency }}{{ number_format($bestsellerHero->selling_price, 2) }}
                            @endif
                        </div>
                        <a href="{{ route('shop.show', $bestsellerHero) }}" class="lux-btn-black">
                            Go to product ↗
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Bestsellers Carousel Grid on Right --}}
        <div class="lux-carousel-grid">
            @foreach($carouselItems as $item)
                <div class="lux-card">
                    @if($item->has_discount)
                        <span class="lux-badge-discount">-{{ $item->discount_percent_calculated }}%</span>
                    @endif
                    <button type="button" class="lux-wishlist-toggle" onclick="toggleWishlist({{ $item->id }}, this)" aria-label="Wishlist">
                        ♡
                    </button>
                    <a href="{{ route('shop.show', $item) }}" class="lux-card-media">
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy">
                    </a>
                    <span class="lux-card-category">{{ $item->category?->name ?? 'Perfume' }}</span>
                    <a href="{{ route('shop.show', $item) }}" class="lux-card-title" title="{{ $item->name }}">{{ $item->name }}</a>
                    <div class="lux-card-price-row">
                        <span class="lux-card-price">
                            @if($item->has_discount)
                                {{ $currency }}{{ number_format($item->effective_price, 2) }}
                                <span class="lux-card-original-price">{{ $currency }}{{ number_format($item->selling_price, 2) }}</span>
                            @else
                                {{ $currency }}{{ number_format($item->selling_price, 2) }}
                            @endif
                        </span>
                    </div>
                    <div style="margin-top: 14px;">
                        @if($item->has_sizes || !empty($item->available_colorways))
                            <a href="{{ route('shop.show', $item) }}" class="lux-btn-black" style="width: 100%; font-size: 12px; padding: 8px 12px;">Choose options</a>
                        @else
                            <form method="POST" action="{{ route('cart.add', $item) }}">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="lux-btn-black" style="width: 100%; font-size: 12px; padding: 8px 12px;">Add to cart</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- =========================================================================
     4. LIVE CATALOG / ALL PRODUCTS GRID (img_42)
     ========================================================================= --}}
<section class="lux-bestsellers-section" style="margin-bottom: 72px;">
    <div class="lux-section-header">
        <div>
            <h2>CATALOG</h2>
            <p>{{ $featuredProducts->count() }} luxury creations and boutique items available live in stock.</p>
        </div>
        <a href="{{ route('shop.index') }}">View full catalog <span>→</span></a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px;">
        @foreach($featuredProducts as $product)
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
        @endforeach
    </div>
</section>

{{-- =========================================================================
     5. ABOUT US SECTION (img_35)
     ========================================================================= --}}
<section class="lux-about-section">
    <div class="lux-about-grid">
        <div class="lux-about-content">
            <span class="lux-about-kicker">ARTISANAL HERITAGE</span>
            <h2>Pure botanical essence and timeless craftsmanship.</h2>
            <p>
                At taj., every formulation is a harmonious symphony of sustainably sourced natural botanicals, rare oriental resins, and meticulous hand-blended extractions. Crafted to awaken evocative memories and express your individual charisma.
            </p>
            <div>
                <a href="{{ route('shop.index') }}" class="lux-btn-black">Discover the story & catalog ↗</a>
            </div>
        </div>
        <div class="lux-about-portrait" style="background-image: url('{{ asset('assets/images/behance/about_portrait.jpg') }}');"></div>
    </div>
</section>

{{-- =========================================================================
     6. ORDER TRACKING SUMMARY STRIP
     ========================================================================= --}}
<section style="max-width: 1360px; margin: 0 auto 60px; padding: 0 32px;">
    <div style="background: var(--lux-surface); border: 1px solid var(--lux-border); border-radius: var(--lux-radius-md); padding: 24px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; text-align: center;">
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--lux-text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">Total Orders</div>
            <div style="font-size: 24px; font-weight: 800; color: var(--lux-black);">{{ $orderCount }}</div>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--lux-text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">Pending Approval</div>
            <div style="font-size: 24px; font-weight: 800; color: #d97706;">{{ $pendingCount }}</div>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--lux-text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">In Processing</div>
            <div style="font-size: 24px; font-weight: 800; color: #2563eb;">{{ $processingCount }}</div>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--lux-text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">Delivered</div>
            <div style="font-size: 24px; font-weight: 800; color: #10b981;">{{ $deliveredCount }}</div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
function toggleWishlist(productId, btn) {
    let wishlist = JSON.parse(localStorage.getItem('taj_wishlist') || '[]');
    const index = wishlist.indexOf(productId);
    if (index > -1) {
        wishlist.splice(index, 1);
        btn.classList.remove('active');
        btn.innerHTML = '♡';
    } else {
        wishlist.push(productId);
        btn.classList.add('active');
        btn.innerHTML = '♥';
    }
    localStorage.setItem('taj_wishlist', JSON.stringify(wishlist));
    updateWishlistCounter();
}

function updateWishlistCounter() {
    let wishlist = JSON.parse(localStorage.getItem('taj_wishlist') || '[]');
    document.querySelectorAll('.wishlist-counter').forEach(el => {
        el.textContent = wishlist.length;
        el.style.display = wishlist.length > 0 ? 'inline-flex' : 'none';
    });
}
document.addEventListener('DOMContentLoaded', updateWishlistCounter);
</script>
@endpush
