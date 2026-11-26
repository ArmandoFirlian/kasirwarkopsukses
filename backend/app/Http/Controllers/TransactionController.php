<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use App\Support\ApiResponse;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $service)
    {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'pay_amount' => 'required|integer|min:0',
            'payment_method' => 'required|in:cash,qris',
        ]);

        $transaction = $this->service->createPos(
            $data['items'],
            (int) $data['pay_amount'],
            $data['payment_method'],
            $request->user()->id,
        );

        return ApiResponse::success($transaction, 'Transaksi berhasil diproses', 201);
    }

    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d']);

        $day = Carbon::createFromFormat('!Y-m-d', $request->query('date', now()->toDateString()));

        $transactions = Transaction::query()
            ->whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->withCount('details')
            ->latest()
            ->get();

        return ApiResponse::success($transactions);
    }

    /** Struk digital satu transaksi. */
    public function show(int $id)
    {
        return ApiResponse::success(
            Transaction::with(['details.product', 'user:id,name'])->findOrFail($id)
        );
    }
}
