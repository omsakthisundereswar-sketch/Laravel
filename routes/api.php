<?php

use App\Http\Controllers\Api\CustomerController as ApiCustomerController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/orders', [ApiOrderController::class, 'store']);
Route::get('/customers/{email}/orders', [ApiCustomerController::class, 'orders']);
Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
