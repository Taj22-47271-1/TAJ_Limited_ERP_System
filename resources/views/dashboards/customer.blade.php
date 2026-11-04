@extends('layouts.customer')
@section('title','Store')
@section('content')
@php $heroProducts = $featuredProducts->take(3)->values(); @endphp
<section class="vc-home-intro">
    <div>
        <span class="vc-kicker">TAJ LIMITED ONLINE STORE</span>
        <h1>Simple shopping.<br>Live inventory.</h1>
    </div>
    <p>Browse available products, pay online or choose cash on delivery, then track every approved order from your account.</p>
</section>

@if($heroProducts->isNotEmpty())
<section class="vc-hero-grid">
    @foreach($heroProducts as $index => $product)
        <a href="{{ route('shop.show',$product) }}" class="vc-hero-product vc-hero-product-{{ $index + 1 }}">
            @if($product->has_discount)
                <span class="vc-discount-tag" style="top: 14px; right: 14px;">-{{ $product->discount_percent_calculated }}% OFF</span>
            @endif
            <div class="vc-product-art">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                @else
                    <span class="vc-product-letter">{{ strtoupper(substr($product->name,0,1)) }}</span>
                @endif
            </div>
            <div class="vc-product-label">
                <span>{{ $product->name }}</span>
                <strong>
                    @if($product->has_discount)
                        {{ $companySettings->currency_symbol }}{{ number_format($product->effective_price,2) }}
                    @else
                        {{ $companySettings->currency_symbol }}{{ number_format($product->selling_price,2) }}
                    @endif
                </strong>
            </div>
        </a>
    @endforeach
</section>
@endif

@include('shop.partials.clothing-showcase')

<section class="vc-section-head">
    <div><span class="vc-kicker">NEW ARRIVALS</span><h2>Available products</h2></div>
    <a href="{{ route('shop.index') }}">Shop all <span>→</span></a>
</section>

<div class="vc-product-grid">
    @foreach($featuredProducts as $product)
        <article class="vc-product-card">
            <a class="vc-product-media" href="{{ route('shop.show',$product) }}">
                @if($product->has_discount)
                    <span class="vc-discount-tag">-{{ $product->discount_percent_calculated }}%</span>
                @endif
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async">
                @else
                    <span class="vc-product-letter">{{ strtoupper(substr($product->name,0,1)) }}</span>
                @endif
                <span class="vc-stock-chip">{{ number_format($product->available_stock,0) }} {{ $product->unit }} available</span>
            </a>
            <div class="vc-product-row">
                <div>
                    <a href="{{ route('shop.show',$product) }}">{{ $product->name }}</a>
                    <small>
                        {{ $product->category?->name ?? 'Product' }}
                        @if($product->has_sizes) · Sizes {{ implode('/', $product->normalizedSizes()) }}@endif
                        @if(!empty($product->available_colorways)) · {{ count($product->available_colorways) }} Colors @endif
                    </small>
                </div>
                <div class="vc-product-price-col">
                    @if($product->has_discount)
                        <span class="vc-orig-price-tag">{{ $companySettings->currency_symbol }}{{ number_format($product->selling_price,2) }}</span>
                        <strong class="vc-disc-price-tag">{{ $companySettings->currency_symbol }}{{ number_format($product->effective_price,2) }}</strong>
                    @else
                        <strong>{{ $companySettings->currency_symbol }}{{ number_format($product->selling_price,2) }}</strong>
                    @endif
                </div>
            </div>
            @if($product->has_sizes || !empty($product->available_colorways))
                <a class="vc-add-button vc-add-link" href="{{ route('shop.show',$product) }}">Choose options</a>
            @else
                <form method="POST" action="{{ route('cart.add',$product) }}">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button class="vc-add-button" type="submit">Add to cart</button>
                </form>
            @endif
        </article>
    @endforeach
</div>

<section class="vc-account-strip">
    <div><small>Total orders</small><strong>{{ $orderCount }}</strong></div>
    <div><small>Pending approval</small><strong>{{ $pendingCount }}</strong></div>
    <div><small>In processing</small><strong>{{ $processingCount }}</strong></div>
    <div><small>Delivered</small><strong>{{ $deliveredCount }}</strong></div>
</section>

<section class="vc-recent-orders">
    <div class="vc-section-head vc-section-head-tight">
        <div><span class="vc-kicker">YOUR HISTORY</span><h2>Recent orders</h2></div>
        <a href="{{ route('customer.orders') }}">View all orders <span>→</span></a>
    </div>
    <div class="vc-order-list">
        @forelse($recentOrders as $order)
            <a href="{{ route('customer.orders.show',$order) }}">
                <div>
                    <strong>{{ $order->order_no }}</strong>
                    <small>{{ $order->created_at->format('d M Y, h:i A') }} · {{ $order->items->count() }} item(s) · {{ ucfirst($order->payment_method) }}</small>
                </div>
                <span class="status-badge status-{{ $order->order_status }}">{{ ucfirst(str_replace('_',' ',$order->order_status)) }}</span>
                <strong>{{ $companySettings->currency_symbol }}{{ number_format($order->total,2) }}</strong>
                <span class="vc-arrow">›</span>
            </a>
        @empty
            <div class="vc-empty">You haven't placed any orders yet.</div>
        @endforelse
    </div>
</section>
@endsection
