@extends('layouts.app')

@section('title', 'Create Order')
@section('html-class', 'billing-page')
@section('body-class', 'billing-page')

@section('content')
<div class="billing-layout">
    <header class="billing-header">
        <div>
            <h1>Store Billing <span class="fw-normal">— New Order</span></h1>
            <p>Create a customer bill and update stock in one step.</p>
        </div>
    </header>

    <form class="mt-4" method="POST" action="{{ route('orders.store') }}" id="order-form">
        @csrf

        <section class="billing-panel">
            <h2 class="billing-section-title">Customer</h2>
            <div class="billing-fields">
                <div>
                    <label class="billing-label" for="customer-email">Email</label>
                    <input class="billing-control" id="customer-email" type="email" name="customer[email]" value="{{ old('customer.email') }}" placeholder="e.g. thomas@example.com" autocomplete="email" required>
                </div>
                <div>
                    <label class="billing-label" for="customer-name">Name</label>
                    <input class="billing-control" id="customer-name" name="customer[name]" value="{{ old('customer.name') }}" placeholder="Auto-filled if email exists" autocomplete="name" required>
                </div>
            </div>
        </section>

        <div class="billing-content-grid">
            <div class="billing-column">
                <section class="billing-panel">
                    <h2 class="billing-section-title">Products</h2>
                    <div class="billing-table-wrap">
                        <table class="billing-table">
                            <thead>
                                <tr>
                                    <th scope="col">Product</th>
                                    <th scope="col">Qty</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Line Total</th>
                                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="items-container">
                                @foreach (old('items', [['product_id' => '', 'quantity' => 1]]) as $index => $oldItem)
                                    <tr class="item-row">
                                        <td>
                                            <select class="billing-control product-control product-select" name="items[{{ $index }}][product_id]" required>
                                                <option value="">Choose a product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-tax="{{ $product->tax_percentage }}" data-stock="{{ $product->stock_quantity }}" @selected((string) ($oldItem['product_id'] ?? '') === (string) $product->id)>
                                                        {{ $product->name }} ({{ $product->stock_quantity }} in stock)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input class="billing-control quantity-control quantity-input" type="number" min="1" name="items[{{ $index }}][quantity]" value="{{ $oldItem['quantity'] ?? 1 }}" required>
                                        </td>
                                        <td class="unit-price billing-muted">—</td>
                                        <td class="line-total fw-semibold">—</td>
                                        <td><button class="billing-remove remove-item" type="button" aria-label="Remove product row">×</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button class="billing-add" id="add-item" type="button">+ Add Product</button>
                </section>

                <section class="billing-panel billing-payment" aria-labelledby="payment-title">
                    <h2 class="billing-section-title" id="payment-title">Payment</h2>
                    <div class="billing-total-row"><span>Subtotal</span><strong id="subtotal">₹0.00</strong></div>
                    <div class="billing-total-row"><span>Tax</span><strong id="tax-total">₹0.00</strong></div>
                    <div class="billing-total-row billing-grand-total"><span>Grand Total</span><strong id="grand-total">₹0.00</strong></div>
                    <div class="mt-3">
                        <label class="billing-label" for="amount-given">Amount Given by Customer</label>
                        <input class="billing-control" id="amount-given" type="number" min="0" step="0.01" placeholder="₹0.00" inputmode="decimal">
                    </div>
                    <div class="billing-total-row billing-change"><span id="change-label">Balance to Return</span><strong id="change-total">₹0.00</strong></div>
                </section>
            </div>

            <aside class="billing-column">
                <section class="billing-panel billing-stock" aria-labelledby="low-stock-title">
                    <h2 class="billing-section-title" id="low-stock-title">⚠ Low Stock Alert</h2>
                    @if ($lowStockProducts->isEmpty())
                        <p class="billing-muted mb-0">No products are below the low-stock threshold.</p>
                    @else
                        <ul>
                            @foreach ($lowStockProducts as $product)
                                <li>{{ $product->name }} — {{ $product->stock_quantity }} units left</li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="billing-muted mt-3 mb-0">Alert threshold: {{ config('store.low_stock_threshold') }} units</p>
                </section>

                <div>
                    <button class="billing-submit" type="submit">Generate Bill</button>
                    <p class="billing-submit-note">The bill opens after order creation. Stock is checked again on the server before saving.</p>
                </div>
            </aside>
        </div>
    </form>
</div>

<template id="product-row-template">
    <tr class="item-row">
        <td>
            <select class="billing-control product-control product-select" name="items[__INDEX__][product_id]" required>
                <option value="">Choose a product</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-tax="{{ $product->tax_percentage }}" data-stock="{{ $product->stock_quantity }}">
                        {{ $product->name }} ({{ $product->stock_quantity }} in stock)
                    </option>
                @endforeach
            </select>
        </td>
        <td><input class="billing-control quantity-control quantity-input" type="number" min="1" name="items[__INDEX__][quantity]" value="1" required></td>
        <td class="unit-price billing-muted">—</td>
        <td class="line-total fw-semibold">—</td>
        <td><button class="billing-remove remove-item" type="button" aria-label="Remove product row">×</button></td>
    </tr>
