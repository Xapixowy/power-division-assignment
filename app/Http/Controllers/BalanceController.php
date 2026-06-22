<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\BalanceRequest;
use App\Http\Resources\BalanceResource;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;

class BalanceController extends Controller
{
    public function __construct(
        private readonly BalanceService $balanceService
    )
    {
    }

    public function __invoke(
        BalanceRequest $request,
        User           $user
    ): JsonResponse
    {
        $result = $this->balanceService->process(
            $user->id,
            $request->integer('amount'),
            $request->enum('type', TransactionType::class)
        );

        return BalanceResource::make($result)->response();
    }
}
