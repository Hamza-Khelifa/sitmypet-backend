<?php

namespace Tests\Feature\Backoffice;

use App\Domains\Identity\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficeFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'pet-owner', 'guard_name' => 'web']);
    }

    public function test_standard_users_cannot_access_backoffice(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pet-owner');

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/backoffice/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_backoffice_and_update_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($admin, 'sanctum')->patchJson("/api/v1/backoffice/users/{$targetUser->id}/status", [
            'status' => 'suspended'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'status' => 'suspended'
        ]);
    }

    public function test_admin_cannot_refund_bookings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/v1/backoffice/bookings/00000000-0000-0000-0000-000000000000/refund");
        
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_refund_route(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        // We use a fake uuid, expect 404 ModelNotFound because the route is reached but booking doesn't exist
        $response = $this->actingAs($superAdmin, 'sanctum')->postJson("/api/v1/backoffice/bookings/00000000-0000-0000-0000-000000000000/refund");
        
        $response->assertStatus(404);
    }
}
