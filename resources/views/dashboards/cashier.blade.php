@extends('layouts.app')
@php
    $dashRole = auth()->user()->roleDefinition?->name ?? ucwords(str_replace('_',' ',auth()->user()->role));
@endphp
@section('title', $dashRole . ' Dashboard')
@section('content')
<section class="cashier-v2-hero">
    <div>
        <span class="eyebrow">STAFF OPERATIONS</span>
        <h1>Welcome back, {{ auth()->user()->name }}</h1>
        <p>Create in-house sales, review available stock, and process customer online orders without access to total profit or accounting.</p>
    </div>
    @if(auth()->user()->hasPermission('pos.use'))
        <a class="btn cashier-cta" href="{{ route('pos') }}">Open Point of Sale</a>
    @endif
</section>

<section class="cashier-summary-grid">
    <article class="cashier-summary">
        <small>My Sales Today</small>
        <strong>{{ $companySettings->currency_symbol }}{{ number_format($mySalesToday,2) }}</strong>
        <span>{{ $myInvoicesToday }} invoices</span>
    </article>
    <article class="cashier-summary">
        <small>Pending Online Orders</small>
        <strong>{{ $pendingOrders }}</strong>
        <span>Need approval</span>
    </article>
    <article class="cashier-summary">
        <small>Orders In Progress</small>
        <strong>{{ $activeOrders }}</strong>
        <span>Approved → delivery</span>
    </article>
    <article class="cashier-summary">
        <small>Customers</small>
        <strong>{{ $customerCount }}</strong>
        <span>Available customer records</span>
    </article>
</section>

<div class="dashboard-two-col cashier-dashboard-grid">
    <section class="card cashier-products">
        <div class="card-head">
            <div>
                <span class="eyebrow dark">PRODUCTS</span>
                <h2>Quick Product Access</h2>
            </div>
            @if(auth()->user()->hasPermission('products.manage'))
                <a href="{{ route('products.create') }}">+ Add Product</a>
            @endif
        </div>
        <div class="cashier-product-grid">
            @forelse($products as $p)
                <a href="{{ auth()->user()->hasPermission('products.manage') ? route('products.edit', $p) : route('products.index') }}">
                    <span class="product-thumb">
                        @if($p->image_url)
                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}">
                        @else
                            {{ strtoupper(substr($p->name,0,1)) }}
                        @endif
                    </span>
                    <strong>{{ $p->name }}</strong>
                    <small>{{ $companySettings->currency_symbol }}{{ number_format($p->selling_price,2) }} · {{ number_format($p->available_stock,0) }} available</small>
                </a>
            @empty
                <p class="muted">No products available.</p>
            @endforelse
        </div>
    </section>

    <section class="card cashier-side-actions">
        <div class="card-head">
            <div>
                <span class="eyebrow dark">OPERATIONS</span>
                <h2>Quick Staff Actions</h2>
            </div>
        </div>
        <div class="cashier-action-list">
            @if(auth()->user()->hasPermission('pos.use'))
                <a class="cashier-action" href="{{ route('pos') }}">
                    <span>▣</span>
                    <div>
                        <strong>In-house Sale</strong>
                        <small>Scan products and create invoice</small>
                    </div>
                </a>
            @endif

            @if(auth()->user()->hasPermission('orders.view'))
                <a class="cashier-action" href="{{ route('orders.index',['status'=>'pending']) }}">
                    <span>📦</span>
                    <div>
                        <strong>Approve Online Orders</strong>
                        <small>Review customer orders</small>
                    </div>
                </a>
            @endif

            @if(auth()->user()->hasPermission('products.manage'))
                <a class="cashier-action" href="{{ route('products.create') }}">
                    <span>◫</span>
                    <div>
                        <strong>Add Product</strong>
                        <small>Add details, image and selling price</small>
                    </div>
                </a>
            @endif

            @if(auth()->user()->hasPermission('customers.manage'))
                <a class="cashier-action" href="{{ route('customers.index') }}">
                    <span>👥</span>
                    <div>
                        <strong>Customers</strong>
                        <small>Find or add customer records</small>
                    </div>
                </a>
            @endif

            <div class="cashier-restriction">
                <strong>Access Restricted</strong>
                <p>Total profit, cost figures, accounting journals, user permissions and company settings are restricted to Admin.</p>
            </div>
        </div>
    </section>
</div>

<section class="card cashier-recent">
    <div class="card-head">
        <div>
            <span class="eyebrow dark">MY ACTIVITY</span>
            <h2>Recent In-house Sales</h2>
        </div>
        @if(auth()->user()->hasPermission('sales.view'))
            <a href="{{ route('sales.index') }}">View My Sales →</a>
        @endif
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Payment</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
            @forelse($myRecentSales as $sale)
                <tr>
                    <td><a href="{{ route('sales.show',$sale) }}"><strong>{{ $sale->invoice_no }}</strong></a></td>
                    <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                    <td>{{ $sale->sale_date->format('d M Y') }}</td>
                    <td>{{ ucfirst($sale->payment_method) }}</td>
                    <td class="right"><strong>{{ $companySettings->currency_symbol }}{{ number_format($sale->total,2) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-cell">No sales recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
