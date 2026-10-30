<?php

namespace App\Services;

use App\Jobs\SendOrderConfirmationJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function createOrder(array $payload): Order
    {
        $customerData = $payload['customer'] ?? [];
        $customerName = trim((string) ($customerData['name'] ?? ''));
        $customerEmail = strtolower(trim((string) ($customerData['email'] ?? '')));

        if ($customerName === '' || $customerEmail === '') {
            throw ValidationException::withMessages([
                'customer' => 'Customer name and email are required.',
            ]);
        }

        $customer = Customer::query()->whereRaw('LOWER(email) = ?', [$customerEmail])->first();

        if (! $customer) {
            $customer = Customer::create([
                'name' => $customerName,
                'email' => $customerEmail,
            ]);
        }

        if (empty($payload['items']) || ! is_array($payload['items'])) {
            throw ValidationException::withMessages([
                'items' => 'At least one product must be included in the order.',
            ]);
        }

        $productIds = collect($payload['items'])->pluck('product_id')->filter()->all();
        $productQuery = Product::query()->whereIn('id', $productIds);

        if (DB::connection()->getDriverName() === 'mysql') {
            $productQuery->lockForUpdate();
        }

        $products = $productQuery->get()->keyBy('id');

        if (count($products) !== count($productIds)) {
            throw ValidationException::withMessages([
                'items' => 'One or more selected products could not be found.',
            ]);
        }

        $validatedItems = [];
        $subtotal = 0.0;
        $taxAmount = 0.0;

        foreach ($payload['items'] as $index => $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);

            if (! isset($products[$productId])) {
                throw ValidationException::withMessages([
                    'items.' . $index . '.product_id' => 'The selected product is invalid.',
                ]);
            }

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'items.' . $index . '.quantity' => 'Quantity must be greater than zero.',
                ]);
            }

            $product = $products[$productId];

            if ($quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'items.' . $index . '.quantity' => 'Insufficient stock for ' . $product->name . '. Available: ' . $product->stock_quantity,
                ]);
            }

            $lineSubtotal = round((float) $product->price * $quantity, 2);
            $lineTax = round($lineSubtotal * ((float) $product->tax_percentage / 100), 2);
            $lineTotal = round($lineSubtotal + $lineTax, 2);

            $subtotal += $lineSubtotal;
            $taxAmount += $lineTax;

            $validatedItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'tax_percentage' => $product->tax_percentage,
                'subtotal' => $lineSubtotal,
                'tax_amount' => $lineTax,
                'total' => $lineTotal,
            ];
        }

        $grandTotal = round($subtotal + $taxAmount, 2);

        try {
            return DB::transaction(function () use ($customer, $validatedItems, $subtotal, $taxAmount, $grandTotal) {
                $order = Order::create([
                    'customer_id' => $customer->id,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'grand_total' => $grandTotal,
                ]);

                foreach ($validatedItems as $item) {
                    $order->items()->create([
                        'product_id' => $item['product']->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax_percentage' => $item['tax_percentage'],
                        'subtotal' => $item['subtotal'],
                        'tax_amount' => $item['tax_amount'],
                        'total' => $item['total'],
                    ]);

                    $item['product']->decrement('stock_quantity', $item['quantity']);
                }

                dispatch(new SendOrderConfirmationJob($order->id))->afterCommit();

                Log::info('Order created successfully.', ['order_id' => $order->id, 'customer_id' => $customer->id]);

                return $order->fresh(['customer', 'items.product']);
            }, 3);
        } catch (QueryException $exception) {
            throw ValidationException::withMessages([
                'order' => 'The order could not be completed. Please try again.',
            ]);
        }
    }
}
