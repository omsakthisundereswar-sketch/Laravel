@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="stat-card p-4">
            <div class="text-secondary">Products</div>
            <h2>{{ $productCount }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card p-4">
            <div class="text-secondary">Customers</div>
            <h2>{{ $customerCount }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card p-4">
            <div class="text-secondary">Orders</div>
            <h2>{{ $orderCount }}</h2>
        </div>
    </div>
</div>
<div class="mt-5 page-card p-4">
    <h4>Quick actions</h4>
    <div class="d-flex gap-3 flex-wrap mt-3">
        <a href="{{ route('products.create') }}" class="btn btn-primary">New Product</a>
        <a href="{{ route('customers.create') }}" class="btn btn-outline-primary">New Customer</a>
        <a href="{{ route('orders.create') }}" class="btn btn-success">Create Order</a>
    </div>
</div>
@endsection
