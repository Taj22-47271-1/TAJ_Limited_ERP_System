@extends('layouts.customer')
@section('title',$order->order_no)
@section('content')
<div class="page-head customer-page-head"><div><span class="eyebrow dark">ORDER TRACKING</span><h1>{{ $order->order_no }}</h1><p>Placed {{ $order->created_at->format('d M Y, h:i A') }}</p></div><a class="btn ghost" href="{{ route('customer.orders') }}">Back to Orders</a></div>
<div class="order-detail-layout">
<section>
<div class="card order-tracker-card"><div class="card-head"><div><span class="eyebrow dark">LIVE STATUS</span><h2>{{ ucwords(str_replace('_',' ',$order->order_status)) }}</h2></div><span class="status-badge {{ $order->order_status }}">{{ ucwords(str_replace('_',' ',$order->order_status)) }}</span></div>
@if(in_array($order->order_status,['rejected','cancelled']))
<div class="order-terminal {{ $order->order_status }}"><strong>Order {{ ucfirst($order->order_status) }}</strong><p>{{ optional($order->histories->last())->note }}</p></div>
@else
@php $steps=['pending'=>'Order Placed','approved'=>'Approved','processing'=>'Processing','packed'=>'Packed','shipped'=>'Shipped','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered']; $current=array_search($order->order_status,array_keys($steps),true); $current=$current===false?0:$current; @endphp
<div class="order-tracker">@foreach($steps as $key=>$label)@php $idx=array_search($key,array_keys($steps),true); @endphp<div class="track-step {{ $idx <= $current ? 'done' : '' }} {{ $key===$order->order_status?'current':'' }}"><span>{{ $idx < $current ? '✓' : ($idx===$current ? '●' : '○') }}</span><small>{{ $label }}</small></div>@endforeach</div>
@endif
<div class="history-list">@foreach($order->histories->reverse() as $history)<div><span></span><div><strong>{{ ucwords(str_replace('_',' ',$history->status)) }}</strong><small>{{ $history->created_at->format('d M Y, h:i A') }}{{ $history->changer ? ' · '.$history->changer->name : '' }}</small>@if($history->note)<p>{{ $history->note }}</p>@endif</div></div>@endforeach</div>
</div>
<div class="card"><div class="card-head"><div><span class="eyebrow dark">ITEMS</span><h2>Order Products</h2></div></div><div class="order-items">@foreach($order->items as $item)<div><span class="product-thumb tiny">@if($item->product?->image_url)<img src="{{ $item->product->image_url }}" alt="">@else{{ strtoupper(substr($item->product_name,0,1)) }}@endif</span><div><strong>{{ $item->product_name }}</strong>@if($item->selected_color)<small class="order-size-note" style="color: #0284c7; font-weight: 700;">Color: {{ $item->selected_color }}</small>@endif @if($item->selected_size)<small class="order-size-note">Size {{ $item->selected_size }}</small>@endif<small>{{ number_format($item->quantity,0) }} × {{ $companySettings->currency_symbol }}{{ number_format($item->unit_price,2) }}</small></div><b>{{ $companySettings->currency_symbol }}{{ number_format($item->line_total,2) }}</b></div>@endforeach</div></div>
</section>
<aside class="card order-side"><h2>Order Summary</h2><div><span>Subtotal</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($order->subtotal,2) }}</strong></div><div><span>Delivery</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($order->delivery_fee,2) }}</strong></div><div class="cart-total"><span>Total</span><strong>{{ $companySettings->currency_symbol }}{{ number_format($order->total,2) }}</strong></div><hr><p><strong>Payment:</strong><br>{{ strtoupper($order->payment_method) }} · {{ ucwords(str_replace('_',' ',$order->payment_status)) }}</p>@if($order->payment_reference)<p><strong>Reference:</strong><br>{{ $order->payment_reference }}</p>@endif<p><strong>Delivery to:</strong><br>{{ $order->full_name }}<br>{{ $order->phone }}<br>{{ $order->address }}@if($order->area), {{ $order->area }}@endif @if($order->city), {{ $order->city }}@endif</p>
@if($order->payment_method==='online' && $order->payment_status!=='paid' && $order->order_status==='pending')<a class="btn primary wide" href="{{ route('customer.orders.pay',$order) }}">Pay Online</a>@endif
@if($order->order_status==='pending')<form method="POST" action="{{ route('customer.orders.cancel',$order) }}" onsubmit="return confirm('Cancel this order? Reserved stock will be released.')">@csrf<button class="btn danger wide" type="submit">Cancel Order</button></form>@endif
</aside></div>
@endsection
