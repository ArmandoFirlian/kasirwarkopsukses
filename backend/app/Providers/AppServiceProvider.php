<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Transaction;
use App\Policies\ProductPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
    }
}