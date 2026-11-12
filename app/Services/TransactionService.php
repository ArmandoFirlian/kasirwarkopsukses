<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    /**
     * Transaksi langsung di kasir (POS). Pembayaran divalidasi sebelum apa pun disimpan,
     * dan semua galat dilempar sebagai exception sehingga DB::transaction rollback (KNF-04).
     */
    public function createPos(array $items, int $payAmount, string $method, ?int $userId): Transaction
    {
        return DB::transaction(function () use ($items, $payAmount, $method, $userId) {
            [$lines, $total] = $this->reserveStock($items);

            if ($payAmount < $total) {
                throw ApiException::unprocessable('Uang pembayaran kurang', [
                    'pay_amount' => ["Total Rp{$total}, dibayar Rp{$payAmount}"],
                ]);
            }

            return $this->persist($lines, [
                'total_price' => $total,
                'pay_amount' => $payAmount,
                'change_amount' => $payAmount - $total,
                'payment_method' => $method,
                'source' => Transaction::SOURCE_POS,
                'status' => Transaction::STATUS_COMPLETED,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Pesanan dari pembeli (web). Stok langsung dipesan agar dua pembeli tidak
     * bisa memesan stok terakhir yang sama; dikembalikan bila pesanan dibatalkan.
     */
    public function createWebOrder(array $items, string $customerName, string $method): Transaction
    {
        return DB::transaction(function () use ($items, $customerName, $method) {
            [$lines, $total] = $this->reserveStock($items);

            return $this->persist($lines, [
                'total_price' => $total,
                'pay_amount' => 0,
                'change_amount' => 0,
                'payment_method' => $method,
                'source' => Transaction::SOURCE_WEB,
                'status' => Transaction::STATUS_PENDING,
                'order_code' => $this->newOrderCode(),
                'customer_name' => $customerName,
            ]);
        });
    }

    /** Ubah status pesanan web: pending -> accepted -> completed, atau cancelled. */
    public function updateStatus(int $id, string $status, ?int $payAmount, ?int $userId): Transaction
    {
        return DB::transaction(function () use ($id, $status, $payAmount, $userId) {
            $transaction = Transaction::whereKey($id)->lockForUpdate()->firstOrFail();

            $allowed = Transaction::TRANSITIONS[$transaction->status] ?? [];
            if (! in_array($status, $allowed, true)) {
                throw ApiException::conflict(
                    "Pesanan berstatus {$transaction->status} tidak dapat diubah menjadi {$status}"
                );
            }

            if ($status === Transaction::STATUS_COMPLETED) {
                $pay = $payAmount ?? 0;
                if ($pay < $transaction->total_price) {
                    throw ApiException::unprocessable('Uang pembayaran kurang', [
                        'pay_amount' => ["Total Rp{$transaction->total_price}, dibayar Rp{$pay}"],
                    ]);
                }
                $transaction->pay_amount = $pay;
                $transaction->change_amount = $pay - $transaction->total_price;
            }

            if ($status === Transaction::STATUS_CANCELLED) {
                $this->restoreStock($transaction);
            }

            $transaction->status = $status;
            $transaction->user_id = $userId;
            $transaction->save();

            return $transaction->load('details.product');
        });
    }

    // ── helpers ──────────────────────────────────────────────────────────

    /** Validasi stok, kurangi stok, dan kembalikan baris detail + total harga. */
    private function reserveStock(array $items): array
    {
        $lines = [];
        $total = 0;

        foreach ($items as $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (! $product) {
                throw ApiException::unprocessable("Produk #{$item['product_id']} tidak tersedia");
            }

            $qty = (int) $item['quantity'];

            if ($product->stock < $qty) {
                throw ApiException::conflict("Stok {$product->name} tidak cukup (tersisa {$product->stock})");
            }

            $subtotal = $product->price * $qty;
            $total += $subtotal;

            $product->decrement('stock', $qty);

            $lines[] = [
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => $product->price,
                'subtotal' => $subtotal,
            ];
        }

        return [$lines, $total];
    }

    /** Kembalikan stok saat pesanan dibatalkan. */
    private function restoreStock(Transaction $transaction): void
    {
        foreach ($transaction->details as $detail) {
            Product::whereKey($detail->product_id)->increment('stock', $detail->quantity);
        }
    }

    /** Simpan transaksi + detail, kembalikan transaksi lengkap. */
    private function persist(array $lines, array $attributes): Transaction
    {
        $attributes['invoice_number'] = 'INV-' . now()->format('Ymd-His') . '-' . rand(100, 999);

        $transaction = Transaction::create($attributes);

        foreach ($lines as $line) {
            $transaction->details()->create($line);
        }

        return $transaction->load('details.product');
    }

    private function newOrderCode(): string
    {
        do {
            $code = 'WS-' . strtoupper(Str::random(6));
        } while (Transaction::where('order_code', $code)->exists());

        return $code;
    }
}
