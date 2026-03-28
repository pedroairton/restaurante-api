<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'online',
            'timestamp' => now()->toDateTimeString(),
            'service' => 'API',
            'message' => 'API is working'
        ]);
    });

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/auth/check', [AuthController::class, 'check']);

        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts']);

        Route::apiResource('categories', CategoryController::class);

        Route::get('/products/{product}/sales-stats', [ProductController::class, 'salesStats']);
        Route::apiResource('products', ProductController::class);

        Route::get('/tables', [TableController::class, 'index']);
        Route::patch('/tables/{table}/status', [TableController::class, 'updateStatus']);

        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::apiResource('orders', OrderController::class);
        Route::put('/orders/{order}', [OrderController::class, 'update']);

        Route::get('/reports', [ReportController::class, 'index']);
        // Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf']);
    });
});