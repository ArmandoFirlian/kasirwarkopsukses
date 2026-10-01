<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller {
    public function store(Request $request) {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'pay_amount' => 'required|numeric',
            'payment_method' => 'required|in:cash,qris',
        ]);

        return DB::transaction(function () use ($request) {
            $totalPrice = 0;
            $itemsToInsert = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'message' => "Stok {$product->name} tidak cukup!"
                    ], 400);
                }

                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;

                // Pengurangan Stok Otomatis
                $product->decrement('stock', $item['quantity']);

                $itemsToInsert[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            if ($request->pay_amount < $totalPrice) {
                return response()->json(['message' => 'Uang pembayaran kurang!'], 400);
            }

            $transaction = Transaction::create([
                'invoice_number' => 'INV-' . time() . '-' . rand(100, 999),
                'total_price' => $totalPrice,
                'pay_amount' => $request->pay_amount,
                'change_amount' => $request->pay_amount - $totalPrice,
                'payment_method' => $request->payment_method,
            ]);

            foreach ($itemsToInsert as $detail) {
                $transaction->details()->create($detail);
            }

            return response()->json([
                'message' => 'Transaksi berhasil diproses',
                'data' => $transaction->load('details.product')
            ], 201);
        });
    }

    public function reports() {
        $today = now()->format('Y-m-d');
        return response()->json([
            'date' => $today,
            'total_omset' => Transaction::whereDate('created_at', $today)->sum('total_price'),
            'total_transactions' => Transaction::whereDate('created_at', $today)->count(),
        ], 200);
    }
}