@extends('layouts.app')
@section('title', '3D Showcase Settings')
@section('content')
<div class="page-head">
    <div>
        <span class="muted-title">STOREFRONT & 3D ATELIER</span>
        <h1>3D Showcase & Hotspot Management</h1>
        <p class="muted-note">Customize the 3D turntable podium, interactive detail pins, macro fabric loupe, and color swatches.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.showcase.update') }}" enctype="multipart/form-data" class="stack-form">
    @csrf
    @method('PUT')

    @if($selectedProduct)
        <input type="hidden" name="product_id" value="{{ $selectedProduct->id }}">
    @endif

    <!-- Selector Bar -->
    <div class="card" style="margin-bottom: 24px; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; background: #fafbfc; border: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 22px;">👗</span>
            <div>
                <strong>Configuration Target</strong>
                <small style="display: block; color: #64748b;">
                    @if($selectedProduct)
                        Customizing 3D showcase specifically for <strong>{{ $selectedProduct->name }}</strong>
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
            <span>✨</span> General 3D Showcase Settings
        </h2>
        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
            <label style="grid-column: 1 / -1; display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                <input type="checkbox" name="enabled" value="1" @checked($config['enabled'] ?? true)>
                <span><strong>Enable 3D Clothing Showcase</strong> (Displays luxury turntable stage on customer dashboard and shop)</span>
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

    <!-- Section 2: 4 Interactive Hotspot Pins (As shown in Screenshot 1) -->
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
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <strong style="color: #0f172a; font-size: 13px;">Hotspot #{{ $i + 1 }}</strong>
                    <span style="font-size: 11px; color: #64748b; background: #e2e8f0; padding: 2px 8px; border-radius: 999px;">Pin {{ $i + 1 }}</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <label>
                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Pin Title</span>
                        <input name="hotspots[{{ $i }}][title]" value="{{ old('hotspots.'.$i.'.title', $spot['title']) }}" required>
                    </label>
                    <label>
                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Description</span>
                        <textarea name="hotspots[{{ $i }}][desc]" rows="2">{{ old('hotspots.'.$i.'.desc', $spot['desc']) }}</textarea>
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <label>
                            <span style="font-size: 11px; color: #64748b;">Top Y (%)</span>
                            <input type="number" step="1" min="0" max="100" name="hotspots[{{ $i }}][top]" value="{{ old('hotspots.'.$i.'.top', $spot['top']) }}">
                        </label>
                        <label>
                            <span style="font-size: 11px; color: #64748b;">Left X (%)</span>
                            <input type="number" step="1" min="0" max="100" name="hotspots[{{ $i }}][left]" value="{{ old('hotspots.'.$i.'.left', $spot['left']) }}">
                        </label>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Section 3: Macro Fabric Loupe Magnifier (As shown in Screenshot 2) -->
    <div class="card" style="margin-bottom: 24px;">
        <h2 style="font-size: 18px; margin: 0 0 6px; display: flex; align-items: center; gap: 8px;">
            <span>🔍</span> Macro Fabric Loupe & Technical Weave Analysis
        </h2>
        <p style="font-size: 12px; color: #64748b; margin-bottom: 20px;">
            Configure the microscopic fabric texture lens and technical specification badges shown in Scene 2.
        </p>

        @php $loupe = $config['loupe'] ?? Setting::defaultShowcaseConfig()['loupe']; @endphp
        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px;">
            <label>
                <span>Section Kicker</span>
                <input name="loupe_kicker" value="{{ old('loupe_kicker', $loupe['kicker']) }}" required>
            </label>
            <label>
                <span>Headline Title</span>
                <input name="loupe_title" value="{{ old('loupe_title', $loupe['title']) }}" required>
            </label>
            <label style="grid-column: 1 / -1;">
                <span>Detailed Description</span>
                <textarea name="loupe_desc" rows="2">{{ old('loupe_desc', $loupe['desc']) }}</textarea>
            </label>
            <label style="grid-column: 1 / -1;">
                <span>Fabric Texture Loupe Image</span>
                <div style="display: flex; align-items: center; gap: 18px; margin-top: 6px;">
                    <div style="width: 70px; height: 70px; border-radius: 50%; border: 2px solid #d4af37; overflow: hidden; background: #000; flex: 0 0 auto;">
                        <img src="{{ asset($loupe['image'] ?? 'assets/images/products/saree-fabric-texture.jpg') }}" style="width: 100%; height: 100%; object-fit: cover;">
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

    <!-- Section 4: Variant Morph Swatches -->
    <div class="card" style="margin-bottom: 28px;">
        <h2 style="font-size: 18px; margin: 0 0 6px; display: flex; align-items: center; gap: 8px;">
            <span>🎨</span> Variant Morph Swatches (Colorways)
        </h2>
        <p style="font-size: 12px; color: #64748b; margin-bottom: 18px;">
            Customers can click these swatches in the Variant Morph tab to dynamically change the preview tone and lighting.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
            @php $swatches = $config['swatches'] ?? Setting::defaultShowcaseConfig()['swatches']; @endphp
            @foreach($swatches as $swIdx => $swatch)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px;">
                <label style="display: flex; flex-direction: column; gap: 6px;">
                    <span style="font-size: 11px; color: #64748b; font-weight: 700;">Color Name</span>
                    <input name="swatches[{{ $swIdx }}][name]" value="{{ old('swatches.'.$swIdx.'.name', $swatch['name']) }}">
                </label>
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                    <input type="color" name="swatches[{{ $swIdx }}][color]" value="{{ old('swatches.'.$swIdx.'.color', $swatch['color']) }}" style="width: 44px; height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px; cursor: pointer;">
                    <span style="font-size: 12px; color: #64748b; font-family: monospace;">{{ $swatch['color'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
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
