<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller {
    public function index() {
        return response()->json([
            'status' => 'success',
            'data' => Product::all()
        ], 200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product
        ], 201);
    }
}