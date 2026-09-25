<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@tracket.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@tracket.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'admin@tracket.test',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->from(route('login'))->post(route('login.post'), [
            'email' => 'admin@tracket.test',
            'password' => 'salah-banget',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => 'bukan-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_logout_destroys_session(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_quick_login_as_admin_gives_admin_session(): void
    {
        User::factory()->create([
            'email' => 'admin@tracket.test',
            'role' => 'admin',
        ]);

        $response = $this->get(route('quick-login', ['role' => 'admin']));

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(auth()->user()->isAdmin());
    }

    public function test_technician_cannot_access_admin_reports(): void
    {
        User::factory()->create(['role' => 'technician']);
        $technician = User::where('role', 'technician')->first();
        $this->actingAs($technician);

        $this->get(route('reports.index'))->assertForbidden();
    }

    public function test_admin_can_access_reports(): void
    {
        User::factory()->create(['role' => 'admin']);
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $this->get(route('reports.index'))->assertOk();
    }

    public function test_cashier_can_access_checkin_but_cannot_access_repair_or_reports(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $this->actingAs($cashier);

        $this->get(route('services.create'))->assertOk();
        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('customers.index'))->assertForbidden();
        $this->get(route('technicians.index'))->assertForbidden();
    }

    public function test_cashier_can_create_service_order(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $this->actingAs($cashier);

        $this->post(route('services.store'), [
            'customer_name' => 'Kasir Test',
            'customer_phone' => '081234567899',
            'device_name' => 'Laptop Kasir',
            'issue_description' => 'Tidak menyala',
        ])->assertRedirect();

        $this->assertDatabaseHas('service_orders', ['device_name' => 'Laptop Kasir']);
    }

    public function test_technician_can_access_dashboard_and_service_list(): void
    {
        User::factory()->create(['role' => 'technician']);
        $technician = User::where('role', 'technician')->first();
        $this->actingAs($technician);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('services.index'))->assertOk();
        $this->get(route('spareparts.index'))->assertOk();
    }
}
