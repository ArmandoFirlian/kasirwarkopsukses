<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /** KF-14: laporan harian. Hanya transaksi berstatus completed yang dihitung. */
    public function daily(Request $request)
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d']);

        $date = $request->query('date', now()->toDateString());
        $day = Carbon::createFromFormat('!Y-m-d', $date);

        $query = Transaction::where('status', Transaction::STATUS_COMPLETED)
            ->whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()]);

        return ApiResponse::success([
            'date' => $date,
            'total_omset' => (int) (clone $query)->sum('total_price'),
            'total_transactions' => (clone $query)->count(),
            'by_payment_method' => (clone $query)
                ->selectRaw('payment_method, COUNT(*) as transactions, SUM(total_price) as omset')
                ->groupBy('payment_method')
                ->get(),
        ]);
    }

    /** KF-16: laporan bulanan berisi omzet dan lima menu terlaris. */
    public function monthly(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);

        $month = $request->query('month', now()->format('Y-m'));
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
