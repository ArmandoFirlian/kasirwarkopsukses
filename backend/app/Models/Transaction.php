<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const SOURCE_POS = 'pos';
    public const SOURCE_WEB = 'web';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /** Perpindahan status yang diizinkan. */
    public const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_ACCEPTED, self::STATUS_CANCELLED],
        self::STATUS_ACCEPTED => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
    ];

    protected $fillable = [
        'invoice_number', 'total_price', 'pay_amount', 'change_amount', 'payment_method',
        'source', 'status', 'order_code', 'customer_name', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'integer',
            'pay_amount' => 'integer',
            'change_amount' => 'integer',
        ];
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
