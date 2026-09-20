@extends('layouts.app')
@section('title','Sales')
@section('content')
<div class="page-head"><div><span class="eyebrow dark">SALES</span><h1>Sales</h1><p>Completed POS invoices and customer receivables.</p></div><a class="btn primary" href="{{ route('pos') }}">+ New POS Sale</a></div>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Total</th><th>Paid</th><th>Due</th><th>Method</th></tr></thead><tbody>@forelse($sales as $s)<tr><td><a href="{{ route('sales.show',$s) }}"><strong>{{ $s->invoice_no }}</strong></a></td><td>{{ $s->customer?->name ?? 'Walk-in' }}</td><td>{{ $s->sale_date->format('d M Y') }}</td><td>{{ $companySettings->currency_symbol }}{{ number_format($s->total,2) }}</td><td>{{ $companySettings->currency_symbol }}{{ number_format($s->paid_amount,2) }}</td><td>{{ $companySettings->currency_symbol }}{{ number_format($s->due_amount,2) }}</td><td>{{ strtoupper($s->payment_method) }}</td></tr>@empty<tr><td colspan="7" class="empty-cell">No sales yet.</td></tr>@endforelse</tbody></table></div>{{ $sales->links() }}</div>
@endsection
