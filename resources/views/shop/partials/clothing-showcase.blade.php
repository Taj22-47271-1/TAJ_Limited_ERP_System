@php
    $showcaseSettings = $companySettings->getShowcaseSettings();
    $showcaseItems = $clothingProducts->filter(fn($p) => $p->status && $p->available_stock > 0);
@endphp

@if(($showcaseSettings['enabled'] ?? true) && $showcaseItems->isNotEmpty())
<section class="atelier-showcase-shell" id="atelierShowcase" aria-label="3D Luxury Garment Atelier">
    <!-- Showcase Top Header & Scene Navigation Tabs -->
    <div class="atelier-topbar">
        <div class="atelier-brand-pill">
            <span class="live-dot"></span>
            <strong>{{ $showcaseSettings['tagline'] ?? 'TAJ ATELIER · 3D LUXURY CLOTHING SHOWCASE' }}</strong>
        </div>

        <div class="atelier-scene-tabs" role="tablist">
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
        $cfg = $cProduct->getEffectiveShowcaseConfig($companySettings);
        $enable3D = $cfg['enable_3d'] ?? true;
        $swatches = $cProduct->available_colorways;
        if (empty($swatches)) {
            $swatches = $cfg['swatches'] ?? [];
        }
        $firstSwatchImg = !empty($swatches[0]['image_url']) ? $swatches[0]['image_url'] : $cProduct->image_url;
    @endphp
    <div class="atelier-product-slide {{ $isFirst ? 'active' : '' }}" data-product-index="{{ $index }}" data-product-id="{{ $cProduct->id }}">
        
        <!-- Scene 1: Turntable Stage / Studio Stage -->
        <div class="atelier-scene atelier-scene-turntable active">
            <div class="atelier-layout">
                <!-- Left Editorial Details -->
                <div class="atelier-info-panel">
                    <span class="atelier-kicker">{{ $cProduct->category?->name ?? 'DESIGNER APPAREL' }}</span>
                    <h2 class="atelier-title">{{ $cProduct->name }}</h2>
                    <p class="atelier-desc">{{ $cProduct->description ?: 'Exclusive tailored apparel engineered with breathable craftsmanship, luxurious hand-feel, and timeless drape.' }}</p>
                    
                    <div class="atelier-price-row">
                        @if($cProduct->has_discount)
                            <del style="font-size: 16px; color: #94a3b8; margin-right: 6px;">{{ $companySettings->currency_symbol }}{{ number_format($cProduct->selling_price, 2) }}</del>
                            <span class="atelier-price" style="color: #fbbf24;">{{ $companySettings->currency_symbol }}{{ number_format($cProduct->effective_price, 2) }}</span>
                            <span style="background: linear-gradient(90deg, #f43f5e, #e11d48); color: #fff; font-size: 10px; font-weight: 850; padding: 3px 8px; border-radius: 999px; margin-left: 6px; letter-spacing: 0.05em;">-{{ $cProduct->discount_percent_calculated }}% OFF</span>
                        @else
                            <span class="atelier-price">{{ $companySettings->currency_symbol }}{{ number_format($cProduct->selling_price, 2) }}</span>
                        @endif
                        <span class="atelier-stock-badge">
                            <span class="pulse-dot"></span> IN STOCK · {{ number_format($cProduct->available_stock, 0) }} {{ $cProduct->unit }}
                        </span>
                    </div>

                    <!-- Quick Size & Colorway Selector & Add to Cart -->
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

                        <!-- Colorways / Swatches on Main Stage -->
                        @if(!empty($swatches))
                        <div class="atelier-stage-swatches" style="margin-top: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <label style="font-size: 10px; letter-spacing: 0.12em; font-weight: 800; color: #94a3b8; display: block; margin: 0;">SELECT COLORWAY</label>
                                <span class="active-color-name" id="colorLabel-{{ $cProduct->id }}" style="font-size: 11px; color: #fbbf24; font-weight: 700;">{{ $swatches[0]['name'] ?? '' }}</span>
                            </div>
                            <div class="atelier-swatches stage-swatches" style="display: flex; gap: 9px; align-items: center; flex-wrap: wrap;">
                                @foreach($swatches as $swIdx => $sw)
                                    <button type="button" 
                                            class="swatch-btn {{ $swIdx === 0 ? 'active' : '' }}" 
                                            data-color="{{ $sw['key'] ?? 'color_'.$swIdx }}" 
                                            data-title="{{ $sw['name'] }}" 
                                            data-image="{{ $sw['image_url'] ?? asset($sw['image'] ?? '') }}" 
                                            data-sku="{{ $sw['sku'] ?? $cProduct->sku }}"
                                            data-target-product="{{ $cProduct->id }}" 
                                            style="--swatch-color: {{ $sw['color'] }}; background-color: {{ $sw['color'] }}; width: 28px; height: 28px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.4); cursor: pointer;" 
                                            title="{{ $sw['name'] }}"></button>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <input type="hidden" name="color" id="stageColor-{{ $cProduct->id }}" value="{{ $swatches[0]['name'] ?? '' }}">

                        <div class="atelier-actions" style="margin-top: 18px;">
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
                        <div><span>🌿</span> {{ $cfg['features']['feature_1'] ?? '100% Breathable Weave' }}</div>
                        <div><span>✨</span> {{ $cfg['features']['feature_2'] ?? 'Golden Zari & Fine Stitching' }}</div>
                        <div><span>🚚</span> {{ $cfg['features']['feature_3'] ?? 'Express Secure Delivery' }}</div>
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

                <!-- Right Stage -->
                <div class="atelier-stage-container {{ $enable3D ? '' : 'no-3d-stage' }}" id="stageContainer-{{ $cProduct->id }}" data-enable-3d="{{ $enable3D ? '1' : '0' }}">
                    @if($enable3D)
                        <div class="atelier-halo-ring"></div>
                        <div class="atelier-shimmer-particles"></div>
                    @endif
                    
                    <div class="atelier-3d-turntable">
                        @if($enable3D)
                            <div class="atelier-podium-base">
                                <div class="atelier-podium-ring"></div>
                                <div class="atelier-podium-shadow"></div>
                            </div>
                        @endif
                        
                        <!-- Mannequin Garment -->
                        <div class="atelier-garment-wrapper">
                            <img src="{{ $firstSwatchImg }}" alt="{{ $cProduct->name }}" class="atelier-garment-img" id="garmentImg-{{ $cProduct->id }}" style="transition: opacity 0.35s ease, transform 0.4s ease;">
                            
                            <!-- Interactive Hotspots -->
                            @foreach(($cfg['hotspots'] ?? []) as $hIdx => $spot)
                            <div class="atelier-hotspot" style="top: {{ $spot['top'] }}%; left: {{ $spot['left'] }}%;" data-target="card-spot-{{ $cProduct->id }}-{{ $hIdx }}">
                                <span class="hotspot-pin"></span>
                                <span class="hotspot-ripple"></span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Hotspot Overlay Cards -->
                    @foreach(($cfg['hotspots'] ?? []) as $hIdx => $spot)
                    <div class="atelier-hotspot-card" id="card-spot-{{ $cProduct->id }}-{{ $hIdx }}" style="top: {{ $spot['card_top'] ?? $spot['top'] }}%; @if(isset($spot['card_right'])) right: {{ $spot['card_right'] }}%; @else left: {{ $spot['card_left'] ?? $spot['left'] }}%; @endif">
                        <strong>{{ $spot['title'] }}</strong>
                        <p>{{ $spot['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Scene 2: Fabric Loupe -->
        <div class="atelier-scene atelier-scene-loupe">
            <div class="atelier-loupe-layout">
                <div class="atelier-loupe-viewport">
                    <div class="atelier-loupe-circle">
                        <img src="{{ asset($cfg['loupe']['image'] ?? 'assets/images/products/saree-fabric-texture.jpg') }}" alt="Macro Fabric View" class="atelier-loupe-texture">
                        <div class="loupe-reticle"></div>
                    </div>
                </div>

                <div class="atelier-loupe-info">
                    <span class="atelier-kicker">{{ $cfg['loupe']['kicker'] ?? 'MACRO FABRIC INSPECTION' }}</span>
                    <h3>{{ $cfg['loupe']['title'] ?? 'Microscopic Weave & Thread Analysis' }}</h3>
                    <p>{{ $cfg['loupe']['desc'] ?? 'Examine the fine zari threads, warp-and-weft silk density, and seam precision under ultra-high definition zoom.' }}</p>
                    
                    <div class="atelier-specs-grid">
                        @foreach(($cfg['loupe']['specs'] ?? []) as $spec)
                        <div class="spec-card">
                            <span class="spec-label">{{ $spec['label'] }}</span>
                            <strong class="spec-value">{{ $spec['value'] }}</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Scene 3: Editorial Lookbook -->
        <div class="atelier-scene atelier-scene-lookbook">
            <div class="atelier-lookbook-grid">
                @php $gallery = $cfg['gallery'] ?? []; @endphp
                @if(!empty($gallery))
                    @foreach($gallery as $gIdx => $gImg)
                    <div class="lookbook-card lookbook-card-{{ ($gIdx % 4) + 1 }}">
                        <span class="lookbook-num">{{ sprintf('%02d', $gIdx + 1) }}</span>
                        <div class="lookbook-art"><img src="{{ asset($gImg) }}" alt="Lookbook Angle {{ $gIdx + 1 }}" loading="lazy"></div>
                        <div class="lookbook-caption"><strong>Editorial Angle #{{ $gIdx + 1 }}</strong><small>High Definition</small></div>
                    </div>
                    @endforeach
                @else
                    <div class="lookbook-card lookbook-card-1">
                        <span class="lookbook-num">01</span>
                        <div class="lookbook-art"><img src="{{ $firstSwatchImg }}" alt="{{ $cProduct->name }}" loading="lazy"></div>
                        <div class="lookbook-caption"><strong>Primary Drape</strong><small>Full Silhouette</small></div>
                    </div>
                    <div class="lookbook-card lookbook-card-2">
                        <span class="lookbook-num">02</span>
                        <div class="lookbook-art"><img src="{{ asset($cfg['loupe']['image'] ?? 'assets/images/products/saree-fabric-texture.jpg') }}" alt="Texture" loading="lazy"></div>
                        <div class="lookbook-caption"><strong>Zari Border & Craft</strong><small>Artisan Motif</small></div>
                    </div>
                    <div class="lookbook-card lookbook-card-3">
                        <span class="lookbook-num">03</span>
                        <div class="lookbook-art"><img src="{{ $firstSwatchImg }}" alt="Styling" style="filter: brightness(1.08) contrast(1.05);" loading="lazy"></div>
                        <div class="lookbook-caption"><strong>Contemporary Cut</strong><small>Tailored Fall</small></div>
                    </div>
                    <div class="lookbook-card lookbook-card-4">
                        <span class="lookbook-num">04</span>
                        <div class="lookbook-art"><img src="{{ $firstSwatchImg }}" alt="Elegance" loading="lazy"></div>
                        <div class="lookbook-caption"><strong>Evening Mood</strong><small>Fluid Movement</small></div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Scene 4: Variant Morph -->
        <div class="atelier-scene atelier-scene-morph">
            <div class="atelier-morph-layout">
                <div class="atelier-morph-copy">
                    <span class="atelier-kicker">DYNAMIC COLORWAYS</span>
                    <h3>Silk Flow & Variant Morph</h3>
                    <p>Select any signature shade below. The garment and background lighting adapt dynamically in real-time.</p>
                    
                    <div class="atelier-swatches">
                        @foreach($swatches as $swIdx => $sw)
                            <button type="button" 
                                    class="swatch-btn {{ $swIdx === 0 ? 'active' : '' }}" 
                                    data-color="{{ $sw['key'] ?? 'color_'.$swIdx }}" 
                                    data-title="{{ $sw['name'] }}" 
                                    data-image="{{ $sw['image_url'] ?? asset($sw['image'] ?? '') }}" 
                                    data-sku="{{ $sw['sku'] ?? $cProduct->sku }}"
                                    data-target-product="{{ $cProduct->id }}" 
                                    style="--swatch-color: {{ $sw['color'] }}; background-color: {{ $sw['color'] }};" 
                                    title="{{ $sw['name'] }}"></button>
                        @endforeach
                    </div>
                    <strong class="selected-swatch-name" id="morphLabel-{{ $cProduct->id }}">{{ $swatches[0]['name'] ?? 'Champagne Rose' }} (Selected)</strong>
                </div>

                <div class="atelier-morph-stage">
                    <div class="atelier-morph-ring"></div>
                    <img src="{{ $firstSwatchImg }}" alt="{{ $cProduct->name }}" class="atelier-morph-garment" id="morphGarment-{{ $cProduct->id }}" style="transition: opacity 0.35s ease, filter 0.4s ease;">
                </div>
            </div>
        </div>

    </div>
    @endforeach
</section>
@endif
