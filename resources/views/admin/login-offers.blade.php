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
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                    Festival / Offer Badge
                                    <input name="slides[{{ $index }}][badge]" value="{{ $slide['badge'] ?? '' }}" placeholder="e.g. 🎉 EID UL FITR EXCLUSIVE" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                                </label>
                                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                    Discount Tag
                                    <input name="slides[{{ $index }}][discount]" value="{{ $slide['discount'] ?? '' }}" placeholder="e.g. 15% DISCOUNT" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                                </label>
                            </div>

                            <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                Main Offer Title
                                <input name="slides[{{ $index }}][title]" value="{{ $slide['title'] ?? '' }}" placeholder="e.g. Exclusive Eid Ethnic Luxury Collection" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                            </label>

                            <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                Subtitle / Description
                                <textarea name="slides[{{ $index }}][subtitle]" rows="2" placeholder="e.g. Handcrafted pure silk sarees, royal festive shirts & celebratory discounts." style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">{{ $slide['subtitle'] ?? '' }}</textarea>
                            </label>

                            <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                                Offer Ribbon Tag
                                <input name="slides[{{ $index }}][tag]" value="{{ $slide['tag'] ?? 'SPECIAL OFFER' }}" placeholder="e.g. EID OFFER, PUJA MEGA OFFER, BORO DIN" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                            </label>
                        </div>

                        <!-- Image Upload / Preview -->
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <span style="font-size:12px;font-weight:600;">Slide Graphic / Banner</span>
                            <div style="height:150px;border:1px solid #d1d5db;border-radius:8px;overflow:hidden;background:#0f172a;display:grid;place-items:center;">
                                @php
                                    $imgUrl = !empty($slide['image'])
                                        ? (str_starts_with($slide['image'], 'http') || str_starts_with($slide['image'], 'storage/') || str_starts_with($slide['image'], 'assets/') ? asset($slide['image']) : asset('storage/' . $slide['image']))
                                        : asset('assets/images/offers/eid-offer.svg');
                                @endphp
                                <img src="{{ $imgUrl }}" alt="Offer preview" style="max-height:100%;max-width:100%;object-fit:contain;">
                            </div>
                            <input type="hidden" name="slides[{{ $index }}][existing_image]" value="{{ $slide['image'] ?? '' }}">
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

    <div style="position:sticky;bottom:20px;background:#fff;padding:16px 20px;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 10px 25px rgba(0,0,0,0.08);display:flex;justify-content:flex-end;gap:12px;z-index:20;">
        <a href="{{ route('admin.login-offers') }}" class="btn ghost">Discard Changes</a>
        <button type="submit" class="btn primary">Save Login Offers</button>
    </div>
</form>

<script>
let slideCounter = {{ count($slides) }};

document.getElementById('addSlideBtn').onclick = function() {
    const idx = slideCounter++;
    const container = document.getElementById('slidesContainer');
    const newCard = document.createElement('div');
    newCard.className = 'card slide-item-card';
    newCard.style = 'background:#fafafa;border:1px solid #e5e7eb;border-radius:12px;padding:20px;position:relative;';
    newCard.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <span style="font-weight:700;font-size:14px;color:#1e3a8a;display:flex;align-items:center;gap:6px;">
                <span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:#1e3a8a;color:#fff;text-align:center;line-height:24px;font-size:12px;">${idx + 1}</span>
                Slide #<span class="slide-num">${idx + 1}</span>
            </span>
            <button type="button" class="btn danger-outline remove-slide-btn" style="padding:4px 10px;font-size:12px;" onclick="removeSlide(this)">Remove</button>
        </div>
        <div style="display:grid;grid-template-columns:minmax(0,1.2fr) minmax(220px,.8fr);gap:20px;">
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                        Festival / Offer Badge
                        <input name="slides[${idx}][badge]" value="🔥 SPECIAL FESTIVE OFFER" placeholder="e.g. 🎉 EID UL FITR EXCLUSIVE" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                    </label>
                    <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                        Discount Tag
                        <input name="slides[${idx}][discount]" value="10% DISCOUNT" placeholder="e.g. 15% DISCOUNT" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                    </label>
                </div>
                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                    Main Offer Title
                    <input name="slides[${idx}][title]" value="Seasonal Celebration Offer" placeholder="e.g. Exclusive Eid Ethnic Luxury Collection" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" required>
                </label>
                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                    Subtitle / Description
                    <textarea name="slides[${idx}][subtitle]" rows="2" placeholder="e.g. Special limited-time discount on top products." style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">Enjoy extra savings on luxury items this season.</textarea>
                </label>
                <label style="font-size:12px;font-weight:600;display:flex;flex-direction:column;gap:4px;">
                    Offer Ribbon Tag
                    <input name="slides[${idx}][tag]" value="FESTIVE SPECIAL" placeholder="e.g. EID OFFER, PUJA MEGA OFFER" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                </label>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <span style="font-size:12px;font-weight:600;">Slide Graphic / Banner</span>
                <div style="height:150px;border:1px solid #d1d5db;border-radius:8px;overflow:hidden;background:#0f172a;display:grid;place-items:center;">
                    <img src="{{ asset('assets/images/offers/eid-offer.svg') }}" alt="Offer preview" style="max-height:100%;max-width:100%;object-fit:contain;">
                </div>
                <input type="hidden" name="slides[${idx}][existing_image]" value="assets/images/offers/eid-offer.svg">
                <label style="font-size:12px;color:#4b5563;cursor:pointer;">
                    <span>Upload new image / banner:</span>
                    <input type="file" name="slide_image_${idx}" accept="image/*" style="font-size:12px;margin-top:4px;display:block;">
                </label>
            </div>
        </div>
    `;
    container.appendChild(newCard);
};

function removeSlide(btn) {
    const card = btn.closest('.slide-item-card');
    if (document.querySelectorAll('.slide-item-card').length > 1) {
        card.remove();
        // Renumber slides
        document.querySelectorAll('.slide-item-card').forEach((c, i) => {
            c.querySelector('.slide-num').textContent = (i + 1);
        });
    } else {
        alert('You must have at least one slide.');
    }
}
</script>
@endsection
