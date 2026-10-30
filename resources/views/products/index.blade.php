@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="page-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Products</h3>
        <a href="{{ route('products.create') }}" class="btn btn-primary">Add product</a>
    </div>
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-10">
            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name or product code">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Search</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Price</th>
                    <th>Tax</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->unique_code }}</td>
                        <td>₹{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->tax_percentage }}%</td>
                        <td>{{ $product->stock_quantity }}</td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $products->links() }}
</div>
@endsection
