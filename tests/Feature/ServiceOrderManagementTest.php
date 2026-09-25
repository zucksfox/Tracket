<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    }

    private function createOrder(array $overrides = []): ServiceOrder
    {
        $customer = Customer::create([
            'name' => 'Pelanggan Uji '.uniqid(),
            'phone' => '0812'.substr((string) rand(10000000, 99999999), 0, 8),
        ]);

        return ServiceOrder::create(array_merge([
            'service_code' => 'SRV-202601-0500',
            'customer_id' => $customer->id,
            'device_name' => 'Laptop Uji',
            'issue_description' => 'Keluhan uji otomatis',
            'status' => 'pending',
            'labor_cost' => 100000,
            'total_cost' => 100000,
        ], $overrides));
    }

    public function test_admin_can_create_service_order_with_new_customer(): void
    {
        $response = $this->post(route('services.store'), [
            'customer_name' => 'Pelanggan Baru',
            'customer_phone' => '081234567890',
            'device_name' => 'Laptop Lenovo ThinkPad',
            'issue_description' => 'Layar biru saat booting',
            'labor_cost' => 200000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['phone' => '081234567890']);
        $this->assertDatabaseHas('service_orders', [
            'device_name' => 'Laptop Lenovo ThinkPad',
            'status' => 'pending',
        ]);
    }

    public function test_service_order_requires_device_name_and_issue(): void
    {
        $response = $this->post(route('services.store'), []);

        $response->assertSessionHasErrors(['device_name', 'issue_description']);
    }

    public function test_checkin_accepts_varied_indonesian_08_numbers_without_operator_prefix_rules(): void
    {
        foreach (['081111111111', '082222222222', '083333333333', '085555555555', '087777777777', '088888888888', '089999999999', '0812345678901'] as $phone) {
            $this->post(route('services.store'), [
                'customer_name' => 'Nomor Valid '.$phone,
                'customer_phone' => $phone,
                'device_name' => 'HP '.$phone,
                'issue_description' => 'Tes nomor Indonesia',
            ])->assertRedirect();

            $this->assertDatabaseHas('customers', ['phone' => $phone]);
        }
    }

    public function test_checkin_rejects_international_and_formatted_phone_numbers(): void
    {
        foreach (['6281234567890', '+6281234567890', '0812-3456-7890', '0812 3456 7890', '71234567890'] as $phone) {
            $response = $this->post(route('services.store'), [
                'customer_name' => 'Format Salah '.$phone,
                'customer_phone' => $phone,
                'device_name' => 'HP Format '.md5($phone),
                'issue_description' => 'Tes format nomor',
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('error');
        }
    }

    public function test_checkin_rejects_62_and_formatted_phone_numbers(): void
    {
        $response = $this->post(route('services.store'), [
            'customer_name' => 'Format 62',
            'customer_phone' => '6281234567890',
            'device_name' => 'HP Xiaomi',
            'issue_description' => 'LCD pecah',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('customers', ['phone' => '081234567890']);
    }

    public function test_checkin_rejects_invalid_phone_format(): void
    {
        $response = $this->post(route('services.store'), [
            'customer_name' => 'Salah Format',
            'customer_phone' => '12345',
            'device_name' => 'HP Nokia',
            'issue_description' => 'Tidak menyala',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('service_orders', ['device_name' => 'HP Nokia']);
    }

    public function test_checkin_reuses_existing_customer_with_same_phone(): void
    {
        $existing = Customer::create(['name' => 'Sudah Terdaftar', 'phone' => '087812345678']);

        $this->post(route('services.store'), [
            'customer_name' => 'Ketik Beda',
            'customer_phone' => '087812345678',
            'device_name' => 'Laptop Dell',
            'issue_description' => 'Keyboard mati',
        ]);

        $order = ServiceOrder::where('device_name', 'Laptop Dell')->first();
        $this->assertEquals($existing->id, $order->customer_id);
        $this->assertEquals('Sudah Terdaftar', $order->customer->name);
    }

    public function test_technician_can_update_status_forward(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $this->actingAs($technician);

        $order = $this->createOrder();

        $response = $this->post(route('services.update-status', $order), [
            'status' => 'diagnosing',
        ]);

        $response->assertRedirect();
        $this->assertEquals('diagnosing', $order->fresh()->status);
    }

    public function test_update_status_rejects_invalid_status_value(): void
    {
        $order = $this->createOrder();

        $response = $this->post(route('services.update-status', $order), [
            'status' => 'status-ngawur',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_add_part_deducts_stock_and_updates_total(): void
    {
        $part = Sparepart::create([
            'part_code' => 'SP-100',
            'name' => 'RAM 8GB DDR4',
            'category' => 'Memory',
            'stock' => 10,
            'buy_price' => 300000,
            'sell_price' => 400000,
        ]);

        $order = $this->createOrder();

        $response = $this->post(route('services.add-part', $order), [
            'sparepart_id' => $part->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();

        $this->assertEquals(8, $part->fresh()->stock);
        $orderPart = $order->fresh()->orderParts()->first();
        $this->assertEquals(2, $orderPart->quantity);
        $this->assertEquals(800000.0, (float) $orderPart->subtotal);
        $this->assertEquals(900000.0, (float) $order->fresh()->total_cost);
    }

    public function test_add_part_rejects_quantity_more_than_available_stock(): void
    {
        $part = Sparepart::create([
            'part_code' => 'SP-101',
            'name' => 'Baterai Laptop',
            'category' => 'Power',
            'stock' => 1,
            'buy_price' => 250000,
            'sell_price' => 350000,
        ]);

        $order = $this->createOrder();

        $response = $this->post(route('services.add-part', $order), [
            'sparepart_id' => $part->id,
            'quantity' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(1, $part->fresh()->stock);
        $this->assertCount(0, $order->fresh()->orderParts);
    }

    public function test_remove_part_restores_stock_and_recalculates_total(): void
    {
        $part = Sparepart::create([
            'part_code' => 'SP-102',
            'name' => 'Keyboard Laptop',
            'category' => 'Input',
            'stock' => 7,
            'buy_price' => 150000,
            'sell_price' => 200000,
        ]);

        $order = $this->createOrder(['labor_cost' => 50000, 'total_cost' => 50000]);

        $this->post(route('services.add-part', $order), [
            'sparepart_id' => $part->id,
            'quantity' => 3,
        ]);

        $this->assertEquals(4, $part->fresh()->stock);

        $orderPart = $order->fresh()->orderParts()->first();
        $response = $this->delete(route('services.remove-part', [$order, $orderPart]));

        $response->assertRedirect();
        $this->assertEquals(7, $part->fresh()->stock);
        $this->assertEquals(50000.0, (float) $order->fresh()->total_cost);
        $this->assertDatabaseMissing('service_order_parts', ['id' => $orderPart->id]);
    }

    public function test_cashier_cannot_use_repair_flow_or_manage_spareparts(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $this->actingAs($cashier);
        $order = $this->createOrder();

        $this->post(route('services.update-status', $order), ['status' => 'diagnosing'])->assertForbidden();
        $this->post(route('services.add-part', $order), ['sparepart_id' => 999999, 'quantity' => 1])->assertForbidden();
        $this->post(route('services.cancel', $order))->assertForbidden();
        $this->get(route('spareparts.create'))->assertForbidden();
    }

    public function test_checkout_requires_paid_payment_and_cash_payment_marks_order_paid(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $order = $this->createOrder(['status' => 'ready']);
        $this->actingAs($cashier);

        $this->post(route('services.checkout', $order), [
            'warranty_days' => 30,
            'payment_method' => 'cash',
        ])->assertRedirect();

        $fresh = $order->fresh();
        $this->assertSame('completed', $fresh->status);
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('cash', $fresh->payment_method);
        $this->assertNotNull($fresh->paid_at);
    }

    public function test_qr_checkout_requires_manual_confirmation_before_completion(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $order = $this->createOrder(['status' => 'ready']);
        $this->actingAs($cashier);

        $this->post(route('services.checkout', $order), [
            'warranty_days' => 30,
            'payment_method' => 'qr',
            'confirm_payment' => '0',
        ])->assertSessionHas('error');

        $this->assertSame('ready', $order->fresh()->status);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    public function test_checkout_completes_order_and_activates_warranty(): void
    {
        $order = $this->createOrder(['status' => 'ready']);

        $response = $this->post(route('services.checkout', $order), [
            'warranty_days' => 30,
            'payment_method' => 'cash',
            'technician_notes' => 'Sudah diganti thermal paste.',
        ]);

        $response->assertRedirect();

        $fresh = $order->fresh();
        $this->assertEquals('completed', $fresh->status);
        $this->assertEquals(30, $fresh->warranty_days);
        $this->assertNotNull($fresh->warranty_expires_at);
        $this->assertTrue($fresh->warranty_info['is_active']);
    }

    public function test_checkout_requires_warranty_days(): void
    {
        $order = $this->createOrder(['status' => 'ready']);

        $response = $this->post(route('services.checkout', $order), []);

        $response->assertSessionHasErrors('warranty_days');
    }

    public function test_cancel_order_rolls_back_attached_parts_stock(): void
    {
        $part = Sparepart::create([
            'part_code' => 'SP-103',
            'name' => 'Charger 65W',
            'category' => 'Power',
            'stock' => 5,
            'buy_price' => 180000,
            'sell_price' => 250000,
        ]);

        $order = $this->createOrder();

        $this->post(route('services.add-part', $order), [
            'sparepart_id' => $part->id,
            'quantity' => 2,
        ]);

        $this->assertEquals(3, $part->fresh()->stock);

        $response = $this->post(route('services.cancel', $order));

        $response->assertRedirect();
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(5, $part->fresh()->stock);
    }

    public function test_service_list_supports_status_filter_and_search(): void
    {
        $this->createOrder(['service_code' => 'SRV-202601-0601', 'device_name' => 'Monitor LG']);
        $this->createOrder(['service_code' => 'SRV-202601-0602', 'device_name' => 'PC Rakitan', 'status' => 'completed']);

        $this->get(route('services.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('PC Rakitan')
            ->assertDontSee('Monitor LG');

        $this->get(route('services.index', ['search' => 'Monitor']))
            ->assertOk()
            ->assertSee('Monitor LG');
    }

    public function test_technician_cannot_checkout_or_cancel_service(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $this->actingAs($technician);
        $order = $this->createOrder(['status' => 'ready']);

        $this->post(route('services.checkout', $order), ['warranty_days' => 30])->assertForbidden();
        $this->post(route('services.update-status', $order), ['status' => 'completed'])->assertForbidden();
        $this->post(route('services.update-status', $order), ['status' => 'cancelled'])->assertForbidden();
        $this->assertEquals('ready', $order->fresh()->status);
    }

    public function test_terminal_orders_and_illegal_transitions_are_rejected(): void
    {
        $order = $this->createOrder();
        foreach (['ready', 'completed', 'cancelled'] as $status) {
            $this->post(route('services.update-status', $order), ['status' => $status])->assertStatus(422);
            $this->assertSame('pending', $order->fresh()->status);
        }
        $this->post(route('services.checkout', $order), ['warranty_days' => 30, 'payment_method' => 'cash'])->assertStatus(422);
        $order->update(['status' => 'completed']);
        $this->post(route('services.update-status', $order), ['status' => 'completed', 'labor_cost' => 1])->assertStatus(422);
        $this->post(route('services.cancel', $order))->assertStatus(422);
    }

    public function test_cancel_twice_never_returns_stock_twice_and_terminal_parts_are_immutable(): void
    {
        $part = Sparepart::create(['part_code' => 'SAFE', 'name' => 'Part', 'category' => 'Test', 'stock' => 5, 'buy_price' => 1, 'sell_price' => 2]);
        $order = $this->createOrder();
        $this->post(route('services.add-part', $order), ['sparepart_id' => $part->id, 'quantity' => 2]);
        $item = $order->orderParts()->first();
        $this->post(route('services.cancel', $order))->assertRedirect();
        $this->post(route('services.cancel', $order))->assertStatus(422);
        $this->delete(route('services.remove-part', [$order, $item]))->assertStatus(422);
        $this->post(route('services.add-part', $order), ['sparepart_id' => $part->id, 'quantity' => 1])->assertStatus(422);
        $this->assertSame(5, $part->fresh()->stock);
    }

    public function test_remove_part_must_belong_to_the_order(): void
    {
        $part = Sparepart::create(['part_code' => 'PARENT', 'name' => 'Part', 'category' => 'Test', 'stock' => 5, 'buy_price' => 1, 'sell_price' => 2]);
        $order = $this->createOrder();
        $other = $this->createOrder(['service_code' => 'SRV-202601-0501']);
        $this->post(route('services.add-part', $order), ['sparepart_id' => $part->id, 'quantity' => 2]);
        $this->delete(route('services.remove-part', [$other, $order->orderParts()->first()]))->assertNotFound();
        $this->assertSame(3, $part->fresh()->stock);
    }

    public function test_qr_confirmed_checkout_is_atomic_and_cannot_be_repeated(): void
    {
        $order = $this->createOrder(['status' => 'ready']);
        $this->post(route('services.checkout', $order), ['warranty_days' => 60, 'payment_method' => 'qr', 'confirm_payment' => 1])->assertRedirect();
        $paid = $order->fresh()->paid_at;
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->post(route('services.checkout', $order), ['warranty_days' => 90, 'payment_method' => 'cash'])->assertStatus(422);
        $this->assertSame(60, $order->fresh()->warranty_days);
        $this->assertEquals($paid, $order->fresh()->paid_at);
    }

    public function test_technician_cannot_check_in_new_service(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $this->actingAs($technician);

        $this->get(route('services.create'))->assertForbidden();
    }
}
