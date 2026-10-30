@extends('layouts.app')

@section('title', 'Create Product')

@section('content')
<div class="page-card p-4">
    <h3>Create Product</h3>
    <form method="POST" action="{{ route('products.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Unique Code</label>
                <input class="form-control" name="unique_code" value="{{ old('unique_code') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Price per unit (₹)</label>
                <input class="form-control" type="number" step="0.01" min="0.01" name="price" value="{{ old('price') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tax %</label>
                <input class="form-control" type="number" step="0.01" min="0" max="100" name="tax_percentage" value="{{ old('tax_percentage') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Stock</label>
                <input class="form-control" type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" required>
            </div>
        </div>
        <button class="btn btn-primary mt-4">Save Product</button>
    </form>
</div>
@endsection
