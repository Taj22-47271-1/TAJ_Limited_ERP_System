@extends('layouts.app')
@section('title', '3D Clothing Showcase Management')

@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow dark">STOREFRONT ATELIER</span>
        <h1>3D Clothing Showcase & Hotspot Management</h1>
        <p>Customize the 3D garment stage, 4 interactive hotspot pins, macro fabric loupe, specifications, and variant morph swatches.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('dashboard') }}" target="_blank" class="btn ghost">View Storefront ↗</a>
    </div>
</div>

@if(session('status'))
    <div class="alert success" style="margin-bottom: 20px;">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('admin.showcase.update') }}" enctype="multipart/form-data" class="showcase-form">
    @csrf
    @method('PUT')

    @if($selectedProduct)
        <input type="hidden" name="product_id" value="{{ $selectedProduct->id }}">
    @endif

    <!-- Target Selector: Global vs Product Specific -->
    <div class="card" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; background: #f8fafc; border-left: 4px solid #111;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <span style="font-size: 24px;">🎯</span>
            <div>
                <strong style="font-size: 15px; display: block;">Configuration Target</strong>
                <small class="muted">
                    @if($selectedProduct)
                        Configuring custom settings for: <strong style="color: #0f172a;">{{ $selectedProduct->name }}</strong>
                    @else
                        Configuring <strong>Global Showcase Defaults</strong> (applied across storefront)
                    @endif
                </small>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <select onchange="if(this.value){ window.location='{{ route('admin.showcase') }}?product_id='+this.value; } else { window.location='{{ route('admin.showcase') }}'; }" style="min-width: 260px; height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; background: #fff; font-size: 13px;">
                <option value="">⚙ Global Showcase Defaults</option>
                @foreach($clothingProducts as $cp)
                    <option value="{{ $cp->id }}" @selected($selectedProduct?->id === $cp->id)>✦ Product: {{ $cp->name }} ({{ $companySettings->currency_symbol }}{{ number_format($cp->selling_price,0) }})</option>
                @endforeach
            </select>
            @if($selectedProduct)
                <a href="{{ route('admin.showcase') }}" class="btn ghost" style="height: 42px; line-height: 40px; padding: 0 16px;">Reset to Global</a>
            @endif
        </div>
    </div>

    <!-- Section 1: General & Banner Settings -->
    <div class="card" style="margin-bottom: 24px;">
        <h2 style="font-size: 18px; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
            <span>✨</span> General 3D Showcase & Presentation Settings
        </h2>
        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
            <label style="grid-column: 1 / -1; display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                <input type="checkbox" name="enabled" value="1" @checked($config['enabled'] ?? true)>
                <span><strong>Enable 3D Clothing Showcase</strong> (Displays luxury turntable stage on customer dashboard and shop)</span>
            </label>

            <label style="grid-column: 1 / -1; display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                <input type="checkbox" name="enable_3d" value="1" @checked($config['enable_3d'] ?? true)>
                <span><strong>Enable 3D Orbit Stage & Dynamic Tilt</strong> (Optional: When checked, turntable orbit & parallax tilt are active; uncheck for clean 2D studio view)</span>
            </label>

            <label style="grid-column: 1 / -1;">
                <span>Showcase Header Tagline</span>
                <input name="tagline" value="{{ old('tagline', $config['tagline'] ?? 'TAJ ATELIER · 3D LUXURY CLOTHING SHOWCASE') }}" required>
            </label>

            <label>
                <span>Feature Badge 1</span>
                <input name="feature_1" value="{{ old('feature_1', $config['features']['feature_1'] ?? '🌿 100% Breathable Weave') }}">
            </label>
            <label>
                <span>Feature Badge 2</span>
                <input name="feature_2" value="{{ old('feature_2', $config['features']['feature_2'] ?? '✨ Golden Zari & Fine Stitching') }}">
            </label>
            <label style="grid-column: 1 / -1;">
                <span>Feature Badge 3</span>
                <input name="feature_3" value="{{ old('feature_3', $config['features']['feature_3'] ?? '🚚 Express Secure Delivery') }}">
            </label>
        </div>
    </div>

    <!-- Section 2: 4 Interactive Hotspot Pins -->
    <div class="card" style="margin-bottom: 24px;">
        <h2 style="font-size: 18px; margin: 0 0 6px; display: flex; align-items: center; gap: 8px;">
            <span>📍</span> 4 Interactive Hotspot Pins (3D Stage Detail Callouts)
        </h2>
        <p style="font-size: 12px; color: #64748b; margin-bottom: 20px;">
            These glowing pins appear directly on the clothing item in 3D. Hovering or tapping reveals the craftsmanship details.
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            @php $hotspots = $config['hotspots'] ?? Setting::defaultShowcaseConfig()['hotspots']; @endphp
            @foreach($hotspots as $i => $spot)
            <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; background: #fafafa;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <strong style="color: #0f172a; font-size: 14px;">Hotspot Pin #{{ $i + 1 }}</strong>
                    <span style="font-size: 11px; background: #e2e8f0; padding: 2px 8px; border-radius: 999px; font-weight: 700;">Pin {{ $i + 1 }}</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label>
                        <span style="font-size: 12px; color: #64748b;">Pin Title</span>
                        <input name="hotspots[{{ $i }}][title]" value="{{ old('hotspots.'.$i.'.title', $spot['title']) }}" required>
                    </label>
                    <label>
                        <span style="font-size: 12px; color: #64748b;">Description</span>
                        <textarea name="hotspots[{{ $i }}][desc]" rows="2">{{ old('hotspots.'.$i.'.desc', $spot['desc']) }}</textarea>
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 4px;">
                        <label>
                            <span style="font-size: 11px; color: #64748b;">Top Position (Y: 0-100%)</span>
                            <input type="number" step="1" min="0" max="100" name="hotspots[{{ $i }}][top]" value="{{ old('hotspots.'.$i.'.top', $spot['top']) }}">
                        </label>
                        <label>
                            <span style="font-size: 11px; color: #64748b;">Left Position (X: 0-100%)</span>
                            <input type="number" step="1" min="0" max="100" name="hotspots[{{ $i }}][left]" value="{{ old('hotspots.'.$i.'.left', $spot['left']) }}">
                        </label>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Section 3: Macro Fabric Loupe (Fabric Texture & Specs) -->
    <div class="card" style="margin-bottom: 24px;">
        @php $loupe = $config['loupe'] ?? Setting::defaultShowcaseConfig()['loupe']; @endphp
        <h2 style="font-size: 18px; margin: 0 0 6px; display: flex; align-items: center; gap: 8px;">
            <span>🔬</span> Macro Fabric Loupe & Technical Specifications
        </h2>
        <p style="font-size: 12px; color: #64748b; margin-bottom: 20px;">
            Configure the ultra-high definition fabric inspection circle, texture image, and the 4 specification pills.
        </p>

        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px;">
            <label>
                <span>Loupe Kicker / Section Category</span>
                <input name="loupe_kicker" value="{{ old('loupe_kicker', $loupe['kicker']) }}" required>
            </label>
            <label>
                <span>Loupe Main Title</span>
                <input name="loupe_title" value="{{ old('loupe_title', $loupe['title']) }}" required>
            </label>
            <label style="grid-column: 1 / -1;">
                <span>Detailed Weave & Thread Analysis Description</span>
                <textarea name="loupe_desc" rows="2">{{ old('loupe_desc', $loupe['desc']) }}</textarea>
            </label>
            <label style="grid-column: 1 / -1;">
                <span>Fabric Loupe Texture Image (Square / Macro photo)</span>
                <div style="display: flex; align-items: center; gap: 16px; margin-top: 6px;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; border: 2px solid #d4af37; background: #000; flex-shrink: 0;">
                        <img src="{{ asset($loupe['image'] ?? 'assets/images/products/saree-fabric-texture.jpg') }}" alt="Current Loupe" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <input type="file" name="loupe_image" accept="image/*">
                </div>
            </label>
        </div>

        <h3 style="font-size: 14px; margin: 0 0 12px; color: #334155;">Specification Badges (4 Pills)</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            @php $specs = $loupe['specs'] ?? Setting::defaultShowcaseConfig()['loupe']['specs']; @endphp
            @foreach($specs as $idx => $spec)
            <div style="display: flex; gap: 8px; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px;">
                <input name="specs[{{ $idx }}][label]" value="{{ old('specs.'.$idx.'.label', $spec['label']) }}" placeholder="Label (e.g. Weave Density)" style="flex: 1;">
                <span style="color: #94a3b8; font-weight: bold;">:</span>
                <input name="specs[{{ $idx }}][value]" value="{{ old('specs.'.$idx.'.value', $spec['value']) }}" placeholder="Value (e.g. 320 Threads/Inch)" style="flex: 1.3;">
            </div>
            @endforeach
        </div>
    </div>

    <!-- Section 4: Variant Morph Swatches with Color-Specific Photos -->
    <div class="card" style="margin-bottom: 28px;">
        <h2 style="font-size: 18px; margin: 0 0 6px; display: flex; align-items: center; gap: 8px;">
            <span>🎨</span> Variant Morph Swatches (Colorways & Color-Specific Product Photos)
        </h2>
        <p style="font-size: 12px; color: #64748b; margin-bottom: 18px;">
            Assign a specific product image for each colorway. When customers tap that color swatch, the garment view automatically switches to that color's image!
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            @php $swatches = $config['swatches'] ?? Setting::defaultShowcaseConfig()['swatches']; @endphp
            @foreach($swatches as $swIdx => $swatch)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 11px; font-weight: 700; color: #1e293b;">Swatch #{{ $swIdx + 1 }}</span>
                    <input type="color" name="swatches[{{ $swIdx }}][color]" value="{{ old('swatches.'.$swIdx.'.color', $swatch['color']) }}" style="width: 36px; height: 32px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px; cursor: pointer;">
                </div>

                <label style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="font-size: 11px; color: #64748b; font-weight: 600;">Color Name</span>
                    <input name="swatches[{{ $swIdx }}][name]" value="{{ old('swatches.'.$swIdx.'.name', $swatch['name']) }}" required style="font-size: 12px;">
                </label>

                <label style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="font-size: 11px; color: #64748b; font-weight: 600;">Tone / Tag</span>
                    <input name="swatches[{{ $swIdx }}][tag]" value="{{ old('swatches.'.$swIdx.'.tag', $swatch['tag'] ?? '') }}" placeholder="e.g. Royal Indigo" style="font-size: 12px;">
                </label>

                <!-- Color specific image -->
                <div style="border-top: 1px dashed #cbd5e1; padding-top: 10px; margin-top: 4px;">
                    <span style="font-size: 11px; color: #475569; font-weight: 600; display: block; margin-bottom: 6px;">Color-Specific Image</span>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        @php
                            $swImg = !empty($swatch['image'])
                                ? (str_starts_with($swatch['image'], 'http') || str_starts_with($swatch['image'], 'assets/') || str_starts_with($swatch['image'], 'storage/') ? asset($swatch['image']) : asset('storage/' . $swatch['image']))
                                : '';
                        @endphp
                        <div style="width: 44px; height: 44px; border-radius: 6px; border: 1px solid #cbd5e1; background: #000; overflow: hidden; display: grid; place-items: center; flex-shrink: 0;">
                            @if($swImg)
                                <img src="{{ $swImg }}" alt="Swatch Image" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            @else
                                <span style="font-size: 10px; color: #94a3b8;">None</span>
                            @endif
                        </div>
                        <input type="hidden" name="swatches[{{ $swIdx }}][existing_image]" value="{{ $swatch['image'] ?? '' }}">
                        <input type="file" name="swatch_image_{{ $swIdx }}" accept="image/*" style="font-size: 11px; width: 100%;">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Section 5: Multiple Lookbook & Gallery Photos -->
    <div class="card" style="margin-bottom: 28px;">
        <h2 style="font-size: 18px; margin: 0 0 6px; display: flex; align-items: center; gap: 8px;">
            <span>📖</span> Lookbook & Multiple Product Angles (Gallery)
        </h2>
        <p style="font-size: 12px; color: #64748b; margin-bottom: 16px;">
            Upload additional styling photos, angle shots, or model lookbook captures for this garment.
        </p>

        @php $gallery = $config['gallery'] ?? []; @endphp
        @if(!empty($gallery))
            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
                @foreach($gallery as $gImg)
                    <div style="width: 90px; height: 90px; border-radius: 8px; border: 1px solid #cbd5e1; overflow: hidden; background: #000;">
                        <img src="{{ asset($gImg) }}" alt="Lookbook angle" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                @endforeach
            </div>
        @endif

        <label style="display: block;">
            <span style="font-size: 12px; font-weight: 600; color: #475569;">Upload additional gallery / lookbook images (multiple allowed):</span>
            <input type="file" name="gallery_images[]" multiple accept="image/*" style="margin-top: 6px; display: block; font-size: 13px;">
        </label>
    </div>

    <!-- Bottom Actions -->
    <div style="display: flex; align-items: center; gap: 14px; padding-bottom: 40px;">
        <button type="submit" class="btn primary" style="min-width: 220px; height: 48px; font-size: 14px; font-weight: 700;">
            ✓ Save Showcase Settings
        </button>
        <a href="{{ route('dashboard') }}" target="_blank" class="btn ghost" style="height: 48px; line-height: 46px; padding: 0 20px;">
            👁 View Live Storefront
        </a>
    </div>
</form>
@endsection
