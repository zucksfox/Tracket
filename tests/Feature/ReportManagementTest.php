<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportManagementTest extends TestCase
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

    private function createCompletedOrder(float $laborCost, float $partsSubtotal): ServiceOrder
    {
        $customer = Customer::create(['name' => 'Report Customer', 'phone' => '08'.random_int(100000000, 999999999)]);
        $part = Sparepart::create([
            'part_code' => 'RPT-'.uniqid(),
            'name' => 'Report Part',
            'category' => 'Reporting',
            'stock' => 100,
            'buy_price' => 10000,
            'sell_price' => $partsSubtotal,
        ]);

        $order = ServiceOrder::create([
            'service_code' => 'SRV-202601-'.str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'device_name' => 'Report Device',
            'issue_description' => 'Test report data',
            'status' => 'completed',
            'labor_cost' => $laborCost,
            'total_cost' => $laborCost + $partsSubtotal,
        ]);

        $order->orderParts()->create([
            'sparepart_id' => $part->id,
            'quantity' => 1,
            'unit_price' => $partsSubtotal,
            'subtotal' => $partsSubtotal,
        ]);

        return $order;
    }

    public function test_admin_can_view_reports_page(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Riwayat Transaksi');
    }

    public function test_technician_cannot_access_reports(): void
    {
        $this->actingAs($this->technician);

        $this->get(route('reports.index'))->assertForbidden();
    }

    public function test_reports_show_correct_totals(): void
    {
        $this->actingAs($this->admin);

        $this->createCompletedOrder(100000, 50000);
        $this->createCompletedOrder(200000, 75000);

        $response = $this->get(route('reports.index'));

        $response->assertOk()
            ->assertViewHas('laborTotal', 300000.0)
            ->assertViewHas('partsTotal', 125000.0)
            ->assertViewHas('revenueTotal', 425000.0)
            ->assertViewHas('transactionCount', 2);
    }

    public function test_reports_exclude_non_completed_orders(): void
    {
        $this->actingAs($this->admin);

        // Create a cancelled order that should be excluded
        $customer = Customer::create(['name' => 'Excluded', 'phone' => '089999999999']);
        ServiceOrder::create([
            'service_code' => 'SRV-202601-0998',
            'customer_id' => $customer->id,
            'device_name' => 'Cancelled Device',
            'issue_description' => 'Should not appear',
            'status' => 'cancelled',
            'labor_cost' => 999999,
            'total_cost' => 999999,
        ]);

        $this->createCompletedOrder(50000, 25000);

        $response = $this->get(route('reports.index'));

        $response->assertOk()
            ->assertViewHas('transactionCount', 1)
            ->assertViewHas('revenueTotal', 75000.0);
    }

    public function test_reports_show_transaction_list(): void
    {
        $this->actingAs($this->admin);
        $order = $this->createCompletedOrder(150000, 50000);

        $response = $this->get(route('reports.index'));

        $response->assertOk()
            ->assertSee($order->service_code)
            ->assertSee('Report Device');
    }

    public function test_reports_handle_date_filter(): void
    {
        $this->actingAs($this->admin);
        $this->createCompletedOrder(100000, 50000);

        $response = $this->get(route('reports.index', [
            'start' => now()->subDays(7)->toDateString(),
            'end' => now()->toDateString(),
        ]));

        $response->assertOk()
            ->assertViewHas('transactionCount', 1);
    }

    public function test_reports_filter_status_without_counting_unfinished_bills_as_revenue(): void
    {
        $this->actingAs($this->admin);
        $order = $this->createCompletedOrder(100000, 50000);
        $order->update(['status' => 'ready']);
        $this->get(route('reports.index', ['status' => 'ready']))->assertOk()
            ->assertSee($order->service_code)->assertViewHas('revenueTotal', 0.0);
        $this->getJson(route('reports.index', ['status' => 'invalid']))->assertUnprocessable();
    }

    public function test_reports_reject_invalid_date_range(): void
    {
        $this->actingAs($this->admin);

        $this->getJson(route('reports.index', ['start' => '2026-09-23', 'end' => '2026-09-01']))
            ->assertUnprocessable();
    }
}
