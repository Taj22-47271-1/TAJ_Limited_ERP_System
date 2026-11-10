@extends('layouts.app')

@section('title',$product->exists?'Edit Product':'Add Product')

@section('content')

<div class="page-head">

    <div>

        <span class="eyebrow dark">PRODUCT MANAGEMENT</span>

        <h1>{{ $product->exists ? 'Edit Product' : 'Add Product' }}</h1>

        <p>{{ auth()->user()->hasPermission('inventory.manage') ? 'Manage product identity, cost, image, size options and stock controls.' : 'Add or edit product details, image and customer-facing size options. Stock adjustment is restricted to Admin.' }}</p>

    </div>

    <a class="btn ghost" href="{{ route('products.index') }}">Back</a>

</div>



<div class="card">

<form class="form-grid" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('products.update',$product) : route('products.store') }}">

    @csrf

    @if($product->exists) @method('PUT') @endif



    <label class="span-2">Product name

        <input name="name" value="{{ old('name',$product->name) }}" required>

    </label>



    <label>Category

        <select name="category_id">

            <option value="">No category</option>

            @foreach($categories as $c)

                <option value="{{ $c->id }}" @selected(old('category_id',$product->category_id)==$c->id)>{{ $c->name }}</option>

            @endforeach

        </select>

    </label>



    <label>Unit

        <input name="unit" value="{{ old('unit',$product->unit ?: 'pcs') }}" required>

    </label>



    <label>SKU

        <input name="sku" value="{{ old('sku',$product->sku) }}" required>

    </label>



    <label>Barcode

        <input name="barcode" value="{{ old('barcode',$product->barcode) }}">

    </label>



    @if(auth()->user()->hasPermission('inventory.manage'))

        <label>Purchase price

            <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price',$product->purchase_price ?: 0) }}" required>

        </label>

    @endif



    <div class="span-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin: 10px 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div>
                <strong style="font-size: 14px; color: #0f172a;">🏷️ Pricing & Promotional Discounts</strong>
                <p style="margin: 2px 0 0; font-size: 12px; color: #64748b;">Set regular selling price and optional discount. Entering percentage or sale price automatically computes the other.</p>
            </div>
            <button type="button" id="clearDiscountBtn" class="btn ghost" style="font-size: 11px; padding: 4px 10px; height: auto;">Clear Discount</button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <label style="margin: 0;">Regular Selling Price (Original)
                <input type="number" step="0.01" min="0" name="selling_price" id="sellingPriceInput" value="{{ old('selling_price',$product->selling_price ?: 0) }}" required placeholder="e.g. 2500.00">
            </label>

            <label style="margin: 0;">Discount % (e.g. 20%)
                <input type="number" step="0.1" min="0" max="100" name="discount_percent" id="discountPercentInput" value="{{ old('discount_percent', $product->discount_percent) }}" placeholder="e.g. 20">
                <small style="color: #64748b; font-size: 11px;">Badge percentage (e.g. 20% OFF).</small>
            </label>

            <label style="margin: 0;">Discounted Sale Price
                <input type="number" step="0.01" min="0" name="discount_price" id="discountPriceInput" value="{{ old('discount_price', $product->discount_price) }}" placeholder="e.g. 1999.00">
                <small style="color: #64748b; font-size: 11px;">Actual price customer pays at checkout.</small>
            </label>
        </div>

        <!-- Live Calculation Preview Card -->
        <div id="pricingPreviewBox" style="margin-top: 14px; padding: 12px 16px; border-radius: 8px; background: #ffffff; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Customer Sees:</span>
                <del id="previewOrigPrice" style="color: #94a3b8; font-size: 13px; text-decoration: line-through;">{{ $companySettings->currency_symbol }}0.00</del>
                <strong id="previewFinalPrice" style="color: #0f172a; font-size: 16px; font-weight: 800;">{{ $companySettings->currency_symbol }}0.00</strong>
                <span id="previewDiscountBadge" style="background: #fee2e2; color: #dc2626; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 999px; display: none;">-0% OFF</span>
            </div>
            <div id="previewSavingsText" style="font-size: 12px; color: #059669; font-weight: 600;"></div>
        </div>
    </div>



    @if(!$product->exists && auth()->user()->hasPermission('inventory.manage'))

        <label>Opening stock

            <input type="number" step="0.01" min="0" name="opening_stock" value="{{ old('opening_stock',0) }}">

        </label>

    @endif



    <label>Minimum stock

        <input type="number" step="0.01" min="0" name="min_stock" value="{{ old('min_stock',$product->min_stock ?: 5) }}" required>

    </label>



    <label class="span-2">Product image <small>JPG, PNG or WebP · max 2MB</small>

        <input type="file" name="image" accept="image/png,image/jpeg,image/webp">

    </label>



    @if($product->image_url)

        <div class="span-2 current-image">

            <img src="{{ $product->image_url }}" alt="{{ $product->name }}">

            <span>Current image</span>

        </div>

    @endif



    @php

        $sizeText = old('sizes_text', implode(', ', $product->normalizedSizes()));

        $hasSizes = old('has_sizes', $product->exists ? $product->has_sizes : false);

    @endphp



    <div class="span-2 product-size-config">

        <input type="hidden" name="has_sizes" value="0">

        <label class="check product-size-toggle">

            <input id="hasSizesToggle" type="checkbox" name="has_sizes" value="1" @checked($hasSizes)>

            Enable size selection for this product

        </label>

        <div id="sizeOptionsPanel" class="size-options-panel" @if(!$hasSizes) hidden @endif>
            <label>Available sizes
                <input id="sizesText" name="sizes_text" value="{{ $sizeText }}" placeholder="S, M, L, XL or 50ml, 100ml">
                <small>Use comma-separated sizes. Example: <strong>50ml, 100ml</strong> for perfumes or <strong>S, M, L, XL</strong> for clothing.</small>
            </label>

            <div class="size-preset-row" aria-label="Quick size presets" style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-top: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-right: 4px;">Apparel:</span>
                @foreach(['XS','S','M','L','XL','XXL','3XL'] as $preset)
                    <button type="button" class="size-preset" data-size="{{ $preset }}">{{ $preset }}</button>
                @endforeach
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 4px 0 10px;">Perfume / ml:</span>
                @foreach(['30ml','50ml','100ml','200ml'] as $preset)
                    <button type="button" class="size-preset" data-size="{{ $preset }}">{{ $preset }}</button>
                @endforeach
            </div>

            {{-- Size-specific Pricing Section --}}
            @php
                $existingSizePrices = old('size_prices', $product->exists ? ($product->size_prices ?? []) : []);
                if (!is_array($existingSizePrices)) {
                    $existingSizePrices = [];
                }
            @endphp
            <div style="margin-top: 20px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px; margin-bottom: 6px;">
                    <strong style="font-size: 13px; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                        <span>🏷️</span> Size-Specific Pricing & Discounts (e.g. 50ml vs 100ml)
                    </strong>
                    <small style="font-size: 11px; color: #64748b;">
                        Default product price: <strong>{{ $companySettings->currency_symbol }}<span id="refBaseSellingPrice">{{ number_format((float)($product->selling_price ?: 0), 2) }}</span></strong>
                    </small>
                </div>
                <p style="font-size: 12px; color: #64748b; margin: 0 0 14px;">
                    Set different prices for each size variant. For example, <strong>50ml = ৳5,000</strong> and <strong>100ml = ৳8,075</strong>. When customers switch sizes on the store, the price will update dynamically. Leaving a size blank will use the regular default price above.
                </p>

                <div id="sizePricingTableWrapper">
                    <div style="display: grid; grid-template-columns: 100px 1.2fr 1.2fr 100px; gap: 10px; padding: 6px 12px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0;">
                        <span>Size</span>
                        <span>Selling Price ({{ $companySettings->currency_symbol }})</span>
                        <span>Discount Price ({{ $companySettings->currency_symbol }})</span>
                        <span style="text-align: right;">Discount</span>
                    </div>
                    <div id="sizePricingRows" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                        {{-- Populated dynamically via renderSizePricingRows() --}}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Dynamic Product Colorways / Swatches Manager --}}
    @php
        $existingColorways = old('colorways', $product->exists ? ($product->colorways ?? []) : []);
        if (!is_array($existingColorways)) {
            $existingColorways = [];
        }
    @endphp

    <div class="span-2" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 12px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <strong style="font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    🎨 Product Colorways / Variant Swatches (Optional)
                </strong>
                <p style="margin: 4px 0 0; font-size: 12px; color: #64748b; max-width: 680px;">
                    Add custom color variants for this product. Upload a photo for each colorway. When customers select a colorway on the storefront, the product image and details switch automatically. 
                    <strong>If no colorways are added here, the "Select Colorway" section is completely hidden from the customer page.</strong>
                </p>
            </div>
            <button type="button" id="addColorwayBtn" class="btn" style="background: #0f172a; color: #ffffff; font-size: 12px; font-weight: 700; padding: 8px 14px; border-radius: 8px; border: none; cursor: pointer;">
                ➕ Add Colorway
            </button>
        </div>

        <div id="colorwaysList" style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($existingColorways as $idx => $cw)
                @php
                    $cwName = $cw['name'] ?? '';
                    $cwColor = $cw['color'] ?? '#1e293b';
                    $cwImg = $cw['image'] ?? ($cw['existing_image'] ?? '');
                    $cwImgUrl = '';
                    if (!empty($cwImg)) {
                        $cwImgUrl = (str_starts_with($cwImg, 'http') || str_starts_with($cwImg, 'assets/') || str_starts_with($cwImg, 'storage/')) ? asset($cwImg) : asset('storage/' . $cwImg);
                    }
                @endphp
                <div class="colorway-row" data-index="{{ $idx }}" style="display: grid; grid-template-columns: auto 1fr 120px 1.5fr auto; gap: 12px; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 14px; border-radius: 10px;">
                    <div class="cw-index-badge" style="font-size: 12px; font-weight: 800; color: #94a3b8; width: 24px; text-align: center;">
                        #{{ $loop->iteration }}
                    </div>

                    <div>
                        <label style="margin: 0; font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Colorway Name *</label>
                        <input type="text" name="colorways[{{ $idx }}][name]" value="{{ $cwName }}" placeholder="e.g. Royal Blue" required style="width: 100%; padding: 7px 10px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>

                    <div>
                        <label style="margin: 0; font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Swatch Color</label>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <input type="color" name="colorways[{{ $idx }}][color]" value="{{ $cwColor }}" oninput="this.nextElementSibling.textContent=this.value" style="width: 38px; height: 34px; padding: 2px; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; background: #ffffff;">
                            <span class="color-hex-label" style="font-size: 11px; font-family: monospace; color: #64748b;">{{ $cwColor }}</span>
                        </div>
                    </div>

                    <div>
                        <label style="margin: 0; font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">
                            Colorway Image @if($cwImgUrl)<span style="color: #059669; font-weight: normal;">(Uploaded)</span>@endif
                        </label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @if($cwImgUrl)
                                <div style="width: 38px; height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; overflow: hidden; flex-shrink: 0; background: #ffffff;">
                                    <img src="{{ $cwImgUrl }}" alt="{{ $cwName }}" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <input type="hidden" name="colorways[{{ $idx }}][existing_image]" value="{{ $cwImg }}">
                            @endif
                            <input type="file" name="colorways[{{ $idx }}][image_file]" accept="image/png,image/jpeg,image/webp" style="font-size: 11px; width: 100%;">
                        </div>
                    </div>

                    <div>
                        <label style="margin: 0; font-size: 11px; visibility: hidden; display: block; margin-bottom: 4px;">Action</label>
                        <button type="button" class="btn-remove-cw" onclick="removeColorwayRow(this)" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 7px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;" title="Remove this colorway">
                            ✕ Delete
                        </button>
                    </div>
                </div>
            @empty
                <div id="noColorwaysNotice" style="text-align: center; padding: 24px 16px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; color: #64748b; font-size: 13px;">
                    <div style="font-size: 24px; margin-bottom: 4px;">⚪</div>
                    <strong>No colorway variants added.</strong>
                    <p style="margin: 4px 0 0; font-size: 12px;">This product will display without color swatches. Click <strong>"➕ Add Colorway"</strong> above to add customized shades with photos.</p>
                </div>
            @endforelse
        </div>
        
        <div id="noColorwaysNoticeDynamic" style="display: none; text-align: center; padding: 24px 16px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; color: #64748b; font-size: 13px; margin-top: 12px;">
            <div style="font-size: 24px; margin-bottom: 4px;">⚪</div>
            <strong>No colorway variants added.</strong>
            <p style="margin: 4px 0 0; font-size: 12px;">This product will display without color swatches. Click <strong>"➕ Add Colorway"</strong> above to add customized shades with photos.</p>
        </div>
    </div>

    <label class="span-2">Description

        <textarea name="description" rows="4">{{ old('description',$product->description) }}</textarea>

    </label>



    <label class="check span-2">

        <input type="checkbox" name="status" value="1" @checked(old('status',$product->exists?$product->status:true))>

        Product is active and visible

    </label>



    <div class="span-2 form-note">

        @if($product->exists)

            <strong>Current physical stock:</strong> {{ number_format($product->stock,2) }} ·

            <strong>Reserved:</strong> {{ number_format($product->reserved_stock,2) }} ·

            <strong>Available:</strong> {{ number_format($product->available_stock,2) }}

            @if($product->has_sizes) · <strong>Sizes:</strong> {{ implode(', ', $product->normalizedSizes()) }} @endif

            @if(auth()->user()->hasPermission('inventory.manage')) · Use Stock Management to change quantities. @endif

        @else

            Cashier-created products start with zero stock. Admin can add stock from Stock Management. Enable sizes only when the customer must choose a size before buying.

        @endif

    </div>



    <div class="span-2">

        <button class="btn primary">{{ $product->exists ? 'Save Changes' : 'Create Product' }}</button>

    </div>

