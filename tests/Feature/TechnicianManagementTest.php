<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicianManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // -- Admin CRUD --

    public function test_admin_can_list_users_with_active_service_counts(): void
    {
        $this->actingAs($this->admin);
        User::factory()->create(['name' => 'Teknisi A', 'role' => 'technician']);

        $response = $this->get(route('technicians.index'));

        $response->assertOk()
            ->assertSee('Teknisi A');
    }

    public function test_admin_can_create_new_user(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('technicians.store'), [
            'name' => 'New Tech',
            'email' => 'newtech@tracket.test',
            'password' => 'password123',
            'role' => 'technician',
        ]);

        $response->assertRedirect(route('technicians.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'newtech@tracket.test',
            'role' => 'technician',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $this->actingAs($this->admin);
        $tech = User::factory()->create(['name' => 'Old Name', 'role' => 'technician']);

        $response = $this->put(route('technicians.update', $tech), [
            'name' => 'Updated Tech',
            'email' => $tech->email,
            'role' => 'technician',
        ]);

        $response->assertRedirect(route('technicians.index'));
        $this->assertDatabaseHas('users', ['id' => $tech->id, 'name' => 'Updated Tech']);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $this->actingAs($this->admin);

        $response = $this->delete(route('technicians.destroy', $this->admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_cannot_delete_last_admin(): void
    {
        $this->actingAs($this->admin);

        $response = $this->delete(route('technicians.destroy', $this->admin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'role' => 'admin']);
    }

    public function test_admin_can_delete_technician(): void
    {
        $this->actingAs($this->admin);
        $tech = User::factory()->create(['role' => 'technician']);

        $response = $this->delete(route('technicians.destroy', $tech));

        $response->assertRedirect(route('technicians.index'));
        $this->assertDatabaseMissing('users', ['id' => $tech->id]);
    }

    public function test_admin_can_upgrade_technician_to_admin(): void
    {
        $this->actingAs($this->admin);
        $tech = User::factory()->create(['name' => 'Promotable', 'role' => 'technician']);

        $this->put(route('technicians.update', $tech), [
            'name' => 'Promotable',
            'email' => $tech->email,
            'role' => 'admin',
        ]);

        $this->assertEquals('admin', $tech->fresh()->role);
    }

    // -- Access control --

    public function test_technician_cannot_access_user_management(): void
    {
        $tech = User::factory()->create(['role' => 'technician']);
        $this->actingAs($tech);

        $this->get(route('technicians.index'))->assertForbidden();
        $this->get(route('technicians.create'))->assertForbidden();
    }

    // -- Validation --

    public function test_user_creation_requires_unique_email(): void
    {
        $this->actingAs($this->admin);
        User::factory()->create(['email' => 'duplicate@tracket.test']);

        $response = $this->post(route('technicians.store'), [
            'name' => 'Dup User',
            'email' => 'duplicate@tracket.test',
            'password' => 'password123',
            'role' => 'technician',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_creation_requires_password_min_8_chars(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('technicians.store'), [
            'name' => 'Short Pw',
            'email' => 'short@tracket.test',
            'password' => '1234567',
            'role' => 'technician',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_user_creation_rejects_invalid_role(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('technicians.store'), [
            'name' => 'Invalid Role',
            'email' => 'invalid@tracket.test',
            'password' => 'password123',
            'role' => 'superadmin',
        ]);

        $response->assertSessionHasErrors('role');
    }

    // -- Filtering --

    public function test_technician_list_can_filter_by_role(): void
    {
        $this->actingAs($this->admin);
        User::factory()->create(['name' => 'Admin User', 'role' => 'admin']);
        User::factory()->create(['name' => 'Tech User', 'role' => 'technician']);

        $this->get(route('technicians.index', ['role' => 'technician']))
            ->assertOk()
            ->assertSee('Tech User')
            ->assertDontSee('Admin User');

        $this->get(route('technicians.index', ['role' => 'admin']))
            ->assertOk()
            ->assertSee('Admin User')
            ->assertDontSee('Tech User');
    }
}
