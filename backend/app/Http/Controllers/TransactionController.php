<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTransactionRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Support\ApiResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $service)
    {
    }

    public function store(StoreTransactionRequest $request)
    {
        Gate::authorize('create', Transaction::class);

        $data = $request->validated();

        $transaction = $this->service->createPos(
            $data['items'],
            (int) $data['pay_amount'],
            $data['payment_method'],
            $request->user()->id,
        );

        return ApiResponse::success(
            new TransactionResource($transaction),
            'Transaksi berhasil diproses',
            201
        );
    }

    public function index(IndexTransactionRequest $request)
    {
        Gate::authorize('viewAny', Transaction::class);

        $validated = $request->validated();
        $day = Carbon::createFromFormat('!Y-m-d', $validated['date'] ?? now()->toDateString());

        $query = Transaction::query()
            ->whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->with(['details.product', 'user:id,name'])
            ->withCount('details')
            ->latest();

        if ($request->user()->role === 'kasir') {
            $query->where('user_id', $request->user()->id);
        }

        return ApiResponse::success(
            TransactionResource::collection($query->get())
        );
    }

    /** Struk digital satu transaksi. */
    public function show(int $id)
    {
        $transaction = Transaction::with(['details.product', 'user:id,name'])->findOrFail($id);

        Gate::authorize('view', $transaction);

        return ApiResponse::success(new TransactionResource($transaction));
    }
}