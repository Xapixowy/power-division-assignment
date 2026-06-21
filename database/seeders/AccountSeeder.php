<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@test.pl')->firstOrFail();

        Account::create([
            'user_id' => $user->id,
            'balance' => 20000,
        ]);
    }
}
