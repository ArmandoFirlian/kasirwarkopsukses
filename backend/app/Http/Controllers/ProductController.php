<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->orderBy('name');

        // Hanya pemilik yang boleh melihat menu nonaktif.
        if (! ($request->user()->isOwner() && $request->boolean('include_inactive'))) {
            $query->active();
        }

        return ApiResponse::success($query->get());
    }

    public function show(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        if (! $product->is_active && ! $request->user()->isOwner()) {
            abort(404);
        }

        return ApiResponse::success($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $product = Product::create($validated);

        return ApiResponse::success($product->fresh(), 'Menu berhasil ditambahkan', 201);
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|integer|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($validated);

        return ApiResponse::success($product->fresh(), 'Menu berhasil diperbarui');
    }

    /** KF-05: menu dinonaktifkan, riwayat transaksi tetap utuh. */
    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => false]);

        return ApiResponse::success($product->fresh(), 'Menu dinonaktifkan');
    }

    public function lowStock()
    {
        return ApiResponse::success(
            Product::active()->lowStock()->orderBy('stock')->get()
        );
    }
}