</form>

</div>

@endsection



@push('scripts')

<script>

(function () {
    const toggle = document.getElementById('hasSizesToggle');
    const panel = document.getElementById('sizeOptionsPanel');
    const input = document.getElementById('sizesText');
    const baseSellingInput = document.getElementById('sellingPriceInput');
    const refBaseEl = document.getElementById('refBaseSellingPrice');
    const container = document.getElementById('sizePricingRows');

    if (!toggle || !panel || !input) return;

    const syncPanel = () => { 
        panel.hidden = !toggle.checked; 
        if (toggle.checked) renderSizePricingRows();
    };

    toggle.addEventListener('change', syncPanel);
    syncPanel();

    if (baseSellingInput && refBaseEl) {
        baseSellingInput.addEventListener('input', () => {
            const val = parseFloat(baseSellingInput.value) || 0;
            refBaseEl.textContent = val.toFixed(2);
        });
    }

    const INITIAL_SIZE_PRICES = @json($existingSizePrices ?? []);

    function renderSizePricingRows() {
        if (!container) return;
        const rawSizes = input.value.split(',').map(v => v.trim()).filter(Boolean);
        const sizes = [...new Set(rawSizes)];

        const currentDOM = {};
        container.querySelectorAll('.size-pricing-row').forEach(row => {
            const sz = row.getAttribute('data-size');
            const selling = row.querySelector('.size-selling-input')?.value || '';
            const discount = row.querySelector('.size-discount-input')?.value || '';
            currentDOM[sz] = { selling, discount };
        });

        container.innerHTML = '';

        if (sizes.length === 0) {
            container.innerHTML = '<div style="text-align: center; padding: 14px; color: #94a3b8; font-size: 12px; background: #ffffff; border-radius: 8px; border: 1px dashed #cbd5e1;">Type sizes above (e.g. 50ml, 100ml) or click presets to configure prices per size.</div>';
            return;
        }

        sizes.forEach(sz => {
            const norm = sz.toLowerCase();
            const fromDOM = currentDOM[sz];
            const fromInitial = INITIAL_SIZE_PRICES[sz] || INITIAL_SIZE_PRICES[norm] || {};
            const selling = fromDOM !== undefined ? fromDOM.selling : (fromInitial.selling_price ?? '');
            const discount = fromDOM !== undefined ? fromDOM.discount : (fromInitial.discount_price ?? '');

            const row = document.createElement('div');
            row.className = 'size-pricing-row';
            row.setAttribute('data-size', sz);
            row.style.cssText = 'display: grid; grid-template-columns: 100px 1.2fr 1.2fr 100px; gap: 10px; align-items: center; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;';

            row.innerHTML = `
                <div>
                    <strong style="display: inline-block; background: #0f172a; color: #ffffff; font-size: 12px; font-weight: 800; padding: 4px 10px; border-radius: 6px;">
                        ${sz}
                    </strong>
                </div>
                <div>
                    <input type="number" step="0.01" min="0" 
                           name="size_prices[${sz}][selling_price]" 
                           class="size-selling-input" 
                           placeholder="e.g. 5000" 
                           value="${selling}"
                           oninput="updateSizeDiscountBadge(this)"
                           style="width: 100%; padding: 6px 10px; font-size: 13px; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div>
                    <input type="number" step="0.01" min="0" 
                           name="size_prices[${sz}][discount_price]" 
                           class="size-discount-input" 
                           placeholder="e.g. 4500 (optional)" 
                           value="${discount}"
                           oninput="updateSizeDiscountBadge(this)"
                           style="width: 100%; padding: 6px 10px; font-size: 13px; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div style="text-align: right;">
                    <span class="size-disc-badge" style="font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 999px; display: none;"></span>
                </div>
            `;

            container.appendChild(row);
            const sellInput = row.querySelector('.size-selling-input');
            if (sellInput) updateSizeDiscountBadge(sellInput);
        });
    }

    input.addEventListener('input', renderSizePricingRows);

    document.querySelectorAll('.size-preset').forEach(button => {
        button.addEventListener('click', () => {
            const values = input.value.split(',').map(v => v.trim()).filter(Boolean);
            const exists = values.some(v => v.toLowerCase() === button.dataset.size.toLowerCase());
            if (!exists) values.push(button.dataset.size);
            input.value = values.join(', ');
            renderSizePricingRows();
            input.focus();
        });
    });

    renderSizePricingRows();
})();

