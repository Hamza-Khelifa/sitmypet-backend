<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Domains\Identity\Entities\User;
use App\Domains\Payments\Entities\Wallet;
use App\Domains\Payments\Integrations\Contracts\MangopayGatewayInterface;
use App\Domains\Payments\Integrations\MangopayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentsFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_wallet(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/payments/wallets', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'nationality' => 'ES',
            'country_of_residence' => 'ES',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.currency', 'EUR');

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'kyc_status' => 'created',
        ]);
    }

    public function test_escrow_payin_flow(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/v1/payments/wallets/{$wallet->id}/payins", [
            'amount' => 100.50,
            'return_url' => 'http://localhost/return',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data' => ['transaction_id', 'redirect_url']]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'pay_in',
            'amount' => 100.50,
            'status' => 'pending',
        ]);
    }
}
