@extends('layouts.customer')
@section('title','My Orders')
@section('content')
<div class="page-head customer-page-head"><div><span class="eyebrow dark">ORDER HISTORY</span><h1>My Orders</h1><p>See payment status and track every approved order through delivery.</p></div><a class="btn primary" href="{{ route('shop.index') }}">Shop More</a></div>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Order</th><th>Date</th><th>Payment</th><th>Status</th><th class="right">Total</th><th></th></tr></thead><tbody>
@forelse($orders as $order)<tr><td><strong>{{ $order->order_no }}</strong></td><td>{{ $order->created_at->format('d M Y') }}</td><td>{{ strtoupper($order->payment_method) }}<small class="block">{{ ucwords(str_replace('_',' ',$order->payment_status)) }}</small></td><td><span class="status-badge {{ $order->order_status }}">{{ ucwords(str_replace('_',' ',$order->order_status)) }}</span></td><td class="right"><strong>{{ $companySettings->currency_symbol }}{{ number_format($order->total,2) }}</strong></td><td class="right"><a href="{{ route('customer.orders.show',$order) }}">Track →</a></td></tr>
@empty<tr><td colspan="6" class="empty-cell">You have not placed an order yet.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $orders->links() }}</div></div>
@endsection
