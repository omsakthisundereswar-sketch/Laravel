@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="page-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Orders</h3>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">Create order</a>
    </div>
    <table class="table table-striped">
        <thead>
            <tr><th>ID</th><th>Customer</th><th>Subtotal</th><th>Tax</th><th>Total</th><th>Created</th></tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->customer?->name }}</td>
                    <td>₹{{ number_format($order->subtotal, 2) }}</td>
                    <td>₹{{ number_format($order->tax_amount, 2) }}</td>
                    <td>₹{{ number_format($order->grand_total, 2) }}</td>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $orders->links() }}
</div>
@endsection
