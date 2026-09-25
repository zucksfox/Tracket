<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardDesignTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Artisan::call('migrate', ['--force' => true]);
        $this->travelTo(now()->setDate(2026, 9, 23)->startOfDay());
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    private function order(?User $technician = null, string $status = 'completed', string $date = '2026-09-15'): ServiceOrder
    {
        $customer = Customer::create(['name' => 'Pelanggan Uji', 'phone' => '08'.random_int(100000000, 999999999)]);
        $order = ServiceOrder::create(['service_code' => 'TEST-'.$customer->id, 'customer_id' => $customer->id,
            'technician_id' => $technician?->id, 'device_name' => 'Laptop', 'issue_description' => 'Keluhan bukan kategori',
            'status' => $status, 'labor_cost' => 100000, 'total_cost' => 150000,
            'warranty_expires_at' => '2026-10-15']);
        $order->timestamps = false;
        $order->created_at = $date.' 12:00:00';
        $order->updated_at = $date.' 12:00:00';
        $order->save();
        $part = Sparepart::create(['part_code' => 'P-'.$customer->id, 'name' => 'Part Uji', 'category' => 'Komponen', 'stock' => 2, 'buy_price' => 20000, 'sell_price' => 99000]);
        $order->orderParts()->create(['sparepart_id' => $part->id, 'quantity' => 1, 'unit_price' => 50000, 'subtotal' => 50000]);

        return $order;
    }

    public function test_reports_are_admin_only_and_use_historical_part_subtotals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tech = User::factory()->create(['role' => 'technician']);
        $included = $this->order($tech);
        $excluded = $this->order(null, 'cancelled');
        $this->order(null, 'completed', '2026-08-15');
        $this->actingAs($admin)->get('/reports?start=2026-09-15&end=2026-09-15')->assertOk()
            ->assertViewHas('laborTotal', 100000)->assertViewHas('partsTotal', 50000)
            ->assertViewHas('revenueTotal', 150000)->assertSee($included->service_code)
            ->assertDontSee($excluded->service_code)->assertSee('Riwayat Transaksi');
        $this->actingAs($tech)->get('/reports')->assertForbidden();
    }

    public function test_technician_dashboard_contains_only_assigned_services_and_no_finances(): void
    {
        $tech = User::factory()->create(['role' => 'technician']);
        $other = User::factory()->create(['role' => 'technician']);
        $own = $this->order($tech);
        $hidden = $this->order($other);
        $this->order(null, 'pending');
        $this->actingAs($tech)->get('/dashboard')->assertOk()->assertViewHas('activeCount', 0)
            ->assertViewHas('completedThisMonth', 1)->assertViewHas('chart', [])
            ->assertViewHas('revenueThisMonth', null)->assertSee($own->service_code)
            ->assertDontSee($hidden->service_code)->assertDontSee('Pendapatan Bulan Ini')
            ->assertViewHas('breakdown', fn ($items) => array_sum(array_column($items, 'count')) === 1);
    }

    public function test_date_filters_reject_invalid_reversed_and_unbounded_ranges(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (['/dashboard', '/reports'] as $path) {
            $this->actingAs($admin)->getJson($path.'?start=invalid')->assertUnprocessable();
            $this->getJson($path.'?start=2026-09-23&end=2026-09-01')->assertUnprocessable();
            $this->getJson($path.'?start=2020-01-01&end=2026-09-23')->assertUnprocessable();
        }
    }

    public function test_empty_dashboard_and_report_render_without_invented_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('Belum ada pendapatan')
            ->assertSee('Belum ada garansi aktif')->assertViewHas('activeCount', 0);
        $this->get('/reports')->assertOk()->assertSee('Belum ada transaksi selesai');
    }

    public function test_admin_dashboard_reports_real_totals_without_a_main_table(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->order();
        $this->order(null, 'cancelled');
        $this->order(null, 'ready');
        $this->order(null, 'completed', '2026-08-15');
        $this->actingAs($admin)->get('/dashboard')->assertOk()
            ->assertViewHas('activeCount', 1)->assertViewHas('completedThisMonth', 1)
            ->assertViewHas('revenueThisMonth', 150000)->assertViewHas('criticalPartsCount', 4)
            ->assertViewHas('chart', fn ($chart) => array_sum(array_column($chart, 'labor')) == 100000 && array_sum(array_column($chart, 'parts')) == 50000)
            ->assertSee('Biaya Jasa')->assertSee('Pendapatan Sparepart')->assertSee('Status servis')
            ->assertDontSee('<table', false)->assertDontSee('Keluhan bukan kategori');
    }
}
