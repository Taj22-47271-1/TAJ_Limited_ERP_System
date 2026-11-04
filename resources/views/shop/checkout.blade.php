@extends('layouts.customer')
@section('title','Checkout')
@section('content')
<section class="vc-page-head">
    <div><span class="vc-kicker">CHECKOUT</span><h1>Complete order</h1><p>Enter your delivery details and choose your payment method.</p></div>
    <a class="vc-outline-button" href="{{ route('cart.index') }}">← Back to cart</a>
</section>

<form class="vc-checkout-layout" method="POST" action="{{ route('checkout.store') }}" id="checkoutForm">
@csrf
<section class="vc-panel vc-checkout-form">
    <span class="vc-kicker">DELIVERY INFORMATION</span><h2>Shipping address</h2>
    <div class="vc-form-grid">
        <label><span>Full name</span><input name="full_name" value="{{ old('full_name',auth()->user()->name) }}" required></label>
        <label><span>Phone number</span><input name="phone" value="{{ old('phone',auth()->user()->phone) }}" required></label>
        <label class="vc-span-2"><span>Email address</span><input type="email" name="email" value="{{ old('email',auth()->user()->email) }}" required></label>
        <label class="vc-span-2"><span>Street address</span><textarea name="address" rows="3" required>{{ old('address',auth()->user()->customer?->address) }}</textarea></label>
        <label><span>City</span><input name="city" value="{{ old('city','Dhaka') }}"></label>
        <label><span>Postal code</span><input name="postal_code" value="{{ old('postal_code') }}"></label>
        <label class="vc-span-2"><span>Order note</span><textarea name="notes" rows="2">{{ old('notes') }}</textarea></label>
    </div>

    <div class="vc-payment-options">
        <span class="vc-kicker">PAYMENT</span><h3>Choose a payment method</h3>
        <label><input type="radio" name="payment_method" value="cod" checked><span><strong>Cash on Delivery</strong><small>Pay when your order is delivered.</small></span></label>
        <label><input type="radio" name="payment_method" value="online"><span><strong>Online Payment</strong><small>Continue through the included demo/sandbox payment flow.</small></span></label>
    </div>
</section>

<aside class="vc-panel vc-summary vc-checkout-summary">
    <span class="vc-kicker">YOUR ORDER</span><h2>Order summary</h2>
    @foreach($items as $item)
        <div class="checkout-item-line">
            <span>
                {{ $item['product']->name }}
                @if(!empty($item['color'])) <small style="color: #64748b; font-weight: 600;">· Color: {{ $item['color'] }}</small>@endif
                @if($item['size']) <small style="color: #64748b; font-weight: 600;">· Size: {{ $item['size'] }}</small>@endif
                × {{ number_format($item['quantity'],0) }}
            </span>
            <strong>{{ $companySettings->currency_symbol }}{{ number_format($item['line_total'],2) }}</strong>
        </div>
    @endforeach
    <div><span>Delivery</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($deliveryFee,2) }}</strong></div>
    <div class="vc-summary-total"><span>Total</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($total,2) }}</strong></div>
    <p class="vc-note">Stock is reserved when the order is placed and permanently deducted when Admin or Cashier approves it. Selected colorways and sizes are saved with the order.</p>
    <button class="order-truck-btn" type="submit" id="orderTruckBtn">
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
@endsection
