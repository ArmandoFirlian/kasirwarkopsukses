<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * Galat yang dilempar sengaja oleh aturan bisnis. Karena dilempar (bukan di-return),
 * DB::transaction otomatis rollback sehingga stok tidak berubah (KNF-04).
 */
class ApiException extends RuntimeException
{
    public function __construct(string $message, protected int $status = 422, protected array $errors = [])
    {
        parent::__construct($message);
    }

    public static function conflict(string $message): self
    {
        return new self($message, 409);
    }

    public static function unprocessable(string $message, array $errors = []): self
    {
        return new self($message, 422, $errors);
    }

    public static function forbidden(string $message = 'Anda tidak berhak mengakses fitur ini'): self
    {
        return new self($message, 403);
    }

    public function render(): JsonResponse
    {
        return response()->json(array_filter([
            'status' => 'error',
            'message' => $this->getMessage(),
            'errors' => $this->errors ?: null,
        ], fn ($v) => $v !== null), $this->status);
    }
}
