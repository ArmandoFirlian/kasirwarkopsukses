<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

/** Endpoint publik untuk pembeli di web (tanpa login). */
class PublicOrderController extends Controller
{
    public function __construct(private TransactionService $service)
    {
    }

    public function menu()
    {
        $menu = Product::active()->orderBy('name')->get()->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->price,
            'available' => $p->stock > 0,
        ]);

        return ApiResponse::success($menu);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:50',
            'items' => 'required|array|min:1|max:30',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'payment_method' => 'required|in:cash,qris',
        ]);

        $order = $this->service->createWebOrder(
            $data['items'],
            $data['customer_name'],
            $data['payment_method'],
        );

        return ApiResponse::success($this->payload($order), 'Pesanan diterima', 201);
    }

    public function show(string $code)
    {
        $order = Transaction::where('source', Transaction::SOURCE_WEB)
            ->where('order_code', strtoupper($code))
            ->with('details.product')
            ->firstOrFail();

        return ApiResponse::success($this->payload($order));
    }

    /** Hanya data yang perlu diketahui pembeli. */
    private function payload(Transaction $order): array
    {
        return [
            'order_code' => $order->order_code,
            'status' => $order->status,
            'customer_name' => $order->customer_name,
            'payment_method' => $order->payment_method,
            'total_price' => $order->total_price,
            'created_at' => $order->created_at,
            'items' => $order->details->map(fn ($d) => [
                'name' => $d->product->name,
                'quantity' => $d->quantity,
                'price' => $d->price,
                'subtotal' => $d->subtotal,
            ])->values(),
        ];
    }
}
