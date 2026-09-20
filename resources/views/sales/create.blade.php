@extends('layouts.app')
@section('title','Point of Sale')
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow dark">POINT OF SALE</span>
        <h1>New Sale</h1>
        <p>Search, scan, select size when required, sell and post inventory/accounting automatically.</p>
    </div>
    <a class="btn ghost" href="{{ route('sales.index') }}">Sales History</a>
</div>

<form id="posForm" method="POST" action="{{ route('sales.store') }}">
@csrf
<input type="hidden" name="sale_date" value="{{ now()->toDateString() }}">
<div class="pos-layout">
    <section class="card pos-products">
        <div class="toolbar">
            <input id="productSearch" placeholder="Search product, SKU or barcode">
            <span class="hint">Barcode scanner works as keyboard input</span>
        </div>
        <div class="product-grid" id="productGrid">
            @foreach($products as $p)
                <button
                    type="button"
                    class="product-tile"
                    data-id="{{ $p->id }}"
                    data-name="{{ e($p->name) }}"
                    data-sku="{{ e($p->sku) }}"
                    data-barcode="{{ e($p->barcode) }}"
                    data-price="{{ $p->selling_price }}"
                    data-stock="{{ $p->available_stock }}"
                    data-sizes="{{ e(json_encode($p->normalizedSizes())) }}">
                    <span class="product-avatar">
                        @if($p->image_url)<img src="{{ $p->image_url }}" alt="">@else{{ strtoupper(substr($p->name,0,2)) }}@endif
                    </span>
                    <strong>{{ $p->name }}</strong>
                    <small>{{ $p->sku }} • Available {{ number_format($p->available_stock,0) }}</small>
                    @if($p->has_sizes)<small class="pos-size-hint">Sizes: {{ implode(' · ', $p->normalizedSizes()) }}</small>@endif
                    <b>{{ $companySettings->currency_symbol }}{{ number_format($p->selling_price,2) }}</b>
                </button>
            @endforeach
        </div>
    </section>

    <aside class="card pos-cart">
        <h2>Current Cart</h2>
        <label>Customer
            <select name="customer_id">
                <option value="">Walk-in customer</option>
                @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
        </label>
        <div id="cartLines" class="cart-lines"><div class="empty-state">Select a product to begin.</div></div>
        <div id="cartInputs"></div>
        <div class="pos-totals">
            <label>Discount<input id="discount" type="number" step="0.01" name="discount" value="0"></label>
            <label>Tax<input id="tax" type="number" step="0.01" name="tax" value="0"></label>
            <div><span>Subtotal</span><strong id="subtotalText">{{ $companySettings->currency_symbol }}0.00</strong></div>
            <div class="grand"><span>Total</span><strong id="totalText">{{ $companySettings->currency_symbol }}0.00</strong></div>
        </div>
        <label>Payment method
            <select name="payment_method">
                <option value="cash">Cash</option><option value="card">Card</option><option value="bank">Bank</option><option value="bkash">bKash</option><option value="nagad">Nagad</option><option value="credit">Credit</option>
            </select>
        </label>
        <label>Paid amount<input id="paidAmount" type="number" step="0.01" name="paid_amount" value="0"></label>
        <button class="btn primary wide" type="submit">Complete Sale</button>
    </aside>
</div>
</form>

<dialog id="posSizeDialog" class="pos-size-dialog">
    <form method="dialog">
        <div class="pos-size-dialog-head">
            <div><small>SELECT SIZE</small><h3 id="posSizeProductName">Product</h3></div>
            <button value="cancel" aria-label="Close">×</button>
        </div>
        <div id="posSizeOptions" class="pos-size-options"></div>
        <p>Choose the size being sold. The selected size will be printed with the sale item.</p>
    </form>
</dialog>
@endsection

@push('scripts')
<script>window.initPOS({currency:@json($companySettings->currency_symbol ?? '৳')});</script>
@endpush
