@extends('layouts.app')
@section('title', 'Login Offers & Promo Slider')

@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow dark">MARKETING & PROMOTIONS</span>
        <h1>Login Page Offer Banners & Slider</h1>
        <p>Manage dynamic festive promotional offers (Eid, Puja, Boro Din, 10% discounts) displayed on the login page.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('login') }}" target="_blank" class="btn ghost">Preview Login Page ↗</a>
    </div>
</div>

@if(session('status'))
    <div class="alert success" style="margin-bottom:20px;">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('admin.login-offers.update') }}" enctype="multipart/form-data" class="showcase-form">
    @csrf
    @method('PUT')

    <!-- Global Slider Settings -->
    <div class="card" style="margin-bottom:24px;">
        <h2 style="font-size:18px;margin-bottom:6px;">Slider Controls</h2>
        <p class="muted" style="font-size:13px;margin-bottom:18px;">Configure autoplay behavior and slide transition duration. Slides will automatically loop back from the beginning infinitely.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;align-items:center;">
            <label class="check-toggle" style="display:flex;align-items:center;gap:12px;cursor:pointer;">
                <input type="checkbox" name="enabled" value="1" @checked($offersConfig['enabled'] ?? true) style="width:20px;height:20px;accent-color:#111;">
                <div>
                    <strong style="display:block;font-size:14px;">Enable Login Page Promo Slider</strong>
                    <small class="muted">Show offer slides on the split login screen</small>
                </div>
            </label>

            <div>
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Slide Transition Interval (Seconds)</label>
                <select name="interval_seconds" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;">
                    @foreach([2, 3, 4, 5, 6, 8, 10] as $sec)
                        <option value="{{ $sec }}" @selected(($offersConfig['interval_seconds'] ?? 4) == $sec)>Every {{ $sec }} seconds ({{ $sec * 1000 }}ms)</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Slides List -->
    <div class="card" style="margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
            <div>
                <h2 style="font-size:18px;margin-bottom:4px;">Promotional Offer Slides</h2>
                <p class="muted" style="font-size:13px;margin:0;">Add 3, 4, 6 or as many offer banners as desired. Visitors can click any slide to immediately focus and log in.</p>
            </div>
            <button type="button" class="btn secondary" id="addSlideBtn" style="font-size:13px;">+ Add New Slide</button>
        </div>

        <div id="slidesContainer" style="display:flex;flex-direction:column;gap:20px;">
            @php $slides = $offersConfig['slides'] ?? \App\Models\Setting::defaultLoginOffers()['slides']; @endphp
            @foreach($slides as $index => $slide)
                <div class="card slide-item-card" data-index="{{ $index }}" style="background:#fafafa;border:1px solid #e5e7eb;border-radius:12px;padding:20px;position:relative;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                        <span style="font-weight:700;font-size:14px;color:#1e3a8a;display:flex;align-items:center;gap:6px;">
                            <span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:#1e3a8a;color:#fff;text-align:center;line-height:24px;font-size:12px;">{{ $index + 1 }}</span>
                            Slide #<span class="slide-num">{{ $index + 1 }}</span>
                        </span>
                        @if(count($slides) > 1)
                            <button type="button" class="btn danger-outline remove-slide-btn" style="padding:4px 10px;font-size:12px;" onclick="removeSlide(this)">Remove</button>
                        @endif
                    </div>

                    <div style="display:grid;grid-template-columns:minmax(0,1.2fr) minmax(220px,.8fr);gap:20px;">
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                Featured Catalog Product (Optional - Links Real Pricing & Live Image)
                                <select name="slides[{{ $index }}][product_id]" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" onchange="autoFillProduct(this, {{ $index }})">
                                    <option value="">-- Custom Banner / General Promotion --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" 
                                                data-name="{{ $p->name }}"
                                                data-price="{{ $p->selling_price }}"
                                                data-disc-price="{{ $p->effective_price }}"
                                                data-disc-percent="{{ $p->discount_percent_calculated }}"
                                                data-desc="{{ $p->description }}"
                                                data-img="{{ $p->image_url }}"
                                                @selected(($slide['product_id'] ?? null) == $p->id)>
                                            {{ $p->name }} ({{ $companySettings->currency_symbol }}{{ number_format($p->effective_price, 2) }} @if($p->has_discount) · {{ $p->discount_percent_calculated }}% OFF @endif)
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                    Festival / Offer Badge
                                    <input name="slides[{{ $index }}][badge]" id="slideBadge_{{ $index }}" value="{{ $slide['badge'] ?? '' }}" placeholder="e.g. 🎉 EID UL FITR EXCLUSIVE" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                                </label>
                                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                    Discount Tag
                                    <input name="slides[{{ $index }}][discount]" id="slideDiscount_{{ $index }}" value="{{ $slide['discount'] ?? '' }}" placeholder="e.g. 15% DISCOUNT" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                                </label>
                            </div>

                            <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                Main Offer Title
                                <input name="slides[{{ $index }}][title]" id="slideTitle_{{ $index }}" value="{{ $slide['title'] ?? '' }}" placeholder="e.g. Exclusive Eid Ethnic Luxury Collection" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                            </label>

                            <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                Subtitle / Description
                                <textarea name="slides[{{ $index }}][subtitle]" id="slideSubtitle_{{ $index }}" rows="2" placeholder="e.g. Handcrafted pure silk sarees, royal festive shirts & celebratory discounts." style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">{{ $slide['subtitle'] ?? '' }}</textarea>
                            </label>

                            <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                Offer Ribbon Tag
                                <input name="slides[{{ $index }}][tag]" value="{{ $slide['tag'] ?? 'SPECIAL OFFER' }}" placeholder="e.g. EID OFFER, PUJA MEGA OFFER, BORO DIN" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                            </label>
                        </div>

                        <!-- Image Upload / Preview -->
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <span style="font-size:12px;font-weight:600;">Product / Slide Graphic</span>
                            <div style="height:170px;border:1px solid #d1d5db;border-radius:8px;overflow:hidden;background:#0f172a;display:grid;place-items:center;padding:12px;">
                                @php
                                    $imgUrl = !empty($slide['image'])
                                        ? (str_starts_with($slide['image'], 'http') || str_starts_with($slide['image'], 'storage/') || str_starts_with($slide['image'], 'assets/') ? asset($slide['image']) : asset('storage/' . $slide['image']))
                                        : asset('assets/images/offers/eid-offer.svg');
                                @endphp
                                <img src="{{ $imgUrl }}" id="slideImgPreview_{{ $index }}" alt="Offer preview" style="max-height:100%;max-width:100%;object-fit:contain;">
                            </div>
                            <input type="hidden" name="slides[{{ $index }}][existing_image]" id="slideExistingImg_{{ $index }}" value="{{ $slide['image'] ?? '' }}">
                            <label style="font-size:12px;color:#4b5563;cursor:pointer;">
                                <span>Upload new image / banner:</span>
                                <input type="file" name="slide_image_{{ $index }}" accept="image/*" style="font-size:12px;margin-top:4px;display:block;">
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;margin-bottom:40px;">
        <button type="submit" class="btn primary" style="padding:12px 28px;font-size:14px;">Save Login Offer Slider</button>
    </div>
