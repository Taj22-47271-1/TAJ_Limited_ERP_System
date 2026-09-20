@extends('layouts.app')
@section('title','New Purchase')
@section('content')
<div class="page-head"><div><span class="eyebrow dark">STOCK IN</span><h1>New Purchase</h1><p>Receive supplier stock and post inventory + payable journals.</p></div><a class="btn ghost" href="{{ route('purchases.index') }}">Back</a></div>
<form method="POST" action="{{ route('purchases.store') }}" class="card">@csrf<div class="form-grid"><label>Supplier<select name="supplier_id" required><option value="">Select supplier</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></label><label>Purchase date<input type="date" name="purchase_date" value="{{ now()->toDateString() }}" required></label></div>
<h3>Items</h3><div id="purchaseRows" class="line-items"></div><button type="button" class="btn ghost" id="addPurchaseRow">+ Add item</button>
<div class="form-grid compact"><label>Discount<input type="number" step="0.01" name="discount" value="0"></label><label>Tax<input type="number" step="0.01" name="tax" value="0"></label><label>Paid amount<input type="number" step="0.01" name="paid_amount" value="0"></label><label class="span-2">Notes<textarea name="notes"></textarea></label></div><button class="btn primary">Receive Purchase</button></form>
<template id="purchaseRowTpl"><div class="line-item"><select data-name="product_id" required><option value="">Product</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} — {{ $p->sku }}</option>@endforeach</select><input data-name="quantity" type="number" step="0.01" min="0.01" value="1" placeholder="Qty" required><input data-name="unit_cost" type="number" step="0.01" min="0" placeholder="Unit cost" required><button type="button" class="remove-row">×</button></div></template>
@push('scripts')<script>window.setupDynamicRows('purchaseRows','purchaseRowTpl','addPurchaseRow','items');</script>@endpush
@endsection
