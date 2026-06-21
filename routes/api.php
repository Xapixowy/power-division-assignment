<?php

use App\Http\Controllers\BalanceController;
use Illuminate\Support\Facades\Route;

Route::post('/users/{user}/balance', BalanceController::class)
    ->middleware('auth.api_key');
