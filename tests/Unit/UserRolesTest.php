<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_is_identified_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isTechnician());
    }

    public function test_technician_user_is_identified_correctly(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);

        $this->assertTrue($technician->isTechnician());
        $this->assertFalse($technician->isAdmin());
    }

    public function test_password_is_hashed_automatically(): void
    {
        $user = User::factory()->make(['password' => 'rahasia-123']);

        $this->assertNotEquals('rahasia-123', $user->password);
        $this->assertTrue(strlen($user->password) > 30);
    }
}
