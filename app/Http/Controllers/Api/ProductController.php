<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function lowStock(): JsonResponse
    {
        $threshold = config('store.low_stock_threshold', 10);

        $products = Product::query()->lowStock($threshold)->orderBy('stock_quantity')->get()->map(function ($product) use ($threshold) {
            return [
                'name' => $product->name,
                'code' => $product->unique_code,
                'stock_quantity' => $product->stock_quantity,
                'threshold' => $threshold,
            ];
        });

        return response()->json([
            'threshold' => $threshold,
            'products' => $products,
        ]);
    }
}
