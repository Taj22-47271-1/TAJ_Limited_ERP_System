@extends('layouts.customer')
@section('title', 'taj. — Online Store')
@section('content')

@php
    $currency = $companySettings->currency_symbol ?? '৳';
@endphp

{{-- =========================================================================
     1. CLEAN BRANDED HERO SECTION (Replacing unwanted stock photo banners)
     ========================================================================= --}}
<section style="max-width: 1360px; margin: 32px auto 44px; padding: 0 32px;">
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 20px; padding: clamp(36px, 5vw, 56px); color: #ffffff; position: relative; overflow: hidden; box-shadow: 0 12px 36px rgba(0,0,0,0.08);">
        <div style="position: relative; z-index: 2; max-width: 640px;">
            <span style="display: inline-block; font-size: 11px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; background: rgba(255,255,255,0.15); padding: 5px 14px; border-radius: 999px; margin-bottom: 16px; border: 1px solid rgba(255,255,255,0.2);">
                TAJ LIMITED ONLINE STORE
            </span>
            <h1 style="font-size: clamp(32px, 5vw, 48px); font-weight: 900; letter-spacing: -0.03em; line-height: 1.1; margin: 0 0 14px; color: #ffffff;">
                Simple shopping.<br><span style="color: #c5a880;">Live ERP inventory.</span>
            </h1>
            <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1; margin: 0 0 28px;">
                Browse available products, pay online or choose cash on delivery nationwide. Every product is synchronized live with TAJ Limited ERP stock.
            </p>
            <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                <a href="{{ route('shop.index') }}" class="lux-btn-black" style="background: #ffffff; color: #0a0a0a !important; border-color: #ffffff; padding: 12px 26px; font-size: 14px;">
                    Explore Catalog <span>→</span>
                </a>
                <a href="{{ route('shop.index', ['filter' => 'sale']) }}" class="lux-btn-black" style="background: transparent; color: #ffffff !important; border-color: rgba(255,255,255,0.4); padding: 12px 24px; font-size: 14px;">
                    🔥 Special Offers
                </a>
            </div>
        </div>
        <div style="position: absolute; right: -60px; bottom: -60px; width: 340px; height: 340px; background: radial-gradient(circle, rgba(197, 168, 128, 0.18) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
    </div>
</section>

{{-- =========================================================================
     2. ORDER TRACKING SUMMARY STRIP
     ========================================================================= --}}
<section style="max-width: 1360px; margin: 0 auto 52px; padding: 0 32px;">
    <div style="background: #ffffff; border: 1px solid var(--lux-border); border-radius: var(--lux-radius-md); padding: 24px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; text-align: center; box-shadow: var(--lux-shadow-soft);">
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--lux-text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">Total Orders</div>
            <div style="font-size: 26px; font-weight: 900; color: var(--lux-black);">{{ $orderCount }}</div>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--lux-text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">Pending Approval</div>
            <div style="font-size: 26px; font-weight: 900; color: #d97706;">{{ $pendingCount }}</div>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--lux-text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">In Processing</div>
            <div style="font-size: 26px; font-weight: 900; color: #2563eb;">{{ $processingCount }}</div>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--lux-text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">Delivered</div>
            <div style="font-size: 26px; font-weight: 900; color: #10b981;">{{ $deliveredCount }}</div>
        </div>
    </div>
</section>

{{-- =========================================================================
     3. LIVE INVENTORY CATALOG (4-Column Minimal Luxury Cards)
     ========================================================================= --}}
<section class="lux-bestsellers-section" style="margin-bottom: 72px;">
    <div class="lux-section-header">
        <div>
            <h2>LIVE INVENTORY</h2>
            <p>{{ $featuredProducts->count() }} products synchronized live with ERP stock.</p>
        </div>
        <a href="{{ route('shop.index') }}">View full catalog <span>→</span></a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px;">
        @foreach($featuredProducts as $product)
            @php
                $swatches = $product->available_colorways;
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
                <span class="lux-card-category">{{ $product->category?->name ?? 'Product' }}</span>
                <a href="{{ route('shop.show', $product) }}" class="lux-card-title" title="{{ $product->name }}">{{ $product->name }}</a>

                @if(!empty($swatches))
                    <div style="display: flex; gap: 6px; margin-bottom: 8px; align-items: center;">
                        @foreach(array_slice($swatches, 0, 5) as $sw)
                            <span style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $sw['color'] }}; border: 1px solid rgba(0,0,0,0.15); display: inline-block;" title="{{ $sw['name'] }}"></span>
                        @endforeach
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
        @endforeach
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
