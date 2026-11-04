@extends('layouts.customer')
@section('title','Cart')
@section('content')
<section class="vc-page-head">
    <div><span class="vc-kicker">SHOPPING CART</span><h1>Your cart</h1><p>Review product, selected colorway, size, and quantity before checkout.</p></div>
    <a class="vc-outline-button" href="{{ route('shop.index') }}">Continue shopping</a>
</section>

<div class="vc-cart-layout">
<section class="vc-panel vc-cart-panel">
    @forelse($items as $item)
        <div class="vc-cart-item">
            <a class="vc-cart-image" href="{{ route('shop.show',$item['product']) }}">
                @if(!empty($item['image']))
                    <img src="{{ $item['image'] }}" alt="{{ $item['product']->name }}" loading="lazy">
                @elseif($item['product']->image_url)
                    <img src="{{ $item['product']->image_url }}" alt="{{ $item['product']->name }}" loading="lazy">
                @else
                    <span class="vc-product-letter">{{ strtoupper(substr($item['product']->name,0,1)) }}</span>
                @endif
            </a>
            <div class="vc-cart-info">
                <a href="{{ route('shop.show',$item['product']) }}">{{ $item['product']->name }}</a>
                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap; margin-top: 4px;">
                    @if(!empty($item['color']))
                        <span class="vc-cart-color-chip" style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 999px; background: #f1f5f9; font-size: 11px; font-weight: 700; color: #1e293b; border: 1px solid #cbd5e1;">
                            @if(!empty($item['colorway']['color']))
                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: {{ $item['colorway']['color'] }}; display: inline-block; border: 1px solid rgba(0,0,0,0.15);"></span>
                            @endif
                            Color: {{ $item['color'] }}
                        </span>
                    @endif
                    @if($item['size'])
                        <span class="vc-cart-size-chip">Size: {{ $item['size'] }}</span>
                    @endif
                </div>
                <small style="margin-top: 4px;">
                    @if($item['has_discount'])
                        <del style="color: #94a3b8; margin-right: 4px;">{{ $companySettings->currency_symbol }}{{ number_format($item['original_price'],2) }}</del>
                        <strong style="color: #e11d48; font-weight: 750;">{{ $companySettings->currency_symbol }}{{ number_format($item['unit_price'],2) }}</strong>
                        <span style="background: #ffe4e6; color: #be123c; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">-{{ $item['discount_percent'] }}% OFF</span>
                    @else
                        {{ $companySettings->currency_symbol }}{{ number_format($item['unit_price'],2) }} each
                    @endif
                    · {{ number_format($item['product']->available_stock,0) }} in stock
                </small>
            </div>
            <form method="POST" action="{{ route('cart.update',$item['product']) }}" class="vc-qty-form">
                @csrf @method('PUT')
                @if($item['size'])<input type="hidden" name="size" value="{{ $item['size'] }}">@endif
                @if(!empty($item['color']))<input type="hidden" name="color" value="{{ $item['color'] }}">@endif
                <input type="number" name="quantity" min="0" max="{{ floor($item['product']->available_stock) }}" value="{{ $item['quantity'] }}">
                <button type="submit">Update</button>
            </form>
            <strong class="vc-cart-line-total">{{ $companySettings->currency_symbol }}{{ number_format($item['line_total'],2) }}</strong>
            <form method="POST" action="{{ route('cart.remove',$item['product']) }}">
                @csrf @method('DELETE')
                @if($item['size'])<input type="hidden" name="size" value="{{ $item['size'] }}">@endif
                @if(!empty($item['color']))<input type="hidden" name="color" value="{{ $item['color'] }}">@endif
                <button class="vc-remove" type="submit">Remove</button>
            </form>
        </div>
    @empty
        <div class="vc-empty">Your cart is empty. <a href="{{ route('shop.index') }}">Browse products</a>.</div>
    @endforelse
</section>

<aside class="vc-panel vc-summary">
    <span class="vc-kicker">ORDER SUMMARY</span><h2>Summary</h2>
    <div><span>Subtotal</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($subtotal,2) }}</strong></div>
    <div><span>Delivery</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($deliveryFee,2) }}</strong></div>
    <div class="vc-summary-total"><span>Total</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($total,2) }}</strong></div>
    @if($items->isNotEmpty())<a class="vc-primary-button vc-block-button" href="{{ route('checkout') }}">Proceed to checkout</a>@endif
</aside>
</div>
@endsection
