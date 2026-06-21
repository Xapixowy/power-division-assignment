<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BalanceService
{
    public function process(int $userId, int $amount, TransactionType $type): Account
    {
        return DB::transaction(function () use ($userId, $amount, $type) {
            $account = Account::where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($type === TransactionType::Debit && $account->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => ['Insufficient balance for this transaction.']
                ]);
            }

            $account->balance = $type === TransactionType::Credit
                ? $account->balance + $amount
                : $account->balance - $amount;
            $account->save();
            $account->transactions()->create([
                'amount' => $amount,
                'type' => $type,
            ]);
            
            return $account;
        });
    }
}
