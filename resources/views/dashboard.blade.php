@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<section class="dashboard-hero">
    <div><span class="eyebrow">BUSINESS COMMAND CENTER</span><h1>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }}.</h1><p>Monitor sales, stock, purchases and accounting performance for {{ $companySettings->company_name ?? 'TAJ Limited' }}.</p></div>
    <div class="hero-actions"><a class="btn light" href="{{ route('pos') }}">+ New POS Sale</a><a class="btn outline-light" href="{{ route('purchases.create') }}">Record Purchase</a></div>
</section>

<section class="kpi-grid">
    <article class="kpi-card"><div class="kpi-icon orange">↗</div><div><span>Today's Sales</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($salesToday,2) }}</strong><small>Live from completed invoices</small></div></article>
    <article class="kpi-card"><div class="kpi-icon green">⌁</div><div><span>Today's Profit</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($profitToday,2) }}</strong><small>Gross margin less expenses</small></div></article>
    <article class="kpi-card"><div class="kpi-icon blue">↓</div><div><span>Today's Purchases</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($purchasesToday,2) }}</strong><small>Received inventory</small></div></article>
    <article class="kpi-card"><div class="kpi-icon purple">▦</div><div><span>Stock Value</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($stockValue,2) }}</strong><small>At current purchase cost</small></div></article>
</section>

<div class="dashboard-grid">
    <section class="card chart-card"><div class="card-head"><div><span class="eyebrow dark">PERFORMANCE</span><h2>Sales — Last 7 Days</h2></div><a href="{{ route('sales.index') }}">View sales →</a></div>
        <div class="bar-chart">@foreach($salesChart as $point)<div class="bar-col"><div class="bar-value">{{ number_format($point['total']/1000,1) }}k</div><div class="bar-track"><div class="bar-fill" style="height:{{ $point['height'] }}%"></div></div><span>{{ $point['label'] }}</span></div>@endforeach</div>
    </section>
    <section class="card"><div class="card-head"><div><span class="eyebrow dark">ATTENTION</span><h2>Low Stock</h2></div><a href="{{ route('products.index') }}">Inventory →</a></div>
        <div class="list-stack">@forelse($lowStock as $product)<div class="stock-row"><div><strong>{{ $product->name }}</strong><span>{{ $product->sku }}</span></div><div class="stock-pill danger">{{ number_format($product->stock,0) }} {{ $product->unit }}</div></div>@empty<div class="empty-state">All products are above their minimum stock levels.</div>@endforelse</div>
    </section>
</div>

<div class="dashboard-grid lower">
    <section class="card"><div class="card-head"><div><span class="eyebrow dark">LATEST ACTIVITY</span><h2>Recent Sales</h2></div><a href="{{ route('sales.index') }}">All sales →</a></div><div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Customer</th><th>Date</th><th class="right">Total</th></tr></thead><tbody>@forelse($recentSales as $sale)<tr><td><a href="{{ route('sales.show',$sale) }}"><strong>{{ $sale->invoice_no }}</strong></a></td><td>{{ $sale->customer?->name ?? 'Walk-in' }}</td><td>{{ $sale->sale_date->format('d M Y') }}</td><td class="right"><strong>{{ $companySettings->currency_symbol }}{{ number_format($sale->total,2) }}</strong></td></tr>@empty<tr><td colspan="4" class="empty-cell">No sales yet.</td></tr>@endforelse</tbody></table></div></section>
    <section class="card quick-panel"><div class="card-head"><div><span class="eyebrow dark">QUICK ACTIONS</span><h2>Run Your Business</h2></div></div><div class="quick-grid"><a href="{{ route('pos') }}"><span>◫</span><strong>POS Sale</strong><small>Scan or select products</small></a><a href="{{ route('purchases.create') }}"><span>↓</span><strong>Purchase</strong><small>Receive new stock</small></a><a href="{{ route('expenses.index') }}"><span>−</span><strong>Expense</strong><small>Record business cost</small></a><a href="{{ route('reports.profit-loss') }}"><span>↗</span><strong>P&L Report</strong><small>Review profitability</small></a></div></section>
</div>
@endsection
