@php
    $showcaseItems = ($clothingProducts ?? collect([$product ?? null]))->filter()->values();
    $globalConfig = $companySettings?->getShowcaseSettings() ?? \App\Models\Setting::defaultShowcaseConfig();
@endphp

@if($showcaseItems->isNotEmpty() && ($globalConfig['enabled'] ?? true))
<section class="atelier-luxury-section" id="clothingShowcaseSection">
    <div class="atelier-header">
        <div class="atelier-brand-tag">
            <span class="atelier-gold-dot"></span>
            <span>{{ $globalConfig['tagline'] ?? 'TAJ ATELIER · 3D LUXURY CLOTHING SHOWCASE' }}</span>
        </div>
        <div class="atelier-scene-nav" role="tablist">
            <button type="button" class="atelier-tab-btn active" data-scene="turntable">
                <span class="tab-icon">✨</span> {{ ($globalConfig['enable_3d'] ?? true) ? '3D Stage' : 'Studio Stage' }}
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
        $swatches = $cfg['swatches'] ?? [];
        $firstSwatchImg = !empty($swatches[0]['image']) ? asset($swatches[0]['image']) : $cProduct->image_url;
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

                        <!-- Colorways / Swatches on Main Stage -->
                        @if(!empty($swatches))
                        <div class="atelier-stage-swatches" style="margin-top: 14px;">
                            <label style="font-size: 10px; letter-spacing: 0.12em; font-weight: 800; color: #94a3b8; display: block; margin-bottom: 8px;">SELECT COLORWAY</label>
                            <div class="atelier-swatches stage-swatches" style="display: flex; gap: 8px; align-items: center;">
                                @foreach($swatches as $swIdx => $sw)
                                    @php
                                        $swImg = !empty($sw['image'])
                                            ? (str_starts_with($sw['image'], 'http') || str_starts_with($sw['image'], 'assets/') || str_starts_with($sw['image'], 'storage/') ? asset($sw['image']) : asset('storage/' . $sw['image']))
                                            : '';
                                    @endphp
                                    <button type="button" class="swatch-btn {{ $swIdx === 0 ? 'active' : '' }}" data-color="{{ $sw['key'] ?? 'color_'.$swIdx }}" data-title="{{ $sw['name'] }}" data-image="{{ $swImg }}" data-target-product="{{ $cProduct->id }}" style="--swatch-color: {{ $sw['color'] }}; width: 26px; height: 26px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.4); cursor: pointer;" title="{{ $sw['name'] }}"></button>
                                @endforeach
                                <span class="active-color-name" id="colorLabel-{{ $cProduct->id }}" style="font-size: 11px; color: #cbd5e1; margin-left: 6px;">{{ $swatches[0]['name'] ?? '' }}</span>
                            </div>
                        </div>
                        @endif

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
                            <img src="{{ $cProduct->image_url }}" alt="{{ $cProduct->name }}" class="atelier-garment-img" id="garmentImg-{{ $cProduct->id }}" style="transition: opacity 0.35s ease, transform 0.4s ease;">
                            
                            <!-- Interactive Hotspots -->
                            @foreach(($cfg['hotspots'] ?? []) as $hIdx => $spot)
                            <div class="atelier-hotspot" style="top: {{ $spot['top'] }}%; left: {{ $spot['left'] }}%;" data-target="card-spot-{{ $cProduct->id }}-{{ $hIdx }}">
                                <span class="hotspot-pin"></span>
                                <span class="hotspot-ripple"></span>
                            </div>
                            <div class="atelier-hotspot-card" id="card-spot-{{ $cProduct->id }}-{{ $hIdx }}" style="top: {{ max(4, min(80, $spot['top'] - 8)) }}%; {{ $spot['left'] > 50 ? 'right: 4%;' : 'left: 4%;' }} width: 230px;">
                                <strong>{{ $spot['title'] }}</strong>
                                <small>{{ $spot['desc'] }}</small>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @if($enable3D)
                    <div class="atelier-stage-hint">
                        <span>✦ Move cursor or drag to rotate 3D view</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Scene 2: Fabric Texture Loupe -->
        <div class="atelier-scene atelier-scene-loupe">
            <div class="atelier-loupe-view">
                <div class="atelier-loupe-intro">
                    <span class="atelier-kicker">{{ $cfg['loupe']['kicker'] ?? 'MACRO FABRIC INSPECTION' }}</span>
                    <h3>{{ $cfg['loupe']['title'] ?? 'Microscopic Weave & Thread Analysis' }}</h3>
                    <p>{{ $cfg['loupe']['desc'] ?? 'Examine the fine zari threads, warp-and-weft silk density, and seam precision under ultra-high definition zoom.' }}</p>
                </div>
                <div class="atelier-loupe-stage">
                    <div class="atelier-loupe-lens">
                        <img src="{{ asset($cfg['loupe']['image'] ?? 'assets/images/products/saree-fabric-texture.jpg') }}" alt="Fabric Weave Texture" class="atelier-loupe-texture">
                        <div class="loupe-reticle"></div>
                    </div>
                    <div class="atelier-loupe-specs">
                        @foreach(($cfg['loupe']['specs'] ?? []) as $spec)
                        <div class="spec-pill"><strong>{{ $spec['label'] }}:</strong> {{ $spec['value'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Scene 3: Lookbook & Multi-Angle Photos -->
        <div class="atelier-scene atelier-scene-lookbook">
            <div class="atelier-lookbook-header">
                <span class="atelier-kicker">EDITORIAL LOOKBOOK</span>
                <h3>The Curated Collection</h3>
                <p>Styling perspectives and silhouette captures.</p>
            </div>
            <div class="atelier-lookbook-grid">
                @php $customGallery = $cfg['gallery'] ?? []; @endphp
                @if(!empty($customGallery))
                    @foreach($customGallery as $gIdx => $gItem)
                        <div class="lookbook-card lookbook-card-{{ ($gIdx % 4) + 1 }}">
                            <span class="lookbook-num">{{ sprintf('%02d', $gIdx + 1) }}</span>
                            <div class="lookbook-art"><img src="{{ asset($gItem) }}" alt="Lookbook angle {{ $gIdx + 1 }}"></div>
                            <div class="lookbook-caption"><strong>Perspective #{{ $gIdx + 1 }}</strong><small>Curated Shot</small></div>
                        </div>
                    @endforeach
                @else
                    <div class="lookbook-card lookbook-card-1">
                        <span class="lookbook-num">01</span>
                        <div class="lookbook-art"><img src="{{ $cProduct->image_url }}" alt="{{ $cProduct->name }}"></div>
                        <div class="lookbook-caption"><strong>Primary Drape</strong><small>Full Silhouette</small></div>
                    </div>
                    <div class="lookbook-card lookbook-card-2">
                        <span class="lookbook-num">02</span>
                        <div class="lookbook-art"><img src="{{ asset($cfg['loupe']['image'] ?? 'assets/images/products/saree-fabric-texture.jpg') }}" alt="Texture"></div>
                        <div class="lookbook-caption"><strong>Zari Border & Craft</strong><small>Artisan Motif</small></div>
                    </div>
                    <div class="lookbook-card lookbook-card-3">
                        <span class="lookbook-num">03</span>
                        <div class="lookbook-art"><img src="{{ $cProduct->image_url }}" alt="Styling" style="filter: brightness(1.08) contrast(1.05);"></div>
                        <div class="lookbook-caption"><strong>Contemporary Cut</strong><small>Tailored Fall</small></div>
                    </div>
                    <div class="lookbook-card lookbook-card-4">
                        <span class="lookbook-num">04</span>
                        <div class="lookbook-art"><img src="{{ $cProduct->image_url }}" alt="Elegance"></div>
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
                        @foreach(($cfg['swatches'] ?? []) as $swIdx => $sw)
                            @php
                                $swImg = !empty($sw['image'])
                                    ? (str_starts_with($sw['image'], 'http') || str_starts_with($sw['image'], 'assets/') || str_starts_with($sw['image'], 'storage/') ? asset($sw['image']) : asset('storage/' . $sw['image']))
                                    : '';
                            @endphp
                            <button type="button" class="swatch-btn {{ $swIdx === 0 ? 'active' : '' }}" data-color="{{ $sw['key'] ?? 'color_'.$swIdx }}" data-title="{{ $sw['name'] }}" data-image="{{ $swImg }}" data-target-product="{{ $cProduct->id }}" style="--swatch-color: {{ $sw['color'] }};" title="{{ $sw['name'] }}"></button>
                        @endforeach
                    </div>
                    <strong class="selected-swatch-name" id="morphLabel-{{ $cProduct->id }}">{{ $cfg['swatches'][0]['name'] ?? 'Champagne Rose' }} (Selected)</strong>
                </div>

                <div class="atelier-morph-stage">
                    <div class="atelier-morph-ring"></div>
                    <img src="{{ $cProduct->image_url }}" alt="{{ $cProduct->name }}" class="atelier-morph-garment" id="morphGarment-{{ $cProduct->id }}" style="transition: opacity 0.35s ease, filter 0.4s ease;">
                </div>
            </div>
        </div>

    </div>
    @endforeach
</section>
@endif
