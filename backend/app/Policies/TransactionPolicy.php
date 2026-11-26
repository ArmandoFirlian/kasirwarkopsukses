<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'kasir'], true);
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'kasir'], true);
    }

    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $transaction->user_id === $user->id;
    }

    public function viewDailyReport(User $user): bool
    {
        return $user->role === 'admin';
    }
}