<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->account = Account::create([
        'user_id' => $this->user->id,
        'balance' => 10000,
        'currency' => 'PLN',
    ]);

    $this->apiKey = config('app.api_key');
    $this->url = "/api/v1/users/{$this->user->id}/balance";
});

it('returns 401 without api key', function () {
    $this->postJson($this->url, ['amount' => 100, 'type' => 'credit'])
        ->assertStatus(401);
});

it('returns 401 with invalid api key', function () {
    $this->withHeader('X-API-KEY', 'invalid')
        ->postJson($this->url, ['amount' => 100, 'type' => 'credit'])
        ->assertStatus(401);
});

it('returns 422 when amount is missing', function () {
    $this->withHeader('X-API-KEY', $this->apiKey)
        ->postJson($this->url, ['type' => 'credit'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});

it('returns 422 when amount is not integer', function () {
    $this->withHeader('X-API-KEY', $this->apiKey)
        ->postJson($this->url, ['amount' => 'abc', 'type' => 'credit'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});

it('returns 422 when type is invalid', function () {
    $this->withHeader('X-API-KEY', $this->apiKey)
        ->postJson($this->url, ['amount' => 100, 'type' => 'invalid'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

it('credits account balance', function () {
    $this->withHeader('X-API-KEY', $this->apiKey)
        ->postJson($this->url, ['amount' => 500, 'type' => 'credit'])
        ->assertStatus(200)
        ->assertJsonFragment(['balance' => 10500]);

    expect($this->account->fresh()->balance)->toBe(10500);
});

it('debits account balance', function () {
    $this->withHeader('X-API-KEY', $this->apiKey)
        ->postJson($this->url, ['amount' => 500, 'type' => 'debit'])
        ->assertStatus(200)
        ->assertJsonFragment(['balance' => 9500]);

    expect($this->account->fresh()->balance)->toBe(9500);
});

it('returns 422 when debit exceeds balance', function () {
    $this->withHeader('X-API-KEY', $this->apiKey)
        ->postJson($this->url, ['amount' => 99999, 'type' => 'debit'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});

it('returns correct json structure', function () {
    $this->withHeader('X-API-KEY', $this->apiKey)
        ->postJson($this->url, ['amount' => 100, 'type' => 'credit'])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user_id',
                'balance',
                'last_transaction_at',
            ],
        ]);
});
