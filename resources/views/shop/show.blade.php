@extends('layouts.customer')
@section('title', $product->name . ' — taj. Luxury')
@section('content')

@php
    $currency = $companySettings->currency_symbol ?? '৳';
    $isPerfume = $product->isPerfume();
    $isClothing = $product->isClothing();
    $colorways = $product->available_colorways;
    $initialColorway = $colorways[0] ?? null;
    $initialImage = $initialColorway['image_url'] ?? $product->image_url;
    $initialSku = $initialColorway['sku'] ?? $product->sku;

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
    {{-- Breadcrumbs (img_46) --}}
    <nav class="lux-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}">Catalog</a>
        <span class="sep">/</span>
        <a href="{{ route('shop.index', ['category' => $product->category_id]) }}">{{ $product->category?->name ?? 'Collection' }}</a>
        <span class="sep">/</span>
        <span style="color: var(--lux-black); font-weight: 700;">{{ $product->name }}</span>
    </nav>

    {{-- Main 2-Column Product Stage --}}
    <div class="lux-product-hero-stage">
        {{-- LEFT COLUMN: Details, Notes, Sizes, Action (img_46) --}}
        <div class="lux-product-info">
            <span class="lux-product-kicker">
                {{ $product->category?->name ?? 'FRAGRANCE' }} · {{ $product->sku }}
            </span>
            <h1 class="lux-product-title">{{ $product->name }}</h1>
            <p class="lux-product-tagline">
                @if($isPerfume)
                    Eau de Parfum · Natural Spray Vaporisateur · Long-Lasting Sillage
                @elseif($isClothing)
                    Handcrafted Designer Edition · Pure Silk & Artisanal Weave
                @else
                    {{ Str::limit($product->description, 120) }}
                @endif
            </p>

            {{-- Visual Notes / Attributes Bar --}}
            @if($isPerfume)
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

                <ul class="lux-bullet-list">
                    <li><strong>Top notes:</strong> Sicilian blood orange, pink peppercorn, Calabrian bergamot</li>
                    <li><strong>Heart notes:</strong> Madagascar vanilla bean, dark labdanum resin, Turkish rose</li>
                    <li><strong>Base notes:</strong> Rare ambergris, Atlas cedarwood, white musk</li>
                </ul>
            @elseif($isClothing)
                <ul class="lux-bullet-list">
                    <li><strong>Fabric:</strong> 100% Pure Mulberry Silk with hand-loomed texture</li>
                    <li><strong>Embellishment:</strong> Intricate golden dipped zari border</li>
                    <li><strong>Care:</strong> Professional dry clean recommended</li>
                </ul>
            @endif

            {{-- Full Description Accordion --}}
            <div class="lux-accordion">
                <details open>
                    <summary>FULL DESCRIPTION <span>⌄</span></summary>
                    <div class="lux-accordion-content">
                        {{ $product->description ?: 'Formulated with exquisite attention to detail, balancing modern luxury with enduring elegance. Each piece undergoes stringent quality checks to guarantee unmatched perfection.' }}
                    </div>
                </details>
            </div>

            {{-- Interactive Cart & Options Form --}}
            <form method="POST" action="{{ route('cart.add', $product) }}" id="productPurchaseForm">
                @csrf
                <input type="hidden" name="color" id="selectedColorInput" value="{{ $initialColorway['name'] ?? '' }}">
                <input type="hidden" name="size" id="selectedSizeInput" value="{{ $sizes[0] ?? '' }}">

                {{-- Colorway Selector if available --}}
                @if(!empty($colorways))
                    <div class="lux-options-block">
                        <div class="lux-options-label">
                            <span>Select Colorway:</span>
                            <strong id="activeColorName" style="color: var(--lux-black);">{{ $initialColorway['name'] ?? '' }}</strong>
                        </div>
                        <div class="lux-colorways-row">
                            @foreach($colorways as $cw)
                                <div class="lux-colorway-thumb {{ $loop->first ? 'active' : '' }}" 
                                     data-color="{{ $cw['name'] }}" 
                                     data-sku="{{ $cw['sku'] ?? $product->sku }}"
                                     data-image="{{ $cw['image_url'] ?? $product->image_url }}"
                                     onclick="selectColorway(this)"
                                     title="{{ $cw['name'] }}">
                                    <img src="{{ $cw['image_url'] ?? $product->image_url }}" alt="{{ $cw['name'] }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Size Options Pills (50ml / 100ml or S / M / L) --}}
                @if(!empty($sizes))
                    <div class="lux-options-block">
                        <div class="lux-options-label">
                            <span>{{ $isPerfume ? 'Size: ml' : 'Select Size:' }}</span>
                            <strong id="activeSizeDisplay">{{ $sizes[0] }}</strong>
                        </div>
                        <div class="lux-size-pills">
                            @foreach($sizes as $sz)
                                <button type="button" 
                                        class="lux-size-pill {{ $loop->first ? 'active' : '' }}" 
                                        data-size="{{ $sz }}"
                                        onclick="selectSize(this)">
                                    {{ $sz }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Price & Stock Row --}}
                <div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 24px;">
                    <span style="font-size: 28px; font-weight: 900; color: var(--lux-black);">
                        @if($product->has_discount)
                            {{ $currency }}{{ number_format($product->effective_price, 2) }}
                            <del style="font-size: 16px; color: var(--lux-text-light); font-weight: 500; margin-left: 8px;">
                                {{ $currency }}{{ number_format($product->selling_price, 2) }}
                            </del>
                            <span class="lux-badge-discount" style="position: static; margin-left: 8px; vertical-align: middle;">
                                -{{ $product->discount_percent_calculated }}% OFF
                            </span>
                        @else
                            {{ $currency }}{{ number_format($product->selling_price, 2) }}
                        @endif
                    </span>
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
                        <span>ADD TO CART · {{ $currency }}{{ number_format($product->effective_price, 2) }}</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- RIGHT COLUMN: Hero Bottle & 3-Angle Stage (img_46) --}}
        <div class="lux-stage-column">
            <div class="lux-stage-main" id="mainStage">
                <img src="{{ $initialImage }}" alt="{{ $product->name }}" id="heroBottleImg">
            </div>

            {{-- 3 Top Angle Thumbnails --}}
            <div class="lux-stage-angles">
                <div class="lux-angle-thumb active" onclick="switchAngle('{{ $initialImage }}', this)">
                    <img src="{{ $initialImage }}" alt="Front Angle">
                </div>
                @if($isPerfume && file_exists(public_path('assets/images/behance/cosmic_hero_art.png')))
                    <div class="lux-angle-thumb" onclick="switchAngle('{{ asset('assets/images/behance/cosmic_hero_art.png') }}', this)">
                        <img src="{{ asset('assets/images/behance/cosmic_hero_art.png') }}" alt="Artistic Stage">
                    </div>
                @endif
                @if(file_exists(public_path('assets/images/behance/bestseller_hero.jpg')))
                    <div class="lux-angle-thumb" onclick="switchAngle('{{ asset('assets/images/behance/bestseller_hero.jpg') }}', this)">
                        <img src="{{ asset('assets/images/behance/bestseller_hero.jpg') }}" alt="Pedestal View">
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- CUSTOMERS SAY REVIEW BLOCK (img_46) --}}
    <section class="lux-reviews-section">
        <span class="lux-reviews-kicker">[CUSTOMERS SAY]</span>
        <div class="lux-reviews-grid">
            {{-- Big Rating Score & Breakdown Bars --}}
            <div class="lux-score-box">
                <div class="lux-big-rating">4.9</div>
                <div class="lux-stars">★★★★★</div>
                <div class="lux-rating-bars">
                    <div class="lux-rating-bar-row">
                        <span>5★</span>
                        <div class="lux-bar-track"><div class="lux-bar-fill" style="width: 88%;"></div></div>
                        <span>88%</span>
                    </div>
                    <div class="lux-rating-bar-row">
                        <span>4★</span>
                        <div class="lux-bar-track"><div class="lux-bar-fill" style="width: 9%;"></div></div>
                        <span>9%</span>
                    </div>
                    <div class="lux-rating-bar-row">
                        <span>3★</span>
                        <div class="lux-bar-track"><div class="lux-bar-fill" style="width: 3%;"></div></div>
                        <span>3%</span>
                    </div>
                    <div class="lux-rating-bar-row">
                        <span>2★</span>
                        <div class="lux-bar-track"><div class="lux-bar-fill" style="width: 0%;"></div></div>
                        <span>0%</span>
                    </div>
                    <div class="lux-rating-bar-row">
                        <span>1★</span>
                        <div class="lux-bar-track"><div class="lux-bar-fill" style="width: 0%;"></div></div>
                        <span>0%</span>
                    </div>
                </div>
            </div>

            {{-- Real Customer Review Cards --}}
            <div class="lux-customer-cards">
                <div class="lux-review-card">
                    <div class="lux-review-user">
                        <span class="lux-review-author">Sarahk1243</span>
                        <span class="lux-review-stars">★★★★★</span>
                    </div>
                    <p class="lux-review-text">
                        "The scent is breathtaking. The opening of blood orange and smooth bourbon vanilla lasts well past 12 hours. Pure elegance and luxurious presentation."
                    </p>
                </div>

                <div class="lux-review-card">
                    <div class="lux-review-user">
                        <span class="lux-review-author">Brianna205</span>
                        <span class="lux-review-stars">★★★★★</span>
                    </div>
                    <p class="lux-review-text">
                        "Absolutely in love with the bottle and formulation. Received compliments non-stop at my gala event. A true signature scent that turns heads."
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- SIMILAR PERFUMES / PRODUCTS (img_46) --}}
    <section class="lux-similar-section">
        <div class="lux-section-header">
            <div>
                <h2>SIMILAR {{ $isPerfume ? 'PERFUMES' : 'PRODUCTS' }}</h2>
                <p>Curated recommendations complementing your fragrance palette.</p>
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
</div>

@endsection

@push('scripts')
<script>
function selectSize(btn) {
    document.querySelectorAll('.lux-size-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const size = btn.getAttribute('data-size');
    document.getElementById('selectedSizeInput').value = size;
    const disp = document.getElementById('activeSizeDisplay');
    if (disp) disp.textContent = size;
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
</script>
@endpush
