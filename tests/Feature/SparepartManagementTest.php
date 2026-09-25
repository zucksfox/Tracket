<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparepartManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->technician = User::factory()->create(['role' => 'technician']);
    }

    private function createPart(array $overrides = []): Sparepart
    {
        return Sparepart::create(array_merge([
            'part_code' => 'SP-TEST-'.uniqid(),
            'name' => 'Test Part',
            'category' => 'Testing',
            'stock' => 10,
            'buy_price' => 50000,
            'sell_price' => 75000,
        ], $overrides));
    }

    // -- Admin CRUD --

    public function test_admin_can_create_sparepart(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('spareparts.store'), [
            'part_code' => 'SP-ADMIN-001',
            'name' => 'SSD 512GB NVMe',
            'category' => 'Storage',
            'stock' => 15,
            'buy_price' => 400000,
            'sell_price' => 550000,
        ]);

        $response->assertRedirect(route('spareparts.index'));
        $this->assertDatabaseHas('spareparts', [
            'part_code' => 'SP-ADMIN-001',
            'name' => 'SSD 512GB NVMe',
        ]);
    }

    public function test_admin_can_update_sparepart(): void
    {
        $this->actingAs($this->admin);
        $part = $this->createPart(['name' => 'Old Name', 'stock' => 3]);

        $response = $this->put(route('spareparts.update', $part), [
            'part_code' => $part->part_code,
            'name' => 'Updated Part',
            'category' => $part->category,
            'stock' => 20,
            'buy_price' => 80000,
            'sell_price' => 120000,
        ]);

        $response->assertRedirect(route('spareparts.index'));
        $this->assertDatabaseHas('spareparts', ['id' => $part->id, 'name' => 'Updated Part', 'stock' => 20]);
    }

    public function test_admin_cannot_delete_part_used_in_transaction(): void
    {
        $this->actingAs($this->admin);
        $part = $this->createPart();

        // Create a service order that uses this part
        $customer = Customer::create(['name' => 'C', 'phone' => '081111111111']);
        $order = ServiceOrder::create([
            'service_code' => 'SRV-202601-0999',
            'customer_id' => $customer->id,
            'device_name' => 'Device X',
            'issue_description' => 'Issue X',
            'status' => 'pending',
        ]);
        $order->orderParts()->create([
            'sparepart_id' => $part->id,
            'quantity' => 1,
            'unit_price' => 75000,
            'subtotal' => 75000,
        ]);

        $response = $this->delete(route('spareparts.destroy', $part));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('spareparts', ['id' => $part->id]);
    }

    public function test_admin_can_delete_unused_part(): void
    {
        $this->actingAs($this->admin);
        $part = $this->createPart();

        $response = $this->delete(route('spareparts.destroy', $part));

        $response->assertRedirect(route('spareparts.index'));
        $this->assertNotNull($part->fresh()->deleted_at, 'Sparepart should be soft-deleted');
    }

    // -- Technician access --

    public function test_technician_can_view_sparepart_list(): void
    {
        $this->actingAs($this->technician);
        $this->createPart(['name' => 'Technician Visible']);

        $response = $this->get(route('spareparts.index'));

        $response->assertOk();
        $response->assertSee('Technician Visible');
    }

    public function test_technician_cannot_create_sparepart(): void
    {
        $this->actingAs($this->technician);

        $response = $this->post(route('spareparts.store'), [
            'part_code' => 'SP-TECH-001',
            'name' => 'Should Fail',
            'category' => 'X',
            'stock' => 1,
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        $response->assertForbidden();
    }

    // -- Stock filter --

    public function test_critical_filter_shows_only_low_stock_parts(): void
    {
        $this->actingAs($this->admin);
        $this->createPart(['part_code' => 'SP-CRIT', 'name' => 'Critical One', 'stock' => 1]);
        $this->createPart(['part_code' => 'SP-OK', 'name' => 'Healthy One', 'stock' => 100]);

        $response = $this->get(route('spareparts.index', ['filter' => 'critical']));

        $response->assertOk()
            ->assertSee('Critical One')
            ->assertDontSee('Healthy One');
    }

    // -- Validation --

    public function test_sparepart_creation_requires_unique_part_code(): void
    {
        $this->actingAs($this->admin);
        $this->createPart(['part_code' => 'SP-DUP']);

        $response = $this->post(route('spareparts.store'), [
            'part_code' => 'SP-DUP',
            'name' => 'Duplicate',
            'category' => 'X',
            'stock' => 5,
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        $response->assertSessionHasErrors('part_code');
    }

    public function test_sparepart_creation_requires_required_fields(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('spareparts.store'), []);

        $response->assertSessionHasErrors(['part_code', 'name', 'category', 'stock', 'buy_price', 'sell_price']);
    }
}
