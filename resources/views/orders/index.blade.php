@extends('layouts.app')
@section('title', 'Online Orders')
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow dark">ONLINE SALES</span>
        <h1>Online Orders</h1>
        <p>Review customer details, payment status, and stock availability before approving orders.</p>
    </div>
</div>

<div class="card">
    <form class="toolbar" method="GET" action="{{ route('orders.index') }}">
        <input name="q" value="{{ request('q') }}" placeholder="Search order #, customer, phone...">
        <select name="status">
            <option value="">All statuses</option>
            @foreach(\App\Models\OnlineOrder::STATUSES as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>
            @endforeach
        </select>
        <button class="btn secondary" type="submit">Filter</button>
        @if(request()->filled('q') || request()->filled('status'))
            <a href="{{ route('orders.index') }}" class="btn ghost">Clear Filter</a>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Placed</th>
                    <th class="right">Total</th>
                    <th class="right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>
                        <a href="{{ route('orders.show', $order) }}"><strong>{{ $order->order_no }}</strong></a>
                    </td>
                    <td>
                        <strong>{{ $order->full_name }}</strong>
                        <small class="block muted">{{ $order->phone }}</small>
                    </td>
                    <td>
                        <span class="payment-badge {{ $order->payment_method }}">{{ strtoupper($order->payment_method) }}</span>
                        <small class="block muted">{{ ucwords(str_replace('_',' ',$order->payment_status)) }}</small>
                    </td>
                    <td>
                        <span class="status-badge {{ $order->order_status }}">{{ ucwords(str_replace('_',' ',$order->order_status)) }}</span>
                    </td>
                    <td>
                        <small>{{ $order->created_at->format('d M Y, h:i A') }}</small>
                    </td>
                    <td class="right">
                        <strong>{{ $companySettings->currency_symbol }}{{ number_format($order->total, 2) }}</strong>
                    </td>
                    <td class="right actions">
                        <a class="btn ghost btn-sm" href="{{ route('orders.show', $order) }}">Review →</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty-cell">No online orders found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $orders->links() }}
    </div>
</div>
@endsection
