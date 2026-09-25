<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        return $admin;
    }

    public function test_admin_can_view_customer_list(): void
    {
        $this->actingAsAdmin();

        Customer::create(['name' => 'Andi Wijaya', 'phone' => '081111111111']);

        $response = $this->get(route('customers.index'));

        $response->assertOk();
        $response->assertSee('Andi Wijaya');
    }

    public function test_admin_can_create_new_customer(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('customers.store'), [
            'name' => 'Rina Kusuma',
            'phone' => '082222222222',
            'address' => 'Jl. Kenanga No. 5',
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Rina Kusuma',
            'phone' => '082222222222',
        ]);
    }

    public function test_customer_creation_requires_unique_phone(): void
    {
        $this->actingAsAdmin();

        Customer::create(['name' => 'Pertama', 'phone' => '083333333333']);

        $response = $this->post(route('customers.store'), [
            'name' => 'Kedua Duplikat',
            'phone' => '083333333333',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseMissing('customers', ['name' => 'Kedua Duplikat']);
    }

    public function test_customer_creation_requires_name_and_phone(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('customers.store'), []);

        $response->assertSessionHasErrors(['name', 'phone']);
    }

    public function test_admin_can_update_customer(): void
    {
        $this->actingAsAdmin();

        $customer = Customer::create(['name' => 'Nama Lama', 'phone' => '084444444444']);

        $response = $this->put(route('customers.update', $customer), [
            'name' => 'Nama Baru',
            'phone' => '084444444444',
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Nama Baru']);
    }

    public function test_customer_with_service_history_cannot_be_deleted(): void
    {
        $this->actingAsAdmin();

        $customer = Customer::create(['name' => 'Pelanggan Setia', 'phone' => '085555555555']);
        ServiceOrder::create([
            'service_code' => 'SRV-202601-0100',
            'customer_id' => $customer->id,
            'device_name' => 'Laptop C',
            'issue_description' => 'Keluhan C',
            'status' => 'pending',
        ]);

        $response = $this->delete(route('customers.destroy', $customer));

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_customer_without_history_can_be_deleted(): void
    {
        $this->actingAsAdmin();

        $customer = Customer::create(['name' => 'Sekali Lewat', 'phone' => '086666666666']);

        $response = $this->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $this->assertNotNull($customer->fresh()->deleted_at, 'Customer should be soft-deleted');
    }

    public function test_customer_lookup_api_returns_matching_customer(): void
    {
        $this->actingAsAdmin();

        Customer::create(['name' => 'Budi Lookup', 'phone' => '087777777777']);

        $response = $this->getJson(route('customers.lookup', ['phone' => '087777777777']));

        $response->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('customer.name', 'Budi Lookup');
    }

    public function test_customer_lookup_api_returns_not_found_for_unknown_phone(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson(route('customers.lookup', ['phone' => '089999999999']));

        $response->assertOk()->assertJsonPath('found', false);
    }

    public function test_guest_cannot_access_customer_pages(): void
    {
        $this->get(route('customers.index'))->assertRedirect(route('login'));
    }

    public function test_technician_cannot_access_customer_crud(): void
    {
        User::factory()->create(['role' => 'technician']);
        $technician = User::where('role', 'technician')->first();
        $this->actingAs($technician);

        $this->get(route('customers.index'))->assertForbidden();
    }
}
