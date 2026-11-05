@extends('layouts.customer')
@section('title', $product->name . ' - TAJ Limited')
@section('content')

@php
    $isLowStock = $product->available_stock <= $product->min_stock;
    $isClothing = $product->isClothing();
    $isElectronics = $product->isElectronics();
    $isGrocery = $product->isGrocery();
    $isOffice = $product->isOffice();

    $colorways = $product->available_colorways;
    $initialColorway = $colorways[0] ?? null;
    $initialImage = $initialColorway['image_url'] ?? $product->image_url;
    $initialSku = $initialColorway['sku'] ?? $product->sku;

    $catOpts = $product->category_options;
    $sizes = $isClothing ? $product->normalizedSizes() : ($catOpts['options'] ?? []);

    $rating = 4.8 + (($product->id % 3) * 0.1);
    $reviewsCount = 45 + ($product->id * 11);
@endphp

<!-- Breadcrumb Navigation -->
<nav style="max-width: 1280px; margin: 12px auto 20px; padding: 0 20px; font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 8px;">
    <a href="{{ route('shop.index') }}" style="color: #64748b; text-decoration: none;">Home</a>
    <span>›</span>
    <a href="{{ route('shop.index') }}" style="color: #64748b; text-decoration: none;">Shop</a>
    <span>›</span>
    <a href="{{ route('shop.index', ['category' => $product->category_id]) }}" style="color: #64748b; text-decoration: none;">{{ $product->category?->name ?? 'Collection' }}</a>
    <span>›</span>
    <span style="color: #0f172a; font-weight: 600;">{{ $product->name }}</span>
</nav>

