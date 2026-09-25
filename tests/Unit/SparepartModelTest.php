<?php

namespace Tests\Unit;

use App\Models\Sparepart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparepartModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_low_stock_returns_true_when_stock_is_two_or_less(): void
    {
        $low = new Sparepart(['stock' => 2]);
        $critical = new Sparepart(['stock' => 0]);
        $healthy = new Sparepart(['stock' => 10]);

        $this->assertTrue($low->isLowStock());
        $this->assertTrue($critical->isLowStock());
        $this->assertFalse($healthy->isLowStock());
    }

    public function test_sparepart_can_be_created_with_prices(): void
    {
        $part = Sparepart::create([
            'part_code' => 'SP-001',
            'name' => 'SSD SATA 480GB',
            'category' => 'Storage',
            'stock' => 5,
            'buy_price' => 550000,
            'sell_price' => 700000,
        ]);

        $this->assertDatabaseHas('spareparts', [
            'id' => $part->id,
            'part_code' => 'SP-001',
            'sell_price' => 700000,
        ]);
    }
}
