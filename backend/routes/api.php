<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public endpoints (pembeli web, tanpa login)
|--------------------------------------------------------------------------
*/

Route::prefix('v1/public')->group(function () {
    Route::get('/menu', [PublicOrderController::class, 'menu']);
    Route::post('/orders', [PublicOrderController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/orders/{code}', [PublicOrderController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Auth endpoints
|--------------------------------------------------------------------------
*/

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| Protected endpoints (kasir & owner, via mobile app)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Products — read: kasir + owner, write: owner only
    Route::middleware('role:owner')->group(function () {
        Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update'])->whereNumber('id');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('role:kasir,owner')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show'])->whereNumber('id');
    });

    // Transactions (POS) — kasir creates, both can view
    Route::post('/transactions', [TransactionController::class, 'store'])->middleware('role:kasir');
    Route::middleware('role:kasir,owner')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/transactions/{id}', [TransactionController::class, 'show'])->whereNumber('id');
    });

    // Web orders management — kasir & owner
    Route::middleware('role:kasir,owner')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->whereNumber('id');
    });

    // Reports — owner only
    Route::middleware('role:owner')->group(function () {
        Route::get('/reports/daily', [ReportController::class, 'daily']);
        Route::get('/reports/monthly', [ReportController::class, 'monthly']);
    });
});