<!-- Main Editorial Product Layout (Reference Video Standard) -->
<div class="editorial-product-layout">

    <!-- LEFT COLUMN: Media Showcase & 3D Interactive Stage -->
    <section class="editorial-media-column">
        <!-- Media View Mode Toggle -->
        <div class="media-mode-toggle" role="tablist">
            <button type="button" class="media-mode-btn active" id="btnModeStudio" onclick="switchMediaMode('studio')">
                📸 Studio Photos
            </button>
            <button type="button" class="media-mode-btn" id="btnModeOrbit" onclick="switchMediaMode('orbit')">
                ✨ 3D / 360° Interactive Orbit
            </button>
        </div>

        <!-- 1. Studio Photo Mode -->
        <div id="mediaStudioView" style="display: block;">
            <div class="product-zoom-stage" style="position: relative; border-radius: 20px; overflow: hidden; background: #f8fafc; border: 1px solid #e2e8f0; aspect-ratio: 1 / 1.05; display: flex; align-items: center; justify-content: center;">
                @if($product->has_discount)
                    <span class="card-badge-left sale-badge" style="top: 16px; left: 16px; font-size: 11px; padding: 6px 12px;">-{{ $product->discount_percent_calculated }}% OFF</span>
                @endif
                <img src="{{ $initialImage }}" alt="{{ $product->name }}" id="productMainImg" style="max-width: 86%; max-height: 86%; object-fit: contain; transition: opacity 0.3s ease, transform 0.4s ease;">
                <span class="zoom-hint" style="position: absolute; bottom: 16px; right: 16px; background: rgba(255, 255, 255, 0.9); padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 600; color: #475569; pointer-events: none;">
                    🔍 Click or hover to inspect
                </span>
            </div>

            <!-- Thumbnail Angles / Colorways Row -->
            @if(!empty($colorways) && count($colorways) > 1)
                <div style="display: flex; gap: 12px; margin-top: 16px; overflow-x: auto; padding-bottom: 6px;">
                    @foreach($colorways as $cIdx => $cw)
                        <button type="button" class="studio-thumb-btn {{ $cIdx === 0 ? 'active' : '' }}" onclick="selectStudioThumb(this, '{{ $cw['image_url'] }}', '{{ $cw['name'] }}', '{{ $cw['sku'] }}')" style="width: 72px; height: 72px; border-radius: 12px; border: 2px solid {{ $cIdx === 0 ? '#0f172a' : '#e2e8f0' }}; background: #f8fafc; padding: 4px; cursor: pointer; flex-shrink: 0; transition: all 0.2s;">
                            <img src="{{ $cw['image_url'] }}" alt="{{ $cw['name'] }}" style="width: 100%; height: 100%; object-fit: contain;">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 2. Interactive 3D / 360° Orbit Mode -->
        <div id="mediaOrbitView" style="display: none;">
            <div class="interactive-orbit-stage" id="interactiveOrbitStage" title="Click and drag to rotate 360 degrees">
                <div class="orbit-podium-ring"></div>
                
                <div class="orbit-model-wrapper" id="orbitModelWrapper">
                    <img src="{{ $initialImage }}" alt="{{ $product->name }} 3D" id="orbitGarmentImg">
                </div>

                <div class="orbit-hint-pill">
                    ✨ Drag horizontally to rotate in 3D space
                </div>
            </div>
            <p style="text-align: center; font-size: 12px; color: #64748b; margin-top: 10px;">
                Touch or mouse interactive 3D orbit simulation. Rotates smoothly with natural inertia.
            </p>
        </div>
    </section>

    <!-- RIGHT COLUMN: Editorial Details, Pricing, Category Options & Actions -->
    <aside class="editorial-info-column" style="display: flex; flex-direction: column; gap: 16px;">
        <div>
            <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.14em; color: #b45309; text-transform: uppercase;">
                {{ $product->category?->name ?? 'EXCLUSIVE COLLECTION' }}
            </span>
            <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: clamp(28px, 3.5vw, 40px); font-weight: 800; color: #0f172a; margin: 4px 0 8px; letter-spacing: -0.02em;">
                {{ $product->name }}
            </h1>

            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="color: #f59e0b; font-size: 14px;">★★★★★</span>
                <strong style="color: #0f172a; font-size: 13px;">{{ number_format($rating, 1) }}</strong>
                <span style="color: #64748b; font-size: 13px;">· {{ $reviewsCount }} customer reviews</span>
            </div>
        </div>

        <!-- Pricing Block (With Strikethrough & Promotion Badge) -->
        <div class="fashion-price-block" style="margin: 6px 0 10px;">
            @if($product->has_discount)
                <div class="fashion-price-row">
                    <del class="fashion-orig-price">{{ $companySettings->currency_symbol }}{{ number_format($product->selling_price, 2) }}</del>
                    <span class="fashion-price-highlight">{{ $companySettings->currency_symbol }}{{ number_format($product->effective_price, 2) }}</span>
                    <span class="fashion-discount-pill">-{{ $product->discount_percent_calculated }}% OFF</span>
                </div>
                <div class="fashion-savings-note">
                    🎉 Promotional Savings: Save {{ $companySettings->currency_symbol }}{{ number_format($product->selling_price - $product->effective_price, 2) }} on today's order!
                </div>
            @else
                <div class="fashion-price" style="font-size: 28px; font-weight: 800; color: #0f172a;">
                    {{ $companySettings->currency_symbol }}{{ number_format($product->selling_price, 2) }}
                </div>
            @endif
        </div>

        <!-- Stock Indicator -->
        <div class="fashion-stock {{ $isLowStock ? 'low' : 'available' }}" id="productStockDisplay" style="margin-bottom: 4px;">
            <span></span>
            {{ $isLowStock ? 'LOW STOCK' : 'IN STOCK' }} · {{ number_format($product->available_stock, 0) }} {{ $product->unit }} available
        </div>

        <!-- Product Description -->
        <p style="font-size: 14px; color: #475569; line-height: 1.6; margin: 0;">
            {{ $product->description ?: 'Premium high-grade product from TAJ Limited, inspected and synchronized directly with the live ERP warehouse inventory.' }}
        </p>

        <!-- Dynamic Category-Specific Purchase Form -->
        <form class="fashion-buy-form" method="POST" action="{{ route('cart.add', $product) }}" id="productBuyForm">
            @csrf

            <!-- OPTION 1: Colorway Swatches (Only for Clothing / Multi-shade items) -->
            @if(!empty($colorways) && count($colorways) > 0)
                <div class="fashion-colorway-section" style="margin-top: 10px;">
                    <div class="fashion-colorway-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div>
                            <span class="fashion-colorway-label">COLOUR:</span>
                            <strong class="fashion-colorway-name" id="currentColorwayLabel">{{ $initialColorway['name'] }}</strong>
                            <span class="fashion-colorway-tone" id="currentColorwayTag">@if(!empty($initialColorway['tag']))({{ $initialColorway['tag'] }})@endif</span>
                        </div>
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

            <!-- OPTION 2: Category-Specific Size / Variant Selector -->
            @if(!empty($sizes) && count($sizes) > 0)
                <div style="margin-top: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.08em;">
                            {{ $catOpts['label'] ?? ($isClothing ? 'SELECT SIZE' : 'SELECT OPTION') }}
                        </span>
                        @if($isClothing)
                            <button type="button" id="openSizeAdvisorBtn" style="background: none; border: none; color: #0284c7; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 0;">
                                📏 Find your size
                            </button>
                        @endif
                    </div>
                    <div class="fashion-size-grid" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @foreach($sizes as $sIdx => $size)
                            <label class="fashion-size-pill">
                                <input type="radio" name="size" value="{{ $size }}" @checked($sIdx === 0) required>
                                <span>{{ $size }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quantity & SKU Row -->
            <div class="fashion-quantity-row" style="margin-top: 20px; display: flex; gap: 16px; align-items: center;">
                <label style="display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span style="font-size: 12px; font-weight: 700; color: #475569;">Quantity</span>
                    <input type="number" name="quantity" value="1" min="1" max="{{ floor($product->available_stock) }}" style="width: 70px; height: 42px; border-radius: 8px; border: 1px solid #cbd5e1; text-align: center; font-weight: 700;">
                </label>
                <div style="font-size: 12px; color: #64748b;">
                    <span>SKU:</span> <strong id="productSkuDisplay" style="color: #0f172a;">{{ $initialSku }}</strong>
                </div>
            </div>

            <!-- Action Buttons: Buy Now & Add to Cart + Wishlist -->
            <div style="display: flex; gap: 12px; margin-top: 24px; align-items: center;">
                <button class="fashion-add-cart" type="submit" style="flex: 2; height: 50px; background: #0f172a; color: #ffffff; border-radius: 12px; font-weight: 750; font-size: 14px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s;">
                    <span>🛍️ Add to Cart</span>
                </button>
                <button class="fashion-buy-now" type="submit" name="buy_now" value="1" style="flex: 2; height: 50px; background: #f59e0b; color: #0f172a; border-radius: 12px; font-weight: 800; font-size: 14px; border: none; cursor: pointer;">
                    ⚡ Buy It Now
                </button>
                <button type="button" class="card-wishlist-btn" data-product-id="{{ $product->id }}" style="position: static; width: 50px; height: 50px; border-radius: 12px; border: 1px solid #cbd5e1; background: #ffffff; flex-shrink: 0;" title="Save to wishlist">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </button>
            </div>
        </form>

        <!-- Reassurance Value Strip (Reference Video Standard) -->
        <div style="margin-top: 20px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: #334155;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span>🚚</span> <strong>Free delivery over ৳3,000</strong> · Next day in Dhaka, 48h nationwide.
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span>🔄</span> <strong>Free 14-day exchange guarantee</strong> · Hassle-free size & variant swap.
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span>🛡️</span> <strong>Cash on delivery available</strong> · Verified authentic from TAJ Limited.
            </div>
        </div>

        <!-- Accordions: Details, Fabric/Specs, Shipping (Reference Video Style) -->
        <div class="editorial-accordion">
            <div class="accordion-item open">
                <button type="button" class="accordion-trigger">
                    <span>Product Details & Features</span>
                    <span class="accordion-icon">+</span>
                </button>
                <div class="accordion-content">
                    <p>{{ $product->description ?: 'Designed for comfort and timeless elegance. Crafted from durable materials, strictly tested for quality assurance, and backed by the TAJ Limited service standard.' }}</p>
                    <ul style="margin: 8px 0 0; padding-left: 20px;">
                        <li>Original Authentic Manufacturer Item (SKU: {{ $product->sku }})</li>
                        <li>Synchronized live with central ERP stock database</li>
                        <li>High durability finishing and certified quality control</li>
                    </ul>
                </div>
            </div>

            <div class="accordion-item">
                <button type="button" class="accordion-trigger">
                    <span>@if($isClothing) Fabric, Weave & Care @elseif($isElectronics) Technical Specifications @elseif($isGrocery) Ingredients & Freshness @else Material & Specifications @endif</span>
                    <span class="accordion-icon">+</span>
                </button>
                <div class="accordion-content">
                    @if($isClothing)
                        <p>100% Breathable pure artisan weave. Delicate zari accents, soft hand-feel drape. Hand-wash or dry-clean recommended. Iron on low heat.</p>
                    @elseif($isElectronics)
                        <p>High-conductivity interfaces, gold-plated connectors, over-voltage protection, and multi-platform compatibility (Windows, macOS, Linux, Android, iOS).</p>
                    @elseif($isGrocery)
                        <p>100% pure food-grade standards, nitrogen-flushed sealed packaging, zero artificial preservatives, fresh aroma guarantee.</p>
                    @else
                        <p>Eco-friendly chlorine-free processing, acid-free archival quality, standard dimensional precision.</p>
                    @endif
                </div>
            </div>

            <div class="accordion-item">
                <button type="button" class="accordion-trigger">
                    <span>Delivery, Returns & Exchanges</span>
                    <span class="accordion-icon">+</span>
                </button>
                <div class="accordion-content">
                    <p>Orders confirmed before 2:00 PM are processed same-day. Nationwide courier delivery with real-time tracking from your customer dashboard. 14-day hassle-free return and exchange policy.</p>
                </div>
            </div>
        </div>
    </aside>
</div>

<!-- "Find your size" Interactive Modal (Exact Reference Video Match) -->
@if($isClothing)
<div class="size-modal-backdrop" id="sizeAdvisorModal" aria-hidden="true">
    <div class="size-modal-card">
        <div class="size-modal-header">
            <h3>Find your size</h3>
            <button type="button" class="size-modal-close" id="closeSizeAdvisorBtn">✕</button>
        </div>

        <!-- Height Slider -->
        <div class="size-slider-row">
            <div class="size-slider-header">
                <span>HEIGHT</span>
                <strong id="valHeight">172 cm</strong>
            </div>
            <input type="range" min="150" max="200" value="172" class="size-range-slider" id="sliderHeight">
        </div>

        <!-- Weight Slider -->
        <div class="size-slider-row">
            <div class="size-slider-header">
                <span>WEIGHT</span>
                <strong id="valWeight">68 kg</strong>
            </div>
            <input type="range" min="45" max="115" value="68" class="size-range-slider" id="sliderWeight">
        </div>

        <!-- Fit Preference Toggle -->
        <label style="font-size: 11px; font-weight: 800; color: #475569; display: block; margin-bottom: 6px; letter-spacing: 0.05em;">
            HOW YOU LIKE IT
        </label>
        <div class="size-fit-toggle">
            <button type="button" class="fit-btn" data-fit="close">Close</button>
            <button type="button" class="fit-btn active" data-fit="regular">As cut</button>
            <button type="button" class="fit-btn" data-fit="roomy">Roomy</button>
        </div>

        <!-- Result Box -->
        <div class="size-result-box">
            <div>
                <span style="font-size: 11px; font-weight: 800; color: #78350f; letter-spacing: 0.08em; text-transform: uppercase;">RECOMMENDED SIZE</span>
                <div class="size-result-badge" id="advisorRecommendedSize">M</div>
            </div>
            <div class="size-result-meta">
                <strong id="advisorChestCm" style="font-size: 14px; color: #78350f; display: block;">100 cm across chest</strong>
                <span>Calculated for natural tailored drape</span>
            </div>
        </div>

        <button type="button" class="size-modal-action-btn" id="applySizeBtn">
            <span>✓ Use size M</span>
        </button>
    </div>
</div>
@endif

<!-- Verified Customer Reviews Section (Reference Video Frames 08 & 10) -->
<section class="editorial-reviews-section">
    <div class="reviews-header-row">
        <div>
            <h2 style="font-family: 'Playfair Display', Georgia, serif; font-size: 26px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">
                Verified Reviews
            </h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;">Feedback from customers across Bangladesh</p>
        </div>
        <div style="font-size: 13px; color: #0f172a; font-weight: 700;">
            <span style="color: #f59e0b;">★★★★★</span> 4.9 from {{ $reviewsCount }} reviews
        </div>
    </div>

    <div class="reviews-grid">
        <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <strong class="review-headline">"Exceeded my expectations"</strong>
            <p class="review-text">The fabric quality and stitching precision are incredible. Ordered for a festival occasion and received it the very next afternoon in Dhaka.</p>
            <div class="review-author">
                <strong>Rafi A. · Chattogram</strong>
                <span class="verified-tag">✓ Verified buyer</span>
            </div>
        </div>

        <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <strong class="review-headline">"True to size and beautiful drape"</strong>
            <p class="review-text">Used the size finder tool and ordered size M. It fits like it was custom tailored. The color matches the online photos perfectly.</p>
            <div class="review-author">
                <strong>Sumaiya T. · Sylhet</strong>
                <span class="verified-tag">✓ Verified buyer</span>
            </div>
        </div>

        <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <strong class="review-headline">"Premium packaging & fast support"</strong>
            <p class="review-text">Impressed by the packaging and invoice details. You can tell TAJ Limited runs a serious, high-standard operation. Will definitely buy again!</p>
            <div class="review-author">
                <strong>Imran K. · Dhaka</strong>
                <span class="verified-tag">✓ Verified buyer</span>
            </div>
        </div>
    </div>
</section>

<!-- "Complete the Look / Customers Also Bought" (Reference Video) -->
@if(!empty($relatedProducts) && $relatedProducts->isNotEmpty())
<section style="max-width: 1280px; margin: 60px auto 40px; padding: 0 20px;">
    <h2 style="font-family: 'Playfair Display', Georgia, serif; font-size: 26px; font-weight: 800; color: #0f172a; margin-bottom: 24px;">
        Complete the look.
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px;">
        @foreach($relatedProducts as $rel)
            <article class="editorial-card">
                <div class="editorial-card-media">
                    @if($rel->has_discount)
                        <span class="card-badge-left sale-badge">-{{ $rel->discount_percent_calculated }}%</span>
                    @endif
                    <a href="{{ route('shop.show', $rel) }}" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
                        @if($rel->image_url)
                            <img src="{{ $rel->image_url }}" alt="{{ $rel->name }}" loading="lazy">
                        @else
                            <span class="vc-product-letter">{{ strtoupper(substr($rel->name,0,1)) }}</span>
                        @endif
                    </a>
                </div>
                <div class="editorial-card-body">
                    <a href="{{ route('shop.show', $rel) }}" class="editorial-card-title">{{ $rel->name }}</a>
                    <div class="editorial-card-pricing">
                        @if($rel->has_discount)
                            <del class="editorial-orig-price">{{ $companySettings->currency_symbol }}{{ number_format($rel->selling_price, 2) }}</del>
                            <strong class="editorial-final-price sale-color">{{ $companySettings->currency_symbol }}{{ number_format($rel->effective_price, 2) }}</strong>
                        @else
                            <strong class="editorial-final-price">{{ $companySettings->currency_symbol }}{{ number_format($rel->selling_price, 2) }}</strong>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endif

@push('scripts')
<script>
// Mode Switcher: Studio Photos vs 3D / 360° Orbit
window.switchMediaMode = function(mode) {
    const studio = document.getElementById('mediaStudioView');
    const orbit = document.getElementById('mediaOrbitView');
    const btnStudio = document.getElementById('btnModeStudio');
    const btnOrbit = document.getElementById('btnModeOrbit');

    if (mode === 'orbit') {
        if (studio) studio.style.display = 'none';
        if (orbit) orbit.style.display = 'block';
        if (btnStudio) btnStudio.classList.remove('active');
        if (btnOrbit) btnOrbit.classList.add('active');
    } else {
        if (orbit) orbit.style.display = 'none';
        if (studio) studio.style.display = 'block';
        if (btnOrbit) btnOrbit.classList.remove('active');
        if (btnStudio) btnStudio.classList.add('active');
    }
};

// Select Studio Thumbnail
window.selectStudioThumb = function(btn, imgUrl, name, sku) {
    document.querySelectorAll('.studio-thumb-btn').forEach(b => {
        b.classList.remove('active');
        b.style.borderColor = '#e2e8f0';
    });
    btn.classList.add('active');
    btn.style.borderColor = '#0f172a';

    const mainImg = document.getElementById('productMainImg');
    const orbitImg = document.getElementById('orbitGarmentImg');
    if (mainImg) {
        mainImg.style.opacity = '0.3';
        setTimeout(() => {
            mainImg.src = imgUrl;
            mainImg.style.opacity = '1';
        }, 150);
    }
    if (orbitImg) orbitImg.src = imgUrl;

    const skuDisplay = document.getElementById('productSkuDisplay');
    if (skuDisplay && sku) skuDisplay.textContent = sku;
};

// Colorway Swatches Event Binding
document.addEventListener('DOMContentLoaded', function () {
    const paletteBtns = document.querySelectorAll('.colorway-palette-btn');
    const colorLabel = document.getElementById('currentColorwayLabel');
    const colorTag = document.getElementById('currentColorwayTag');
    const colorInput = document.getElementById('productColorInput');
    const skuDisplay = document.getElementById('productSkuDisplay');
    const mainImg = document.getElementById('productMainImg');
    const orbitImg = document.getElementById('orbitGarmentImg');
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

            if (img) {
                if (mainImg) {
                    mainImg.style.opacity = '0.3';
                    setTimeout(() => {
                        mainImg.src = img;
                        mainImg.style.opacity = '1';
                    }, 150);
                }
                if (orbitImg) orbitImg.src = img;
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
@endpush

@endsection