</template>
@endsection

@push('scripts')
    <script>
        const itemsContainer = document.getElementById('items-container');
        const rowTemplate = document.getElementById('product-row-template');
        const amountGivenInput = document.getElementById('amount-given');
        const customers = @json($customers->map(fn ($customer) => ['name' => $customer->name, 'email' => mb_strtolower($customer->email)])->values());
        const currencyFormatter = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' });
        let nextRowIndex = {{ collect(old('items', [['product_id' => '', 'quantity' => 1]]))->keys()->max() + 1 }};

        function formatCurrency(amount) {
            return currencyFormatter.format(amount);
        }

        function updateProductOptions() {
            const selects = [...itemsContainer.querySelectorAll('.product-select')];

            selects.forEach((select) => {
                const selectedElsewhere = new Set(
                    selects
                        .filter((otherSelect) => otherSelect !== select)
                        .map((otherSelect) => otherSelect.value)
                        .filter(Boolean)
                );

                [...select.options].forEach((option) => {
                    option.disabled = option.value !== '' && selectedElsewhere.has(option.value);
                });
            });
        }

        function updateOrderTotals() {
            let subtotal = 0;
            let taxTotal = 0;

            itemsContainer.querySelectorAll('.item-row').forEach((row) => {
                const select = row.querySelector('.product-select');
                const quantityInput = row.querySelector('.quantity-input');
                const option = select.selectedOptions[0];
                const quantity = Math.max(0, Number.parseInt(quantityInput.value, 10) || 0);

                if (!option || !option.value || quantity === 0) {
                    row.querySelector('.unit-price').textContent = '—';
                    row.querySelector('.line-total').textContent = '—';

                    return;
                }

                const unitPrice = Number(option.dataset.price);
                const taxPercentage = Number(option.dataset.tax);
                const lineSubtotal = Math.round((unitPrice * quantity + Number.EPSILON) * 100) / 100;
                const lineTax = Math.round((lineSubtotal * taxPercentage / 100 + Number.EPSILON) * 100) / 100;

                subtotal += lineSubtotal;
                taxTotal += lineTax;
                row.querySelector('.unit-price').textContent = formatCurrency(unitPrice);
                row.querySelector('.line-total').textContent = formatCurrency(lineSubtotal + lineTax);
            });

            subtotal = Math.round((subtotal + Number.EPSILON) * 100) / 100;
            taxTotal = Math.round((taxTotal + Number.EPSILON) * 100) / 100;
            const grandTotal = subtotal + taxTotal;
            const amountGiven = Number.parseFloat(amountGivenInput.value);
            const changeLabel = document.getElementById('change-label');

            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('tax-total').textContent = formatCurrency(taxTotal);
            document.getElementById('grand-total').textContent = formatCurrency(grandTotal);

            if (Number.isFinite(amountGiven)) {
                const change = Math.round((amountGiven - grandTotal + Number.EPSILON) * 100) / 100;
                changeLabel.textContent = change >= 0 ? 'Balance to Return' : 'Amount Due';
                document.getElementById('change-total').textContent = formatCurrency(Math.abs(change));
            } else {
                changeLabel.textContent = 'Balance to Return';
                document.getElementById('change-total').textContent = formatCurrency(0);
            }
        }

        document.getElementById('add-item').addEventListener('click', () => {
            const newRow = rowTemplate.content.firstElementChild.cloneNode(true);

            newRow.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replaceAll('__INDEX__', nextRowIndex);
            });

            nextRowIndex += 1;
            itemsContainer.appendChild(newRow);
            updateProductOptions();
            updateOrderTotals();
            newRow.querySelector('.product-select').focus();
        });

        itemsContainer.addEventListener('change', (event) => {
            if (event.target.matches('.product-select')) {
                updateProductOptions();
            }

            updateOrderTotals();
        });

        itemsContainer.addEventListener('input', updateOrderTotals);
        itemsContainer.addEventListener('click', (event) => {
            if (!event.target.matches('.remove-item') || itemsContainer.querySelectorAll('.item-row').length === 1) {
                return;
            }

            event.target.closest('.item-row').remove();
            updateProductOptions();
            updateOrderTotals();
        });

        document.getElementById('customer-email').addEventListener('input', (event) => {
            const email = event.target.value.trim().toLowerCase();
            const existingCustomer = customers.find((customer) => customer.email === email);

            if (existingCustomer) {
                document.getElementById('customer-name').value = existingCustomer.name;
            }
        });

        amountGivenInput.addEventListener('input', updateOrderTotals);
        updateProductOptions();
        updateOrderTotals();
    </script>
@endpush
