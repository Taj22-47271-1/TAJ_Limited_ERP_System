@extends('layouts.customer')
@section('title', $product->name . ' — taj. Luxury')
@section('content')

@php
    $currency = $companySettings->currency_symbol ?? '৳';
    $isPerfume = $product->isPerfume();
    $isClothing = $product->isClothing();
    $colorways = $product->available_colorways;
    $initialImage = (!empty($colorways) && !empty($colorways[0]['image_url'])) ? $colorways[0]['image_url'] : $product->image_url;

    $catOpts = $product->category_options;
    $sizes = $isPerfume ? ($product->size_options ?: ['50ml', '100ml']) : ($product->has_sizes ? $product->normalizedSizes() : []);

    $similarProducts = \App\Models\Product::where('status', true)
        ->where('id', '!=', $product->id)
        ->where(function($q) use ($product) {
            $q->where('category_id', $product->category_id);
        })
        ->limit(4)
        ->get();

    if ($similarProducts->isEmpty()) {
        $similarProducts = \App\Models\Product::where('status', true)->where('id', '!=', $product->id)->limit(4)->get();
    }
@endphp

<div class="lux-details-layout">
    {{-- Breadcrumbs --}}
    <nav class="lux-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('shop.index', ['category' => $product->category_id]) }}">{{ $product->category?->name ?? 'Collection' }}</a>
        <span class="sep">/</span>
        <span style="color: var(--lux-black); font-weight: 700;">{{ $product->name }}</span>
    </nav>

    {{-- Main 2-Column Product Stage --}}
    <div class="lux-product-hero-stage">
        {{-- LEFT COLUMN: Details, Notes, Sizes, Action --}}
        <div class="lux-product-info">
            <span class="lux-product-kicker">
                {{ $product->category?->name ?? 'PRODUCT' }} · SKU: {{ $product->sku }}
            </span>
            <h1 class="lux-product-title">{{ $product->name }}</h1>
            
            @if($isPerfume)
                <p class="lux-product-tagline">
                    Eau de Parfum · Natural Spray Vaporisateur · Long-Lasting Sillage
                </p>
                {{-- Visual Scent Notes for Fragrances --}}
                <div class="lux-scent-notes-row">
                    <div class="lux-scent-note-chip">
                        <span class="lux-scent-icon">🍊</span>
                        <span>Blood Orange</span>
                    </div>
                    <div class="lux-scent-note-chip">
                        <span class="lux-scent-icon">🌿</span>
                        <span>Labdanum</span>
                    </div>
                    <div class="lux-scent-note-chip">
                        <span class="lux-scent-icon">🍦</span>
                        <span>Bourbon Vanilla</span>
                    </div>
                    <div class="lux-scent-note-chip">
                        <span class="lux-scent-icon">🪵</span>
                        <span>Smoky Amber</span>
                    </div>
                </div>
            @endif

            {{-- Full Description Accordion --}}
            <div class="lux-accordion">
                <details open>
                    <summary>FULL DESCRIPTION <span>⌄</span></summary>
                    <div class="lux-accordion-content">
                        {{ $product->description ?: 'Premium quality product manufactured to strict standards. Carefully inspected and backed by TAJ Limited quality guarantee.' }}
                    </div>
                </details>
            </div>

            {{-- Interactive Cart & Options Form --}}
            <form method="POST" action="{{ route('cart.add', $product) }}" id="productPurchaseForm">
                @csrf
                <input type="hidden" name="color" id="selectedColorInput" value="{{ $colorways[0]['name'] ?? '' }}">
                <input type="hidden" name="size" id="selectedSizeInput" value="{{ $sizes[0] ?? '' }}">

                {{-- Colorway Selector ONLY if product actually has colorways --}}
                @if(!empty($colorways))
                    <div class="lux-options-block">
                        <div class="lux-options-label">
                            <span>Select Colorway:</span>
                            <strong id="activeColorName" style="color: var(--lux-black);">{{ $colorways[0]['name'] ?? '' }}</strong>
                        </div>
                        <div class="lux-colorways-row">
                            @foreach($colorways as $cw)
                                <div class="lux-colorway-thumb {{ $loop->first ? 'active' : '' }}" 
                                     data-color="{{ $cw['name'] }}" 
                                     data-sku="{{ $cw['sku'] ?? $product->sku }}"
                                     data-image="{{ $cw['image_url'] ?? $product->image_url }}"
                                     onclick="selectColorway(this)"
                                     title="{{ $cw['name'] }}">
                                    @if(!empty($cw['image']))
                                        <img src="{{ $cw['image_url'] ?? $product->image_url }}" alt="{{ $cw['name'] }}">
                                    @elseif(!empty($cw['color']))
                                        <span style="display: block; width: 100%; height: 100%; border-radius: 50%; background-color: {{ $cw['color'] }};"></span>
                                    @else
                                        <img src="{{ $product->image_url }}" alt="{{ $cw['name'] }}">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @php
                    $initialSize = !empty($sizes) ? $sizes[0] : null;
                    $initialPricing = $product->getPricingForSize($initialSize);
                @endphp

                {{-- Size Options Pills (50ml / 100ml or S / M / L) --}}
                @if(!empty($sizes))
                    <div class="lux-options-block">
                        <div class="lux-options-label">
                            <span>{{ $isPerfume ? 'Size: ml' : 'Select Size:' }}</span>
                            <strong id="activeSizeDisplay">{{ $initialSize }}</strong>
                        </div>
                        <div class="lux-size-pills">
                            @foreach($sizes as $sz)
                                @php
                                    $szPricing = $product->getPricingForSize($sz);
                                @endphp
                                <button type="button" 
                                        class="lux-size-pill {{ $sz === $initialSize ? 'active' : '' }}" 
                                        data-size="{{ $sz }}"
                                        data-effective-price="{{ $szPricing['effective_price'] }}"
                                        data-selling-price="{{ $szPricing['selling_price'] }}"
                                        data-has-discount="{{ $szPricing['has_discount'] ? '1' : '0' }}"
                                        data-discount-percent="{{ $szPricing['discount_percent'] ?? '' }}"
                                        onclick="selectSize(this)">
                                    {{ $sz }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Price & Stock Row --}}
                <div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
                    <div id="productDisplayPrice" style="display: inline-flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                        <span class="lux-current-price" style="font-size: 28px; font-weight: 900; color: var(--lux-black);">
                            {{ $currency }}{{ number_format($initialPricing['effective_price'], 2) }}
                        </span>
                        @if($initialPricing['has_discount'])
                            <del class="lux-orig-price" style="font-size: 16px; color: var(--lux-text-light); font-weight: 500;">
                                {{ $currency }}{{ number_format($initialPricing['selling_price'], 2) }}
                            </del>
                            <span class="lux-badge-discount" style="position: static; vertical-align: middle;">
                                -{{ $initialPricing['discount_percent'] }}% OFF
                            </span>
                        @endif
                    </div>
                    <span style="font-size: 12px; font-weight: 600; color: #10b981; margin-left: auto;">
                        ● In Stock ({{ number_format($product->available_stock, 0) }} units)
                    </span>
                </div>

                {{-- Actions: Quantity + Wishlist + Add to Cart --}}
                <div class="lux-actions-row">
                    <div class="lux-cart-qty-ctrl" style="height: 54px;">
                        <button type="button" onclick="adjustQty(-1)" style="width: 40px; height: 54px; font-size: 16px;">−</button>
                        <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="{{ $product->available_stock }}" 
                               style="width: 44px; text-align: center; border: 0; font-size: 15px; font-weight: 800; outline: none;" readonly>
                        <button type="button" onclick="adjustQty(1)" style="width: 40px; height: 54px; font-size: 16px;">+</button>
                    </div>

                    <button type="button" class="lux-wishlist-big-btn" onclick="toggleWishlist({{ $product->id }}, this)" aria-label="Add to Wishlist">
                        ♡
                    </button>

                    <button type="submit" class="lux-add-to-cart-btn">
                        <span id="addToCartBtnLabel">ADD TO CART · {{ $currency }}{{ number_format($initialPricing['effective_price'], 2) }}</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- RIGHT COLUMN: Product Media Stage (Clean, Never Cropped) --}}
        <div class="lux-stage-column">
            <div class="lux-stage-main" id="mainStage">
                <img src="{{ $initialImage }}" alt="{{ $product->name }}" id="heroBottleImg">
            </div>

            {{-- Angle Thumbnails (Only if perfume has alternate art) --}}
            @if($isPerfume && file_exists(public_path('assets/images/behance/cosmic_hero_art.png')))
                <div class="lux-stage-angles">
                    <div class="lux-angle-thumb active" onclick="switchAngle('{{ $initialImage }}', this)">
                        <img src="{{ $initialImage }}" alt="Front Angle">
                    </div>
                    <div class="lux-angle-thumb" onclick="switchAngle('{{ asset('assets/images/behance/cosmic_hero_art.png') }}', this)">
                        <img src="{{ asset('assets/images/behance/cosmic_hero_art.png') }}" alt="Artistic Stage">
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- CUSTOMERS SAY REVIEW BLOCK --}}
    @php
        $avgRating = $product->averageRating();
        $totalReviews = $product->reviewCount();
        $breakdown = $product->ratingBreakdown();
        $productReviews = $product->reviews;
    @endphp

    <section class="lux-reviews-section" id="customerReviewsSection">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
            <div>
                <span class="lux-reviews-kicker" style="display: inline-block; margin-bottom: 4px;">[CUSTOMERS SAY]</span>
                <div style="font-size: 13px; color: var(--lux-text-muted);">
                    Based on {{ $totalReviews > 0 ? $totalReviews : 'verified buyer' }} {{ \Illuminate\Support\Str::plural('rating', max(1, $totalReviews)) }}
                </div>
            </div>
            <button type="button" id="toggleReviewBtn" class="lux-btn-black" style="padding: 10px 22px; font-size: 13px; font-weight: 700; border-radius: 999px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                <span style="color: #fbbf24;">★</span> <span>Write a Review</span>
            </button>
        </div>

        {{-- Collapsible Write Review Form --}}
        <div id="writeReviewBox" style="display: none; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 26px; margin-bottom: 36px; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;">Write Your Review for {{ $product->name }}</h3>
                <button type="button" id="closeReviewBtn" style="border: 0; background: transparent; font-size: 18px; cursor: pointer; color: #94a3b8;" aria-label="Close">✕</button>
            </div>

            @if($errors->has('rating') || $errors->has('review') || $errors->has('customer_name'))
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                    {{ $errors->first('rating') ?: ($errors->first('review') ?: $errors->first('customer_name')) }}
                </div>
            @endif

            <form method="POST" action="{{ route('shop.reviews.store', $product) }}" id="productReviewForm">
                @csrf
                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; margin-bottom: 8px;">Your Rating *</label>
                    <div class="star-rating-picker" style="display: flex; gap: 8px; align-items: center;">
                        <input type="hidden" name="rating" id="reviewRatingInput" value="5">
                        <div id="starPickerGroup" style="display: flex; gap: 4px; font-size: 28px; color: #fbbf24; cursor: pointer; user-select: none;">
                            <span class="star-item" data-value="1">★</span>
                            <span class="star-item" data-value="2">★</span>
                            <span class="star-item" data-value="3">★</span>
                            <span class="star-item" data-value="4">★</span>
                            <span class="star-item" data-value="5">★</span>
                        </div>
                        <span id="starRatingLabel" style="font-size: 13px; font-weight: 700; color: #d97706; margin-left: 8px;">5 Stars - Excellent</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 18px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; margin-bottom: 6px;">Your Name *</label>
                        <input type="text" name="customer_name" value="{{ auth()->check() ? auth()->user()->name : '' }}" placeholder="e.g. Tanvir Ahmed" required style="width: 100%; height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 14px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; margin-bottom: 6px;">Email (Optional)</label>
                        <input type="email" name="customer_email" value="{{ auth()->check() ? auth()->user()->email : '' }}" placeholder="e.g. tanvir@gmail.com" style="width: 100%; height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 14px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; margin-bottom: 6px;">Your Experience / Review *</label>
                    <textarea name="review" rows="4" placeholder="Tell other customers about the fragrance notes, quality, longevity, packaging, or customer service..." required style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px 14px; font-size: 14px; outline: none; resize: vertical; box-sizing: border-box; font-family: inherit;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" id="cancelReviewBtn" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; font-weight: 700; font-size: 13px; cursor: pointer;">Cancel</button>
                    <button type="submit" class="btn primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>

        <div class="lux-reviews-grid">
            <div class="lux-score-box">
                <div class="lux-big-rating">{{ number_format($avgRating, 1) }}</div>
                <div class="lux-stars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($avgRating))
                            ★
                        @else
                            ☆
                        @endif
                    @endfor
                </div>
                <div class="lux-rating-bars">
                    @foreach([5, 4, 3, 2, 1] as $star)
                        @php $pct = $breakdown[$star] ?? 0; @endphp
                        <div class="lux-rating-bar-row">
                            <span>{{ $star }}★</span>
                            <div class="lux-bar-track"><div class="lux-bar-fill" style="width: {{ $pct }}%;"></div></div>
                            <span>{{ $pct }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lux-customer-cards">
                @if($productReviews->isNotEmpty())
                    @foreach($productReviews->take(4) as $rev)
                        @php
                            $safeRating = max(1, min(5, (int)($rev->rating ?? 5)));
                        @endphp
                        <div class="lux-review-card">
                            <div class="lux-review-user">
                                <div>
                                    <span class="lux-review-author">{{ $rev->customer_name }}</span>
                                    @if($rev->is_verified)
                                        <span style="font-size: 10px; background: #ecfdf5; color: #059669; padding: 2px 6px; border-radius: 999px; font-weight: 700; margin-left: 6px;">Verified Buyer</span>
                                    @endif
                                </div>
                                <span class="lux-review-stars" style="color: #f59e0b;">
                                    {{ str_repeat('★', $safeRating) }}{{ str_repeat('☆', 5 - $safeRating) }}
                                </span>
                            </div>
                            <p class="lux-review-text" style="color: #475569; font-size: 13px; line-height: 1.6; margin: 0 0 8px;">
                                "{{ $rev->review }}"
                            </p>
                            <small style="color: #94a3b8; font-size: 11px;">{{ optional($rev->created_at)->diffForHumans() ?? 'Recently' }}</small>
                        </div>
                    @endforeach
                @else
                    {{-- Default Verified Testimonials (Matching Reference Design Image 8) --}}
                    <div class="lux-review-card">
                        <div class="lux-review-user">
                            <div>
                                <span class="lux-review-author">Verified Customer</span>
                                <span style="font-size: 10px; background: #ecfdf5; color: #059669; padding: 2px 6px; border-radius: 999px; font-weight: 700; margin-left: 6px;">Verified Buyer</span>
                            </div>
                            <span class="lux-review-stars">★★★★★</span>
                        </div>
                        <p class="lux-review-text">
                            "High-quality product that arrived in perfect condition. Packaging was excellent and delivery was prompt."
                        </p>
                        <small style="color: #94a3b8; font-size: 11px;">Recent purchase</small>
                    </div>

                    <div class="lux-review-card">
                        <div class="lux-review-user">
                            <div>
                                <span class="lux-review-author">Satisfied Buyer</span>
                                <span style="font-size: 10px; background: #ecfdf5; color: #059669; padding: 2px 6px; border-radius: 999px; font-weight: 700; margin-left: 6px;">Verified Buyer</span>
                            </div>
                            <span class="lux-review-stars">★★★★★</span>
                        </div>
                        <p class="lux-review-text">
                            "Exactly as described on the website. Very pleased with my order and will be shopping again."
                        </p>
                        <small style="color: #94a3b8; font-size: 11px;">Recent purchase</small>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- SIMILAR PRODUCTS --}}
    @if($similarProducts->isNotEmpty())
        <section class="lux-similar-section">
            <div class="lux-section-header">
                <div>
                    <h2>SIMILAR PRODUCTS</h2>
                    <p>More items you may be interested in.</p>
                </div>
                <a href="{{ route('shop.index') }}">View all <span>→</span></a>
            </div>

            <div class="lux-similar-grid">
                @foreach($similarProducts as $sim)
                    <div class="lux-card">
                        @if($sim->has_discount)
                            <span class="lux-badge-discount">-{{ $sim->discount_percent_calculated }}%</span>
                        @endif
                        <button type="button" class="lux-wishlist-toggle" onclick="toggleWishlist({{ $sim->id }}, this)">
                            ♡
                        </button>
                        <a href="{{ route('shop.show', $sim) }}" class="lux-card-media">
                            <img src="{{ $sim->image_url }}" alt="{{ $sim->name }}" loading="lazy">
                        </a>
                        <span class="lux-card-category">{{ $sim->category?->name ?? 'Collection' }}</span>
                        <a href="{{ route('shop.show', $sim) }}" class="lux-card-title">{{ $sim->name }}</a>
                        <div class="lux-card-price-row">
                            <span class="lux-card-price">
                                @if($sim->has_discount)
                                    {{ $currency }}{{ number_format($sim->effective_price, 2) }}
                                    <span class="lux-card-original-price">{{ $currency }}{{ number_format($sim->selling_price, 2) }}</span>
                                @else
                                    {{ $currency }}{{ number_format($sim->selling_price, 2) }}
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

