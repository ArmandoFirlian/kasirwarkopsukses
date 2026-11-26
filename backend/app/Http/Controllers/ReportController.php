<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /** KF-14: laporan harian. Hanya transaksi berstatus completed yang dihitung. */
    public function daily(ReportRequest $request): JsonResponse
    {
        Gate::authorize('viewDailyReport', Transaction::class);

        $validated = $request->validated();
        $date = $validated['date'] ?? now()->toDateString();
        $day = Carbon::createFromFormat('!Y-m-d', $date);

        $baseQuery = Transaction::where('status', Transaction::STATUS_COMPLETED)
            ->whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()]);

        $transactions = (clone $baseQuery)
            ->with(['details.product', 'user:id,name'])
            ->latest()
            ->get();

        return response()->json([
            'date' => $date,
            'total_omset' => (int) (clone $baseQuery)->sum('total_price'),
            'total_transactions' => (clone $baseQuery)->count(),
            'by_payment_method' => (clone $baseQuery)
                ->selectRaw('payment_method, COUNT(*) as transactions, SUM(total_price) as omset')
                ->groupBy('payment_method')
                ->get(),
            'transactions' => TransactionResource::collection($transactions)->resolve(),
        ]);
    }

    /** KF-16: laporan bulanan berisi omzet dan lima menu terlaris. */
    public function monthly(ReportRequest $request)
    {
        Gate::authorize('viewDailyReport', Transaction::class);

        $validated = $request->validated();
        $month = $validated['month'] ?? now()->format('Y-m');
        $start = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = Transaction::where('status', Transaction::STATUS_COMPLETED)
            ->whereBetween('created_at', [$start, $end]);

        $topItems = TransactionDetail::query()
            ->whereHas('transaction', fn ($q) => $q->where('status', Transaction::STATUS_COMPLETED)
                ->whereBetween('created_at', [$start, $end]))
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('product:id,name')
            ->get();

        return ApiResponse::success([
            'month' => $month,
            'total_omset' => (int) (clone $query)->sum('total_price'),
            'total_transactions' => (clone $query)->count(),
            'top_items' => $topItems,
        ]);
    }
}