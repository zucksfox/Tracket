<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_service_code_starts_at_0001_for_new_month(): void
    {
        $code = ServiceOrder::generateServiceCode();

        $prefix = 'SRV-'.date('Ym').'-';
        $this->assertStringStartsWith($prefix, $code);
        $this->assertStringEndsWith('-0001', $code);
    }

    public function test_generate_service_code_increments_sequentially(): void
    {
        $customer = Customer::create(['name' => 'C1', 'phone' => '081100000001']);

        ServiceOrder::create([
            'service_code' => 'SRV-'.date('Ym').'-0001',
            'customer_id' => $customer->id,
            'device_name' => 'Laptop A',
            'issue_description' => 'Keluhan A',
            'status' => 'pending',
        ]);

        $code = ServiceOrder::generateServiceCode();

        $this->assertEquals('SRV-'.date('Ym').'-0002', $code);
    }

    public function test_recalculate_total_sums_labor_cost_and_parts(): void
    {
        $customer = Customer::create(['name' => 'C', 'phone' => '081100022233']);
        $part1 = Sparepart::create(['part_code' => 'SP-A', 'name' => 'Part A', 'category' => 'X', 'stock' => 5, 'buy_price' => 10000, 'sell_price' => 50000]);
        $part2 = Sparepart::create(['part_code' => 'SP-B', 'name' => 'Part B', 'category' => 'X', 'stock' => 5, 'buy_price' => 10000, 'sell_price' => 75000]);

        $order = ServiceOrder::create([
            'service_code' => 'SRV-202601-0099',
            'customer_id' => $customer->id,
            'device_name' => 'Laptop B',
            'issue_description' => 'Keluhan B',
            'status' => 'in_progress',
            'labor_cost' => 150000,
            'total_cost' => 150000,
        ]);

        // Simulasikan dua part terpasang (tanpa lewat controller)
        $order->orderParts()->create([
            'sparepart_id' => $part1->id,
            'quantity' => 2,
            'unit_price' => 50000,
            'subtotal' => 100000,
        ]);
        $order->orderParts()->create([
            'sparepart_id' => $part2->id,
            'quantity' => 1,
            'unit_price' => 75000,
            'subtotal' => 75000,
        ]);

        $order->recalculateTotal();

        $this->assertEquals(325000.0, (float) $order->fresh()->total_cost);
    }

    public function test_status_meta_returns_correct_label_for_each_status(): void
    {
        $labels = [
            'pending' => 'Menunggu Diagnosa',
            'diagnosing' => 'Sedang Diagnosa',
            'in_progress' => 'Sedang Dikerjakan',
            'ready' => 'Siap Diambil',
            'completed' => 'Selesai & Diambil',
            'cancelled' => 'Dibatalkan',
        ];

        foreach ($labels as $status => $expectedLabel) {
            $order = new ServiceOrder(['status' => $status]);
            $this->assertEquals($expectedLabel, $order->status_meta['label'], "Status {$status} salah label");
        }
    }

    public function test_next_action_returns_null_for_completed_order(): void
    {
        $order = new ServiceOrder(['status' => 'completed']);

        $this->assertNull($order->next_action);
    }

    public function test_next_action_for_pending_points_to_diagnosing(): void
    {
        $order = new ServiceOrder(['status' => 'pending']);

        $this->assertEquals('diagnosing', $order->next_action['target_status']);
    }
}
