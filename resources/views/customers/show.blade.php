@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="page-card p-4">
    <h3>{{ $customer->name }}</h3>
    <p><strong>Email:</strong> {{ $customer->email }}</p>
    <h5 class="mt-4">Order history</h5>
    @if ($customer->orders->isEmpty())
        <p class="text-muted">No orders yet.</p>
    @else
        <table class="table">
            <thead><tr><th>ID</th><th>Date</th><th>Total</th></tr></thead>
            <tbody>
                @foreach ($customer->orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                        <td>₹{{ number_format($order->grand_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