</form>

<template id="slideTemplate">
    <div class="card slide-item-card" data-index="__INDEX__" style="background:#fafafa;border:1px solid #e5e7eb;border-radius:12px;padding:20px;position:relative;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <span style="font-weight:700;font-size:14px;color:#1e3a8a;display:flex;align-items:center;gap:6px;">
                <span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:#1e3a8a;color:#fff;text-align:center;line-height:24px;font-size:12px;">__NUM__</span>
                Slide #<span class="slide-num">__NUM__</span>
            </span>
            <button type="button" class="btn danger-outline remove-slide-btn" style="padding:4px 10px;font-size:12px;" onclick="removeSlide(this)">Remove</button>
        </div>

        <div style="display:grid;grid-template-columns:minmax(0,1.2fr) minmax(220px,.8fr);gap:20px;">
            <div style="display:flex;flex-direction:column;gap:12px;">
                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                    Featured Catalog Product (Optional)
                    <select name="slides[__INDEX__][product_id]" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" onchange="autoFillProduct(this, __INDEX__)">
                        <option value="">-- Custom Banner / General Promotion --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" 
                                    data-name="{{ $p->name }}"
                                    data-price="{{ $p->selling_price }}"
                                    data-disc-price="{{ $p->effective_price }}"
                                    data-disc-percent="{{ $p->discount_percent_calculated }}"
                                    data-desc="{{ $p->description }}"
                                    data-img="{{ $p->image_url }}">
                                {{ $p->name }} ({{ $companySettings->currency_symbol }}{{ number_format($p->effective_price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </label>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                        Festival / Offer Badge
                        <input name="slides[__INDEX__][badge]" id="slideBadge___INDEX__" value="🎉 SPECIAL OFFER" placeholder="e.g. 🎉 EID UL FITR EXCLUSIVE" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                    </label>
                    <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                        Discount Tag
                        <input name="slides[__INDEX__][discount]" id="slideDiscount___INDEX__" value="10% DISCOUNT" placeholder="e.g. 15% DISCOUNT" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                    </label>
                </div>

                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                    Main Offer Title
                    <input name="slides[__INDEX__][title]" id="slideTitle___INDEX__" value="New Festive Luxury Offer" placeholder="e.g. Exclusive Eid Ethnic Luxury Collection" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                </label>

                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                    Subtitle / Description
                    <textarea name="slides[__INDEX__][subtitle]" id="slideSubtitle___INDEX__" rows="2" placeholder="e.g. Handcrafted festive apparel and seasonal collections." style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">Special promotional offer available for a limited time.</textarea>
                </label>

                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                    Offer Ribbon Tag
                    <input name="slides[__INDEX__][tag]" value="FESTIVE SPECIAL" placeholder="e.g. EID OFFER, PUJA MEGA OFFER" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                </label>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;">
                <span style="font-size:12px;font-weight:600;">Product / Slide Graphic</span>
                <div style="height:170px;border:1px solid #d1d5db;border-radius:8px;overflow:hidden;background:#0f172a;display:grid;place-items:center;padding:12px;">
                    <img src="{{ asset('assets/images/offers/eid-offer.svg') }}" id="slideImgPreview___INDEX__" alt="Offer preview" style="max-height:100%;max-width:100%;object-fit:contain;">
                </div>
                <input type="hidden" name="slides[__INDEX__][existing_image]" id="slideExistingImg___INDEX__" value="assets/images/offers/eid-offer.svg">
                <label style="font-size:12px;color:#4b5563;cursor:pointer;">
                    <span>Upload new image / banner:</span>
                    <input type="file" name="slide_image___INDEX__" accept="image/*" style="font-size:12px;margin-top:4px;display:block;">
                </label>
            </div>
        </div>
    </div>
</template>

<script>
let slideIndexCounter = {{ count($slides) }};

document.getElementById('addSlideBtn').addEventListener('click', function() {
    const container = document.getElementById('slidesContainer');
    const template = document.getElementById('slideTemplate').innerHTML;
    const newNum = container.querySelectorAll('.slide-item-card').length + 1;
    
    let rendered = template.replace(/__INDEX__/g, slideIndexCounter);
    rendered = rendered.replace(/__NUM__/g, newNum);
    
    const wrapper = document.createElement('div');
    wrapper.innerHTML = rendered;
    container.appendChild(wrapper.firstElementChild);
    
    slideIndexCounter++;
    updateRemoveButtons();
});

window.removeSlide = function(btn) {
    const container = document.getElementById('slidesContainer');
    const cards = container.querySelectorAll('.slide-item-card');
    if (cards.length <= 1) {
        alert('You must keep at least one promotional slide!');
        return;
    }
    
    const card = btn.closest('.slide-item-card');
    card.remove();
    
    // Re-index displayed slide numbers
    container.querySelectorAll('.slide-item-card').forEach((c, idx) => {
        c.querySelector('.slide-num').textContent = idx + 1;
    });
    updateRemoveButtons();
};

function updateRemoveButtons() {
    const container = document.getElementById('slidesContainer');
    const cards = container.querySelectorAll('.slide-item-card');
    cards.forEach(card => {
        const removeBtn = card.querySelector('.remove-slide-btn');
        if (removeBtn) {
            removeBtn.style.display = cards.length > 1 ? 'inline-block' : 'none';
        }
    });
}

window.autoFillProduct = function(select, index) {
    const selectedOpt = select.options[select.selectedIndex];
    if (!selectedOpt.value) return;

    const name = selectedOpt.dataset.name;
    const discPercent = selectedOpt.dataset.discPercent;
    const desc = selectedOpt.dataset.desc;
    const img = selectedOpt.dataset.img;

    const titleInput = document.getElementById('slideTitle_' + index);
    if (titleInput) titleInput.value = name;

    const subtitleInput = document.getElementById('slideSubtitle_' + index);
    if (subtitleInput && desc) subtitleInput.value = desc;

    const discountInput = document.getElementById('slideDiscount_' + index);
    if (discountInput && discPercent) discountInput.value = discPercent + '% DISCOUNT';

    const previewImg = document.getElementById('slideImgPreview_' + index);
    const existingImg = document.getElementById('slideExistingImg_' + index);
    if (previewImg && img) previewImg.src = img;
    if (existingImg && img) existingImg.value = img;
};
</script>
@endsection
