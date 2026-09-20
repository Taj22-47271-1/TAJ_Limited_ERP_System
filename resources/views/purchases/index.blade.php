@extends('layouts.app')
@section('title','Purchases')
@section('content')
<div class="page-head"><div><span class="eyebrow dark">PROCUREMENT</span><h1>Purchases</h1><p>Receive inventory and automatically post supplier accounting.</p></div><a class="btn primary" href="{{ route('purchases.create') }}">+ New Purchase</a></div>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Supplier</th><th>Date</th><th>Total</th><th>Paid</th><th>Due</th></tr></thead><tbody>@forelse($purchases as $p)<tr><td><a href="{{ route('purchases.show',$p) }}"><strong>{{ $p->invoice_no }}</strong></a></td><td>{{ $p->supplier->name }}</td><td>{{ $p->purchase_date->format('d M Y') }}</td><td>{{ $companySettings->currency_symbol }}{{ number_format($p->total,2) }}</td><td>{{ $companySettings->currency_symbol }}{{ number_format($p->paid_amount,2) }}</td><td>{{ $companySettings->currency_symbol }}{{ number_format($p->due_amount,2) }}</td></tr>@empty<tr><td colspan="6" class="empty-cell">No purchases yet.</td></tr>@endforelse</tbody></table></div>{{ $purchases->links() }}</div>
@endsection
