@extends('layouts.app')

@section('title', 'Order Details')
@section('html-class', 'billing-page')
@section('body-class', 'billing-page')

@section('content')
<div class="billing-layout">
    <header class="billing-header">
        <div>
            <h1>Store Billing <span class="fw-normal">— Bill #{{ $order->id }}</span></h1>
            <p>Created {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <a class="btn btn-light" href="{{ route('orders.create') }}">New Order</a>
    </header>

    <section class="billing-panel mt-4">
        <div class="d-flex flex-wrap justify-content-between gap-3">
            <div>
                <div class="billing-label">Customer</div>
                <strong>{{ $order->customer?->name }}</strong>
                <div class="billing-muted">{{ $order->customer?->email }}</div>
            </div>
            <a href="{{ route('orders.index') }}">View order history</a>
        </div>

        <div class="billing-table-wrap mt-4">
            <table class="billing-table">
                <thead>
                    <tr><th scope="col">Product</th><th scope="col">Qty</th><th scope="col">Unit Price</th><th scope="col">Subtotal</th><th scope="col">Tax</th><th scope="col">Line Total</th></tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product?->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₹{{ number_format($item->unit_price, 2) }}</td>
                            <td>₹{{ number_format($item->subtotal, 2) }}</td>
                            <td>₹{{ number_format($item->tax_amount, 2) }}</td>
                            <td class="fw-semibold">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="billing-payment billing-summary mt-4 ms-auto p-3 rounded-2">
            <div class="billing-total-row"><span>Subtotal</span><strong>₹{{ number_format($order->subtotal, 2) }}</strong></div>
            <div class="billing-total-row"><span>Tax</span><strong>₹{{ number_format($order->tax_amount, 2) }}</strong></div>
            <div class="billing-total-row billing-grand-total"><span>Grand Total</span><strong>₹{{ number_format($order->grand_total, 2) }}</strong></div>
        </div>
        <p class="billing-muted mt-3 mb-0">Order confirmation has been queued for the customer.</p>
    </section>
</div>
@endsection
