@extends('layouts.app')
@section('title','Suppliers')
@section('content')
<div class="page-head"><div><span class="eyebrow dark">PROCUREMENT</span><h1>Suppliers</h1><p>Maintain supplier contacts and credit limits.</p></div></div>
<div class="split-layout"><section class="card"><h2>Add Supplier</h2><form class="stack-form" method="POST" action="{{ route('suppliers.store') }}">@csrf<label>Name<input name="name" required></label><label>Company<input name="company"></label><label>Phone<input name="phone"></label><label>Email<input type="email" name="email"></label><label>Credit limit<input type="number" step="0.01" name="credit_limit" value="0"></label><label>Address<textarea name="address"></textarea></label><button class="btn primary">Add Supplier</button></form></section>
<section class="card"><div class="table-wrap"><table><thead><tr><th>Supplier</th><th>Phone</th><th>Company</th><th>Credit Limit</th></tr></thead><tbody>@forelse($suppliers as $s)<tr><td><strong>{{ $s->name }}</strong><small class="block">{{ $s->email }}</small></td><td>{{ $s->phone }}</td><td>{{ $s->company }}</td><td>{{ $companySettings->currency_symbol }}{{ number_format($s->credit_limit,2) }}</td></tr>@empty<tr><td colspan="4" class="empty-cell">No suppliers yet.</td></tr>@endforelse</tbody></table></div>{{ $suppliers->links() }}</section></div>
@endsection
