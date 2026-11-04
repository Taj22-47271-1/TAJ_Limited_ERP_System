@php
    $showcaseItems = ($clothingProducts ?? collect([$product ?? null]))->filter()->values();
@endphp

@if($showcaseItems->isNotEmpty())
<section class="atelier-luxury-section" id="clothingShowcaseSection">
    <div class="atelier-header">
        <div class="atelier-brand-tag">
            <span class="atelier-gold-dot"></span>
            <span>TAJ ATELIER · 3D LUXURY CLOTHING SHOWCASE</span>
        </div>
        <div class="atelier-scene-nav" role="tablist">
            <button type="button" class="atelier-tab-btn active" data-scene="turntable">
                <span class="tab-icon">✨</span> 3D Stage
            </button>
            <button type="button" class="atelier-tab-btn" data-scene="loupe">
                <span class="tab-icon">🔍</span> Fabric Loupe
            </button>
            <button type="button" class="atelier-tab-btn" data-scene="lookbook">
                <span class="tab-icon">📖</span> Lookbook
            </button>
            <button type="button" class="atelier-tab-btn" data-scene="morph">
                <span class="tab-icon">🎨</span> Variant Morph
            </button>
        </div>
    </div>

    @foreach($showcaseItems as $index => $cProduct)
    @php
        $sizes = $cProduct->normalizedSizes();
        $isFirst = $index === 0;
    @endphp
    <div class="atelier-product-slide {{ $isFirst ? 'active' : '' }}" data-product-index="{{ $index }}" data-product-id="{{ $cProduct->id }}">
        
        <!-- Scene 1 & 2: 3D Turntable Podium Stage -->
        <div class="atelier-scene atelier-scene-turntable active">
            <div class="atelier-layout">
                <!-- Left Editorial Details -->
                <div class="atelier-info-panel">
                    <span class="atelier-kicker">{{ $cProduct->category?->name ?? 'DESIGNER APPAREL' }}</span>
                    <h2 class="atelier-title">{{ $cProduct->name }}</h2>
                    <p class="atelier-desc">{{ $cProduct->description ?: 'Exclusive tailored apparel engineered with breathable craftsmanship, luxurious hand-feel, and timeless drape.' }}</p>
                    
                    <div class="atelier-price-row">
                        <span class="atelier-price">{{ $companySettings->currency_symbol }}{{ number_format($cProduct->selling_price, 2) }}</span>
                        <span class="atelier-stock-badge">
                            <span class="pulse-dot"></span> IN STOCK · {{ number_format($cProduct->available_stock, 0) }} {{ $cProduct->unit }}
                        </span>
                    </div>

                    <!-- Quick Size Selector & Add to Cart -->
                    <form method="POST" action="{{ route('cart.add', $cProduct) }}" class="atelier-buy-form">
                        @csrf
                        <div class="atelier-size-group">
                            <label>SELECT SIZE</label>
                            <div class="atelier-sizes">
                                @foreach($sizes as $sIdx => $size)
                                    <label class="atelier-size-pill">
                                        <input type="radio" name="size" value="{{ $size }}" @checked($sIdx === 0) required>
                                        <span>{{ $size }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="atelier-actions">
                            <button type="submit" class="atelier-add-btn">
                                <span>Add to Cart</span>
                            </button>
                            <button type="submit" name="buy_now" value="1" class="atelier-buynow-btn">
                                <span>Buy It Now</span>
                            </button>
                        </div>
                    </form>

                    <!-- Features bullets -->
                    <div class="atelier-features">
                        <div><span>🌿</span> 100% Breathable Weave</div>
                        <div><span>✨</span> Golden Zari & Fine Stitching</div>
                        <div><span>🚚</span> Express Secure Delivery</div>
                    </div>

                    <!-- Carousel Controls -->
                    @if($showcaseItems->count() > 1)
                    <div class="atelier-controls">
                        <button type="button" class="atelier-arrow-btn atelier-prev-btn" aria-label="Previous item">←</button>
                        <span class="atelier-counter"><strong class="curr-idx">{{ sprintf('%02d', $index + 1) }}</strong> / {{ sprintf('%02d', $showcaseItems->count()) }}</span>
                        <button type="button" class="atelier-arrow-btn atelier-next-btn" aria-label="Next item">→</button>
                    </div>
                    @endif
                </div>

                <!-- Right 3D Stage with Glowing Halo Ring & Turntable Podium -->
                <div class="atelier-stage-container" id="stageContainer-{{ $cProduct->id }}">
                    <div class="atelier-halo-ring"></div>
                    <div class="atelier-shimmer-particles"></div>
                    
                    <!-- 3D Perspective Turntable Stage -->
                    <div class="atelier-3d-turntable">
                        <div class="atelier-podium-base">
                            <div class="atelier-podium-ring"></div>
                            <div class="atelier-podium-shadow"></div>
                        </div>
                        
                        <!-- Mannequin Garment -->
                        <div class="atelier-garment-wrapper">
                            <img src="{{ $cProduct->image_url }}" alt="{{ $cProduct->name }}" class="atelier-garment-img" id="garmentImg-{{ $cProduct->id }}">
                            
                            <!-- Interactive Hotspots (as in video frame_00 & frame_05) -->
                            <div class="atelier-hotspot" style="top: 24%; left: 36%;" data-target="card-fabric-{{ $cProduct->id }}">
                                <span class="hotspot-pin"></span>
                                <span class="hotspot-ripple"></span>
                            </div>
                            <div class="atelier-hotspot-card" id="card-fabric-{{ $cProduct->id }}" style="top: 14%; left: 4%; width: 230px;">
                                <strong>Mulberry Silk & Weave</strong>
                                <small>Handwoven 4-ply organic silk with exquisite fluid drape and breathable featherlight softness.</small>
                            </div>

                            <div class="atelier-hotspot" style="top: 50%; left: 56%;" data-target="card-zari-{{ $cProduct->id }}">
                                <span class="hotspot-pin"></span>
                                <span class="hotspot-ripple"></span>
                            </div>
                            <div class="atelier-hotspot-card" id="card-zari-{{ $cProduct->id }}" style="top: 40%; right: 4%; width: 230px;">
                                <strong>Artisan Zari Embroidery</strong>
                                <small>Intricate gold-thread woven border crafted by master artisans with heirloom royal finish.</small>
                            </div>

                            <div class="atelier-hotspot" style="top: 74%; left: 32%;" data-target="card-silhouette-{{ $cProduct->id }}">
                                <span class="hotspot-pin"></span>
                                <span class="hotspot-ripple"></span>
                            </div>
                            <div class="atelier-hotspot-card" id="card-silhouette-{{ $cProduct->id }}" style="top: 66%; left: 2%; width: 220px;">
                                <strong>Regal Fall & Silhouette</strong>
                                <small>Precision pleating engineered for effortless modern movement and timeless elegance.</small>
                            </div>

                            <div class="atelier-hotspot" style="top: 86%; left: 62%;" data-target="card-care-{{ $cProduct->id }}">
                                <span class="hotspot-pin"></span>
                                <span class="hotspot-ripple"></span>
                            </div>
                            <div class="atelier-hotspot-card" id="card-care-{{ $cProduct->id }}" style="top: 76%; right: 2%; width: 210px;">
                                <strong>Heritage Longevity</strong>
                                <small>Colorfast natural dyes · Pre-shrunk weave · Designed to last across generations.</small>
                            </div>
                        </div>
                    </div>

                    <!-- 3D Stage Hint -->
                    <div class="atelier-stage-hint">
                        <span>✦ Move cursor or drag to rotate 3D view</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scene 3 & 4: Fabric Texture Loupe (as in video frame_04) -->
        <div class="atelier-scene atelier-scene-loupe">
            <div class="atelier-loupe-view">
                <div class="atelier-loupe-intro">
                    <span class="atelier-kicker">MACRO FABRIC INSPECTION</span>
                    <h3>Microscopic Weave & Thread Analysis</h3>
                    <p>Examine the fine zari threads, warp-and-weft silk density, and seam precision under ultra-high definition zoom.</p>
                </div>
                <div class="atelier-loupe-stage">
                    <div class="atelier-loupe-lens">
                        <img src="{{ asset('assets/images/products/saree-fabric-texture.jpg') }}" alt="Fabric Weave Texture" class="atelier-loupe-texture">
                        <div class="loupe-reticle"></div>
                    </div>
                    <div class="atelier-loupe-specs">
                        <div class="spec-pill"><strong>Weave Density:</strong> 320 Threads/Inch</div>
                        <div class="spec-pill"><strong>Zari Type:</strong> Golden Dipped Zari</div>
                        <div class="spec-pill"><strong>Fiber:</strong> 100% Pure Mulberry Silk</div>
                        <div class="spec-pill"><strong>Finish:</strong> Hand-Polished Soft Lustre</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scene 5 & 6: Editorial Floating Lookbook Cards (as in video frame_06) -->
        <div class="atelier-scene atelier-scene-lookbook">
            <div class="atelier-lookbook-header">
                <span class="atelier-kicker">EDITORIAL LOOKBOOK</span>
                <h3>The Curated Collection</h3>
                <p>Styling perspectives and silhouette captures.</p>
            </div>
            <div class="atelier-lookbook-grid">
                <div class="lookbook-card lookbook-card-1">
                    <span class="lookbook-num">01</span>
                    <div class="lookbook-art"><img src="{{ $cProduct->image_url }}" alt="{{ $cProduct->name }}"></div>
                    <div class="lookbook-caption"><strong>Primary Drape</strong><small>Full Silhouette</small></div>
                </div>
                <div class="lookbook-card lookbook-card-2">
                    <span class="lookbook-num">02</span>
                    <div class="lookbook-art"><img src="{{ asset('assets/images/products/saree-fabric-texture.jpg') }}" alt="Texture"></div>
                    <div class="lookbook-caption"><strong>Zari Border</strong><small>Heirloom Motif</small></div>
                </div>
                <div class="lookbook-card lookbook-card-3">
                    <span class="lookbook-num">04</span>
                    <div class="lookbook-art"><img src="{{ $cProduct->image_url }}" alt="Styling" style="filter: brightness(1.08) contrast(1.05);"></div>
                    <div class="lookbook-caption"><strong>Contemporary Cut</strong><small>Royal Fall</small></div>
                </div>
                <div class="lookbook-card lookbook-card-4">
                    <span class="lookbook-num">05</span>
                    <div class="lookbook-art"><img src="{{ $cProduct->image_url }}" alt="Elegance"></div>
                    <div class="lookbook-caption"><strong>Evening Mood</strong><small>Fluid Movement</small></div>
                </div>
            </div>
        </div>

        <!-- Scene 7 & 8: Variant Morph (as in video frame_07, frame_08) -->
        <div class="atelier-scene atelier-scene-morph">
            <div class="atelier-morph-layout">
                <div class="atelier-morph-copy">
                    <span class="atelier-kicker">DYNAMIC COLORWAYS</span>
                    <h3>Silk Flow & Variant Morph</h3>
                    <p>Select any signature shade below. The garment and background lighting adapt dynamically in real-time.</p>
                    
                    <div class="atelier-swatches">
                        <button type="button" class="swatch-btn active" data-color="rose" data-title="Champagne Rose" style="--swatch-color: #e5b2a2;"></button>
                        <button type="button" class="swatch-btn" data-color="sapphire" data-title="Royal Sapphire" style="--swatch-color: #2b59c3;"></button>
                        <button type="button" class="swatch-btn" data-color="noir" data-title="Midnight Noir" style="--swatch-color: #1a1a24;"></button>
                        <button type="button" class="swatch-btn" data-color="emerald" data-title="Imperial Emerald" style="--swatch-color: #1a7f53;"></button>
                        <button type="button" class="swatch-btn" data-color="gold" data-title="Regal Gold" style="--swatch-color: #d4af37;"></button>
                    </div>
                    <strong class="selected-swatch-name">Champagne Rose (Selected)</strong>
                </div>

                <div class="atelier-morph-stage">
                    <div class="atelier-morph-ring"></div>
                    <img src="{{ $cProduct->image_url }}" alt="{{ $cProduct->name }}" class="atelier-morph-garment">
                </div>
            </div>
        </div>

    </div>
    @endforeach
</section>
@endif
