@extends('layouts.customer')
@section('title','Checkout — taj. Luxury')
@section('content')

@php
    $currency = $companySettings->currency_symbol ?? '৳';
@endphp

<div class="lux-details-layout" style="max-width: 1040px; margin-top: 24px;">
    {{-- Header --}}
    <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 28px; border-bottom: 1px solid var(--lux-border); padding-bottom: 18px;">
        <div>
            <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; color: var(--lux-accent-gold);">CHECKOUT</span>
            <h1 style="font-size: 32px; font-weight: 900; letter-spacing: -0.03em; margin: 4px 0 0; color: var(--lux-black);">Complete Your Order</h1>
        </div>
        <a href="{{ route('cart.index') }}" style="font-size: 13px; font-weight: 700; color: var(--lux-black); text-decoration: none;">
            ← Back to Cart
        </a>
    </div>

    <form class="vc-checkout-layout" method="POST" action="{{ route('checkout.store') }}" id="checkoutForm">
        @csrf
        {{-- Left: Delivery & Payment Details --}}
        <section class="vc-panel vc-checkout-form" style="background: #ffffff; border: 1px solid var(--lux-border); border-radius: var(--lux-radius-md); padding: 32px;">
            <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; color: var(--lux-text-muted);">
                DELIVERY INFORMATION
            </span>
            <h2 style="font-size: 20px; font-weight: 800; margin: 6px 0 20px; color: var(--lux-black);">Shipping Address</h2>

            <div class="vc-form-grid">
                <label><span>Full name</span><input name="full_name" value="{{ old('full_name',auth()->user()->name) }}" required></label>
                <label><span>Phone number</span><input name="phone" value="{{ old('phone',auth()->user()->phone) }}" required></label>
                <label class="vc-span-2"><span>Email address</span><input type="email" name="email" value="{{ old('email',auth()->user()->email) }}" required></label>
                <label class="vc-span-2"><span>Street address</span><textarea name="address" rows="3" required>{{ old('address',auth()->user()->customer?->address) }}</textarea></label>
                <label><span>City</span><input name="city" value="{{ old('city','Dhaka') }}"></label>
                <label><span>Postal code</span><input name="postal_code" value="{{ old('postal_code') }}"></label>
                <label class="vc-span-2"><span>Order note (optional)</span><textarea name="notes" rows="2">{{ old('notes') }}</textarea></label>
            </div>

            <div class="vc-payment-options" style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--lux-border);">
                <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; color: var(--lux-text-muted);">
                    PAYMENT METHOD
                </span>
                <h3 style="font-size: 18px; font-weight: 800; margin: 6px 0 16px; color: var(--lux-black);">Select Payment</h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border: 1px solid var(--lux-border); border-radius: var(--lux-radius-sm); cursor: pointer; background: var(--lux-surface);">
                        <input type="radio" name="payment_method" value="cod" checked style="accent-color: #000000;">
                        <div>
                            <strong style="display: block; font-size: 14px; color: var(--lux-black);">Cash on Delivery (COD)</strong>
                            <small style="color: var(--lux-text-muted); font-size: 12px;">Pay safely in cash when your parcel is delivered to your door.</small>
                        </div>
                    </label>
                    <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border: 1px solid var(--lux-border); border-radius: var(--lux-radius-sm); cursor: pointer; background: var(--lux-surface);">
                        <input type="radio" name="payment_method" value="online" style="accent-color: #000000;">
                        <div>
                            <strong style="display: block; font-size: 14px; color: var(--lux-black);">Online Digital Payment</strong>
                            <small style="color: var(--lux-text-muted); font-size: 12px;">Cards, bKash, Nagad sandbox verification.</small>
                        </div>
                    </label>
                </div>
            </div>
        </section>

        {{-- Right: Order Summary & Animated Truck Place Order Button --}}
        <aside class="vc-panel vc-summary vc-checkout-summary" style="background: var(--lux-surface); border: 1px solid var(--lux-border); border-radius: var(--lux-radius-md); padding: 32px;">
            <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; color: var(--lux-text-muted);">
                SUMMARY
            </span>
            <h2 style="font-size: 20px; font-weight: 800; margin: 6px 0 20px; color: var(--lux-black);">Your Items</h2>

            <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px;">
                @foreach($items as $item)
                    <div style="display: flex; justify-content: space-between; align-items: baseline; font-size: 13px; border-bottom: 1px solid var(--lux-border); padding-bottom: 10px;">
                        <div>
                            <span style="font-weight: 700; color: var(--lux-text);">{{ $item['product']->name }}</span>
                            @if(!empty($item['color'])) <small style="display: block; color: var(--lux-text-muted); font-size: 11px;">Color: {{ $item['color'] }}</small>@endif
                            @if($item['size']) <small style="display: block; color: var(--lux-text-muted); font-size: 11px;">Size: {{ $item['size'] }}</small>@endif
                            <small style="color: var(--lux-text-light); font-size: 11px;">Qty: {{ number_format($item['quantity'], 0) }}</small>
                        </div>
                        <strong style="font-weight: 800; color: var(--lux-black);">
                            {{ $currency }}{{ number_format($item['line_total'], 2) }}
                        </strong>
                    </div>
                @endforeach
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px;">
                <span style="color: var(--lux-text-muted);">Delivery Fee</span>
                <strong style="color: var(--lux-black);">{{ $currency }}{{ number_format($deliveryFee, 2) }}</strong>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 900; margin-top: 14px; padding-top: 14px; border-top: 2px solid var(--lux-border); color: var(--lux-black);">
                <span>Total</span>
                <span>{{ $currency }}{{ number_format($total, 2) }}</span>
            </div>

            <p style="font-size: 11px; color: var(--lux-text-muted); line-height: 1.5; margin: 18px 0 24px;">
                Stock is reserved immediately upon placement and synchronized with TAJ ERP inventory.
            </p>

            {{-- ANIMATED DELIVERY TRUCK CONFIRMATION BUTTON (input_file_1.png) --}}
            <button class="order-truck-btn lux-order-truck-btn" type="submit" id="orderTruckBtn">
                <span class="default">Place Order</span>
                <span class="success">Order Placed <svg viewBox="0 0 12 10"><polyline points="1.5 6 4.5 9 10.5 1"></polyline></svg></span>
                <div class="box"></div>
                <div class="truck">
                    <div class="back"></div>
                    <div class="front"><div class="window"></div></div>
                    <div class="light top"></div>
                </div>
                <div class="lines"></div>
            </button>
        </aside>
    </form>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkoutForm');
    const btn = document.getElementById('orderTruckBtn');
    if (!form || !btn) return;

    let isSubmitting = false;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (isSubmitting) return;

        // Check HTML5 validity
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        isSubmitting = true;
        btn.disabled = true;
        const defaultSpan = btn.querySelector('.default');
        const originalText = defaultSpan ? defaultSpan.textContent : 'Place Order';
        if (defaultSpan) defaultSpan.textContent = 'Processing...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(function(res) {
            if (!res.ok) {
                return res.json().then(function(err) { throw err; });
            }
            return res.json();
        })
        .then(function(data) {
            if (!data.success) {
                throw new Error(data.message || 'Order could not be placed.');
            }

            // Backend order creation succeeded: run delivery animation
            if (defaultSpan) defaultSpan.style.opacity = '0';
            btn.classList.add('animate');

            // At 2.2s, after box entered and car drove forward, show order placed checkmark
            setTimeout(function() {
                btn.classList.add('done');
            }, 2200);

            // At 2.8s, redirect to order confirmation
            setTimeout(function() {
                window.location.href = data.redirect_url || "{{ route('dashboard') }}";
            }, 2800);
        })
        .catch(function(err) {
            isSubmitting = false;
            btn.disabled = false;
            btn.classList.remove('animate', 'done');
            if (defaultSpan) {
                defaultSpan.style.opacity = '1';
                defaultSpan.textContent = originalText;
            }

            let msg = 'Could not place order. Please review your details and try again.';
            if (err && err.errors) {
                msg = Object.values(err.errors).flat().join('\n');
            } else if (err && err.message) {
                msg = err.message;
            }
            alert(msg);
        });
    });
});
</script>
@endpush
