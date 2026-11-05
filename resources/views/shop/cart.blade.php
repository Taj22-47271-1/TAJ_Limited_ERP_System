@extends('layouts.customer')
@section('title','Shopping Bag — taj. Luxury')
@section('content')

@php
    $currency = $companySettings->currency_symbol ?? '৳';
@endphp

<div class="lux-cart-page">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 32px; border-bottom: 1px solid var(--lux-border); padding-bottom: 20px;">
        <div>
            <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; color: var(--lux-accent-gold);">
                YOUR SELECTION
            </span>
            <h1 style="font-size: 36px; font-weight: 900; letter-spacing: -0.04em; margin: 4px 0 0; color: var(--lux-black);">Shopping Bag</h1>
        </div>
        <a href="{{ route('shop.index') }}" style="font-size: 13px; font-weight: 700; color: var(--lux-black); text-decoration: none;">
            Continue Shopping →
        </a>
    </div>

    @if(empty($items))
        <div style="text-align: center; padding: 80px 20px; background: var(--lux-surface); border-radius: var(--lux-radius-md); border: 1px solid var(--lux-border);">
            <div style="font-size: 40px; margin-bottom: 12px;">🛍️</div>
            <h2 style="font-size: 20px; font-weight: 800; color: var(--lux-black); margin: 0 0 8px;">Your shopping bag is empty</h2>
            <p style="font-size: 14px; color: var(--lux-text-muted); margin-bottom: 24px;">Discover our exclusive collection of luxury fragrances and apparel.</p>
            <a href="{{ route('shop.index') }}" class="lux-btn-black">Explore Catalog</a>
        </div>
    @else
        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 40px; align-items: start;">
            {{-- Items List (img_56) --}}
            <div style="display: flex; flex-direction: column;">
                @foreach($items as $item)
                    <div class="lux-cart-item-row">
                        <div class="lux-cart-thumb">
                            @if(!empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['product']->name }}">
                            @elseif($item['product']->image_url)
                                <img src="{{ $item['product']->image_url }}" alt="{{ $item['product']->name }}">
                            @else
                                <span style="font-weight: 800; font-size: 18px;">{{ strtoupper(substr($item['product']->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('shop.show', $item['product']) }}" style="font-size: 15px; font-weight: 800; color: var(--lux-black); text-decoration: none;">
                                {{ $item['product']->name }}
                            </a>
                            <div class="lux-cart-article">
                                Article number: {{ $item['product']->sku }}
                                @if(!empty($item['color'])) · Color: <strong>{{ $item['color'] }}</strong> @endif
                                @if(!empty($item['size'])) · Size: <strong>{{ $item['size'] }}</strong> @endif
                            </div>
                            <div style="margin-top: 6px; font-size: 13px;">
                                @if($item['has_discount'])
                                    <strong>{{ $currency }}{{ number_format($item['unit_price'], 2) }}</strong>
                                    <del style="color: var(--lux-text-light); margin-left: 6px;">{{ $currency }}{{ number_format($item['original_price'], 2) }}</del>
                                    <span class="lux-badge-discount" style="position: static; margin-left: 6px; vertical-align: middle;">-{{ $item['discount_percent'] }}%</span>
                                @else
                                    <strong>{{ $currency }}{{ number_format($item['unit_price'], 2) }}</strong>
                                @endif
                            </div>
                        </div>

                        {{-- Quantity Stepper --}}
                        <div>
                            <form method="POST" action="{{ route('cart.update', $item['product']) }}" style="display: inline-flex; align-items: center; gap: 8px;">
                                @csrf @method('PUT')
                                @if($item['size'])<input type="hidden" name="size" value="{{ $item['size'] }}">@endif
                                @if(!empty($item['color']))<input type="hidden" name="color" value="{{ $item['color'] }}">@endif
                                <div class="lux-cart-qty-ctrl">
                                    <button type="submit" name="quantity" value="{{ max(0, $item['quantity'] - 1) }}">−</button>
                                    <span>{{ $item['quantity'] }}</span>
                                    <button type="submit" name="quantity" value="{{ min(floor($item['product']->available_stock), $item['quantity'] + 1) }}">+</button>
                                </div>
                            </form>
                        </div>

                        {{-- Line Total & Remove --}}
                        <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                            <strong style="font-size: 16px; font-weight: 900; color: var(--lux-black);">
                                {{ $currency }}{{ number_format($item['line_total'], 2) }}
                            </strong>
                            <form method="POST" action="{{ route('cart.remove', $item['product']) }}">
                                @csrf @method('DELETE')
                                @if($item['size'])<input type="hidden" name="size" value="{{ $item['size'] }}">@endif
                                @if(!empty($item['color']))<input type="hidden" name="color" value="{{ $item['color'] }}">@endif
                                <button type="submit" style="background: none; border: 0; color: var(--lux-text-light); cursor: pointer; font-size: 13px;" title="Remove item">
                                    ✕
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Summary Card --}}
            <div style="background: var(--lux-surface); border: 1px solid var(--lux-border); border-radius: var(--lux-radius-md); padding: 28px;">
                <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 20px; color: var(--lux-black);">Order Summary</h3>
                <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 10px;">
                    <span style="color: var(--lux-text-muted);">Subtotal</span>
                    <strong style="color: var(--lux-black);">{{ $currency }}{{ number_format($subtotal, 2) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 16px;">
                    <span style="color: var(--lux-text-muted);">Estimated Delivery</span>
                    <strong style="color: var(--lux-black);">Calculated at checkout</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 900; padding-top: 14px; border-top: 1px solid var(--lux-border); color: var(--lux-black); margin-bottom: 24px;">
                    <span>Total</span>
                    <span>{{ $currency }}{{ number_format($subtotal, 2) }}</span>
                </div>
                <a href="{{ route('checkout') }}" class="lux-btn-black" style="width: 100%; height: 50px; font-size: 14px;">
                    Proceed to Checkout →
                </a>
            </div>
        </div>
    @endif
</div>

@endsection
