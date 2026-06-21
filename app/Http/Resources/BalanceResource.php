<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->resource->user_id,
            'balance' => $this->resource->account->balance / 100,
            'last_transaction_at' => $this->resource->transaction->created_at->toIso8601ZuluString(),
        ];
    }
}
