<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::post('/transactions', [TransactionController::class, 'store']);
Route::get('/reports', [TransactionController::class, 'reports']);