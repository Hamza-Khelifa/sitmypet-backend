<?php

namespace Database\Factories;

use App\Domains\Payments\Entities\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Domains\Identity\Entities\User::factory(),
            'mangopay_wallet_id' => '123456',
            'mangopay_user_id' => '654321',
            'balance' => 0.00,
            'currency' => 'EUR',
        ];
    }
}
