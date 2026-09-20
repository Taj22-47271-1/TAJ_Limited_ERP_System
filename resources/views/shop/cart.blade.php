@extends('layouts.customer')
@section('title','Cart')
@section('content')
<section class="vc-page-head">
    <div><span class="vc-kicker">SHOPPING CART</span><h1>Your cart</h1><p>Review product, size and quantity before checkout.</p></div>
    <a class="vc-outline-button" href="{{ route('shop.index') }}">Continue shopping</a>
</section>

<div class="vc-cart-layout">
<section class="vc-panel vc-cart-panel">
    @forelse($items as $item)
        <div class="vc-cart-item">
            <a class="vc-cart-image" href="{{ route('shop.show',$item['product']) }}">
                @if($item['product']->image_url)
                    <img src="{{ $item['product']->image_url }}" alt="{{ $item['product']->name }}">
                @else
                    <span class="vc-product-letter">{{ strtoupper(substr($item['product']->name,0,1)) }}</span>
                @endif
            </a>
            <div class="vc-cart-info">
                <a href="{{ route('shop.show',$item['product']) }}">{{ $item['product']->name }}</a>
                @if($item['size'])<span class="vc-cart-size-chip">Size {{ $item['size'] }}</span>@endif
                <small>{{ $companySettings->currency_symbol }}{{ number_format($item['product']->selling_price,2) }} each · {{ number_format($item['product']->available_stock,0) }} available</small>
            </div>
            <form method="POST" action="{{ route('cart.update',$item['product']) }}" class="vc-qty-form">
                @csrf @method('PUT')
                @if($item['size'])<input type="hidden" name="size" value="{{ $item['size'] }}">@endif
                <input type="number" name="quantity" min="0" max="{{ floor($item['product']->available_stock) }}" value="{{ $item['quantity'] }}">
                <button>Update</button>
            </form>
            <strong class="vc-cart-line-total">{{ $companySettings->currency_symbol }}{{ number_format($item['line_total'],2) }}</strong>
            <form method="POST" action="{{ route('cart.remove',$item['product']) }}">
                @csrf @method('DELETE')
                @if($item['size'])<input type="hidden" name="size" value="{{ $item['size'] }}">@endif
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