@endsection

@push('scripts')
<script>
const LUX_CURRENCY = "{{ $currency }}";

function selectSize(btn) {
    document.querySelectorAll('.lux-size-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const size = btn.getAttribute('data-size');
    const input = document.getElementById('selectedSizeInput');
    if (input) input.value = size;
    const disp = document.getElementById('activeSizeDisplay');
    if (disp) disp.textContent = size;

    // Dynamically update price based on selected size variant
    const effPrice = parseFloat(btn.getAttribute('data-effective-price'));
    const sellPrice = parseFloat(btn.getAttribute('data-selling-price'));
    const hasDiscount = btn.getAttribute('data-has-discount') === '1';
    const discPct = btn.getAttribute('data-discount-percent');

    if (!isNaN(effPrice)) {
        const effFormatted = LUX_CURRENCY + effPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const sellFormatted = LUX_CURRENCY + sellPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const priceContainer = document.getElementById('productDisplayPrice');
        if (priceContainer) {
            if (hasDiscount) {
                priceContainer.innerHTML = `
                    <span class="lux-current-price" style="font-size: 28px; font-weight: 900; color: var(--lux-black);">${effFormatted}</span>
                    <del class="lux-orig-price" style="font-size: 16px; color: var(--lux-text-light); font-weight: 500;">${sellFormatted}</del>
                    <span class="lux-badge-discount" style="position: static; vertical-align: middle;">-${discPct}% OFF</span>
                `;
            } else {
                priceContainer.innerHTML = `
                    <span class="lux-current-price" style="font-size: 28px; font-weight: 900; color: var(--lux-black);">${effFormatted}</span>
                `;
            }
        }

        const btnLabel = document.getElementById('addToCartBtnLabel');
        if (btnLabel) {
            btnLabel.textContent = `ADD TO CART · ${effFormatted}`;
        }
    }
}

function selectColorway(thumb) {
    document.querySelectorAll('.lux-colorway-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
    const color = thumb.getAttribute('data-color');
    const img = thumb.getAttribute('data-image');
    document.getElementById('selectedColorInput').value = color;
    const nameEl = document.getElementById('activeColorName');
    if (nameEl) nameEl.textContent = color;
    if (img) {
        const hero = document.getElementById('heroBottleImg');
        if (hero) hero.src = img;
    }
}

function switchAngle(src, thumb) {
    document.querySelectorAll('.lux-angle-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
    const hero = document.getElementById('heroBottleImg');
    if (hero) {
        hero.style.opacity = '0';
        setTimeout(() => {
            hero.src = src;
            hero.style.opacity = '1';
        }, 150);
    }
}

function adjustQty(delta) {
    const input = document.getElementById('qtyInput');
    let val = parseInt(input.value) || 1;
    val = Math.max(1, Math.min(parseInt(input.max) || 999, val + delta));
    input.value = val;
}

// Review Drawer & Star Picker Handler
(function() {
    const toggleBtn = document.getElementById('toggleReviewBtn');
    const closeBtn = document.getElementById('closeReviewBtn');
    const cancelBtn = document.getElementById('cancelReviewBtn');
    const reviewBox = document.getElementById('writeReviewBox');
    const ratingInput = document.getElementById('reviewRatingInput');
    const ratingLabel = document.getElementById('starRatingLabel');
    const starGroup = document.getElementById('starPickerGroup');

    const ratingDescriptions = {
        1: '1 Star - Needs Improvement',
        2: '2 Stars - Fair',
        3: '3 Stars - Good / Average',
        4: '4 Stars - Very Good',
        5: '5 Stars - Excellent'
    };

    function showReviewBox() {
        if (reviewBox) {
            reviewBox.style.display = 'block';
            reviewBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function hideReviewBox() {
        if (reviewBox) reviewBox.style.display = 'none';
    }

    if (toggleBtn) toggleBtn.addEventListener('click', showReviewBox);
    if (closeBtn) closeBtn.addEventListener('click', hideReviewBox);
    if (cancelBtn) cancelBtn.addEventListener('click', hideReviewBox);

    if (starGroup && ratingInput) {
        const stars = starGroup.querySelectorAll('.star-item');

        function setStars(val) {
            stars.forEach(s => {
                const sVal = parseInt(s.getAttribute('data-value'));
                if (sVal <= val) {
                    s.style.color = '#fbbf24';
                    s.textContent = '★';
                } else {
                    s.style.color = '#cbd5e1';
                    s.textContent = '★';
                }
            });
            if (ratingLabel) {
                ratingLabel.textContent = ratingDescriptions[val] || (val + ' Stars');
            }
        }

        stars.forEach(s => {
            s.addEventListener('mouseenter', function() {
                const hoverVal = parseInt(this.getAttribute('data-value'));
                setStars(hoverVal);
            });

            s.addEventListener('click', function() {
                const selectedVal = parseInt(this.getAttribute('data-value'));
                ratingInput.value = selectedVal;
                setStars(selectedVal);
            });
        });

        starGroup.addEventListener('mouseleave', function() {
            const currentVal = parseInt(ratingInput.value) || 5;
            setStars(currentVal);
        });

        // Initialize with default 5
        setStars(5);
    }

    @if($errors->has('rating') || $errors->has('review') || $errors->has('customer_name'))
        showReviewBox();
    @endif

    const form = document.getElementById('productReviewForm');
    if (form) {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                btn.textContent = 'Submitting...';
            }
        });
    }
})();
</script>
@endpush
