<?php

namespace Tests\Feature;

use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparepartNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_read_critical_sparepart_snapshot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sparepart::create(['part_code' => 'LOW-1', 'name' => 'Baterai Demo', 'category' => 'Baterai', 'stock' => 2, 'buy_price' => 1, 'sell_price' => 2]);
        Sparepart::create(['part_code' => 'OUT-1', 'name' => 'Kabel Demo', 'category' => 'Kabel', 'stock' => 0, 'buy_price' => 1, 'sell_price' => 2]);
        Sparepart::create(['part_code' => 'OK-1', 'name' => 'Layar Demo', 'category' => 'Layar', 'stock' => 10, 'buy_price' => 1, 'sell_price' => 2]);

        $this->actingAs($admin)->getJson(route('stock-notifications.index'))
            ->assertOk()
            ->assertJsonPath('parts.0.name', 'Kabel Demo')
            ->assertJsonPath('parts.0.stock', 0)
            ->assertJsonPath('parts.1.name', 'Baterai Demo')
            ->assertJsonCount(2, 'parts');
    }

    public function test_non_admin_cannot_read_stock_notifications(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);

        $this->actingAs($technician)->getJson(route('stock-notifications.index'))->assertForbidden();

        $this->app['auth']->forgetGuards();
        $this->flushSession();
        $this->get(route('stock-notifications.index'))->assertRedirect(route('login'));
    }
}
