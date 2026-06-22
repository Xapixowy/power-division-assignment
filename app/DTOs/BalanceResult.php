<?php

namespace App\DTOs;

use App\Models\Account;
use App\Models\Transaction;

readonly class BalanceResult
{
    public function __construct(
        public Account     $account,
        public Transaction $transaction,
    )
    {
    }
}
