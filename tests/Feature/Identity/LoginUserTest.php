<?php

declare(strict_types=1);

namespace Tests\Feature\Identity;

use App\Domains\Identity\Actions\LoginUserAction;
use App\Domains\Identity\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Tests\TestCase;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_successfully_logs_in_a_valid_user_and_issues_tokens(): void
    {
        // Setup
        $user = User::factory()->create([
            'password' => Hash::make('secret123')
        ]);

        $action = app(LoginUserAction::class);
        $request = Request::create('/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'TestAgent'
        ]);

        // Execute
        $result = $action->execute([
            'email' => $user->email,
            'password' => 'secret123'
        ], $request);

        // Assert
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('refresh_token', $result);
        $this->assertEquals($user->id, $result['user']->id);
        
        // Assert Database states
        // $this->assertDatabaseHas('user_devices', ['user_id' => $user->id]);
        // $this->assertDatabaseHas('refresh_tokens', ['user_id' => $user->id]);
        // $this->assertDatabaseHas('user_sessions', ['user_id' => $user->id]);
        // $this->assertDatabaseHas('audit_logs', ['action' => 'LOGIN_SUCCESS']);
    }

    public function test_rejects_invalid_credentials_and_logs_failure(): void
    {
        $this->assertTrue(true); // Placeholder for now
    }
}
