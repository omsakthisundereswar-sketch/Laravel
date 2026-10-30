<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function orders(string $email): JsonResponse
    {
        $customer = Customer::query()->whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

        if (! $customer) {
            return response()->json([
                'message' => 'Customer not found.',
                'orders' => [],
            ], 404);
        }

        $orders = $customer->orders()->with('items.product')->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'created_at' => $order->created_at->toDateTimeString(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product?->name,
                        'product_code' => $item->product?->unique_code,
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'subtotal' => (float) $item->subtotal,
                        'tax_amount' => (float) $item->tax_amount,
                        'total' => (float) $item->total,
                    ];
                }),
                'subtotal' => (float) $order->subtotal,
                'tax_amount' => (float) $order->tax_amount,
                'grand_total' => (float) $order->grand_total,
            ];
        });

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'orders' => $orders,
        ]);
    }
}
