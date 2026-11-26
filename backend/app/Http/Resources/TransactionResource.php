<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'user_id' => $this->user_id,
            'kasir_name' => $this->whenLoaded('user', fn () => $this->user->name),
            'total_price' => (float) $this->total_price,
            'pay_amount' => (float) $this->pay_amount,
            'change_amount' => (float) $this->change_amount,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'details' => TransactionDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}