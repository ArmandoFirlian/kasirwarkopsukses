<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class)->except(['create', 'edit']);
Route::post('/transactions', [TransactionController::class, 'store']);
Route::get('/reports', [TransactionController::class, 'reports']);