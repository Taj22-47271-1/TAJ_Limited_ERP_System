@extends('layouts.customer')
@section('title','Shop')
@section('content')
<section class="vc-shop-head">
    <div><span class="vc-kicker">TAJ LIMITED STORE</span><h1>All products</h1><p>Every item below reflects the same live stock used by the in-house POS.</p></div>
    <a class="vc-outline-button" href="{{ route('cart.index') }}">Cart · {{ number_format(collect(session('cart', []))->sum(fn($line) => is_array($line) ? (float)($line['quantity'] ?? 0) : (float)$line), 0) }}</a>
</section>

<section class="vc-filterbar">
    <form method="GET" action="{{ route('shop.index') }}">
        <div class="vc-filter-search"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 21-4.35-4.35m2.35-5.15A7.5 7.5 0 1 1 4 11.5a7.5 7.5 0 0 1 15 0Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg><input name="q" value="{{ request('q') }}" placeholder="Search by product name or SKU"></div>
        <select name="category" onchange="this.form.submit()"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category')===(string)$category->id)>{{ $category->name }}</option>@endforeach</select>
        <button type="submit">Search</button>
        @if(request()->filled('q') || request()->filled('category'))<a href="{{ route('shop.index') }}">Clear</a>@endif
    </form>
</section>

<div class="vc-product-grid vc-product-grid-shop">
    @forelse($products as $product)
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
                <span class="vc-stock-chip">In stock · {{ number_format($product->available_stock,0) }}</span>
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
    @empty
        <div class="vc-empty vc-span-all">No products matched your search.</div>
    @endforelse
</div>
<div class="pagination customer-pagination">{{ $products->links() }}</div>
@endsection
