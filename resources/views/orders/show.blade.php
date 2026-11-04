@extends('layouts.app')
@section('title', 'Order ' . $order->order_no)
@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow dark">ORDER REVIEW</span>
        <h1>{{ $order->order_no }}</h1>
        <p>Placed {{ $order->created_at->format('d M Y, h:i A') }}</p>
    </div>
    <a class="btn ghost" href="{{ route('orders.index') }}">← Back to Orders</a>
</div>

<div class="order-detail-layout">
    <section class="order-main-content">
        <div class="card">
            <div class="card-head">
                <div>
                    <span class="eyebrow dark">CUSTOMER</span>
                    <h2>{{ $order->full_name }}</h2>
                </div>
                <span class="status-badge {{ $order->order_status }}">{{ ucwords(str_replace('_',' ',$order->order_status)) }}</span>
            </div>
            <div class="detail-grid">
                <div>
                    <small>Email</small>
                    <strong>{{ $order->email }}</strong>
                </div>
                <div>
                    <small>Phone</small>
                    <strong>{{ $order->phone }}</strong>
                </div>
                <div class="span-2">
                    <small>Delivery Address</small>
                    <strong>{{ $order->address }}@if($order->area), {{ $order->area }}@endif @if($order->city), {{ $order->city }}@endif @if($order->postal_code) ({{ $order->postal_code }})@endif</strong>
                </div>
                @if($order->notes)
                <div class="span-2">
                    <small>Customer Note</small>
                    <p class="muted" style="margin-top: 4px;">{{ $order->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div>
                    <span class="eyebrow dark">PRODUCTS</span>
                    <h2>Order Items ({{ $order->items->count() }})</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="right">Qty</th>
                            <th class="right">Price</th>
                            <th class="right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <span class="product-thumb tiny">
                                        @if($item->product?->image_url)
                                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}">
                                        @else
                                            {{ strtoupper(substr($item->product_name,0,1)) }}
                                        @endif
                                    </span>
                                    <div>
                                        <strong>{{ $item->product_name }}</strong>
                                        @if($item->selected_size)
                                            <small class="order-size-note">Size: {{ $item->selected_size }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="right">{{ number_format($item->quantity,0) }}</td>
                            <td class="right">{{ $companySettings->currency_symbol }}{{ number_format($item->unit_price,2) }}</td>
                            <td class="right"><strong>{{ $companySettings->currency_symbol }}{{ number_format($item->line_total,2) }}</strong></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div>
                    <span class="eyebrow dark">HISTORY</span>
                    <h2>Status Timeline</h2>
                </div>
            </div>
            <div class="history-list">
                @foreach($order->histories->reverse() as $history)
                    <div>
                        <span></span>
                        <div>
                            <strong>{{ ucwords(str_replace('_',' ',$history->status)) }}</strong>
                            <small>{{ $history->created_at->format('d M Y, h:i A') }}{{ $history->changer ? ' · ' . $history->changer->name : '' }}</small>
                            @if($history->note)
                                <p>{{ $history->note }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <aside class="card order-side">
        <h2>Order Summary</h2>
        <div>
            <span>Subtotal</span>
            <strong>{{ $companySettings->currency_symbol }}{{ number_format($order->subtotal,2) }}</strong>
        </div>
        <div>
            <span>Delivery</span>
            <strong>{{ $companySettings->currency_symbol }}{{ number_format($order->delivery_fee,2) }}</strong>
        </div>
        <div class="cart-total">
            <span>Total</span>
            <strong>{{ $companySettings->currency_symbol }}{{ number_format($order->total,2) }}</strong>
        </div>

        <hr style="margin: 14px 0; border: 0; border-top: 1px solid #edf0f4;">

        <div style="margin-bottom: 14px;">
            <p><strong>Payment Method:</strong> {{ strtoupper($order->payment_method) }}</p>
            <p><strong>Payment Status:</strong> <span class="status-badge {{ $order->payment_status === 'paid' ? 'delivered' : 'pending' }}">{{ ucwords(str_replace('_',' ',$order->payment_status)) }}</span></p>
            @if($order->payment_reference)
                <p><strong>Reference:</strong> <code>{{ $order->payment_reference }}</code></p>
            @endif
        </div>

        @if($order->order_status === 'pending')
            @if(auth()->user()->hasPermission('orders.approve'))
                <form method="POST" action="{{ route('orders.approve', $order) }}" style="margin-bottom: 12px;">
                    @csrf
                    @if($order->payment_method === 'online' && $order->payment_status !== 'paid')
                        <div class="alert warning" style="margin-bottom: 10px; font-size: 11px;">
                            Payment is pending. Approving confirms payment verification and commits reserved stock.
                        </div>
                        <button class="btn primary wide" type="submit">Confirm Payment & Approve</button>
                    @else
                        <button class="btn primary wide" type="submit">Approve Order</button>
                    @endif
                </form>
            @endif

            @if(auth()->user()->isAdmin())
                <form class="reject-form" method="POST" action="{{ route('orders.reject', $order) }}" onsubmit="return confirm('Are you sure you want to reject this order?');">
                    @csrf
                    <label>
                        Reject reason
                        <textarea name="reason" rows="2" placeholder="Reason for cancellation..."></textarea>
                    </label>
                    <button class="btn danger wide" type="submit" style="margin-top: 8px;">Reject Order</button>
                </form>
            @endif
        @elseif(in_array($order->order_status, ['approved','processing','packed','shipped','out_for_delivery']))
            @php
                $next = [
                    'approved'=>'Processing',
                    'processing'=>'Packed',
                    'packed'=>'Shipped',
                    'shipped'=>'Out for Delivery',
                    'out_for_delivery'=>'Delivered'
                ][$order->order_status] ?? null;
            @endphp
            @if($next && auth()->user()->hasPermission('orders.progress'))
                <form method="POST" action="{{ route('orders.progress', $order) }}">
                    @csrf
                    <button class="btn primary wide" type="submit">Move to {{ $next }} →</button>
                </form>
            @endif
        @else
            <div class="order-terminal {{ $order->order_status }}">
                <strong>{{ ucwords(str_replace('_',' ',$order->order_status)) }}</strong>
            </div>
        @endif
    </aside>
</div>
@endsection
