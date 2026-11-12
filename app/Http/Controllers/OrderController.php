<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

/** Pengelolaan pesanan web oleh kasir. */
class OrderController extends Controller
{
    public function __construct(private TransactionService $service)
    {
    }

    public function index(Request $request)
    {
        $request->validate(['status' => 'nullable|in:pending,accepted,completed,cancelled']);

        $orders = Transaction::query()
            ->where('source', Transaction::SOURCE_WEB)
            ->when(
                $request->query('status'),
                fn ($q, $status) => $q->where('status', $status),
                fn ($q) => $q->whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_ACCEPTED]),
            )
            ->with('details.product')
            ->oldest()
            ->get();

        return ApiResponse::success($orders);
    }

    public function updateStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => 'required|in:accepted,completed,cancelled',
            'pay_amount' => 'nullable|integer|min:0',
        ]);

        $order = $this->service->updateStatus(
            $id,
            $data['status'],
            isset($data['pay_amount']) ? (int) $data['pay_amount'] : null,
            $request->user()->id,
        );

        return ApiResponse::success($order, 'Status pesanan diperbarui');
    }
}
