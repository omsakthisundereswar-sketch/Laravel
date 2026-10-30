@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="page-card p-4">
    <h3>{{ $product->name }}</h3>
    <dl class="row mt-4">
        <dt class="col-sm-3">Code</dt>
        <dd class="col-sm-9">{{ $product->unique_code }}</dd>
        <dt class="col-sm-3">Price</dt>
        <dd class="col-sm-9">₹{{ number_format($product->price, 2) }}</dd>
        <dt class="col-sm-3">Tax percentage</dt>
        <dd class="col-sm-9">{{ $product->tax_percentage }}%</dd>
        <dt class="col-sm-3">Stock on hand</dt>
        <dd class="col-sm-9">{{ $product->stock_quantity }}</dd>
    </dl>
</div>
@endsection
