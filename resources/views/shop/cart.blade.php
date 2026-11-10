@extends('layouts.customer')
@section('title', 'Shopping Cart — TAJ Limited')
@section('content')

@php
    $currency = $companySettings->currency_symbol ?? '৳';
@endphp

<div style="max-width: 1200px; margin: 36px auto 80px; padding: 0 24px;">
    {{-- Page Header --}}
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 32px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px;">
        <div>
            <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; color: #b45309;">
                YOUR ORDER
            </span>
            <h1 style="font-size: 32px; font-weight: 900; letter-spacing: -0.03em; margin: 6px 0 0; color: #0f172a;">
                Shopping Cart
                @if($items->isNotEmpty())
                    <span style="font-size: 16px; font-weight: 600; color: #64748b;">({{ $items->count() }} {{ Str::plural('item', $items->count()) }})</span>
                @endif
            </h1>
        </div>
        <a href="{{ route('shop.index') }}" style="font-size: 13px; font-weight: 700; color: #0f172a; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            ← Continue Shopping
        </a>
    </div>

    @if($items->isEmpty())
        <div style="text-align: center; padding: 80px 24px; background: #f8fafc; border-radius: 16px; border: 1px dashed #cbd5e1;">
            <div style="font-size: 48px; margin-bottom: 16px;">🛍️</div>
            <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 8px;">Your shopping cart is empty</h2>
            <p style="font-size: 14px; color: #64748b; margin: 0 0 24px;">Explore our catalog to find products and exclusive deals.</p>
            <a href="{{ route('shop.index') }}" class="btn primary" style="height: 44px; padding: 0 24px; font-size: 14px; border-radius: 999px; text-decoration: none; display: inline-flex; align-items: center;">
                Browse Products
            </a>
        </div>
    @else
        <div class="lux-cart-layout" style="display: grid; grid-template-columns: 1fr 380px; gap: 36px; align-items: start;">
            {{-- LEFT COLUMN: Cart Items Table / Cards --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
                <div class="lux-cart-head" style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b; display: grid; grid-template-columns: 2fr 1fr 1fr 40px; gap: 16px;">
                    <span>Product</span>
                    <span style="text-align: center;">Quantity</span>
                    <span style="text-align: right;">Total</span>
                    <span></span>
                </div>

                <div style="display: flex; flex-direction: column;">
                    @foreach($items as $item)
                        <div class="lux-cart-row" style="padding: 24px; border-bottom: 1px solid #f1f5f9; display: grid; grid-template-columns: 2fr 1fr 1fr 40px; gap: 16px; align-items: center;">
                            {{-- Product Media & Info --}}
                            <div style="display: flex; gap: 16px; align-items: center;">
                                <a href="{{ route('shop.show', $item['product']) }}" style="width: 80px; height: 80px; flex-shrink: 0; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; display: flex; align-items: center; justify-content: center; padding: 6px; text-decoration: none;">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['product']->name }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                </a>
                                <div>
                                    <a href="{{ route('shop.show', $item['product']) }}" style="font-size: 15px; font-weight: 800; color: #0f172a; text-decoration: none; line-height: 1.3; display: block; margin-bottom: 4px;">
                                        {{ $item['product']->name }}
                                    </a>
                                    
                                    {{-- Selected Size / Color Attributes --}}
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 6px;">
                                        @if(!empty($item['color']))
                                            <span style="font-size: 11px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 2px 8px; border-radius: 999px; border: 1px solid #e2e8f0;">
                                                Color: {{ $item['color'] }}
                                            </span>
                                        @endif
                                        @if(!empty($item['size']))
                                            <span style="font-size: 11px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 2px 8px; border-radius: 999px; border: 1px solid #e2e8f0;">
                                                Size: {{ $item['size'] }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Unit Price Breakdown --}}
                                    <div style="font-size: 13px;">
                                        @if($item['has_discount'])
                                            <span style="font-weight: 800; color: #dc2626;">{{ $currency }}{{ number_format($item['unit_price'], 2) }}</span>
                                            <del style="font-size: 12px; color: #94a3b8; margin-left: 4px;">{{ $currency }}{{ number_format($item['original_price'], 2) }}</del>
                                            <span style="font-size: 10px; font-weight: 800; color: #dc2626; background: #fef2f2; padding: 1px 6px; border-radius: 4px; margin-left: 4px;">
                                                -{{ $item['discount_percent'] }}%
                                            </span>
                                        @else
                                            <span style="font-weight: 700; color: #334155;">{{ $currency }}{{ number_format($item['unit_price'], 2) }}</span>
                                        @endif
                                        <small style="color: #94a3b8; font-size: 11px;">each</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Quantity Stepper --}}
                            <div style="display: flex; justify-content: center;">
                                <form method="POST" action="{{ route('cart.update', $item['product']) }}" class="lux-cart-qty-form" style="display: inline-flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 999px; overflow: hidden; background: #ffffff; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                                    @csrf @method('PUT')
                                    @if($item['size'])<input type="hidden" name="size" value="{{ $item['size'] }}">@endif
                                    @if(!empty($item['color']))<input type="hidden" name="color" value="{{ $item['color'] }}">@endif
                                    <input type="hidden" name="quantity" class="qty-field" value="{{ (int)$item['quantity'] }}">
                                    
                                    <button type="button" class="lux-cart-step-btn" onclick="stepCartRow(this, -1, {{ (int)$item['product']->available_stock }})" title="Decrease quantity" style="width: 36px; height: 36px; border: 0; background: #f8fafc; font-size: 16px; font-weight: 800; color: #475569; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; justify-content: center;">−</button>
                                    
                                    <span class="qty-val-text" style="width: 36px; text-align: center; font-size: 14px; font-weight: 800; color: #0f172a; user-select: none;">
                                        {{ (int)$item['quantity'] }}
                                    </span>
                                    
                                    <button type="button" class="lux-cart-step-btn" onclick="stepCartRow(this, 1, {{ (int)$item['product']->available_stock }})" title="Increase quantity" style="width: 36px; height: 36px; border: 0; background: #f8fafc; font-size: 16px; font-weight: 800; color: #475569; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; justify-content: center;">+</button>
                                </form>
                            </div>

                            {{-- Subtotal for Product --}}
                            <div style="text-align: right;">
                                <span style="font-size: 16px; font-weight: 900; color: #0f172a;">
                                    {{ $currency }}{{ number_format($item['line_total'], 2) }}
                                </span>
                            </div>

                            {{-- Remove Button --}}
                            <div style="text-align: right;">
                                <form method="POST" action="{{ route('cart.remove', $item['product']) }}">
                                    @csrf @method('DELETE')
                                    @if($item['size'])<input type="hidden" name="size" value="{{ $item['size'] }}">@endif
                                    @if(!empty($item['color']))<input type="hidden" name="color" value="{{ $item['color'] }}">@endif
                                    <button type="submit" style="width: 32px; height: 32px; border-radius: 50%; border: 1px solid #f1f5f9; background: #ffffff; color: #94a3b8; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s;" 
                                            onmouseover="this.style.color='#ef4444'; this.style.borderColor='#fca5a5'; this.style.background='#fef2f2';"
                                            onmouseout="this.style.color='#94a3b8'; this.style.borderColor='#f1f5f9'; this.style.background='#ffffff';"
                                            title="Remove from cart">
                                        ✕
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT COLUMN: Cart Summary --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
                <h3 style="font-size: 18px; font-weight: 900; color: #0f172a; margin: 0 0 20px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
                    Cart Summary
                </h3>

                {{-- Subtotal --}}
                <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 12px;">
                    <span style="color: #64748b;">Subtotal</span>
                    <strong style="color: #0f172a;">{{ $currency }}{{ number_format($originalSubtotal, 2) }}</strong>
                </div>

                {{-- Discount --}}
                @if($discountTotal > 0)
                    <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 12px;">
                        <span style="color: #10b981; font-weight: 600;">Total Discount</span>
                        <strong style="color: #10b981;">-{{ $currency }}{{ number_format($discountTotal, 2) }}</strong>
                    </div>
                @endif

                {{-- Delivery Charge --}}
                <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 16px;">
                    <span style="color: #64748b;">Delivery Charge</span>
                    @if($deliveryFee == 0)
                        <span style="font-weight: 800; color: #10b981; background: #ecfdf5; padding: 2px 8px; border-radius: 4px; font-size: 12px;">FREE</span>
                    @else
                        <strong style="color: #0f172a;">{{ $currency }}{{ number_format($deliveryFee, 2) }}</strong>
                    @endif
                </div>

                @if($subtotal < 3000 && $subtotal > 0)
                    <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #b45309; font-weight: 600; margin-bottom: 18px; line-height: 1.4;">
                        🚚 Add <strong>{{ $currency }}{{ number_format(3000 - $subtotal, 2) }}</strong> more to get <strong>FREE delivery</strong>!
                    </div>
                @endif

                {{-- Grand Total --}}
                <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 900; color: #0f172a; padding-top: 16px; border-top: 2px solid #f1f5f9; margin-bottom: 24px;">
                    <span>Grand Total</span>
                    <span>{{ $currency }}{{ number_format($total, 2) }}</span>
                </div>

                {{-- Proceed to Checkout Button --}}
                <a href="{{ route('checkout') }}" class="btn primary" style="width: 100%; height: 50px; font-size: 15px; font-weight: 800; border-radius: 12px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.1);">
                    Proceed to Checkout <span>→</span>
                </a>

                <div style="margin-top: 18px; text-align: center; font-size: 12px; color: #94a3b8;">
                    🔒 Secure checkout · Cash on Delivery available
                </div>
            </div>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function stepCartRow(btn, delta, maxStock) {
    const form = btn.closest('form');
    if (!form) return;
    const input = form.querySelector('.qty-field');
    const display = form.querySelector('.qty-val-text');
    let current = parseInt(input.value) || 1;
    let next = current + delta;

    if (next <= 0) {
        if (!confirm('Remove this item from your cart?')) {
            return;
        }
        next = 0;
    } else if (maxStock && next > maxStock) {
        alert('Only ' + maxStock + ' units are available in stock.');
        return;
    }

    input.value = next;
    if (display) display.textContent = next;
    btn.disabled = true;
    form.submit();
}
</script>
@endpush
