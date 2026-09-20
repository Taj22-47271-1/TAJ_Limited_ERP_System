@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.cashier')
@section('title','Online Orders')
@section('content')
<div class="page-head"><div><span class="eyebrow dark">ONLINE SALES</span><h1>Customer Orders</h1><p>Review customer details, payment and stock before approval.</p></div></div>
<div class="card"><form class="toolbar" method="GET"><input name="q" value="{{ request('q') }}" placeholder="Order, customer or phone"><select name="status"><option value="">All statuses</option>@foreach(\App\Models\OnlineOrder::STATUSES as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>@endforeach</select><button class="btn secondary">Filter</button></form><div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Payment</th><th>Status</th><th>Placed</th><th class="right">Total</th><th></th></tr></thead><tbody>
@forelse($orders as $order)<tr><td><strong>{{ $order->order_no }}</strong></td><td>{{ $order->full_name }}<small class="block">{{ $order->phone }}</small></td><td>{{ strtoupper($order->payment_method) }}<small class="block">{{ ucwords(str_replace('_',' ',$order->payment_status)) }}</small></td><td><span class="status-badge {{ $order->order_status }}">{{ ucwords(str_replace('_',' ',$order->order_status)) }}</span></td><td>{{ $order->created_at->format('d M Y H:i') }}</td><td class="right"><strong>{{ $companySettings->currency_symbol }}{{ number_format($order->total,2) }}</strong></td><td class="right"><a href="{{ route('orders.show',$order) }}">Review →</a></td></tr>
@empty<tr><td colspan="7" class="empty-cell">No orders found.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $orders->links() }}</div></div>
@endsection
