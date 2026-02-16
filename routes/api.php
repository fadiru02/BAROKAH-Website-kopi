<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\XenditWebhookController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/orders', [OrderController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);

Route::post('/webhooks/xendit', [XenditWebhookController::class, 'handleInvoice']);
