<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Product::class);

        $query = Product::query()->orderBy('name');

        if (! ($request->user()->role === 'admin' && $request->boolean('include_inactive'))) {
            $query->active();
        }

        return ApiResponse::success(ProductResource::collection($query->get()));
    }

    public function show(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        if (! $product->is_active && $request->user()->role !== 'admin') {
            abort(404);
        }

        return ApiResponse::success(new ProductResource($product));
    }

    public function store(StoreProductRequest $request)
    {
        Gate::authorize('create', Product::class);

        $product = Product::create($request->validated());

        return ApiResponse::success(
            new ProductResource($product->fresh()),
            'Menu berhasil ditambahkan',
            201
        );
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $product = Product::findOrFail($id);

        Gate::authorize('update', $product);

        $product->update($request->validated());

        return ApiResponse::success(
            new ProductResource($product->fresh()),
            'Menu berhasil diperbarui'
        );
    }

    /** KF-05: menu dinonaktifkan, riwayat transaksi tetap utuh. */
    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);

        Gate::authorize('delete', $product);

        $product->update(['is_active' => false]);

        return ApiResponse::success(
            new ProductResource($product->fresh()),
            'Menu dinonaktifkan'
        );
    }

    public function lowStock()
    {
        Gate::authorize('create', Product::class);

        $products = Product::active()->lowStock()->orderBy('stock')->get();

        return ApiResponse::success(ProductResource::collection($products));
    }
}