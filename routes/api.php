<?php

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
});