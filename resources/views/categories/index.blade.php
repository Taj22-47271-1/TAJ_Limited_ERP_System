@extends('layouts.app')
@section('title','Categories')
@section('content')
<div class="page-head"><div><span class="eyebrow dark">CATALOG</span><h1>Categories</h1><p>Organize products into logical groups.</p></div></div>
<div class="split-layout"><section class="card"><h2>Add Category</h2><form class="stack-form" method="POST" action="{{ route('categories.store') }}">@csrf<label>Name<input name="name" required></label><label>Description<textarea name="description" rows="4"></textarea></label><button class="btn primary">Add Category</button></form></section>
<section class="card"><div class="table-wrap"><table><thead><tr><th>Name</th><th>Products</th><th>Status</th><th></th></tr></thead><tbody>@foreach($categories as $c)<tr><td><strong>{{ $c->name }}</strong></td><td>{{ $c->products_count }}</td><td>{{ $c->status?'Active':'Inactive' }}</td><td class="right actions"><form method="POST" action="{{ route('categories.destroy',$c) }}" onsubmit="return confirm('Delete category?')">@csrf @method('DELETE')<button>Delete</button></form></td></tr>@endforeach</tbody></table></div></section></div>
@endsection
