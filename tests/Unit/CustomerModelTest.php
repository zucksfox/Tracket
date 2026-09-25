<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\ServiceOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_has_many_service_orders(): void
    {
        $customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Melati No. 10',
        ]);

        ServiceOrder::create([
            'service_code' => 'SRV-202601-0001',
            'customer_id' => $customer->id,
            'device_name' => 'Laptop ASUS ROG',
            'issue_description' => 'Tidak bisa booting',
            'status' => 'pending',
            'labor_cost' => 0,
            'total_cost' => 0,
        ]);

        ServiceOrder::create([
            'service_code' => 'SRV-202601-0002',
            'customer_id' => $customer->id,
            'device_name' => 'Printer Epson L3210',
            'issue_description' => 'Head macet',
            'status' => 'pending',
            'labor_cost' => 0,
            'total_cost' => 0,
        ]);

        $this->assertCount(2, $customer->serviceOrders);
        $this->assertInstanceOf(ServiceOrder::class, $customer->serviceOrders->first());
    }

    public function test_customer_can_be_created_with_minimal_data(): void
    {
        $customer = Customer::create([
            'name' => 'Siti Aminah',
            'phone' => '089876543210',
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Siti Aminah',
            'phone' => '089876543210',
        ]);
    }
}
