<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return response()->json(array_filter([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], fn ($v) => $v !== null), $status);
    }
}
