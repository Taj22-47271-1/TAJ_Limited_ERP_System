@extends('layouts.app')
@section('title','Admin Dashboard')
@section('content')
<section class="admin-v2-hero">
    <div><span class="eyebrow">BUSINESS OVERVIEW</span><h1>Welcome back, {{ auth()->user()->name }}</h1><p>Manage products, in-house sales, online orders, inventory, accounting and staff access from one dashboard.</p></div>
    <div class="admin-v2-actions"><a href="{{ route('pos') }}">New POS Sale</a><a href="{{ route('orders.index',['status'=>'pending']) }}">Review Orders</a><a href="{{ route('products.create') }}">Add Product</a></div>
</section>

<section class="erp-kpi-grid admin-kpis">
    <article class="erp-kpi"><span class="erp-kpi-icon">৳</span><div><small>Today's Sales</small><strong>{{ $companySettings->currency_symbol }}{{ number_format($salesToday,2) }}</strong><em>In-house + online</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">▣</span><div><small>In-house Sales</small><strong>{{ $companySettings->currency_symbol }}{{ number_format($inhouseSalesToday,2) }}</strong><em>POS today</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">◉</span><div><small>Online Sales</small><strong>{{ $companySettings->currency_symbol }}{{ number_format($onlineSalesToday,2) }}</strong><em>{{ $pendingOrders }} pending orders</em></div></article>
    <article class="erp-kpi profit-card"><span class="erp-kpi-icon">↗</span><div><small>Total Profit</small><strong>{{ $companySettings->currency_symbol }}{{ number_format($totalProfit,2) }}</strong><em>Admin-only financial KPI</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">◫</span><div><small>Products</small><strong>{{ number_format($productCount) }}</strong><em>Stock value {{ $companySettings->currency_symbol }}{{ number_format($stockValue,0) }}</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">👥</span><div><small>Customers</small><strong>{{ number_format($customerCount) }}</strong><em>Registered shoppers</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">♙</span><div><small>Cashiers</small><strong>{{ number_format($cashierCount) }}</strong><em>{{ $activeUserCount }} active users</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">−</span><div><small>Today's Expenses</small><strong>{{ $companySettings->currency_symbol }}{{ number_format($expensesToday,2) }}</strong><em>Today's profit {{ $companySettings->currency_symbol }}{{ number_format($profitToday,2) }}</em></div></article>
</section>

<div class="dashboard-two-col">
<section class="card chart-card">
    <div class="card-head"><div><span class="eyebrow dark">LAST 7 DAYS</span><h2>Sales Performance</h2></div><a href="{{ route('reports.profit-loss') }}">Financial Report →</a></div>
    <div class="mini-bar-chart">
        @foreach($salesChart as $point)<div class="mini-bar-col"><span class="mini-bar-value">{{ number_format($point['total']/1000,1) }}k</span><div class="mini-bar" style="height:{{ $point['height'] }}%"></div><small>{{ $point['label'] }}</small></div>@endforeach
    </div>
</section>
<section class="card quick-panel">
    <div class="card-head"><div><span class="eyebrow dark">QUICK CONTROL</span><h2>Management Actions</h2></div></div>
    <div class="quick-action-grid">
        <a href="{{ route('products.create') }}"><strong>+ Product</strong><span>Create product with image</span></a>
        <a href="{{ route('inventory.movements') }}"><strong>Stock</strong><span>Adjust & review ledger</span></a>
        <a href="{{ route('orders.index',['status'=>'pending']) }}"><strong>Orders</strong><span>Approve customer orders</span></a>
        <a href="{{ route('admin.users') }}"><strong>Cashiers</strong><span>Create / deactivate users</span></a>
        <a href="{{ route('admin.roles.index') }}"><strong>Roles</strong><span>Permissions & access</span></a>
        <a href="{{ route('expenses.index') }}"><strong>Expenses</strong><span>Record business costs</span></a>
    </div>
</section>
</div>

<div class="dashboard-two-col">
<section class="card">
    <div class="card-head"><div><span class="eyebrow dark">ONLINE</span><h2>Recent Orders</h2></div><a href="{{ route('orders.index') }}">All Orders →</a></div>
    <div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Payment</th><th>Status</th><th class="right">Total</th></tr></thead><tbody>
    @forelse($recentOrders as $o)<tr><td><a href="{{ route('orders.show',$o) }}"><strong>{{ $o->order_no }}</strong></a></td><td>{{ $o->full_name }}</td><td>{{ strtoupper($o->payment_method) }}<small class="block">{{ ucfirst(str_replace('_',' ',$o->payment_status)) }}</small></td><td><span class="status-badge {{ $o->order_status }}">{{ ucwords(str_replace('_',' ',$o->order_status)) }}</span></td><td class="right"><strong>{{ $companySettings->currency_symbol }}{{ number_format($o->total,2) }}</strong></td></tr>@empty<tr><td colspan="5" class="empty-cell">No online orders yet.</td></tr>@endforelse
    </tbody></table></div>
</section>
<section class="card">
    <div class="card-head"><div><span class="eyebrow dark">INVENTORY</span><h2>Low Stock</h2></div><a href="{{ route('products.index') }}">Products →</a></div>
    <div class="stock-watch-list">@forelse($lowStock as $p)<div><span class="product-thumb tiny">@if($p->image_url)<img src="{{ $p->image_url }}" alt="">@else{{ strtoupper(substr($p->name,0,1)) }}@endif</span><div><strong>{{ $p->name }}</strong><small>{{ $p->sku }}</small></div><span class="stock-pill danger">{{ number_format($p->available_stock,0) }} available</span></div>@empty<p class="muted">No low-stock products.</p>@endforelse</div>
</section>
</div>
@endsection