function updateSizeDiscountBadge(inputEl) {
    const row = inputEl.closest('.size-pricing-row');
    if (!row) return;
    const sellVal = parseFloat(row.querySelector('.size-selling-input')?.value);
    const discVal = parseFloat(row.querySelector('.size-discount-input')?.value);
    const badge = row.querySelector('.size-disc-badge');
    if (!badge) return;

    if (!isNaN(sellVal) && !isNaN(discVal) && discVal > 0 && discVal < sellVal && sellVal > 0) {
        const pct = Math.round(((sellVal - discVal) / sellVal) * 100);
        badge.textContent = `-${pct}% OFF`;
        badge.style.display = 'inline-block';
        badge.style.background = '#fee2e2';
        badge.style.color = '#dc2626';
    } else {
        badge.style.display = 'none';
    }
}

// Colorways Dynamic Row Manager
let cwIndexCounter = {{ count($existingColorways) > 0 ? count($existingColorways) + 50 : 0 }};

function addColorwayRow() {
    const list = document.getElementById('colorwaysList');
    const notice = document.getElementById('noColorwaysNotice');
    const dynamicNotice = document.getElementById('noColorwaysNoticeDynamic');
    if (notice) notice.style.display = 'none';
    if (dynamicNotice) dynamicNotice.style.display = 'none';

    cwIndexCounter++;
    const idx = cwIndexCounter;
    const count = list.querySelectorAll('.colorway-row').length + 1;

    const row = document.createElement('div');
    row.className = 'colorway-row';
    row.setAttribute('data-index', idx);
    row.style.cssText = 'display: grid; grid-template-columns: auto 1fr 120px 1.5fr auto; gap: 12px; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 14px; border-radius: 10px; margin-top: 4px;';

    row.innerHTML = `
        <div class="cw-index-badge" style="font-size: 12px; font-weight: 800; color: #94a3b8; width: 24px; text-align: center;">
            #${count}
        </div>
        <div>
            <label style="margin: 0; font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Colorway Name *</label>
            <input type="text" name="colorways[${idx}][name]" placeholder="e.g. Royal Blue" required style="width: 100%; padding: 7px 10px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>
        <div>
            <label style="margin: 0; font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Swatch Color</label>
            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="color" name="colorways[${idx}][color]" value="#2563eb" oninput="this.nextElementSibling.textContent=this.value" style="width: 38px; height: 34px; padding: 2px; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; background: #ffffff;">
                <span class="color-hex-label" style="font-size: 11px; font-family: monospace; color: #64748b;">#2563eb</span>
            </div>
        </div>
        <div>
            <label style="margin: 0; font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Colorway Image</label>
            <input type="file" name="colorways[${idx}][image_file]" accept="image/png,image/jpeg,image/webp" style="font-size: 11px; width: 100%;">
        </div>
        <div>
            <label style="margin: 0; font-size: 11px; visibility: hidden; display: block; margin-bottom: 4px;">Action</label>
            <button type="button" class="btn-remove-cw" onclick="removeColorwayRow(this)" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 7px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;" title="Remove this colorway">
                ✕ Delete
            </button>
        </div>
    `;

    list.appendChild(row);
    const textInput = row.querySelector('input[type="text"]');
    if (textInput) textInput.focus();
}

function removeColorwayRow(btn) {
    const row = btn.closest('.colorway-row');
    if (!row) return;
    const list = document.getElementById('colorwaysList');
    row.remove();

    const rows = list.querySelectorAll('.colorway-row');
    rows.forEach((r, i) => {
        const badge = r.querySelector('.cw-index-badge');
        if (badge) badge.textContent = `#${i + 1}`;
    });

    if (rows.length === 0) {
        const notice = document.getElementById('noColorwaysNotice');
        const dynamicNotice = document.getElementById('noColorwaysNoticeDynamic');
        if (notice) notice.style.display = 'block';
        else if (dynamicNotice) dynamicNotice.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const addBtn = document.getElementById('addColorwayBtn');
    if (addBtn) {
        addBtn.addEventListener('click', addColorwayRow);
    }
});
</script>

@endpush

